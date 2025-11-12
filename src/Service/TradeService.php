<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Service;

use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\Trade;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Helpers;

class TradeService {

	public function __construct( private Helpers $helpers, private Trade $trade ) {
	}

	public function create( array $data ): int {
		$postId = wp_insert_post(
			array(
				'post_type'    => $this->trade->get_slug(),
				'post_status'  => 'publish',
				'post_title'   => $data['title'] ?? __( 'Trade', 'mistletoe-matchup-fantasy-draft' ),
				'post_content' => $data['content'] ?? '',
			),
			true
		);
		if ( is_wp_error( $postId ) ) {
			return 0;
		}
		update_post_meta( $postId, '_snowdraft_trade_status', 'proposed' );
		if ( isset( $data['tropes_out'] ) ) {
			Helpers::update_json_meta( $postId, '_snowdraft_trade_tropes_out', (array) $data['tropes_out'] );
		}
		if ( isset( $data['tropes_in'] ) ) {
			Helpers::update_json_meta( $postId, '_snowdraft_trade_tropes_in', (array) $data['tropes_in'] );
		}
		if ( isset( $data['expires_at'] ) ) {
			update_post_meta( $postId, '_snowdraft_trade_expires_at', (string) $data['expires_at'] );
		}
		return (int) $postId;
	}

	public function transition( int $tradeId, string $to ): bool {
		$allowed = array( 'proposed', 'accepted', 'rejected', 'withdrawn', 'executed', 'expired', 'countered', 'commissioner_pending' );
		if ( ! in_array( $to, $allowed, true ) ) {
			return false;
		}
		return (bool) update_post_meta( $tradeId, '_snowdraft_trade_status', $to );
	}
}
