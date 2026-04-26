<?php

declare(strict_types=1);

namespace GreyPanel\Model;

class Statistic
{
    public int $id;
    public string $nick;
    public string $steamId;
    public int $frags;
    public int $deaths;
    public int $headshots;
    public int $shots;
    public int $hits;
    public float $skill;
    public int $gameTime; // в секундах
    public int $lastSeen;
    public int $rank;
    public int $teamkills;
    public int $damage;
    public int $defusing;
    public int $defused;
    public int $planted;
    public int $explode;
    public int $rounds;
    public int $wint;
    public int $winct;
    public int $connects;

    // Дополнительные поля для некоторых драйверов
    public ?int $suicides = null;
    public ?string $skillName = null;
    public ?string $skillColor = null;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->nick = $data['nick'] ?? 'Unknown';
        $this->steamId = $data['steam_id'] ?? '';
        $this->frags = (int)($data['frags'] ?? 0);
        $this->deaths = (int)($data['deaths'] ?? 0);
        $this->headshots = (int)($data['headshots'] ?? 0);
        $this->shots = (int)($data['shots'] ?? 0);
        $this->hits = (int)($data['hits'] ?? 0);
        $this->skill = (float)($data['skill'] ?? 0);
        $this->gameTime = (int)($data['gametime'] ?? 0);
        $this->lastSeen = (int)($data['lasttime'] ?? 0);
        $this->rank = (int)($data['position'] ?? 0);
        $this->teamkills = (int)($data['teamkills'] ?? 0);
        $this->damage = (int)($data['damage'] ?? 0);
        $this->defusing = (int)($data['defusing'] ?? 0);
        $this->defused = (int)($data['defused'] ?? 0);
        $this->planted = (int)($data['planted'] ?? 0);
        $this->explode = (int)($data['explode'] ?? 0);
        $this->rounds = (int)($data['rounds'] ?? 0);
        $this->wint = (int)($data['wint'] ?? 0);
        $this->winct = (int)($data['winct'] ?? 0);
        $this->connects = (int)($data['connects'] ?? 0);
        $this->suicides = isset($data['suicides']) ? (int)$data['suicides'] : null;
        $this->skillName = $data['skill_name'] ?? null;
        $this->skillColor = $data['skill_color'] ?? null;
    }

    public function getKdRatio(): float
    {
        return $this->deaths === 0 ? $this->frags : round($this->frags / $this->deaths, 2);
    }

    public function getHsPercent(): float
    {
        return $this->frags === 0 ? 0 : round($this->headshots / $this->frags * 100, 2);
    }

    public function getGameTimeFormatted(): string
    {
        if ($this->gameTime < 60) return "{$this->gameTime} сек";
        $hours = intdiv($this->gameTime, 3600);
        $minutes = intdiv($this->gameTime % 3600, 60);
        return $hours ? "{$hours} ч {$minutes} мин" : "{$minutes} мин";
    }

    public function getLastSeenFormatted(): string
    {
        if ($this->lastSeen == 0) return 'Давно';
        return date('d.m.Y H:i', $this->lastSeen);
    }
}