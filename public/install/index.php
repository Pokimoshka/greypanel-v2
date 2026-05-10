<?php
declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__, 2));
define('ENV_FILE', ROOT_DIR . '/.env');

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$availableLangs = [];
foreach (glob(__DIR__ . '/messages/install.*.yaml') as $file) {
    if (preg_match('/install\.(\w+)\.yaml$/', basename($file), $matches)) {
        $code = $matches[1];
        $data = Yaml::parseFile($file);
        $availableLangs[$code] = $data['language_name'] ?? $code;
    }
}

$lang = $_SESSION['install_lang'] ?? 'ru';
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $availableLangs)) {
    $_SESSION['install_lang'] = $_GET['lang'];
    header('Location: /install/');
    exit;
}
if (!array_key_exists($lang, $availableLangs)) {
    $lang = 'ru';
}

$messages = Yaml::parseFile(__DIR__ . '/messages/install.' . $lang . '.yaml');
function __($key)
{
    global $messages;
    return $messages[$key] ?? $key;
}

if (file_exists(ENV_FILE) && filesize(ENV_FILE) > 0) {
    $envContent = file_get_contents(ENV_FILE);
    if (preg_match('/^APP_INSTALLED\s*=\s*true\s*$/m', $envContent)) {
        header('Location: /');
        exit;
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function checkRequirements(): array
{
    $results = [];
    $phpVersion = phpversion();
    $phpOk = version_compare($phpVersion, '8.4', '>=');
    $results[] = [
        'name' => "PHP " . __('version') . " (≥ 8.4, $phpVersion)",
        'status' => $phpOk,
    ];

    $extensions = ['pdo_mysql', 'gd', 'openssl', 'ftp', 'session', 'mbstring', 'fileinfo'];
    foreach ($extensions as $ext) {
        $results[] = [
            'name' => __('extension') . " «{$ext}»",
            'status' => extension_loaded($ext),
        ];
    }

    $writableDirs = [
        'var/cache' => __('cache'),
        'var/logs' => __('logs'),
        'var/lock' => __('lock'),
        'public/upload' => __('upload'),
    ];
    foreach ($writableDirs as $dir => $label) {
        $fullPath = ROOT_DIR . '/' . $dir;
        if (!is_dir($fullPath)) {
            @mkdir($fullPath, 0777, true);
        }
        $writable = is_writable($fullPath);
        $results[] = [
            'name' => __('writable') . " «{$label}»",
            'status' => $writable,
        ];
    }

    $rootWritable = is_writable(ROOT_DIR);
    $results[] = [
        'name' => __('root_writable'),
        'status' => $rootWritable,
    ];

    return [
        'success' => $phpOk && !in_array(false, array_column($results, 'status'), true),
        'results' => $results,
    ];
}

function runInstallation(array $data): array|string
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

        $stmt = $pdo->query("SHOW TABLES LIKE '{$db_prefix}%'");
        if ($stmt->rowCount() > 0) {
            return __('tables_exist') . " '{$db_prefix}'";
        }

        $sqlFile = __DIR__ . '/migrations.sql';
        if (!file_exists($sqlFile)) {
            return __('migration_missing') . ": $sqlFile";
        }
        $sql = file_get_contents($sqlFile);
        $sql = str_replace('{prefix}', $db_prefix, $sql);
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($queries as $query) {
            if (empty($query)) {
                continue;
            }
            $pdo->exec($query);
        }

        $now = time();
        $stmt = $pdo->prepare("INSERT INTO `{$db_prefix}user_groups` (`name`, `flags`, `is_default`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([__('default_group_user'), '', 1, $now, $now]);
        $stmt->execute([__('default_group_admin'), 'abcdefghijklmnopqrstuvwxyz', 0, $now, $now]);
        $rootGroupId = $pdo->lastInsertId();

        $hashedPassword = password_hash($data['admin_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO `{$db_prefix}users` (`username`, `password_hash`, `email`, `group_id`, `money`, `reg_data`, `reg_ip`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?)");
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
            'chat_enabled' => '1'
        ];
        $settingStmt = $pdo->prepare("INSERT INTO `{$db_prefix}settings` (`key`, `value`) VALUES (?, ?)");
        foreach ($defaultSettings as $key => $value) {
            $settingStmt->execute([$key, $value]);
        }

        $menuItems = [
            ['num' => 1, 'text' => __('menu.forum'), 'desc' => __('menu.forum_desc'), 'module' => 'forum', 'icon' => 'fa fa-comments', 'active' => 1, 'ses' => 0],
            ['num' => 2, 'text' => __('menu.vip'), 'desc' => __('menu.vip_desc'), 'module' => 'vip', 'icon' => 'fa fa-bolt', 'active' => 1, 'ses' => 1],
            ['num' => 3, 'text' => __('menu.bans'), 'desc' => __('menu.bans_desc'), 'module' => 'bans', 'icon' => 'fa fa-gavel', 'active' => 1, 'ses' => 0],
            ['num' => 4, 'text' => __('menu.stats'), 'desc' => __('menu.stats_desc'), 'module' => 'stats', 'icon' => 'fa fa-bar-chart', 'active' => 1, 'ses' => 0],
            ['num' => 5, 'text' => __('menu.auth'), 'desc' => __('menu.auth_desc'), 'module' => 'auth', 'icon' => 'fa fa-key', 'active' => 1, 'ses' => 2],
        ];
        $menuStmt = $pdo->prepare("INSERT INTO `{$db_prefix}menu` (`num`, `text`, `description`, `module`, `icon`, `active`, `ses`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($menuItems as $item) {
            $menuStmt->execute([$item['num'], $item['text'], $item['desc'], $item['module'], $item['icon'], $item['active'], $item['ses']]);
        }

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
            return __('env_write_error');
        }
        chmod(ENV_FILE, 0600);

        session_destroy();

        $installDir = __DIR__;
        if (!deleteDirectory($installDir)) {
            $disabledDir = $installDir . '_disabled_' . time();
            if (!rename($installDir, $disabledDir)) {
                return __('install_delete_error');
            }
        }

        return ['success' => true];
    } catch (Exception $e) {
        return __('install_error') . ': ' . $e->getMessage();
    }
}

function deleteDirectory($dir)
{
    if (!is_dir($dir)) {
        return false;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if (empty($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'error' => __('csrf_error')]);
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
                echo json_encode(['success' => false, 'error' => __('prefix_invalid')]);
                exit;
            }

            try {
                $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $_SESSION['install_state'] = [
                    'db_host' => $db_host,
                    'db_name' => $db_name,
                    'db_user' => $db_user,
                    'db_pass' => $db_pass,
                    'db_prefix' => $db_prefix,
                ];
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => __('connect_error') . ': ' . $e->getMessage()]);
            }
            break;

        case 'save_admin':
            $installState = $_SESSION['install_state'] ?? null;
            if (!$installState) {
                echo json_encode(['success' => false, 'error' => __('state_lost')]);
                exit;
            }

            $admin_username = trim($input['admin_username'] ?? '');
            $admin_email = trim($input['admin_email'] ?? '');
            $admin_password = $input['admin_password'] ?? '';
            $admin_password2 = $input['admin_password2'] ?? '';

            if (empty($admin_username) || empty($admin_email) || empty($admin_password)) {
                echo json_encode(['success' => false, 'error' => __('admin_required')]);
                exit;
            }
            if ($admin_password !== $admin_password2) {
                echo json_encode(['success' => false, 'error' => __('password_mismatch')]);
                exit;
            }
            if (strlen($admin_password) < 12) {
                echo json_encode(['success' => false, 'error' => __('password_length')]);
                exit;
            }
            if (!preg_match('/[a-z]/', $admin_password) || !preg_match('/[A-Z]/', $admin_password) || !preg_match('/[0-9]/', $admin_password) || !preg_match('/[^a-zA-Z0-9]/', $admin_password)) {
                echo json_encode(['success' => false, 'error' => __('password_complexity')]);
                exit;
            }
            if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'error' => __('email_invalid')]);
                exit;
            }

            $_SESSION['install_state']['admin_username'] = $admin_username;
            $_SESSION['install_state']['admin_email'] = $admin_email;
            $_SESSION['install_state']['admin_password'] = $admin_password;

            echo json_encode(['success' => true]);
            break;

        case 'install':
            $installState = $_SESSION['install_state'] ?? null;
            if (!$installState) {
                echo json_encode(['success' => false, 'error' => __('state_lost')]);
                exit;
            }

            $result = runInstallation($installState);
            if (isset($result['success']) && $result['success'] === true) {
                echo json_encode(['success' => true, 'redirect' => '/']);
            } else {
                echo json_encode(['success' => false, 'error' => $result]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => __('unknown_action')]);
    }
    exit;
}

