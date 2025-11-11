<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest;

use RXNLabs\MistletoeMatchupFantasyDraft\Service\DraftService;
use RXNLabs\MistletoeMatchupFantasyDraft\Service\PusherService;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Nonces;
use WP_REST_Request;
use WP_REST_Response;

class DraftController
{
    public function __construct(
        private DraftService $draft,
        private PusherService $pusher,
        private Nonces $nonces
    ) {}

    public function register_routes(): void
    {
        register_rest_route('snowdraft/v1', '/draft/(?P<league>\d+)/action', [
            'methods' => 'POST',
            'callback' => [$this, 'action'],
            'permission_callback' => [$this, 'can_manage_league']
        ]);

        register_rest_route('snowdraft/v1', '/pusher/auth', [
            'methods' => 'POST',
            'callback' => [$this, 'pusher_auth'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function can_manage_league(WP_REST_Request $req): bool
    {
        // Basic guard: require nonce
        $nonce = $req->get_header('X-WP-Nonce');
        if (!$this->nonces->verify((string)$nonce)) return false;
        return current_user_can('manage_snowdraft_leagues');
    }

    public function action(WP_REST_Request $req): WP_REST_Response
    {
        $leagueId = (int) $req['league'];
        $body = $req->get_json_params();
        $type = (string)($body['type'] ?? '');
        switch ($type) {
            case 'start':
                $result = $this->draft->start($leagueId);
                break;
            case 'make-pick':
                $result = $this->draft->makePick($leagueId, (int)($body['teamId'] ?? 0), (int)($body['tropeId'] ?? 0));
                break;
            default:
                $result = ['ok' => true, 'message' => 'noop'];
        }
        return new WP_REST_Response($result, 200);
    }

    public function pusher_auth(WP_REST_Request $req): WP_REST_Response
    {
        $socketId = (string)$req->get_param('socket_id');
        $channelName = (string)$req->get_param('channel_name');
        if (!$socketId || !$channelName) {
            return new WP_REST_Response(['error' => 'missing params'], 400);
        }
        // Minimal check: logged-in user
        if (!is_user_logged_in()) {
            return new WP_REST_Response(['error' => 'unauthorized'], 403);
        }
        $auth = $this->pusher->client()->authorizeChannel($channelName, $socketId);
        return new WP_REST_Response($auth, 200);
    }
}
