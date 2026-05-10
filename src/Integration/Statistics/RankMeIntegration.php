<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Model\Statistic;

class RankMeIntegration extends AbstractStatsIntegration
{
    protected function getEngineType(): int
    {
        return 4;
    }

    protected function getDefaultTable(): string
    {
        return 'rankme';
    }

    protected function getBaseQuery(): string
    {
        return "SELECT id, name AS nick, steam AS steam_id, kills AS frags, deaths,
                       headshots, shots, hits, score AS skill, connected AS gametime,
                       lastconnect AS lasttime, assists, round_win, round_lose, suicides,
                       plant, defuse
                FROM {$this->table}";
    }

    protected function mapRow(array $row, int $position): Statistic
    {
        $row['position'] = $position;
        $row['teamkills'] = 0;
        $row['damage'] = 0;
        $row['defusing'] = $row['defuse'] ?? 0;
        $row['defused'] = $row['defuse'] ?? 0;
        $row['planted'] = $row['plant'] ?? 0;
        $row['explode'] = $row['explode'] ?? 0;
        $row['rounds'] = ($row['round_win'] ?? 0) + ($row['round_lose'] ?? 0);
        $row['wint'] = $row['round_win'] ?? 0;
        $row['winct'] = $row['round_lose'] ?? 0;
        $row['connects'] = $row['connects'] ?? 0;
        return new Statistic($row);
    }

    public function getSortTypes(): array
    {
        return [
            0 => 'По убийствам',
            1 => 'По скиллу (очкам)',
            2 => 'По времени игры',
        ];
    }

    protected function getOrderClause(int $sortType): string
    {
        return match ($sortType) {
            1 => 'score DESC',
            2 => 'connected DESC',
            default => 'kills DESC',
        };
    }
}
