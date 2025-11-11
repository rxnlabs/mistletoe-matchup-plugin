<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest;

use RXNLabs\MistletoeMatchupFantasyDraft\Http\Nonces;
use RXNLabs\MistletoeMatchupFantasyDraft\Repository\MatchupRepo;
use RXNLabs\MistletoeMatchupFantasyDraft\Repository\MovieRepo;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Helpers;
use WP_REST_Request;
use WP_REST_Response;

class MatchupsController
{
    public function __construct(
        private MatchupRepo $matchups,
        private MovieRepo $movies,
        private Nonces $nonces
    ) {}

    public function register_routes(): void
    {
        register_rest_route('snowdraft/v1', '/matchups', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'create'],
                'permission_callback' => [$this, 'can_manage_matchups'],
            ],
            [
                'methods' => 'GET',
                'callback' => [$this, 'list'],
                'permission_callback' => '__return_true',
            ],
        ]);

        register_rest_route('snowdraft/v1', '/matchups/(?P<id>\d+)/assign-movie', [
            'methods' => 'POST',
            'callback' => [$this, 'assign_movie'],
            'permission_callback' => [$this, 'can_manage_matchups'],
        ]);
    }

    public function can_manage_matchups(WP_REST_Request $req): bool
    {
        $nonce = $req->get_header('X-WP-Nonce');
        if (!$this->nonces->verify((string)$nonce)) return false;
        return current_user_can('manage_snowdraft_matchups') || current_user_can('edit_snowdraft_matchup');
    }

    public function create(WP_REST_Request $req): WP_REST_Response
    {
        $data = $req->get_json_params() ?: [];
        $leagueId = (int)($data['league_id'] ?? 0);
        $title = $data['title'] ?? 'Matchup';
        $postId = wp_insert_post([
            'post_type' => 'snowdraft_matchup',
            'post_status' => 'publish',
            'post_title' => $title,
        ], true);
        if (is_wp_error($postId)) return new WP_REST_Response(['error' => 'failed'], 500);
        update_post_meta($postId, '_snowdraft_league_id', $leagueId);
        if (isset($data['week_number'])) update_post_meta($postId, '_snowdraft_week_number', (int)$data['week_number']);
        if (isset($data['team_ids'])) Helpers::update_json_meta($postId, '_snowdraft_team_ids', (array)$data['team_ids']);
        if (isset($data['user_ids'])) Helpers::update_json_meta($postId, '_snowdraft_user_ids', (array)$data['user_ids']);
        update_post_meta($postId, '_snowdraft_status', 'scheduled');
        return new WP_REST_Response(['id' => (int)$postId], 201);
    }

    public function assign_movie(WP_REST_Request $req): WP_REST_Response
    {
        $id = (int)$req['id'];
        $matchup = $this->matchups->get($id);
        if (!$matchup) return new WP_REST_Response(['error' => 'not_found'], 404);
        $leagueId = (int) get_post_meta($id, '_snowdraft_league_id', true);

        // naive: pick any random movie not used in this league yet
        $used = [];
        $used_q = new \WP_Query([
            'post_type' => 'snowdraft_matchup',
            'post_status' => 'any',
            'meta_query' => [
                ['key' => '_snowdraft_league_id', 'value' => $leagueId, 'compare' => '='],
                ['key' => '_snowdraft_movie_id', 'compare' => 'EXISTS']
            ],
            'fields' => 'ids',
            'nopaging' => true,
        ]);
        foreach ($used_q->posts as $mid) {
            $m = (int) get_post_meta($mid, '_snowdraft_movie_id', true);
            if ($m) $used[] = $m;
        }
        $args = [
            'post_type' => 'snowdraft_movie',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'post__not_in' => $used,
        ];
        $q = new \WP_Query($args);
        if (empty($q->posts)) return new WP_REST_Response(['error' => 'no_unused_movies'], 400);
        $movie = $q->posts[0];
        update_post_meta($id, '_snowdraft_movie_id', $movie->ID);
        update_post_meta($id, '_snowdraft_assigned_at', gmdate('c'));
        return new WP_REST_Response(['matchupId' => $id, 'movieId' => (int)$movie->ID], 200);
    }

    public function list(WP_REST_Request $req): WP_REST_Response
    {
        $leagueId = (int)($req->get_param('league') ?? 0);
        $week = (int)($req->get_param('week') ?? 0);
        $meta_query = [];
        if ($leagueId) $meta_query[] = ['key' => '_snowdraft_league_id', 'value' => $leagueId, 'compare' => '='];
        if ($week) $meta_query[] = ['key' => '_snowdraft_week_number', 'value' => $week, 'compare' => '='];
        $q = new \WP_Query([
            'post_type' => 'snowdraft_matchup',
            'post_status' => 'any',
            'meta_query' => $meta_query,
            'nopaging' => true,
        ]);
        $items = [];
        foreach ($q->posts as $p) {
            $items[] = [
                'id' => $p->ID,
                'title' => get_the_title($p),
                'league_id' => (int) get_post_meta($p->ID, '_snowdraft_league_id', true),
                'week' => (int) get_post_meta($p->ID, '_snowdraft_week_number', true),
                'movie_id' => (int) get_post_meta($p->ID, '_snowdraft_movie_id', true),
                'status' => (string) get_post_meta($p->ID, '_snowdraft_status', true),
            ];
        }
        return new WP_REST_Response(['items' => $items], 200);
    }
}