$requirements = checkRequirements();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title><?= __('title') ?></title>
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
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><?= __('title') ?></h3>
                <div>
                    <select x-model="currentLang" @change="changeLanguage" class="form-select form-select-sm">
                        <?php foreach ($availableLangs as $code => $name): ?>
                            <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div x-show="error" class="alert alert-danger animate__animated animate__shakeX" x-text="error"></div>

            <div x-show="step === 1" x-transition.opacity>
                <h4 class="mb-3"><?= __('step1_title') ?></h4>
                <table class="table table-sm" x-show="requirements">
                    <thead><tr><th><?= __('requirement') ?></th><th style="width:100px"><?= __('status') ?></th></tr></thead>
                    <tbody>
                        <template x-for="item in requirements" :key="item.name">
                            <tr>
                                <td x-text="item.name"></td>
                                <td>
                                    <i x-show="item.status" class="fas fa-check-circle text-success"></i>
                                    <i x-show="!item.status" class="fas fa-times-circle text-danger"></i>
                                    <span x-text="item.status ? '<?= __('ok') ?>' : '<?= __('error') ?>'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="allRequirementsOk" class="alert alert-success"><?= __('all_ok') ?></div>
                <div x-show="!allRequirementsOk" class="alert alert-warning" x-text="'<?= __('not_ok') ?>'"></div>
                <button class="btn btn-primary" @click="nextStep(2)" :disabled="!allRequirementsOk"><?= __('continue') ?></button>
                <button class="btn btn-secondary ms-2" @click="checkRequirements()"><i class="fas fa-redo"></i> <?= __('check_again') ?></button>
            </div>

            <div x-show="step === 2" x-transition.opacity>
                <h4 class="mb-3"><?= __('step2_title') ?></h4>
                <div x-show="!dbTested">
                    <div class="mb-3">
                        <label class="form-label"><?= __('db_host') ?></label>
                        <input type="text" class="form-control" x-model="dbHost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('db_name') ?></label>
                        <input type="text" class="form-control" x-model="dbName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('db_user') ?></label>
                        <input type="text" class="form-control" x-model="dbUser" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('db_pass') ?></label>
                        <input type="password" class="form-control" x-model="dbPass">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('db_prefix') ?></label>
                        <input type="text" class="form-control" x-model="dbPrefix">
                    </div>
                    <button class="btn btn-primary" @click="testDB()"><?= __('test_connection') ?></button>
                </div>
                <div x-show="dbTested && dbConnected">
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= __('db_success') ?></div>
                    <button class="btn btn-primary" @click="nextStep(3)"><?= __('next') ?></button>
                </div>
                <div x-show="dbTested && !dbConnected">
                    <div class="alert alert-danger" x-text="dbError"></div>
                    <button class="btn btn-primary" @click="dbTested = false"><?= __('change') ?></button>
                </div>
            </div>

            <div x-show="step === 3" x-transition.opacity>
                <h4 class="mb-3"><?= __('step3_title') ?></h4>
                <div class="mb-3">
                    <label class="form-label"><?= __('admin_username') ?></label>
                    <input type="text" class="form-control" x-model="adminUsername" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= __('admin_email') ?></label>
                    <input type="email" class="form-control" x-model="adminEmail" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= __('admin_password') ?></label>
                    <input type="password" class="form-control" x-model="adminPassword" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= __('admin_password2') ?></label>
                    <input type="password" class="form-control" x-model="adminPassword2" required>
                </div>
                <button class="btn btn-primary" @click="saveAdmin()"><?= __('save_admin') ?></button>
            </div>

            <div x-show="step === 4" x-transition.opacity>
                <h4 class="mb-3"><?= __('step4_title') ?></h4>
                <p><?= __('install_text') ?></p>
                <button class="btn btn-primary" @click="install()"><?= __('install_button') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('installer', () => ({
            step: 1,
            error: '',
            currentLang: '<?= $lang ?>',
            requirements: <?= json_encode($requirements['results']) ?>,
            allRequirementsOk: <?= json_encode($requirements['success']) ?>,
            dbHost: 'localhost',
            dbName: '',
            dbUser: '',
            dbPass: '',
            dbPrefix: 'grey_',
            dbTested: false,
            dbConnected: false,
            dbError: '',
            adminUsername: '',
            adminEmail: '',
            adminPassword: '',
            adminPassword2: '',

            async changeLanguage() {
                window.location.href = '?lang=' + this.currentLang;
            },

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
                    this.error = '<?= __('error_occurred') ?>';
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
                    } else {
                        this.dbTested = true;
                        this.dbConnected = false;
                        this.dbError = data.error;
                    }
                } catch (e) {
                    this.error = '<?= __('error_occurred') ?>';
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
                            admin_username: this.adminUsername,
                            admin_email: this.adminEmail,
                            admin_password: this.adminPassword,
                            admin_password2: this.adminPassword2,
                            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                        })
                    });
                    let data = await resp.json();
                    if (data.success) {
                        this.step = 4;
                    } else {
                        this.error = data.error;
                    }
                } catch (e) {
                    this.error = '<?= __('error_occurred') ?>';
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
                    this.error = '<?= __('error_occurred') ?>';
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