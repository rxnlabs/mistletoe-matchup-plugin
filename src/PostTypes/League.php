<?php
declare(strict_types=1);
namespace RXNLabs\MistletoeMatchupFantasyDraft\PostTypes;

class League {
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
				'public'                => false,
				'hierarchical'          => false,
				'show_ui'               => true,
				'show_in_nav_menus'     => true,
				'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'comments' ),
				'has_archive'           => true,
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
}
