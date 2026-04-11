<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Repository\VipServerRepositoryInterface;
use GreyPanel\Repository\VipPrivilegeRepositoryInterface;
use GreyPanel\Service\EncryptionServiceInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use Psr\Log\LoggerInterface;
use PDO;
use PDOException;

final class VipActivationService implements VipActivationServiceInterface
{
    public function __construct(
        private VipServerRepositoryInterface $serverRepo,
        private VipPrivilegeRepositoryInterface $privilegeRepo,
        private EncryptionServiceInterface $encryption,
        private LoggerInterface $logger
    ) {
        $this->encryption = $encryption;
    }

    public function activate(
        int $userId,
        string $username,
        string $plainPassword,
        int $serverId,
        int $privilegeId,
        int $days
    ): bool {
        $server = $this->serverRepo->findById($serverId);
        if (!$server) {
            return false;
        }

        $privilege = $this->privilegeRepo->findById($privilegeId);
        if (!$privilege) {
            return false;
        }

        switch ($server['type']) {
            case 0:
                return $this->activateAmx($server, $username, md5($plainPassword), $privilege['flags'], $days);
            case 1:
                return $this->activateFtp($server, $username, $plainPassword, $privilege['flags'], $days);
            case 2:
                return $this->activateSql($server, $username, $plainPassword, $privilege['flags'], $days);
            default:
                return false;
        }
    }

    private function activateAmx(array $server, string $username, string $passwordHash, string $flags, int $days): bool
    {
        $dsn = "mysql:host={$server['host']};dbname={$server['database']};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $server['user'], $this->encryption->decrypt($server['encrypted_password']));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->logger->error('AmxBans connection failed: ' . $e->getMessage(), ['server_id' => $server['id']]);
            return false;
        }

        $prefix = $server['prefix'] ?: 'amx_';
        $amxId = (int)($server['amx_id'] ?? 0);

        $stmt = $pdo->prepare("SELECT id FROM {$prefix}amxadmins WHERE username = ? OR steamid = ?");
        $stmt->execute([$username, $username]);
        $adminId = $stmt->fetchColumn();

        if ($adminId) {
            $pdo->prepare("UPDATE {$prefix}amxadmins SET password = ? WHERE id = ?")
                ->execute([$passwordHash, $adminId]);
        } else {
            $pdo->prepare("INSERT INTO {$prefix}amxadmins (username, password, steamid, nickname, created, expired, flags, days) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$username, $passwordHash, $username, $username, time(), 0, '', 0]);
            $adminId = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("SELECT id FROM {$prefix}admins_servers WHERE admin_id = ? AND server_id = ?");
        $stmt->execute([$adminId, $amxId]);
        $serverRightId = $stmt->fetchColumn();

        if ($serverRightId) {
            $pdo->prepare("UPDATE {$prefix}admins_servers SET custom_flags = ?, use_static_bantime = 'yes' WHERE id = ?")
                ->execute([$flags, $serverRightId]);
        } else {
            $pdo->prepare("INSERT INTO {$prefix}admins_servers (admin_id, server_id, custom_flags, use_static_bantime) 
                           VALUES (?, ?, ?, 'yes')")
                ->execute([$adminId, $amxId, $flags]);
        }

        $this->logger->info("VIP activated via AmxBans", ['user' => $username, 'flags' => $flags]);
        return true;
    }

    private function activateFtp(array $server, string $username, string $password, string $flags, int $days): bool
    {
        $decryptedPassword = $this->encryption->decrypt($server['encrypted_password']);

        $options = FtpConnectionOptions::fromArray([
            'host' => $server['host'],
            'username' => $server['user'],
            'password' => $decryptedPassword,
            'port' => 21,
            'passive' => true,
            'ssl' => false,
            'timeout' => 30,
        ]);

        $filesystem = new Filesystem(new FtpAdapter($options));

        try {
            $content = $filesystem->read($server['database']);
        } catch (\Exception $e) {
            $this->logger->error('FTP read failed: ' . $e->getMessage());
            return false;
        }

        $lines = explode("\n", $content);
        $newLine = "\"{$username}\" \"{$password}\" \"{$flags}\" \"a\" ; GreyPanel auto-activation";

        $found = false;
        foreach ($lines as &$line) {
            if (strpos($line, "\"$username\"") !== false) {
                $line = $newLine;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $lines[] = $newLine;
        }

        $newContent = implode("\n", $lines);
        try {
            $filesystem->write($server['database'], $newContent);
        } catch (\Exception $e) {
            $this->logger->error('FTP write failed: ' . $e->getMessage());
            return false;
        }

        $this->logger->info("VIP activated via FTP", ['user' => $username, 'flags' => $flags]);
        return true;
    }

    private function activateSql(array $server, string $username, string $password, string $flags, int $days): bool
    {
        $dsn = "mysql:host={$server['host']};dbname={$server['database']};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $server['user'], $this->encryption->decrypt($server['encrypted_password']));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->logger->error('SourceMod DB connection failed: ' . $e->getMessage());
            return false;
        }

        $table = $server['prefix'] ?: 'users';
        $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE auth = ?");
        $stmt->execute([$username]);
        $userId = $stmt->fetchColumn();

        if ($userId) {
            $pdo->prepare("UPDATE {$table} SET password = ?, access = 'a', flags = ? WHERE id = ?")
                ->execute([$password, $flags, $userId]);
        } else {
            $pdo->prepare("INSERT INTO {$table} (auth, password, access, flags) VALUES (?, ?, 'a', ?)")
                ->execute([$username, $password, $flags]);
        }

        $this->logger->info("VIP activated via SourceMod SQL", ['user' => $username, 'flags' => $flags]);
        return true;
    }
}