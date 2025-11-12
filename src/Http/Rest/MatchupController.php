<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest;

use RXNLabs\MistletoeMatchupFantasyDraft\Http\Nonce;
use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\Matchup;
use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\Movie;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Helpers;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Share;
use WP_REST_Request;
use WP_REST_Response;

class MatchupController {
	use Share;
	public function __construct(
		private Matchup $matchup,
		private Movie $movie,
		private Nonce $nonce
	) {
		$this->hooks();
	}

	private function hooks(): void {
		$this->core_hooks();
	}

	private function core_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'snowdraft/v1',
			'/matchups',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create' ),
					'permission_callback' => array( $this, 'can_manage_matchups' ),
				),
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'list' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			'snowdraft/v1',
			'/matchups/(?P<id>\d+)/assign-movie',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'assign_movie' ),
				'permission_callback' => array( $this, 'can_manage_matchups' ),
			)
		);
	}

	public function can_manage_matchups( WP_REST_Request $req ): bool {
		$nonce = $req->get_header( 'X-WP-Nonce' );
		if ( ! $this->nonce->verify( (string) $nonce ) ) {
			return false;
		}
		return current_user_can( 'manage_snowdraft_matchups' ) || current_user_can( 'edit_snowdraft_matchup' );
	}

	public function create( WP_REST_Request $req ): WP_REST_Response {
		$data     = $req->get_json_params() ?: array();
		$leagueId = (int) ( $data['league_id'] ?? 0 );
		$title    = $data['title'] ?? 'Matchup';
		$postId   = wp_insert_post(
			array(
				'post_type'   => $this->matchup->get_slug(),
				'post_status' => 'publish',
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $postId ) ) {
			return new WP_REST_Response( array( 'error' => 'failed' ), 500 );
		}
		update_post_meta( $postId, '_snowdraft_league_id', $leagueId );
		if ( isset( $data['week_number'] ) ) {
			update_post_meta( $postId, '_snowdraft_week_number', (int) $data['week_number'] );
		}
		if ( isset( $data['team_ids'] ) ) {
			Helpers::update_json_meta( $postId, '_snowdraft_team_ids', (array) $data['team_ids'] );
		}
		if ( isset( $data['user_ids'] ) ) {
			Helpers::update_json_meta( $postId, '_snowdraft_user_ids', (array) $data['user_ids'] );
		}
		update_post_meta( $postId, '_snowdraft_status', 'scheduled' );
		return new WP_REST_Response( array( 'id' => (int) $postId ), 201 );
	}

	public function assign_movie( WP_REST_Request $req ): WP_REST_Response {
		$id      = (int) $req['id'];
		$matchup = $this->matchup->is_post_type( $id );
		if ( ! $matchup ) {
			return new WP_REST_Response(
				array(
					'error'   => 'not_found',
					'message' => __( 'Cannot locate matchup', 'mistletoe-matchup-fantasy-draft' ),
				),
				404
			);
		}
		$leagueId = (int) get_post_meta( $id, '_snowdraft_league_id', true );

		// naive: pick any random movie not used in this league yet
		$used   = array();
		$used_q = new \WP_Query(
			array(
				'post_type'              => $this->matchup->get_slug(),
				'post_status'            => 'any',
				'meta_query'             => array(
					array(
						'key'     => '_snowdraft_league_id',
						'value'   => $leagueId,
						'compare' => '=',
					),
					array(
						'key'     => '_snowdraft_movie_id',
						'compare' => 'EXISTS',
					),
				),
				'fields'                 => 'ids',
				'nopaging'               => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);
		foreach ( $used_q->posts as $mid ) {
			$m = (int) get_post_meta( $mid, '_snowdraft_movie_id', true );
			if ( $m ) {
				$used[] = $m;
			}
		}
		$args = array(
			'post_type'      => $this->movie->get_slug(),
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'post__not_in'   => $used,
		);
		$q    = new \WP_Query( $args );
		if ( empty( $q->posts ) ) {
			return new WP_REST_Response( array( 'error' => 'no_unused_movies' ), 400 );
		}
		$movie = $q->posts[0];
		update_post_meta( $id, '_snowdraft_movie_id', $movie->ID );
		update_post_meta( $id, '_snowdraft_assigned_at', gmdate( 'c' ) );
		return new WP_REST_Response(
			array(
				'matchupId' => $id,
				'movieId'   => (int) $movie->ID,
			),
			200
		);
	}

	public function list( WP_REST_Request $req ): WP_REST_Response {
		$leagueId   = (int) ( $req->get_param( 'league' ) ?? 0 );
		$week       = (int) ( $req->get_param( 'week' ) ?? 0 );
		$meta_query = array();
		if ( $leagueId ) {
			$meta_query[] = array(
				'key'     => '_snowdraft_league_id',
				'value'   => $leagueId,
				'compare' => '=',
			);
		}
		if ( $week ) {
			$meta_query[] = array(
				'key'     => '_snowdraft_week_number',
				'value'   => $week,
				'compare' => '=',
			);
		}
		$q     = new \WP_Query(
			array(
				'post_type'   => $this->matchup->get_slug(),
				'post_status' => 'any',
				'meta_query'  => $meta_query,
				'nopaging'    => true,
			)
		);
		$items = array();
		foreach ( $q->posts as $p ) {
			$items[] = array(
				'id'        => $p->ID,
				'title'     => get_the_title( $p ),
				'league_id' => (int) get_post_meta( $p->ID, '_snowdraft_league_id', true ),
				'week'      => (int) get_post_meta( $p->ID, '_snowdraft_week_number', true ),
				'movie_id'  => (int) get_post_meta( $p->ID, '_snowdraft_movie_id', true ),
				'status'    => (string) get_post_meta( $p->ID, '_snowdraft_status', true ),
			);
		}
		return new WP_REST_Response( array( 'items' => $items ), 200 );
	}
}
