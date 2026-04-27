<?php

namespace App\Support;

use App\Models\Game;
use Illuminate\Support\Collection;

class StandingsCalculator
{
    /**
     * @param  Collection<int, Game>  $games
     * @return array<int, array<string, int|string>>
     */
    public function calculate(Collection $games): array
    {
        $table = [];

        foreach ($games as $game) {
            if ($game->status !== 'completed' || $game->homeTeam === null || $game->awayTeam === null) {
                continue;
            }

            $homeId = $game->homeTeam->id;
            $awayId = $game->awayTeam->id;

            $table[$homeId] ??= $this->defaultRow($game->homeTeam->name);
            $table[$awayId] ??= $this->defaultRow($game->awayTeam->name);

            $homeScore = (int) ($game->home_score ?? 0);
            $awayScore = (int) ($game->away_score ?? 0);

            $table[$homeId]['played']++;
            $table[$awayId]['played']++;
            $table[$homeId]['gf'] += $homeScore;
            $table[$homeId]['ga'] += $awayScore;
            $table[$awayId]['gf'] += $awayScore;
            $table[$awayId]['ga'] += $homeScore;

            if ($homeScore > $awayScore) {
                $table[$homeId]['wins']++;
                $table[$homeId]['points'] += 3;
                $table[$awayId]['losses']++;
            } elseif ($awayScore > $homeScore) {
                $table[$awayId]['wins']++;
                $table[$awayId]['points'] += 3;
                $table[$homeId]['losses']++;
            } else {
                $table[$homeId]['draws']++;
                $table[$awayId]['draws']++;
                $table[$homeId]['points']++;
                $table[$awayId]['points']++;
            }
        }

        foreach ($table as $teamId => $row) {
            $table[$teamId]['gd'] = $row['gf'] - $row['ga'];
        }

        usort($table, function (array $left, array $right): int {
            return [$right['points'], $right['gd'], $right['gf'], $left['team']]
                <=> [$left['points'], $left['gd'], $left['gf'], $right['team']];
        });

        return array_values($table);
    }

    /**
     * @return array<string, int|string>
     */
    private function defaultRow(string $teamName): array
    {
        return [
            'team' => $teamName,
            'played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'gf' => 0,
            'ga' => 0,
            'gd' => 0,
            'points' => 0,
        ];
    }
}
