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
 * Requires Plugins: groups
 *
 * @package RXNLabs\MistletoeMatchupFantasyDraft
 */
declare( strict_types=1 );
namespace RXNLabs\MistletoeMatchupFantasyDraft;

if ( ! defined( 'ABSPATH' ) || ! file_exists( __DIR__ . '/vendor/autoload.php' ) || defined( 'PHP_UNIT_TESTS_RUNNING' ) ) {
	exit;
}

use League\Container\Container;
use League\Container\ReflectionContainer;

require __DIR__ . '/vendor/autoload.php';

define( 'MISTLETOE_MATCHUP_FANTASY_DRAFT_VERSION', '1.0.0' );

function init(): null|Container {
	static $di_container = null;

	if ( null === $di_container ) {
		$di_container = new Container();
		$di_container->delegate( new ReflectionContainer( cacheResolutions: true ) );

		try {

			$di_container->get( $di_container->add( 'config', Config::class )->addArgument( __FILE__ )->getAlias() );
			$di_container->get( $di_container->add( 'utils_helper', Util\Helpers::class )->getAlias() );

			$di_container->get(
				$di_container->add( 'enqueue', Assets\Enqueue::class )->addArguments(
					array(
						$di_container->get( 'config' ),
					)
				)->getAlias()
			);

			$di_container->get( $di_container->add( 'role_league_commissioner', Roles\League_Commissioner::class )->getAlias() );
			$di_container->get( $di_container->add( 'post_type_trope', PostTypes\Trope::class )->getAlias() );
			$di_container->get( $di_container->add( 'post_type_movie', PostTypes\Movie::class )->getAlias() );
			$di_container->get( $di_container->add( 'post_type_league', PostTypes\League::class )->getAlias() );
			$di_container->get( $di_container->add( 'post_type_team', PostTypes\Team::class )->getAlias() );
			$di_container->get( $di_container->add( 'post_type_matchup', PostTypes\Matchup::class )->getAlias() );
			$di_container->get( $di_container->add( 'post_type_trade', PostTypes\Trade::class )->getAlias() );

			$di_container->get(
				$di_container->add( 'taxonomy_trope_category', Taxonomy\TropeCategory::class )->addArguments(
					array(
						$di_container->get( 'post_type_trope' ),
					)
				)->getAlias()
			);

			$di_container->get( $di_container->add( 'nonce', Http\Nonce::class )->getAlias() );

			$di_container->get(
				$di_container->add( 'service_pusher', Service\PusherService::class )->addArguments(
					array(
						$di_container->get( 'config' ),
					)
				)->getAlias()
			);

			$di_container->get(
				$di_container->add( 'service_draft', Service\DraftService::class )->addArguments(
					array(
						$di_container->get( 'service_pusher' ),
					)
				)->getAlias()
			);

			$di_container->get(
				$di_container->add( 'service_trade', Service\TradeService::class )->addArguments(
					array(
						$di_container->get( 'utils_helper' ),
						$di_container->get( 'post_type_trade' ),
					)
				)->getAlias()
			);

			$di_container->get(
				$di_container->add( 'controller_movie', Http\Rest\MovieController::class )->addArguments(
					array(
						$di_container->get( 'nonce' ),
					)
				)->getAlias()
			);
			$di_container->get(
				$di_container->add( 'controller_draft', Http\Rest\DraftController::class )->addArguments(
					array(
						$di_container->get( 'service_draft' ),
						$di_container->get( 'service_pusher' ),
						$di_container->get( 'nonce' ),
					)
				)->getAlias()
			);

			$di_container->get(
				$di_container->add( 'controller_matchup', Http\Rest\MatchupController::class )->addArguments(
					array(
						$di_container->get( 'post_type_matchup' ),
						$di_container->get( 'post_type_movie' ),
						$di_container->get( 'nonce' ),
					)
				)->getAlias()
			);

			$di_container->get(
				$di_container->add( 'controller_trade', Http\Rest\TradeController::class )->addArguments(
					array(
						$di_container->get( 'service_trade' ),
						$di_container->get( 'nonce' ),
					)
				)->getAlias()
			);

		} catch ( \Throwable $e ) {
			error_log( $e->getMessage() );
		}
	}

	return $di_container;
}

init();
