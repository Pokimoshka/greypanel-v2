<?php
/**
 * Установщик GreyPanel v2
 * После установки папка install будет удалена.
 */

define('ROOT_DIR', dirname(__DIR__, 2));
define('ENV_FILE', ROOT_DIR . '/.env');

// Проверка, не установлена ли уже система
if (file_exists(ENV_FILE) && filesize(ENV_FILE) > 0) {
    $envContent = file_get_contents(ENV_FILE);
    if (strpos($envContent, 'APP_INSTALLED=true') !== false) {
        header('Location: /');
        exit;
    }
}

session_start();

// CSRF токен
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Временный ключ для шифрования данных между шагами
if (empty($_SESSION['state_key'])) {
    $_SESSION['state_key'] = bin2hex(random_bytes(32));
}

/**
 * Шифрование массива данных
 */
function encryptState(array $data): string
{
    $key = hex2bin($_SESSION['state_key']);
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt(json_encode($data), 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Расшифровка строки состояния
 */
function decryptState(string $encoded): ?array
{
    $key = hex2bin($_SESSION['state_key']);
    $data = base64_decode($encoded);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    if ($decrypted === false) return null;
    return json_decode($decrypted, true);
}

/**
 * Проверка окружения
 */
function checkRequirements(): array
{
    $results = [];

    $phpVersion = phpversion();
    $phpOk = version_compare($phpVersion, '8.1', '>=');
    $results[] = [
        'name' => "PHP версия (требуется ≥ 8.1, текущая $phpVersion)",
        'status' => $phpOk,
    ];

    $extensions = ['pdo_mysql', 'gd', 'openssl', 'ftp', 'json', 'session', 'mbstring', 'fileinfo'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $results[] = [
            'name' => "Расширение PHP «{$ext}»",
            'status' => $loaded,
        ];
    }

    $writableDirs = [
        'var/cache' => 'Кэш',
        'var/logs' => 'Логи',
        'var/lock' => 'Блокировки',
        'public/upload' => 'Загрузки',
    ];
    foreach ($writableDirs as $dir => $label) {
        $fullPath = ROOT_DIR . '/' . $dir;
        if (!is_dir($fullPath)) {
            @mkdir($fullPath, 0777, true);
        }
        $writable = is_writable($fullPath);
        $results[] = [
            'name' => "Папка «{$label}» доступна для записи",
            'status' => $writable,
        ];
    }

    $rootWritable = is_writable(ROOT_DIR);
    $results[] = [
        'name' => 'Корневая папка доступна для записи (создание .env)',
        'status' => $rootWritable,
    ];

    return [
        'success' => $phpOk && !in_array(false, array_column($results, 'status'), true),
        'results' => $results,
    ];
}

/**
 * Выполнение установки (последний шаг)
 */
function runInstallation(array $data)
{
    $db_host = $data['db_host'];
    $db_name = $data['db_name'];
    $db_user = $data['db_user'];
    $db_pass = $data['db_pass'];
    $db_prefix = $data['db_prefix'];

    try {
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Проверка существующих таблиц
        $stmt = $pdo->query("SHOW TABLES LIKE '{$db_prefix}%'");
        if ($stmt->rowCount() > 0) {
            return "База данных уже содержит таблицы с префиксом '{$db_prefix}'. Удалите их или выберите другой префикс.";
        }

        $sqlFile = __DIR__ . '/migrations.sql';
        if (!file_exists($sqlFile)) {
            return "Файл миграций не найден: $sqlFile";
        }
        $sql = file_get_contents($sqlFile);
        $sql = str_replace('{prefix}', $db_prefix, $sql);
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        try {
            foreach ($queries as $query) {
                if (empty($query)) continue;
                $pdo->exec($query);
            }

            $now = time();
            // Группы
            $stmt = $pdo->prepare("INSERT INTO `{$db_prefix}user_groups` (`name`, `flags`, `is_default`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Пользователь', '', 1, $now, $now]);
            $stmt->execute(['Создатель', 'abcdefghijklmnopqrstuvwxyz', 0, $now, $now]);
            $rootGroupId = $pdo->lastInsertId();

            // Админ
            $hashedPassword = password_hash($data['admin_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO `{$db_prefix}users` 
                (`username`, `password_hash`, `email`, `group_id`, `money`, `reg_data`, `reg_ip`, `created_at`, `updated_at`) 
                VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['admin_username'],
                $hashedPassword,
                $data['admin_email'],
                $rootGroupId,
                $now,
                $_SERVER['REMOTE_ADDR'],
                $now,
                $now
            ]);

            // Настройки по умолчанию
            $defaultSettings = [
                'site_name' => 'GreyPanel',
                'active_theme' => 'default',
                'app_debug' => '0',
                'session_lifetime' => '7200',
                'session_name' => 'greysession',
                'site_protocol' => 'auto',
                'site_url_manual' => '',
                'default_app' => 'forum',
                'theme' => 'default',
                'cron_key' => bin2hex(random_bytes(16)),
                'chat_enabled' => '1',
                'amxbans_active' => '0',
            ];
            $settingStmt = $pdo->prepare("INSERT INTO `{$db_prefix}settings` (`key`, `value`) VALUES (?, ?)");
            foreach ($defaultSettings as $key => $value) {
                $settingStmt->execute([$key, $value]);
            }

            // Меню
            $menuItems = [
                ['num' => 1, 'text' => 'Форум', 'desc' => 'Форум', 'module' => 'forum', 'icon' => 'fa fa-comments', 'active' => 1, 'ses' => 0],
                ['num' => 2, 'text' => 'Привилегии', 'desc' => 'Покупка привилегий', 'module' => 'vip', 'icon' => 'fa fa-bolt', 'active' => 1, 'ses' => 1],
                ['num' => 3, 'text' => 'Бан лист', 'desc' => 'Список банов', 'module' => 'bans', 'icon' => 'fa fa-gavel', 'active' => 1, 'ses' => 0],
                ['num' => 4, 'text' => 'Статистика', 'desc' => 'CS:GO Статистика', 'module' => 'stats', 'icon' => 'fa fa-bar-chart', 'active' => 1, 'ses' => 0],
                ['num' => 5, 'text' => 'Вход', 'desc' => 'Вход/Регистрация', 'module' => 'auth', 'icon' => 'fa fa-key', 'active' => 1, 'ses' => 2],
            ];
            $menuStmt = $pdo->prepare("INSERT INTO `{$db_prefix}menu` (`num`, `text`, `description`, `module`, `icon`, `active`, `ses`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($menuItems as $item) {
                $menuStmt->execute([$item['num'], $item['text'], $item['desc'], $item['module'], $item['icon'], $item['active'], $item['ses']]);
            }
        } catch (\Exception $e) {
            return "Ошибка выполнения миграций: " . $e->getMessage();
        }

        // .env
        $envContent = "# GreyPanel v2 Environment\n";
        $envContent .= "APP_ENV=prod\n";
        $envContent .= "DB_HOST={$db_host}\n";
        $envContent .= "DB_NAME={$db_name}\n";
        $envContent .= "DB_USER={$db_user}\n";
        $envContent .= "DB_PASS={$db_pass}\n";
        $envContent .= "DB_PREFIX={$db_prefix}\n";
        $envContent .= "DB_CHARSET=utf8mb4\n";
        $envContent .= "SECRET_KEY=" . bin2hex(random_bytes(32)) . "\n";
        $envContent .= "CRON_KEY={$defaultSettings['cron_key']}\n";
        $envContent .= "ENCRYPTION_KEY=" . bin2hex(random_bytes(32)) . "\n";
        $envContent .= "APP_INSTALLED=true\n";

        if (file_put_contents(ENV_FILE, $envContent) === false) {
            return "Не удалось создать файл .env";
        }
        chmod(ENV_FILE, 0600);

        session_destroy();

        // Удаление папки install
        $installDir = __DIR__;
        if (!deleteDirectory($installDir)) {
            $disabledDir = $installDir . '_disabled_' . time();
            if (!rename($installDir, $disabledDir)) {
                return "Не удалось удалить или переместить папку install. Удалите её вручную.";
            }
        }

        return ['success' => true];
    } catch (Exception $e) {
        return "Ошибка при установке: " . $e->getMessage();
    }
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

// Обработка AJAX запросов
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    // Проверка CSRF
    if (empty($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
        exit;
    }

    switch ($action) {
        case 'check_requirements':
            echo json_encode(checkRequirements());
            break;

        case 'test_db_connection':
            $db_host = trim($input['db_host'] ?? 'localhost');
            $db_name = trim($input['db_name'] ?? '');
            $db_user = trim($input['db_user'] ?? '');
            $db_pass = $input['db_pass'] ?? '';
            $db_prefix = trim($input['db_prefix'] ?? 'grey_');

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $db_prefix)) {
                echo json_encode(['success' => false, 'error' => "Префикс может содержать только латинские буквы, цифры и знак подчёркивания."]);
                exit;
            }

            try {
                $pdo = new PDO(
                    "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
                    $db_user,
                    $db_pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $state = encryptState([
                    'db_host' => $db_host,
                    'db_name' => $db_name,
                    'db_user' => $db_user,
                    'db_pass' => $db_pass,
                    'db_prefix' => $db_prefix,
                ]);
                echo json_encode(['success' => true, 'state' => $state]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => "Не удалось подключиться к базе данных: " . $e->getMessage()]);
            }
            break;

        case 'save_admin':
            $state = $input['state'] ?? '';
            $data = decryptState($state);
            if ($data === null) {
                echo json_encode(['success' => false, 'error' => 'Ошибка состояния. Начните заново.']);
                exit;
            }

            $admin_username = trim($input['admin_username'] ?? '');
            $admin_email = trim($input['admin_email'] ?? '');
            $admin_password = $input['admin_password'] ?? '';
            $admin_password2 = $input['admin_password2'] ?? '';

            if (empty($admin_username) || empty($admin_email) || empty($admin_password)) {
                echo json_encode(['success' => false, 'error' => 'Заполните все поля администратора']);
                exit;
            }
            if ($admin_password !== $admin_password2) {
                echo json_encode(['success' => false, 'error' => 'Пароли не совпадают']);
                exit;
            }
            if (strlen($admin_password) < 12) {
                echo json_encode(['success' => false, 'error' => 'Пароль должен быть не менее 12 символов']);
                exit;
            }
            if (!preg_match('/[a-z]/', $admin_password) || !preg_match('/[A-Z]/', $admin_password) || !preg_match('/[0-9]/', $admin_password) || !preg_match('/[^a-zA-Z0-9]/', $admin_password)) {
                echo json_encode(['success' => false, 'error' => 'Пароль должен содержать строчные и прописные буквы, цифры и специальные символы']);
                exit;
            }
            if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'error' => 'Некорректный email']);
                exit;
            }

            $newState = encryptState(array_merge($data, [
                'admin_username' => $admin_username,
                'admin_email' => $admin_email,
                'admin_password' => $admin_password,
            ]));
            echo json_encode(['success' => true, 'state' => $newState]);
            break;

        case 'install':
            $state = $input['state'] ?? '';
            $data = decryptState($state);
            if ($data === null) {
                echo json_encode(['success' => false, 'error' => 'Ошибка состояния. Начните заново.']);
                exit;
            }

            $result = runInstallation($data);
            if (isset($result['success']) && $result['success'] === true) {
                echo json_encode(['success' => true, 'redirect' => '/']);
            } else {
                echo json_encode(['success' => false, 'error' => $result]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
    }
    exit;
}

// Первоначальная проверка окружения для шага 1
$requirements = checkRequirements();
?>
<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Установка GreyPanel v2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.0/dist/cdn.min.js"></script>
    <style>
        body { background: #1a1d23; padding-top: 40px; color: #e0e0e0; }
        .installer-container { max-width: 700px; margin: 0 auto; }
        .card { background: #212529; border: 1px solid #2c3035; }
        .card-header { background: linear-gradient(135deg, #2c3035 0%, #212529 100%); border-bottom: 1px solid #2c3035; }
        .table { color: #e0e0e0; }
        .table td, .table th { border-color: #2c3035; }
        .form-control, .form-select { background-color: #2c3035; border-color: #3a3f47; color: #e0e0e0; }
        .form-control:focus, .form-select:focus { background-color: #2c3035; border-color: #0d6efd; color: #e0e0e0; }
        .btn-primary { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); border: none; }
        .btn-secondary { background: #3a3f47; border-color: #4a4f57; }
        .step-enter-active, .step-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
        .step-enter-from, .step-leave-to { opacity: 0; transform: translateY(10px); }
    </style>
</head>
<body>
<div class="container installer-container" x-data="installer">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Установка GreyPanel v2</h3>
        </div>
        <div class="card-body">
            <!-- Ошибки -->
            <div x-show="error" class="alert alert-danger animate__animated animate__shakeX" x-text="error"></div>

            <!-- Шаг 1: Проверка окружения -->
            <div x-show="step === 1" x-transition.opacity>
                <h4 class="mb-3">Шаг 1: Проверка окружения</h4>
                <table class="table table-sm" x-show="requirements">
                    <thead><tr><th>Требование</th><th style="width:100px">Статус</th></tr></thead>
                    <tbody>
                        <template x-for="item in requirements" :key="item.name">
                            <tr>
                                <td x-text="item.name"></td>
                                <td>
                                    <i x-show="item.status" class="fas fa-check-circle text-success"></i>
                                    <i x-show="!item.status" class="fas fa-times-circle text-danger"></i>
                                    <span x-text="item.status ? ' OK' : ' Ошибка'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="allRequirementsOk" class="alert alert-success">Все проверки пройдены! Можно продолжать установку.</div>
                <div x-show="!allRequirementsOk" class="alert alert-warning" x-text="'Некоторые требования не выполнены. Исправьте их и нажмите «Проверить снова».'"></div>
                <button class="btn btn-primary" @click="nextStep(2)" :disabled="!allRequirementsOk">Продолжить</button>
                <button class="btn btn-secondary ms-2" @click="checkRequirements()"><i class="fas fa-redo"></i> Проверить снова</button>
            </div>

            <!-- Шаг 2: Настройка БД -->
            <div x-show="step === 2" x-transition.opacity>
                <h4 class="mb-3">Шаг 2: Настройка базы данных</h4>
                <div x-show="!dbTested">
                    <div class="mb-3">
                        <label class="form-label">Хост MySQL</label>
                        <input type="text" class="form-control" x-model="dbHost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имя базы данных</label>
                        <input type="text" class="form-control" x-model="dbName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пользователь MySQL</label>
                        <input type="text" class="form-control" x-model="dbUser" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль MySQL</label>
                        <input type="password" class="form-control" x-model="dbPass">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Префикс таблиц</label>
                        <input type="text" class="form-control" x-model="dbPrefix">
                    </div>
                    <button class="btn btn-primary" @click="testDB()">Проверить подключение</button>
                </div>
                <div x-show="dbTested && dbConnected">
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Подключение к базе данных успешно установлено!</div>
                    <button class="btn btn-primary" @click="nextStep(3)">Далее</button>
                </div>
                <div x-show="dbTested && !dbConnected">
                    <div class="alert alert-danger" x-text="dbError"></div>
                    <button class="btn btn-primary" @click="dbTested = false">Изменить данные</button>
                </div>
            </div>

            <!-- Шаг 3: Администратор -->
            <div x-show="step === 3" x-transition.opacity>
                <h4 class="mb-3">Шаг 3: Администратор</h4>
                <div class="mb-3">
                    <label class="form-label">Игровой ник администратора</label>
                    <input type="text" class="form-control" x-model="adminUsername" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email администратора</label>
                    <input type="email" class="form-control" x-model="adminEmail" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" class="form-control" x-model="adminPassword" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Повторите пароль</label>
                    <input type="password" class="form-control" x-model="adminPassword2" required>
                </div>
                <button class="btn btn-primary" @click="saveAdmin()">Далее</button>
            </div>

            <!-- Шаг 4: Установка -->
            <div x-show="step === 4" x-transition.opacity>
                <h4 class="mb-3">Шаг 4: Установка</h4>
                <p>Будут созданы таблицы и настроена панель.</p>
                <button class="btn btn-primary" @click="install()">Выполнить установку</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('installer', () => ({
            step: 1,
            error: '',
            // Шаг 1
            requirements: <?= json_encode($requirements['results']) ?>,
            allRequirementsOk: <?= json_encode($requirements['success']) ?>,
            // Шаг 2
            dbHost: 'localhost',
            dbName: '',
            dbUser: '',
            dbPass: '',
            dbPrefix: 'grey_',
            dbTested: false,
            dbConnected: false,
            dbError: '',
            // Шаг 3
            adminUsername: '',
            adminEmail: '',
            adminPassword: '',
            adminPassword2: '',
            // Общее состояние
            state: '',

            async checkRequirements() {
                try {
                    let resp = await fetch('', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                        body: JSON.stringify({action: 'check_requirements', csrf_token: '<?= $_SESSION['csrf_token'] ?>'})
                    });
                    let data = await resp.json();
                    this.requirements = data.results;
                    this.allRequirementsOk = data.success;
                } catch (e) {
                    this.error = 'Ошибка проверки';
                }
            },

            async testDB() {
                this.error = '';
                try {
                    let resp = await fetch('', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                        body: JSON.stringify({
                            action: 'test_db_connection',
                            db_host: this.dbHost,
                            db_name: this.dbName,
                            db_user: this.dbUser,
                            db_pass: this.dbPass,
                            db_prefix: this.dbPrefix,
                            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                        })
                    });
                    let data = await resp.json();
                    if (data.success) {
                        this.dbTested = true;
                        this.dbConnected = true;
                        this.state = data.state;
                    } else {
                        this.dbTested = true;
                        this.dbConnected = false;
                        this.dbError = data.error;
                    }
                } catch (e) {
                    this.error = 'Ошибка соединения';
                }
            },

            async saveAdmin() {
                this.error = '';
                try {
                    let resp = await fetch('', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                        body: JSON.stringify({
                            action: 'save_admin',
                            state: this.state,
                            admin_username: this.adminUsername,
                            admin_email: this.adminEmail,
                            admin_password: this.adminPassword,
                            admin_password2: this.adminPassword2,
                            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                        })
                    });
                    let data = await resp.json();
                    if (data.success) {
                        this.state = data.state;
                        this.step = 4;
                    } else {
                        this.error = data.error;
                    }
                } catch (e) {
                    this.error = 'Ошибка соединения';
                }
            },

            async install() {
                this.error = '';
                try {
                    let resp = await fetch('', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                        body: JSON.stringify({
                            action: 'install',
                            state: this.state,
                            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                        })
                    });
                    let data = await resp.json();
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        this.error = data.error;
                    }
                } catch (e) {
                    this.error = 'Ошибка соединения';
                }
            },

            nextStep(num) {
                this.error = '';
                this.step = num;
            }
        }));
    });
</script>
</body>
</html>