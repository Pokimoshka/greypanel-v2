<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Model\Statistic;

class LevelsRanksIntegration extends AbstractStatsIntegration
{
    protected function getEngineType(): int
    {
        return 5;
    }

    protected function getDefaultTable(): string
    {
        return 'lvl_base';
    }

    protected function getBaseQuery(): string
    {
        return "SELECT id, name AS nick, steam AS steam_id, kills AS frags, deaths,
                       headshots, shoots AS shots, hits, value AS skill, playtime AS gametime,
                       lastconnect AS lasttime, round_win, round_lose, suicides,
                       plants AS planted, defuses AS defused, connects
                FROM {$this->table}";
    }

    protected function mapRow(array $row, int $position): Statistic
    {
        $row['position'] = $position;
        $row['teamkills'] = 0;
        $row['damage'] = 0;
        $row['defusing'] = 0;
        $row['defused'] = 0;
        $row['planted'] = 0;
        $row['explode'] = 0;
        $row['wint'] = $row['round_win'] ?? 0;
        $row['winct'] = $row['round_lose'] ?? 0;
        $row['rounds'] = ($row['round_win'] ?? 0) + ($row['round_lose'] ?? 0);
        $row['connects'] = 0;
        $row['suicides'] = 0;
        return new Statistic($row);
    }

    public function getSortTypes(): array
    {
        return [
            0 => 'По очкам (value)',
            1 => 'По убийствам',
            2 => 'По времени игры',
        ];
    }

    protected function getOrderClause(int $sortType): string
    {
        return match ($sortType) {
            1 => 'kills DESC',
            2 => 'playtime DESC',
            default => 'value DESC',
        };
    }
}
