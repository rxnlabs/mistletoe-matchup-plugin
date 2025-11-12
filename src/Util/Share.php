<?php
declare(strict_types=1);
namespace RXNLabs\MistletoeMatchupFantasyDraft\Util;

// exit if not loading in WordPress context but don't exit if running our PHPUnit tests
if ( ! defined( 'ABSPATH' ) && ! defined( 'PHPUNIT_TESTS_RUNNING' ) ) {
	exit;
}

/**
 * Create a Trait to share common functions and settings among classes that enhance the plugin.
 *
 * Provides utility methods for managing plugin information.
 */
trait Share {
	/**
	 * The text domain used for this plugin.
	 * @var string
	 */
	private $text_domain = 'crsc-events-manager-zapier';
	/**
	 * The plugin slug that is used to identify this plugin.
	 * @var string
	 */
	private $plugin_slug = 'crsc-events-manager-zapier';

	private $main_plugin_file;

	public function set_main_plugin_file( $main_plugin_file ) {
		$this->main_plugin_file = $main_plugin_file;
	}

	private function get_main_plugin_file() {
		return $this->main_plugin_file;
	}

	/**
	 * Get the plugin's textdomain so that we don't have to remember when defining strings for display.
	 *
	 * @return string Text domain for plugin.
	 */
	public function get_text_domain() {
		$this_plugin = get_plugin_data( $this->get_main_plugin_file(), false );

		return $this_plugin['TextDomain'] ?? $this->text_domain;
	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Get the plugin's version from the Plugin info docblock so that we don't have to update this in multiple places
	 * when the version number is updated.
	 *
	 * @return int Version of the plugin.
	 */
	public function get_version() {
		$this_plugin = get_plugin_data( $this->get_this_plugin_file(), false );

		return $this_plugin['Version'] ?? '1.0.0';
	}

	/**
	 * Retrieve the folder path of the current plugin.
	 *
	 * @return string The directory path of the plugin.
	 */
	public function get_this_plugin_folder_path() {
		return dirname( $this->get_this_plugin_file() );
	}

	/**
	 * Get the name of this plugin folder
	 *
	 * @return string Plugin folder name of this plugin.
	 */
	public function get_this_plugin_folder_name() {
		$main_plugin_file = $this->get_this_plugin_file();
		$parent_dir       = dirname( $main_plugin_file );
		return basename( $parent_dir );
	}

	/**
	 * Get the main plugin file.
	 *
	 * @return string Path to the main plugin file. This needs to be placed directly in the main plugin file.
	 */
	public function get_this_plugin_file() {
		return $this->get_main_plugin_file();
	}

	/**
	 * Get the name of a plugin's folder name using the key that get_plugins() returns.
	 *
	 * The get_plugins() function returns the plugin information key by the plugin folder name + the plugin's main file.
	 * We need to get the name of the plugin's folder or just the main file if the plugin sits at the root of the
	 * plugins folder as a single file.
	 *
	 * @param string $plugin_folder_file_name The plugin's folder name and main plugin file.
	 *
	 * @return string Name of the plugin's folder without the main plugin file or the name of just the main plugin file.
	 */
	public function extract_plugin_folder_name_by_plugin_file_name( $plugin_folder_file_name ) {
		$plugin_folder_name = dirname( $plugin_folder_file_name );

		// if we could not extract the plugin directory name from the key,
		// assume that the plugin is a single file installed at the plugins folder root
		// (e.g. hello.php for the hello dolly plugin)
		if ( empty( $plugin_folder_name ) || '.' === $plugin_folder_name ) {
			$plugin_folder_name = $plugin_folder_file_name;
		}

		return $plugin_folder_name;
	}

	/**
	 * Recursively trims whitespace from strings in an array.
	 *
	 * @param array $dirty_array The input array to process.
	 * @return array The processed array with trimmed values.
	 */
	public function recursive_trim( array $dirty_array ) {
		$clean_array = $dirty_array;
		foreach ( $clean_array as $key => $value ) {
			if ( is_array( $value ) ) {
				// If the value is an array, call the function recursively
				$clean_array[ $key ] = $this->recursive_trim( $value );
			} elseif ( is_string( $value ) ) {
				// If the value is a string, trim whitespace
				$clean_array[ $key ] = trim( $value );
			}
		}

		return $clean_array;
	}

	public function is_groups_plugin_active(): bool {
		return class_exists( '\Groups_Group' ) && class_exists( '\Groups_User' ) && class_exists( '\Groups_Post_Access' );
	}

	public function is_league_commissioner( int $league_id, int $player_id = 0 ): bool {
		if ( ! $this->is_groups_plugin_active() ) {
			return false;
		}

		$find_user_id = $player_id ?: get_current_user_id();
		$find_group   = new \Groups_Group( $league_id );

		return ! empty( $find_group->get_group_id() ) && $find_user_id === absint( $find_group->get_creator_id() );
	}

	/**
	 * Check if a player is a member of a specific league group.
	 *
	 * This function verifies whether a given player is part of the specified league group.
	 * If no player ID is provided, it checks for the currently logged-in user.
	 * This requires the Groups plugin to be active. If the plugin is inactive,
	 * the function will always return false.
	 *
	 * @param int $league_id The ID of the league group to check membership for.
	 * @param int $player_id Optional. The ID of the player to check. Defaults to 0 (current user).
	 *
	 * @return bool True if the player is a member of the league group, false otherwise.
	 */
	public function is_player_in_league( int $league_id, int $player_id = 0 ): bool {
		if ( ! $this->is_groups_plugin_active() ) {
			return false;
		}

		$find_group   = new \Groups_Group( $league_id );
		$find_user_id = $player_id ?: get_current_user_id();

		if ( \Groups_User_Group::read( $find_user_id, $find_group->get_group_id() ) ) {
			return true;
		}
	}

	/**
	 * Retrieve the leagues associated with a player, excluding certain default groups.
	 *
	 * This method fetches the player's leagues using the Groups plugin, ensuring that
	 * default groups like the "Registered" group (ID 1) are excluded from the result.
	 * Returns `false` if the Groups plugin is not active.
	 *
	 * @param int $player_id The ID of the player whose leagues are to be retrieved. Defaults to 0.
	 *
	 * @return bool|array Returns false if the Groups plugin is inactive. Otherwise, returns an array of leagues (groups) associated with the player.
	 */
	public function get_player_leagues( int $player_id = 0 ): bool|array {
		if ( ! $this->is_groups_plugin_active() ) {
			return false;
		}

		$groups_user  = new \Groups_User( $player_id );
		$clean_groups = array();

		if ( ! empty( $groups_user->get_group_ids_deep() ) ) {
			$groups = $groups_user->get_groups();
			if ( ! empty( $groups ) ) {
				foreach ( $groups as $group ) {
					// Exclude the default Registered group (ID 1) from the list of leagues since we cannot delete group 1.
					if ( 1 !== absint( $group->get_group_id() ) ) {
						$clean_groups[] = $group;
					}
				}
			}
		}

		return $clean_groups;
	}


	/**
	 * Search for a partial string match in array values.
	 *
	 * This method searches through an array and returns all elements that contain
	 * the specified search string as a substring. The search is case-insensitive by default.
	 *
	 * @param array  $array The array to search through.
	 * @param string $search_string The partial string to search for.
	 * @param bool   $case_sensitive Whether the search should be case-sensitive. Default false.
	 * @param bool   $return_keys Whether to return matching keys instead of values. Default false.
	 *
	 * @return array Array of matching values or keys.
	 */
	public function search_partial_string_in_array( array $array, string $search_string, bool $case_sensitive = false, bool $return_keys = false ): array {
		$matches = array();

		foreach ( $array as $key => $value ) {
			// Convert value to string for comparison
			$string_value = is_string( $value ) ? $value : (string) $value;

			// Perform case-sensitive or case-insensitive search
			$found = $case_sensitive
				? strpos( $string_value, $search_string ) !== false
				: stripos( $string_value, $search_string ) !== false;

			if ( $found ) {
				$matches[] = $return_keys ? $key : $value;
			}
		}

		return $matches;
	}
}
