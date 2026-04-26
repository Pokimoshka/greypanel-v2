<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Service;
use GreyPanel\Model\Tariff;
use GreyPanel\Model\User;
use GreyPanel\Repository\ServiceServerRepository;
use GreyPanel\Repository\UserGroupRepository;
use GreyPanel\Repository\UserRepository;
use GreyPanel\Repository\UserServiceRepository;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use PDO;
use Psr\Log\LoggerInterface;

class ServiceActivationService
{
    public function __construct(
        private MonitorServerRepositoryInterface $serverRepo,
        private ServiceServerRepository $serviceServerRepo,
        private EncryptionServiceInterface $encryption,
        private UserServiceRepository $userServiceRepo,
        private LoggerInterface $logger,
        private UserRepository $userRepo,
        private UserGroupRepository $groupRepo
    ) {
    }

    public function activate(User $user, Service $service, Tariff $tariff, string $plainPassword): bool
    {
        $rights = $service->getRights();
        $expiresAt = time() + ($tariff->getDurationDays() * 86400);
        $serverIds = $this->serviceServerRepo->getServerIdsForService($service->getId());
        if (empty($serverIds)) {
            $this->logger->error("No servers assigned to service {$service->getId()}");
            return false;
        }

        $success = true;
        foreach ($serverIds as $serverId) {
            $server = $this->serverRepo->findById($serverId);
            if (!$server) {
                continue;
            }
            $activated = $this->activateOnServer($server, $user->getUsername(), $plainPassword, $rights, $tariff->getDurationDays());
            if (!$activated) {
                $success = false;
                $this->logger->error("Activation failed on server {$serverId} for user {$user->getId()}");
            }
        }

        if ($success) {
            // Выдача группы, если у услуги указан group_id
            if ($service->getGroupId()) {
                $group = $this->groupRepo->findById($service->getGroupId());
                if ($group) {
                    $user->setGroup($group);
                    $this->userRepo->update($user);
                    // При следующем запросе пользователь получит новые права
                }
            }

            // Обновляем или создаём запись в user_services
            $existing = $this->userServiceRepo->findActiveByService($user->getId(), $service->getId());
            if ($existing) {
                $newExpires = max($existing->getExpiresAt(), $expiresAt);
                $existing->setExpiresAt($newExpires);
                $this->userServiceRepo->update($existing);
            } else {
                $userService = new \GreyPanel\Model\UserService([
                    'user_id' => $user->getId(),
                    'service_id' => $service->getId(),
                    'tariff_id' => $tariff->getId(),
                    'expires_at' => $expiresAt,
                ]);
                $this->userServiceRepo->create($userService);
            }
        }

        return $success;
    }

    private function activateOnServer(array $server, string $username, string $password, string $rights, int $days): bool
    {
        $type = $server['privilege_storage'] ?? 1; // 1=users.ini, 2=AmxBans, 3=both
        $success = true;

        if ($type == 1 || $type == 3) {
            $success = $success && $this->activateViaFtp($server, $username, $password, $rights, $days);
        }
        if ($type == 2 || $type == 3) {
            $success = $success && $this->activateViaAmxBans($server, $username, $password, $rights, $days);
        }
        return $success;
    }

    private function activateViaAmxBans(array $server, string $username, string $password, string $rights, int $days): bool
    {
        $dbHost = $server['amxbans_db_host'];
        $dbUser = $server['amxbans_db_user'];
        $dbPass = $this->encryption->decrypt($server['amxbans_db_pass'] ?? '');
        $dbName = $server['amxbans_db_name'];
        $prefix = $server['amxbans_db_prefix'] ?: 'amx_';

        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->logger->error("AmxBans connection error: " . $e->getMessage());
            return false;
        }

        $amxId = (int)($server['amx_id'] ?? 0);
        $passwordHash = md5($password); // как требует AmxModX

        $stmt = $pdo->prepare("SELECT id FROM {$prefix}amxadmins WHERE username = ? OR steamid = ?");
        $stmt->execute([$username, $username]);
        $adminId = $stmt->fetchColumn();

        if ($adminId) {
            $pdo->prepare("UPDATE {$prefix}amxadmins SET password = ? WHERE id = ?")->execute([$passwordHash, $adminId]);
        } else {
            $pdo->prepare("INSERT INTO {$prefix}amxadmins (username, password, steamid, nickname, created, expired, flags, days) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$username, $passwordHash, $username, $username, time(), 0, $rights, 0]);
            $adminId = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("SELECT id FROM {$prefix}admins_servers WHERE admin_id = ? AND server_id = ?");
        $stmt->execute([$adminId, $amxId]);
        $serverRightId = $stmt->fetchColumn();

        if ($serverRightId) {
            $pdo->prepare("UPDATE {$prefix}admins_servers SET custom_flags = ?, use_static_bantime = 'yes' WHERE id = ?")->execute([$rights, $serverRightId]);
        } else {
            $pdo->prepare("INSERT INTO {$prefix}admins_servers (admin_id, server_id, custom_flags, use_static_bantime) VALUES (?, ?, ?, 'yes')")->execute([$adminId, $amxId, $rights]);
        }

        return true;
    }

    private function activateViaFtp(array $server, string $username, string $password, string $rights, int $days): bool
    {
        $ftpHost = $server['ftp_host'];
        $ftpUser = $server['ftp_user'];
        $ftpPass = $this->encryption->decrypt($server['ftp_pass'] ?? '');
        $ftpPath = $server['ftp_path']; // например, /cstrike/addons/amxmodx/configs/users.ini

        $options = FtpConnectionOptions::fromArray([
            'host' => $ftpHost,
            'username' => $ftpUser,
            'password' => $ftpPass,
            'port' => 21,
            'passive' => true,
            'timeout' => 30,
        ]);
        $filesystem = new Filesystem(new FtpAdapter($options));

        $tempFile = tempnam(sys_get_temp_dir(), 'vip_');
        try {
            $content = $filesystem->read($ftpPath);
            file_put_contents($tempFile, $content);

            $fp = fopen($tempFile, 'r+');
            if ($fp && flock($fp, LOCK_EX)) {
                $lines = file($tempFile, FILE_IGNORE_NEW_LINES);
                $escapedUsername = str_replace('"', '\"', $username);
                $escapedPassword = str_replace('"', '\"', $password);
                $escapedRights = str_replace('"', '\"', $rights);

                $newLine = "\"{$escapedUsername}\" \"{$escapedPassword}\" \"{$escapedRights}\" \"a\" ; GreyPanel auto-activation";

                $found = false;
                foreach ($lines as &$line) {
                    if (strpos($line, "\"$escapedUsername\"") !== false) {
                        $line = $newLine;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $lines[] = $newLine;
                }

                $newContent = implode("\n", $lines);
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, $newContent);
                flock($fp, LOCK_UN);
                fclose($fp);

                $filesystem->write($ftpPath, file_get_contents($tempFile));
            } else {
                throw new \RuntimeException('Could not lock temp file');
            }
        } finally {
            unlink($tempFile);
        }

        return true;
    }
}
