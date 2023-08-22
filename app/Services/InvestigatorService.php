<?php

namespace App\Services;

use App\Enums\Team;
use App\Traits\MemberHelperTrait;

class InvestigatorService extends VoteService
{
    const SAME_TEAM = 1;

    const DIFFERENT_TEAM = 2;

    use MemberHelperTrait;

    public function compare(string $gameId): int
    {
        $toCompare = array_values(self::getVotes($gameId))[0];

        $firstTeam = $this->getTeamFromUserId($toCompare[0], $gameId);

        if (
            $firstTeam !== $this->getTeamFromUserId($toCompare[1], $gameId) ||
            ($firstTeam === $this->getTeamFromUserId($toCompare[1], $gameId) && $firstTeam === Team::Loners)
        ) {
            return self::DIFFERENT_TEAM;
        }

        return self::SAME_TEAM;

    }

    private function getTeamFromUserId(string $userId, string $gameId): Team
    {
        return Team::role($this->getRoleByUserId($userId, $gameId));
    }
}
