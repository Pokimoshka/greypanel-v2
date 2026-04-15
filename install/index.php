<?php
/**
 * Установщик GreyPanel v2
 * После установки папка install будет удалена.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_DIR', dirname(__DIR__));
define('ENV_FILE', ROOT_DIR . '/.env');
define('INSTALL_DIR', __DIR__);

session_start();

if (file_exists(ENV_FILE) && filesize(ENV_FILE) > 0) {
    header('Location: /');
    exit;
}

$step = $_POST['step'] ?? $_GET['step'] ?? 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        $envOk = true;
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.1', '<')) {
            $error .= "Требуется PHP 8.1 или выше, у вас $phpVersion<br>";
            $envOk = false;
        }
        $extensions = ['pdo_mysql', 'gd', 'openssl', 'ftp', 'json', 'session'];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $error .= "Расширение $ext не загружено<br>";
                $envOk = false;
            }
        }
        $writableDirs = ['var/cache', 'var/logs', 'var/lock', 'public/upload'];
        foreach ($writableDirs as $dir) {
            $fullPath = ROOT_DIR . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
            if (!is_writable($fullPath)) {
                $error .= "Папка $fullPath недоступна для записи<br>";
                $envOk = false;
            }
        }
        if (!is_writable(ROOT_DIR)) {
            $error .= "Корневая папка недоступна для записи (нельзя создать .env)<br>";
            $envOk = false;
        }
        if ($envOk) {
            $_SESSION['env_ok'] = true;
            header('Location: ?step=2');
            exit;
        } else {
            $_SESSION['error'] = $error;
            header('Location: ?step=1');
            exit;
        }
    } elseif ($step == 2) {
        $_SESSION['db_host'] = trim($_POST['db_host'] ?? 'localhost');
        $_SESSION['db_name'] = trim($_POST['db_name'] ?? '');
        $_SESSION['db_user'] = trim($_POST['db_user'] ?? '');
        $_SESSION['db_pass'] = $_POST['db_pass'] ?? '';
        $db_prefix = trim($_POST['db_prefix'] ?? 'grey_');
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $db_prefix)) {
            $_SESSION['error'] = "Префикс таблиц может содержать только латинские буквы, цифры и знак подчёркивания.";
            header('Location: ?step=2');
            exit;
        }
        $_SESSION['db_prefix'] = $db_prefix;
        
        try {
            $pdo = new PDO(
                "mysql:host={$_SESSION['db_host']};dbname={$_SESSION['db_name']};charset=utf8mb4",
                $_SESSION['db_user'],
                $_SESSION['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $_SESSION['db_connected'] = true;
            header('Location: ?step=3');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка подключения к БД: " . $e->getMessage();
            header('Location: ?step=2');
            exit;
        }
    } elseif ($step == 3) {
        $_SESSION['admin_username'] = trim($_POST['admin_username'] ?? '');
        $_SESSION['admin_email'] = trim($_POST['admin_email'] ?? '');
        $_SESSION['admin_password'] = $_POST['admin_password'] ?? '';
        $admin_password2 = $_POST['admin_password2'] ?? '';
        
        if (empty($_SESSION['admin_username']) || empty($_SESSION['admin_email']) || empty($_SESSION['admin_password'])) {
            $_SESSION['error'] = "Заполните все поля администратора";
            header('Location: ?step=3');
            exit;
        }
        if ($_SESSION['admin_password'] !== $admin_password2) {
            $_SESSION['error'] = "Пароли не совпадают";
            header('Location: ?step=3');
            exit;
        }

        if (strlen($_SESSION['admin_password']) < 8) {
            $_SESSION['error'] = "Пароль должен быть не менее 8 символов";
            header('Location: ?step=3');
            exit;
        }
        if (!preg_match('/[A-Za-z]/', $_SESSION['admin_password']) || !preg_match('/[0-9]/', $_SESSION['admin_password'])) {
            $_SESSION['error'] = "Пароль должен содержать хотя бы одну букву и одну цифру";
            header('Location: ?step=3');
            exit;
        }

        if (!filter_var($_SESSION['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Некорректный email";
            header('Location: ?step=3');
            exit;
        }
        header('Location: ?step=4');
        exit;
    } elseif ($step == 4) {
        $result = runInstallation();
        if ($result === true) {
            header('Location: /');
            exit;
        } else {
            $_SESSION['error'] = $result;
            header('Location: ?step=4');
            exit;
        }
    }
}

function runInstallation()
{
    if (!isset($_SESSION['db_connected']) || !$_SESSION['db_connected']) {
        return "Нет подключения к БД";
    }
    
    $db_host = $_SESSION['db_host'];
    $db_name = $_SESSION['db_name'];
    $db_user = $_SESSION['db_user'];
    $db_pass = $_SESSION['db_pass'];
    $db_prefix = $_SESSION['db_prefix'];
    
    try {
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $sqlFile = __DIR__ . '/migrations.sql';
        if (!file_exists($sqlFile)) {
            return "Файл миграций не найден: $sqlFile";
        }
        $sql = file_get_contents($sqlFile);
        $sql = str_replace('{prefix}', $db_prefix, $sql);
        
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($queries as $query) {
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        
        $now = time();
        
        $hashedPassword = password_hash($_SESSION['admin_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO `{$db_prefix}users` 
            (`username`, `password_hash`, `email`, `group`, `money`, `reg_data`, `reg_ip`, `created_at`, `updated_at`) 
            VALUES (?, ?, ?, 4, 0, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            $hashedPassword,
            $_SESSION['admin_email'],
            $now,
            $_SERVER['REMOTE_ADDR'],
            $now,
            $now
        ]);
        
        $defaultSettings = [
            'sitename' => 'GreyPanel',
            'default_app' => 'forum',
            'theme' => 'default',
            'cron_key' => bin2hex(random_bytes(16)),
            'vk_autorize' => '0',
            'vk_app_id' => '',
            'vk_app_key' => '',
            'robocassa' => '0',
            'robocassa_id' => '',
            'robocassa_password1' => '',
            'robocassa_password2' => '',
            'webmoney' => '0',
            'webmoney_secret_key' => '',
            'webmoney_wmr' => '',
            'webmoney_wmz' => '',
            'webmoney_wmu' => '',
            'wmu_kurs' => '1',
            'wmz_kurs' => '1',
            'buy_razban' => '100',
            'amxbans_active' => '0',
            'amxbans_host' => '',
            'amxbans_db' => '',
            'amxbans_user' => '',
            'amxbans_pass' => '',
            'amxbans_prefix' => '',
            'amxbans_forum' => '0',
            'csstats_active' => '0',
            'csstats_host' => '',
            'csstats_db' => '',
            'csstats_user' => '',
            'csstats_pass' => '',
            'csstats_prefix' => '',
            'csstats_table' => '',
            'aes_csstats_active' => '0',
            'aes_csstats_host' => '',
            'aes_csstats_db' => '',
            'aes_csstats_user' => '',
            'aes_csstats_pass' => '',
            'aes_csstats_prefix' => '',
            'aes_csstats_table' => '',
            'chat_enabled' => '1',
            'lgsl_active' => '0',
            'warcraft_buy' => '0',
            'capcha_enabled' => '0',
            'capcha_site_key' => '',
            'capcha_secret_key' => '',
        ];
        $settingStmt = $pdo->prepare("INSERT INTO `{$db_prefix}settings` (`key`, `value`) VALUES (?, ?)");
        foreach ($defaultSettings as $key => $value) {
            $settingStmt->execute([$key, $value]);
        }
        
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
        
        $envContent = "# GreyPanel v2 Environment\n";
        $envContent .= "APP_ENV=prod\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "SITE_NAME={$defaultSettings['sitename']}\n";
        $envContent .= "SITE_URL=http://{$_SERVER['HTTP_HOST']}\n";
        $envContent .= "DB_HOST={$db_host}\n";
        $envContent .= "DB_NAME={$db_name}\n";
        $envContent .= "DB_USER={$db_user}\n";
        $envContent .= "DB_PASS={$db_pass}\n";
        $envContent .= "DB_PREFIX={$db_prefix}\n";
        $envContent .= "DB_CHARSET=utf8mb4\n";
        $envContent .= "SECRET_KEY=" . bin2hex(random_bytes(32)) . "\n";
        $envContent .= "CRON_KEY={$defaultSettings['cron_key']}\n";
        $envContent .= "ENCRYPTION_KEY=" . bin2hex(random_bytes(32)) . "\n";
        $envContent .= "SESSION_NAME=greysession\n";
        $envContent .= "SESSION_LIFETIME=7200\n";
        $envContent .= "DEFAULT_THEME=default\n";
        
        if (file_put_contents(ENV_FILE, $envContent) === false) {
            return "Не удалось создать файл .env";
        }
        
        session_destroy();

        file_put_contents(ENV_FILE, "\nAPP_INSTALLED=true\n", FILE_APPEND);

        $installDir = __DIR__;
        $success = deleteDirectory($installDir);

        if (!$success) {
            rename($installDir, $installDir . '_disabled_' . time());
        }

        header('Location: /');
        exit;
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

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Установка GreyPanel v2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding-top: 40px; }
        .installer-container { max-width: 700px; margin: 0 auto; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container installer-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Установка GreyPanel v2</h3>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <h4>Шаг 1: Проверка окружения</h4>
                <form method="post">
                    <input type="hidden" name="step" value="1">
                    <p>Будут проверены требования к серверу.</p>
                    <button type="submit" class="btn btn-primary">Начать проверку</button>
                </form>
            <?php elseif ($step == 2): ?>
                <h4>Шаг 2: Настройка базы данных</h4>
                <form method="post">
                    <input type="hidden" name="step" value="2">
                    <div class="mb-3"><label class="form-label">Хост MySQL</label><input type="text" name="db_host" class="form-control" value="localhost" required></div>
                    <div class="mb-3"><label class="form-label">Имя базы данных</label><input type="text" name="db_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Пользователь MySQL</label><input type="text" name="db_user" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Пароль MySQL</label><input type="password" name="db_pass" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Префикс таблиц</label><input type="text" name="db_prefix" class="form-control" value="grey_"></div>
                    <button type="submit" class="btn btn-primary">Проверить подключение</button>
                </form>
            <?php elseif ($step == 3): ?>
                <h4>Шаг 3: Администратор</h4>
                <form method="post">
                    <input type="hidden" name="step" value="3">
                    <div class="mb-3"><label class="form-label">Игровой ник администратора</label><input type="text" name="admin_username" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email администратора</label><input type="email" name="admin_email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Пароль</label><input type="password" name="admin_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Повторите пароль</label><input type="password" name="admin_password2" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary">Далее</button>
                </form>
            <?php elseif ($step == 4): ?>
                <h4>Шаг 4: Установка</h4>
                <form method="post">
                    <input type="hidden" name="step" value="4">
                    <p>Будут созданы таблицы и настроена панель.</p>
                    <button type="submit" class="btn btn-primary">Выполнить установку</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>