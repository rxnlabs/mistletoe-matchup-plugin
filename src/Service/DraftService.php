<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Service;

use RXNLabs\MistletoeMatchupFantasyDraft\Repository\DraftRepo;

class DraftService
{
    public function __construct(
        private PusherService $pusher,
        private DraftRepo $draftRepo,
        private \RXNLabs\MistletoeMatchupFantasyDraft\Repository\LeagueRepo $leagueRepo,
        private \RXNLabs\MistletoeMatchupFantasyDraft\Repository\TeamRepo $teamRepo,
        private \RXNLabs\MistletoeMatchupFantasyDraft\Repository\TropeRepo $tropeRepo,
    ) {}

    public function start(int $leagueId): array
    {
        $channel = 'private-snowdraft-league-' . $leagueId;
        $payload = ['status' => 'started', 'leagueId' => $leagueId, 'ts' => time()];
        $this->pusher->trigger($channel, 'draft.started', $payload);
        return $payload;
    }

    public function makePick(int $leagueId, int $teamId, int $tropeId): array
    {
        $channel = 'private-snowdraft-league-' . $leagueId;
        $payload = ['leagueId' => $leagueId, 'teamId' => $teamId, 'tropeId' => $tropeId, 'ts' => time()];
        $this->pusher->trigger($channel, 'pick.made', $payload);
        return $payload;
    }
}
