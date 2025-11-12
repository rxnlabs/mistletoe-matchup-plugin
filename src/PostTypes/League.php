<?php
declare(strict_types=1);
namespace RXNLabs\MistletoeMatchupFantasyDraft\PostTypes;

use RXNLabs\MistletoeMatchupFantasyDraft\Util\Share;

class League {
	use Share;

	private $slug = 'snowdraft_league';

	public function __construct() {
		$this->hooks();
	}

	private function hooks(): void {
		$this->core_hooks();
	}

	private function core_hooks(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_filter( 'comments_open', array( $this, 'comments_policy' ), 10, 2 );
		add_filter( 'pings_open', array( $this, 'comments_policy' ), 10, 2 );
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function register_meta(): void {
		// Leagues
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_draft_type',
			array(
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_roster_size',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_bench_size',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_pick_timer',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_bid_timer',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_auction_budget',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_winner_policy',
			array(
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_is_commissioner_playing',
			array(
				'type'         => 'boolean',
				'single'       => true,
				'default'      => false,
				'show_in_rest' => true,
				'description'  => 'Whether the commissioner is playing in the league',
			)
		);
	}

	public function register_post_type(): void {
		register_post_type(
			$this->get_slug(),
			array(
				'labels'                => array(
					'name'                  => __( 'Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'singular_name'         => __( 'Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'all_items'             => __( 'All Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'archives'              => __( 'Leagues Archives', 'mistletoe-matchup-fantasy-draft' ),
					'attributes'            => __( 'Leagues Attributes', 'mistletoe-matchup-fantasy-draft' ),
					'insert_into_item'      => __( 'Insert into Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'uploaded_to_this_item' => __( 'Uploaded to this Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'featured_image'        => _x( 'Featured Image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'set_featured_image'    => _x( 'Set featured image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'remove_featured_image' => _x( 'Remove featured image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'use_featured_image'    => _x( 'Use as featured image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'filter_items_list'     => __( 'Filter Leagues list', 'mistletoe-matchup-fantasy-draft' ),
					'items_list_navigation' => __( 'Leagues list navigation', 'mistletoe-matchup-fantasy-draft' ),
					'items_list'            => __( 'Leagues list', 'mistletoe-matchup-fantasy-draft' ),
					'new_item'              => __( 'New Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'add_new'               => __( 'Add New', 'mistletoe-matchup-fantasy-draft' ),
					'add_new_item'          => __( 'Add New Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'edit_item'             => __( 'Edit Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'view_item'             => __( 'View Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'view_items'            => __( 'View Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'search_items'          => __( 'Search Leagues', 'mistletoe-matchup-fantasy-draft' ),
					'not_found'             => __( 'No Leagues found', 'mistletoe-matchup-fantasy-draft' ),
					'not_found_in_trash'    => __( 'No Leagues found in trash', 'mistletoe-matchup-fantasy-draft' ),
					'parent_item_colon'     => __( 'Parent Leagues:', 'mistletoe-matchup-fantasy-draft' ),
					'menu_name'             => __( 'Leagues', 'mistletoe-matchup-fantasy-draft' ),
				),
				'public'                => true,
				'hierarchical'          => false,
				'show_ui'               => true,
				'show_in_nav_menus'     => true,
				'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'comments' ),
				'has_archive'           => false,
				'rewrite'               => true,
				'query_var'             => true,
				'menu_position'         => null,
				'menu_icon'             => 'dashicons-groups',
				'show_in_rest'          => true,
				'rest_base'             => $this->get_slug(),
				'rest_controller_class' => 'WP_REST_Posts_Controller',
			)
		);
	}

	public function comments_policy( $open, $post_id ): bool {
		return true;
	}

	public function is_post_type( $id = 0 ): bool {
		$post_id = $id ?: get_the_ID();
		return get_post_type( $post_id ) === $this->get_slug();
	}

	/**
	 * Creates a new league with the given parameters and associates it with a group if the groups plugin is active.
	 *
	 * @param string $name The name of the league to create.
	 * @param string $description Optional. The description of the league. Defaults to an empty string.
	 * @param int $league_commissioner Optional. The ID of the user managing the league. If not provided, the current logged-in user ID will be used. Defaults to 0.
	 *
	 * @return bool True if the league and associated group were successfully created, false otherwise.
	 */
	public function create_league( $name, $description = '', $league_commissioner = 0 ): bool|array {
		$user_id           = $league_commissioner ?: get_current_user_id();
		$clean_description = wp_strip_all_tags( $description );
		$post_id           = wp_insert_post(
			array(
				'post_type'    => $this->get_slug(),
				'post_title'   => $name,
				'post_content' => $clean_description,
				'post_status'  => 'publish',
				'post_author'  => $user_id,
			)
		);

		if ( ! empty( $post_id ) && ! is_wp_error( $post_id ) && $this->is_groups_plugin_active() ) {
			$group_id = \Groups_Group::create(
				array(
					'name'        => $name,
					'description' => $clean_description,
					'creator_id'  => $user_id,
				)
			);

			if ( ! empty( $group_id ) ) {
				\Groups_Post_Access::create(
					array(
						'group_id' => $group_id,
						'post_id'  => $post_id,
					)
				);
			}

			// Group ID is a string, so we need to convert it to an integer.
			if ( is_numeric( $group_id ) ) {
				$group_id = (int) $group_id;
				return array(
					'league_post_id' => $post_id,
					'league_id'      => $group_id,
				);
			}
		}

		return false;
	}

	/**
	 * Adds a player to a specified league.
	 *
	 * This method attempts to add a player to the given league by updating membership data.
	 * It also handles logic for league commissioners who wish to participate in the league
	 * as players and updates relevant league metadata accordingly.
	 *
	 * @param int $player_id The ID of the player to be added to the league.
	 * @param int $league_id The ID of the league to which the player is being added.
	 * @param int $league_post_id (Optional) The post ID representing the league; required if the commissioner is playing.
	 *
	 * @return bool|int Returns false if the operation fails, or a truthy value indicating successful addition.
	 */
	public function add_player_to_league( $player_id, $league_id, $league_post_id = 0 ): bool|int {
		if ( ! $this->is_groups_plugin_active() ) {
			return false;
		}

		// Add a player to the league. This will return false if the user is already a member of the league, including if they are the creator of the league.
		$result = \Groups_User_Group::create(
			array(
				'user_id'  => $player_id,
				'group_id' => $league_id,
			)
		);

		// We need to check if this user is the commissioner of the league and if they want to be added as a player.
		if ( true === $result && $this->is_league_commissioner( $league_id, $player_id ) && ! empty( $league_post_id ) ) {
			$result = update_post_meta( $league_post_id, '_snowdraft_is_commissioner_playing', 1 );
		}

		return $result;
	}

	/**
	 * Checks if the league commissioner is participating as a player in the league.
	 *
	 * This method retrieves metadata for the specified league to determine if the commissioner
	 * has opted to play in the league as a participant.
	 *
	 * @param int $league_id The ID of the league to check for commissioner participation.
	 *
	 * @return bool Returns true if the commissioner is playing, otherwise false.
	 */
	public function is_commissioner_playing( $league_id ): bool {
		return (bool) get_post_meta( $league_id, '_snowdraft_is_commissioner_playing', true );
	}
}
