<?php
declare(strict_types=1);
namespace RXNLabs\MistletoeMatchupFantasyDraft\PostTypes;

class Team {
	private $slug = 'snowdraft_team';
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
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_league_id',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			$this->get_slug(),
			'_snowdraft_icon_key',
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
					'name'                  => __( 'Teams', 'mistletoe-matchup-fantasy-draft' ),
					'singular_name'         => __( 'Teams', 'mistletoe-matchup-fantasy-draft' ),
					'all_items'             => __( 'All Teams', 'mistletoe-matchup-fantasy-draft' ),
					'archives'              => __( 'Teams Archives', 'mistletoe-matchup-fantasy-draft' ),
					'attributes'            => __( 'Teams Attributes', 'mistletoe-matchup-fantasy-draft' ),
					'insert_into_item'      => __( 'Insert into Teams', 'mistletoe-matchup-fantasy-draft' ),
					'uploaded_to_this_item' => __( 'Uploaded to this Teams', 'mistletoe-matchup-fantasy-draft' ),
					'featured_image'        => _x( 'Featured Image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'set_featured_image'    => _x( 'Set featured image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'remove_featured_image' => _x( 'Remove featured image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'use_featured_image'    => _x( 'Use as featured image', $this->get_slug(), 'mistletoe-matchup-fantasy-draft' ),
					'filter_items_list'     => __( 'Filter Teams list', 'mistletoe-matchup-fantasy-draft' ),
					'items_list_navigation' => __( 'Teams list navigation', 'mistletoe-matchup-fantasy-draft' ),
					'items_list'            => __( 'Teams list', 'mistletoe-matchup-fantasy-draft' ),
					'new_item'              => __( 'New Teams', 'mistletoe-matchup-fantasy-draft' ),
					'add_new'               => __( 'Add New', 'mistletoe-matchup-fantasy-draft' ),
					'add_new_item'          => __( 'Add New Teams', 'mistletoe-matchup-fantasy-draft' ),
					'edit_item'             => __( 'Edit Teams', 'mistletoe-matchup-fantasy-draft' ),
					'view_item'             => __( 'View Teams', 'mistletoe-matchup-fantasy-draft' ),
					'view_items'            => __( 'View Teams', 'mistletoe-matchup-fantasy-draft' ),
					'search_items'          => __( 'Search Teams', 'mistletoe-matchup-fantasy-draft' ),
					'not_found'             => __( 'No Teams found', 'mistletoe-matchup-fantasy-draft' ),
					'not_found_in_trash'    => __( 'No Teams found in trash', 'mistletoe-matchup-fantasy-draft' ),
					'parent_item_colon'     => __( 'Parent Teams:', 'mistletoe-matchup-fantasy-draft' ),
					'menu_name'             => __( 'Teams', 'mistletoe-matchup-fantasy-draft' ),
				),
				'public'                => true,
				'hierarchical'          => false,
				'show_ui'               => true,
				'show_in_nav_menus'     => true,
				'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
				'has_archive'           => false,
				'rewrite'               => true,
				'query_var'             => true,
				'menu_position'         => null,
				'menu_icon'             => 'dashicons-nametag',
				'show_in_rest'          => true,
				'rest_base'             => $this->get_slug(),
				'rest_controller_class' => 'WP_REST_Posts_Controller',
			)
		);
	}

	public function comments_policy( $open, $post_id ): bool {
		return false;
	}

	public function is_post_type( $id = 0 ): bool {
		$post_id = $id ?: get_the_ID();
		return get_post_type( $post_id ) === $this->get_slug();
	}
}
