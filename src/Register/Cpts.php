<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Register;

use RXNLabs\MistletoeMatchupFantasyDraft\Config;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Helpers;

class Cpts
{
    public function __construct(private Config $config) {}

    public function register(): void
    {
        $this->league();
        $this->team();
        $this->trope();
        $this->trade();
        $this->movie();
        $this->matchup();

        // Enforce comments policy via filters
        add_filter('comments_open', [$this, 'comments_policy'], 10, 2);
        add_filter('pings_open', [$this, 'comments_policy'], 10, 2);
    }

    public function register_meta(): void
    {
        // Leagues
        register_post_meta('snowdraft_league', '_snowdraft_draft_type', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_league', '_snowdraft_roster_size', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_league', '_snowdraft_bench_size', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_league', '_snowdraft_pick_timer', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_league', '_snowdraft_bid_timer', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_league', '_snowdraft_auction_budget', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_league', '_snowdraft_winner_policy', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);

        // Teams
        register_post_meta('snowdraft_team', '_snowdraft_league_id', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_team', '_snowdraft_icon_key', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);

        // Tropes
        register_post_meta('snowdraft_trope', '_snowdraft_score_value', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_trope', '_snowdraft_categories', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => 'JSON array (stringified)', ]);

        // Trades
        register_post_meta('snowdraft_trade', '_snowdraft_trade_status', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_trade', '_snowdraft_trade_expires_at', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_trade', '_snowdraft_trade_tropes_out', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => 'JSON array (stringified)', ]);
        register_post_meta('snowdraft_trade', '_snowdraft_trade_tropes_in', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => 'JSON array (stringified)', ]);
        register_post_meta('snowdraft_trade', '_snowdraft_trade_budget_delta', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_trade', '_snowdraft_requires_commissioner_approval', [ 'type' => 'boolean', 'single' => true, 'show_in_rest' => true, ]);

        // Movies
        register_post_meta('snowdraft_movie', '_snowdraft_movie_year', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_movie', '_snowdraft_release_channels', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => 'JSON array (stringified)', ]);

        // Matchups
        register_post_meta('snowdraft_matchup', '_snowdraft_league_id', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_matchup', '_snowdraft_week_number', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_matchup', '_snowdraft_movie_id', [ 'type' => 'integer', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_matchup', '_snowdraft_team_ids', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => 'JSON array (stringified)', ]);
        register_post_meta('snowdraft_matchup', '_snowdraft_user_ids', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => 'JSON array (stringified)', ]);
        register_post_meta('snowdraft_matchup', '_snowdraft_assigned_at', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);
        register_post_meta('snowdraft_matchup', '_snowdraft_status', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, ]);
    }

    public function comments_policy($open, $post_id): bool
    {
        $post = get_post($post_id);
        if (!$post) return $open;
        $plugin_types = ['snowdraft_trade','snowdraft_league','snowdraft_team','snowdraft_trope','snowdraft_movie','snowdraft_matchup'];
        if (!in_array($post->post_type, $plugin_types, true)) {
            return $open; // don't affect non-plugin post types
        }
        $chat_types = ['snowdraft_trade', 'snowdraft_league'];
        if (in_array($post->post_type, $chat_types, true)) {
            return true; // chat enabled
        }
        return false; // disabled for other plugin CPTs
    }

    private function league(): void
    {
        register_post_type('snowdraft_league', [
            'label' => __('Leagues', 'mistletoe-matchup-fantasy-draft'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor', 'comments', 'revisions'],
            'capability_type' => ['snowdraft_league', 'snowdraft_leagues'],
        ]);
    }

    private function team(): void
    {
        register_post_type('snowdraft_team', [
            'label' => __('Teams', 'mistletoe-matchup-fantasy-draft'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'revisions'],
            'capability_type' => ['snowdraft_team', 'snowdraft_teams'],
            'map_meta_cap' => true,
        ]);
    }

    private function trope(): void
    {
        register_post_type('snowdraft_trope', [
            'label' => __('Tropes', 'mistletoe-matchup-fantasy-draft'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'revisions'],
            'capability_type' => ['snowdraft_trope', 'snowdraft_tropes'],
        ]);
    }

    private function trade(): void
    {
        register_post_type('snowdraft_trade', [
            'label' => __('Trades', 'mistletoe-matchup-fantasy-draft'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'comments', 'revisions'],
            'capability_type' => ['snowdraft_trade', 'snowdraft_trades'],
        ]);
    }

    private function movie(): void
    {
        register_post_type('snowdraft_movie', [
            'label' => __('Movies', 'mistletoe-matchup-fantasy-draft'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
            'capability_type' => ['snowdraft_movie', 'snowdraft_movies'],
        ]);
    }

    private function matchup(): void
    {
        register_post_type('snowdraft_matchup', [
            'label' => __('Matchups', 'mistletoe-matchup-fantasy-draft'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'revisions'],
            'capability_type' => ['snowdraft_matchup', 'snowdraft_matchups'],
        ]);
    }
}
