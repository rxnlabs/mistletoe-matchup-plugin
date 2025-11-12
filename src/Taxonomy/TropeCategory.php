<?php
declare(strict_types=1);
namespace RXNLabs\MistletoeMatchupFantasyDraft\Taxonomy;

use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\Trope;

class TropeCategory {

	private $slug = 'snowdraft_trope_category';

	public function __construct( private Trope $trope ) {
		$this->hooks();
	}

	private function hooks(): void {
		$this->core_hooks();
	}

	private function core_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function register(): void {
		register_taxonomy(
			$this->get_slug(),
			array( $this->trope->get_slug() ),
			array(
				'hierarchical'          => true,
				'public'                => false,
				'show_in_nav_menus'     => true,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'query_var'             => true,
				'rewrite'               => true,
				'capabilities'          => array(
					'manage_terms' => 'edit_posts',
					'edit_terms'   => 'edit_posts',
					'delete_terms' => 'edit_posts',
					'assign_terms' => 'edit_posts',
				),
				'labels'                => array(
					'name'                       => __( 'Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'singular_name'              => _x( 'Trope Category', 'taxonomy general name', 'mistletoe-matchup-fantasy-draft' ),
					'search_items'               => __( 'Search Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'popular_items'              => __( 'Popular Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'all_items'                  => __( 'All Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'parent_item'                => __( 'Parent Trope Category', 'mistletoe-matchup-fantasy-draft' ),
					'parent_item_colon'          => __( 'Parent Trope Category:', 'mistletoe-matchup-fantasy-draft' ),
					'edit_item'                  => __( 'Edit Trope Category', 'mistletoe-matchup-fantasy-draft' ),
					'update_item'                => __( 'Update Trope Category', 'mistletoe-matchup-fantasy-draft' ),
					'view_item'                  => __( 'View Trope Category', 'mistletoe-matchup-fantasy-draft' ),
					'add_new_item'               => __( 'Add New Trope Category', 'mistletoe-matchup-fantasy-draft' ),
					'new_item_name'              => __( 'New Trope Category', 'mistletoe-matchup-fantasy-draft' ),
					'separate_items_with_commas' => __( 'Separate Trope Categories with commas', 'mistletoe-matchup-fantasy-draft' ),
					'add_or_remove_items'        => __( 'Add or remove Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'choose_from_most_used'      => __( 'Choose from the most used Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'not_found'                  => __( 'No Trope Categories found.', 'mistletoe-matchup-fantasy-draft' ),
					'no_terms'                   => __( 'No Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'menu_name'                  => __( 'Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
					'items_list_navigation'      => __( 'Trope Categories list navigation', 'mistletoe-matchup-fantasy-draft' ),
					'items_list'                 => __( 'Trope Categories list', 'mistletoe-matchup-fantasy-draft' ),
					'most_used'                  => _x( 'Most Used', 'snowdraft_trope_category', 'mistletoe-matchup-fantasy-draft' ),
					'back_to_items'              => __( '&larr; Back to Trope Categories', 'mistletoe-matchup-fantasy-draft' ),
				),
				'show_in_rest'          => true,
				'rest_base'             => 'snowdraft_trope_category',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			)
		);
	}
}
