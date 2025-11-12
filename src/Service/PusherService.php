<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Service;

use RXNLabs\MistletoeMatchupFantasyDraft\Config;
use Pusher\Pusher;

class PusherService {

	private Pusher $pusher;

	public function __construct( private Config $config ) {
		$pc           = $config->pusherConfig();
		$this->pusher = new Pusher(
			$pc['key'] ?? '',
			$pc['secret'] ?? '',
			$pc['app_id'] ?? '',
			array(
				'cluster' => $pc['cluster'] ?? 'us2',
				'useTLS'  => true,
			)
		);
	}

	public function client(): Pusher {
		return $this->pusher;
	}

	public function trigger( string $channel, string $event, array $payload = array() ): void {
		$this->pusher->trigger( $channel, $event, $payload );
	}
}
