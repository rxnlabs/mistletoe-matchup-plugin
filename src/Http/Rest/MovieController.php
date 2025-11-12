<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest;

use RXNLabs\MistletoeMatchupFantasyDraft\Http\Nonce;
use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\Movie;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Share;
use WP_REST_Request;
use WP_REST_Response;

class MovieController {
	use Share;
	public function __construct(
		private Nonce $nonces,
		private Movie $movie
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
			'/movies',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'list' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_or_update' ),
					'permission_callback' => array( $this, 'can_edit_movies' ),
				),
			)
		);
	}

	public function can_edit_movies( WP_REST_Request $req ): bool {
		$nonce = $req->get_header( 'X-WP-Nonce' );
		if ( ! $this->nonces->verify( (string) $nonce ) ) {
			return false;
		}
		return current_user_can( 'manage_snowdraft_movies' ) || current_user_can( 'edit_snowdraft_movie' );
	}

	public function list( WP_REST_Request $req ): WP_REST_Response {
		$s     = sanitize_text_field( (string) ( $req->get_param( 's' ) ?? '' ) );
		$args  = array(
			'post_type'      => $this->movie->get_slug(),
			'post_status'    => 'any',
			's'              => $s,
			'posts_per_page' => 50,
			'no_found_rows'  => true,
		);
		$q     = new \WP_Query( $args );
		$items = array();
		foreach ( $q->posts as $p ) {
			$items[] = array(
				'id'    => $p->ID,
				'title' => get_the_title( $p ),
				'year'  => (int) get_post_meta( $p->ID, '_snowdraft_movie_year', true ),
			);
		}
		return new WP_REST_Response( array( 'items' => $items ), 200 );
	}

	public function create_or_update( WP_REST_Request $req ): WP_REST_Response {
		$data    = $req->get_json_params() ?: array();
		$id      = (int) ( $data['id'] ?? 0 );
		$postarr = array(
			'post_type'    => $this->movie->get_slug(),
			'post_status'  => 'publish',
			'post_title'   => (string) ( $data['title'] ?? '' ),
			'post_content' => (string) ( $data['description'] ?? '' ),
		);
		$postId  = $id > 0 ? wp_update_post( array( 'ID' => $id ) + $postarr, true ) : wp_insert_post( $postarr, true );
		if ( is_wp_error( $postId ) ) {
			return new WP_REST_Response( array( 'error' => 'failed' ), 500 );
		}
		if ( isset( $data['year'] ) ) {
			update_post_meta( $postId, '_snowdraft_movie_year', (int) $data['year'] );
		}
		if ( isset( $data['channels'] ) ) {
			\RXNLabs\MistletoeMatchupFantasyDraft\Util\Helpers::update_json_meta( $postId, '_snowdraft_release_channels', (array) $data['channels'] );
		}
		return new WP_REST_Response( array( 'id' => (int) $postId ), $id > 0 ? 200 : 201 );
	}
}
