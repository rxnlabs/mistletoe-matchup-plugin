<?php
/**
 * Plugin Name:       Mistletoe Matchup Fantasy Draft
 * Plugin URI:        https://rxnlabs.com/mistletoe-matchup-fantasy-draft
 * Description:       A joyful fantasy draft game for made for TV holiday movies. Create leagues, draft holiday movie moments, and compete with friends for festive glory!.
 * Version:           1.0.0
 * Author:            RXN Labs
 * Author URI:        https://rxnlabs.com
 * Text Domain:       mistletoe-matchup-fantasy-draft
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package RXNLabs\MistletoeMatchupFantasyDraft
 */

namespace RXNLabs\MistletoeMatchupFantasyDraft;

use League\Container\Container;

if (!defined('ABSPATH')) exit;

require __DIR__ . '/vendor/autoload.php';

define('MISTLETOE_MATCHUP_FANTASY_DRAFT_VERSION', '1.0.0');

$container = new Container();

$container->add(Config::class, new Config(__FILE__));
$container->add(Assets\Enqueue::class)->addArguments([Config::class]);

$container->add(Register\Cpts::class)->addArguments([Config::class]);
$container->add(Register\Roles::class);

$container->add(Service\PusherService::class)->addArguments([Config::class]);
$container->add(Service\DraftService::class)
  ->addArguments([Service\PusherService::class, Repository\DraftRepo::class, Repository\LeagueRepo::class, Repository\TeamRepo::class, Repository\TropeRepo::class]);

$container->add(Service\TradeService::class)
  ->addArguments([Repository\TradeRepo::class, Repository\TropeRepo::class, Util\Helpers::class]);

$container->add(Repository\LeagueRepo::class);
$container->add(Repository\TeamRepo::class);
$container->add(Repository\TropeRepo::class);
$container->add(Repository\TradeRepo::class);
$container->add(Repository\MovieRepo::class);
$container->add(Repository\MatchupRepo::class);
$container->add(Repository\DraftRepo::class);

$container->add(Http\Nonces::class);

$container->add(Http\Rest\DraftController::class)
  ->addArguments([Service\DraftService::class, Service\PusherService::class, Http\Nonces::class]);

$container->add(Http\Rest\TradeController::class)
  ->addArguments([Service\TradeService::class, Http\Nonces::class]);

$container->add(Http\Rest\MoviesController::class)
  ->addArguments([Repository\MovieRepo::class, Http\Nonces::class]);

$container->add(Http\Rest\MatchupsController::class)
  ->addArguments([Repository\MatchupRepo::class, Repository\MovieRepo::class, Http\Nonces::class]);

$plugin = new Plugin(
  $container->get(Register\Roles::class),
  $container->get(Register\Cpts::class),
  $container->get(Assets\Enqueue::class),
  $container->get(Http\Rest\DraftController::class),
  $container->get(Http\Rest\TradeController::class),
  $container->get(Http\Rest\MoviesController::class),
  $container->get(Http\Rest\MatchupsController::class)
);
$plugin->init();

