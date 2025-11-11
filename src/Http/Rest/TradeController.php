<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest;

use RXNLabs\MistletoeMatchupFantasyDraft\Service\TradeService;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Nonces;
use WP_REST_Request;
use WP_REST_Response;

class TradeController
{
    public function __construct(
        private TradeService $trades,
        private Nonces $nonces
    ) {}

    public function register_routes(): void
    {
        register_rest_route('snowdraft/v1', '/trades', [
            'methods' => 'POST',
            'callback' => [$this, 'create'],
            'permission_callback' => [$this, 'can_edit'],
        ]);

        register_rest_route('snowdraft/v1', '/trades/(?P<id>\d+)/(accept|reject|withdraw|counter)', [
            'methods' => 'POST',
            'callback' => [$this, 'transition'],
            'permission_callback' => [$this, 'can_edit'],
        ]);
    }

    public function can_edit(WP_REST_Request $req): bool
    {
        $nonce = $req->get_header('X-WP-Nonce');
        if (!$this->nonces->verify((string)$nonce)) return false;
        return current_user_can('edit_snowdraft_trade');
    }

    public function create(WP_REST_Request $req): WP_REST_Response
    {
        $data = $req->get_json_params() ?: [];
        $id = $this->trades->create($data);
        if ($id <= 0) return new WP_REST_Response(['error' => 'failed'], 500);
        return new WP_REST_Response(['id' => $id], 201);
    }

    public function transition(WP_REST_Request $req): WP_REST_Response
    {
        $id = (int)$req['id'];
        $action = $req->get_route_params()['action'] ?? null;
        if (!$action) {
            // Extract from matched regex in route path
            $path = $req->get_route();
            if (preg_match('#/trades/\d+/(accept|reject|withdraw|counter)$#', $path, $m)) {
                $action = $m[1];
            }
        }
        $map = [
            'accept' => 'accepted',
            'reject' => 'rejected',
            'withdraw' => 'withdrawn',
            'counter' => 'countered',
        ];
        $to = $map[$action] ?? '';
        if (!$to) return new WP_REST_Response(['error' => 'invalid'], 400);
        $ok = $this->trades->transition($id, $to);
        return new WP_REST_Response(['ok' => (bool)$ok, 'status' => $to], $ok ? 200 : 400);
    }
}
