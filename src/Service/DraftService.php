<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Service;

use RXNLabs\MistletoeMatchupFantasyDraft\Repository\DraftRepo;

class DraftService {

	public function __construct(
		private PusherService $pusher
	) {}

	public function start( int $leagueId ): array {
		$channel = 'private-snowdraft-league-' . $leagueId;
		$payload = array(
			'status'   => 'started',
			'leagueId' => $leagueId,
			'ts'       => time(),
		);
		$this->pusher->trigger( $channel, 'draft.started', $payload );
		return $payload;
	}

	public function makePick( int $leagueId, int $teamId, int $tropeId ): array {
		$channel = 'private-snowdraft-league-' . $leagueId;
		$payload = array(
			'leagueId' => $leagueId,
			'teamId'   => $teamId,
			'tropeId'  => $tropeId,
			'ts'       => time(),
		);
		$this->pusher->trigger( $channel, 'pick.made', $payload );
		return $payload;
	}
}
