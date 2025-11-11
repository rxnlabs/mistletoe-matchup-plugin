<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft;

use RXNLabs\MistletoeMatchupFantasyDraft\Register\Roles;
use RXNLabs\MistletoeMatchupFantasyDraft\Register\Cpts;
use RXNLabs\MistletoeMatchupFantasyDraft\Assets\Enqueue;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest\DraftController;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest\TradeController;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest\MoviesController;
use RXNLabs\MistletoeMatchupFantasyDraft\Http\Rest\MatchupsController;

class Plugin
{
    public function __construct(
        private Roles $roles,
        private Cpts $cpts,
        private Enqueue $enqueue,
        private DraftController $draftController,
        private TradeController $tradeController,
        private MoviesController $moviesController,
        private MatchupsController $matchupsController,
    ) {}

    public function init(): void
    {
        add_action('init', [$this->roles, 'register']);
        add_action('init', [$this->cpts, 'register']);
        add_action('init', [$this->cpts, 'register_meta']);
        add_action('rest_api_init', [$this->draftController, 'register_routes']);
        add_action('rest_api_init', [$this->tradeController, 'register_routes']);
        add_action('rest_api_init', [$this->moviesController, 'register_routes']);
        add_action('rest_api_init', [$this->matchupsController, 'register_routes']);
        add_action('admin_enqueue_scripts', [$this->enqueue, 'admin']);
        add_action('wp_enqueue_scripts', [$this->enqueue, 'public']);
    }
}
