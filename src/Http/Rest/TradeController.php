<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest;

use RXNLabs\MistletoeMatchupFantasyDraft\Service\TradeService;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Nonce;
use WP_REST_Request;
use WP_REST_Response;

class TradeController {

	public function __construct(
		private TradeService $trade,
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
			'/trades',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create' ),
				'permission_callback' => array( $this, 'can_edit' ),
			)
		);

		register_rest_route(
			'snowdraft/v1',
			'/trades/(?P<id>\d+)/(accept|reject|withdraw|counter)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'transition' ),
				'permission_callback' => array( $this, 'can_edit' ),
			)
		);
	}

	public function can_edit( WP_REST_Request $req ): bool {
		$nonce = $req->get_header( 'X-WP-Nonce' );
		if ( ! $this->nonce->verify( (string) $nonce ) ) {
			return false;
		}
		return current_user_can( 'edit_snowdraft_trade' );
	}

	public function create( WP_REST_Request $req ): WP_REST_Response {
		$data = $req->get_json_params() ?: array();
		$id   = $this->trade->create( $data );
		if ( $id <= 0 ) {
			return new WP_REST_Response( array( 'error' => 'failed' ), 500 );
		}
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function transition( WP_REST_Request $req ): WP_REST_Response {
		$id     = (int) $req['id'];
		$action = $req->get_route_params()['action'] ?? null;
		if ( ! $action ) {
			// Extract from matched regex in route path
			$path = $req->get_route();
			if ( preg_match( '#/trades/\d+/(accept|reject|withdraw|counter)$#', $path, $m ) ) {
				$action = $m[1];
			}
		}
		$map = array(
			'accept'   => 'accepted',
			'reject'   => 'rejected',
			'withdraw' => 'withdrawn',
			'counter'  => 'countered',
		);
		$to  = $map[ $action ] ?? '';
		if ( ! $to ) {
			return new WP_REST_Response( array( 'error' => 'invalid' ), 400 );
		}
		$ok = $this->trade->transition( $id, $to );
		return new WP_REST_Response(
			array(
				'ok'     => (bool) $ok,
				'status' => $to,
			),
			$ok ? 200 : 400
		);
	}
}
