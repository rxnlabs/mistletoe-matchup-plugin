<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Util;

class Capabilities
{
    public static function can_manage_league(int $leagueId, int $userId = 0): bool
    {
        $userId = $userId ?: get_current_user_id();
        return user_can($userId, 'manage_snowdraft_leagues');
    }
}
