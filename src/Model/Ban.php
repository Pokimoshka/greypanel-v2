<?php

declare(strict_types=1);

namespace GreyPanel\Model;

class Ban
{
    public const STATUS_ACTIVE = 0;
    public const STATUS_UNBANNED = 1;
    public const STATUS_EXPIRED = 2;
    public const STATUS_BOUGHT_UNBAN = 3;

    public int $bid;
    public string $playerNick;
    public string $adminNick;
    public string $reason;
    public int $created;
    public int $length; // в секундах, 0 = навсегда
    public int $expired;
    public ?int $endTime = null;
    public string $serverName;
    public int $status = self::STATUS_ACTIVE;
    public ?int $adminUserId = null;
    public ?int $unbanType = null;

    public function __construct(array $data)
    {
        $this->bid = (int)$data['bid'];
        $this->playerNick = $data['player_nick'] ?? 'Unknown';
        $this->adminNick = $data['admin_nick'] ?? '';
        $this->reason = $data['ban_reason'] ?? $data['cs_ban_reason'] ?? '';
        $this->created = (int)$data['ban_created'];
        $this->length = (int)$data['ban_length'];
        $this->expired = (int)($data['expired'] ?? 0);
        $this->serverName = $data['server_name'] ?? '';

        // Определяем статус
        if ($data['unban_type'] == -2) {
            $this->status = self::STATUS_BOUGHT_UNBAN;
        } elseif ($this->expired == 1 || ($data['unban_type'] ?? 0) == -1) {
            $this->status = self::STATUS_UNBANNED;
        } elseif ($this->length > 0 && ($this->created + $this->length) < time()) {
            $this->status = self::STATUS_EXPIRED;
        } elseif ($this->length == 0) {
            $this->status = self::STATUS_ACTIVE; // навсегда
        } else {
            $this->endTime = $this->created + $this->length;
            $this->status = self::STATUS_ACTIVE;
        }

        $this->adminUserId = isset($data['admin_user_id']) ? (int)$data['admin_user_id'] : null;
        $this->unbanType = isset($data['unban_type']) ? (int)$data['unban_type'] : null;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getEndDate(): ?string
    {
        if ($this->length === 0) {
            return 'Никогда';
        }
        if ($this->endTime) {
            return date('d.m.Y H:i', $this->endTime);
        }
        return null;
    }

    public function getLengthFormatted(): string
    {
        if ($this->length === 0) {
            return 'Навсегда';
        }
        $days = intdiv($this->length, 86400);
        $hours = intdiv($this->length % 86400, 3600);
        return $days ? "{$days} дн." : "{$hours} ч.";
    }
}
