<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Register;

class Roles
{
    public const ROLE_TEAM_MANAGER = 'snowdraft_team_manager';

    public static function caps(): array
    {
        $caps = [
            'manage_snowdraft_leagues', 'edit_snowdraft_league', 'read_snowdraft_league',
            'manage_snowdraft_teams', 'edit_snowdraft_team', 'read_snowdraft_team',
            'manage_snowdraft_tropes', 'edit_snowdraft_trope', 'read_snowdraft_trope',
            'manage_snowdraft_trades', 'edit_snowdraft_trade', 'read_snowdraft_trade',
            'manage_snowdraft_movies', 'edit_snowdraft_movie', 'read_snowdraft_movie',
            'manage_snowdraft_matchups', 'edit_snowdraft_matchup', 'read_snowdraft_matchup',
        ];
        return apply_filters('snowdraft_caps', $caps);
    }

    public function register(): void
    {
        // Add role if not exists
        if (!get_role(self::ROLE_TEAM_MANAGER)) {
            add_role(self::ROLE_TEAM_MANAGER, __('Team Manager', 'mistletoe-matchup-fantasy-draft'));
        }

        $manager = get_role(self::ROLE_TEAM_MANAGER);
        $admin   = get_role('administrator');

        foreach (self::caps() as $cap) {
            if ($admin && !$admin->has_cap($cap)) {
                $admin->add_cap($cap);
            }
            if ($manager && !in_array($cap, [
                'manage_snowdraft_trades','manage_snowdraft_movies','manage_snowdraft_matchups'
            ], true)) {
                $manager->add_cap($cap);
            }
        }
    }
}
