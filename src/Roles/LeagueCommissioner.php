<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Roles;

class League_Commissioner {

	public $slug = 'snowdraft_league_commissioner';

	public function __construct() {
		$this->hooks();
	}

	public function get_role(): string {
		return $this->slug;
	}

	private function hooks(): void {
		$this->core_hooks();
		$this->vendor_hooks();
	}

	private function core_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	private function vendor_hooks(): void {
	}

	public static function caps(): array {
		$caps = array(
			'manage_snowdraft_leagues',
			'edit_snowdraft_league',
			'read_snowdraft_league',
			'manage_snowdraft_teams',
			'edit_snowdraft_team',
			'read_snowdraft_team',
			'manage_snowdraft_tropes',
			'edit_snowdraft_trope',
			'read_snowdraft_trope',
			'manage_snowdraft_trades',
			'edit_snowdraft_trade',
			'read_snowdraft_trade',
			'manage_snowdraft_movies',
			'edit_snowdraft_movie',
			'read_snowdraft_movie',
			'manage_snowdraft_matchups',
			'edit_snowdraft_matchup',
			'read_snowdraft_matchup',
		);
		return apply_filters( 'snowdraft_caps', $caps );
	}

	public function register(): void {
		if ( ! get_role( $this->get_role() ) ) {
			add_role( $this->get_role(), __( 'League Commissioner', 'mistletoe-matchup-fantasy-draft' ) );
		}

		$manager = get_role( $this->get_role() );
		$admin   = get_role( 'administrator' );

		foreach ( self::caps() as $cap ) {
			if ( $admin && ! $admin->has_cap( $cap ) ) {
				$admin->add_cap( $cap );
			}
			if ( $manager && ! in_array(
				$cap,
				array(
					'manage_snowdraft_trades',
					'manage_snowdraft_movies',
					'manage_snowdraft_matchups',
				),
				true
			) ) {
				$manager->add_cap( $cap );
			}
		}
	}
}
