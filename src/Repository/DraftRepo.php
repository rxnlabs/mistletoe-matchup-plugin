<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Repository;

class DraftRepo
{
    public function is_user_in_league(int $userId, int $leagueId): bool
    {
        // Placeholder: implement actual membership check
        return user_can($userId, 'read_snowdraft_league');
    }
}
