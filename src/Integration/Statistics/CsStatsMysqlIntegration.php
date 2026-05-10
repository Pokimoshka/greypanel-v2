<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Model\Statistic;

class CsStatsMysqlIntegration extends AbstractStatsIntegration
{
    protected function getEngineType(): int
    {
        return 1;
    }

    protected function getDefaultTable(): string
    {
        return 'csstats';
    }

    protected function getBaseQuery(): string
    {
        return "SELECT id, name AS nick, steamid AS steam_id, kills AS frags, deaths,
                       hs AS headshots, shots, hits, skill, connection_time AS gametime,
                       UNIX_TIMESTAMP(last_join) AS lasttime, tks AS teamkills, dmg AS damage,
                       0 AS defusing, bombdefused AS defused, bombplants AS planted,
                       bombexplosions AS explode, (roundt+roundct) AS rounds,
                       wint, winct, connects
                FROM {$this->table}";
    }

    protected function mapRow(array $row, int $position): Statistic
    {
        $row['position'] = $position;
        return new Statistic($row);
    }

    public function getSortTypes(): array
    {
        return [
            0 => 'По убийствам',
            1 => 'По K/D',
            2 => 'По скиллу',
            3 => 'По времени игры',
        ];
    }

    protected function getOrderClause(int $sortType): string
    {
        return match ($sortType) {
            1 => '(kills - deaths) DESC',
            2 => 'skill DESC',
            3 => 'connection_time DESC',
            default => 'kills DESC',
        };
    }
}
