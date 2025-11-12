<?php
declare(strict_types=1);
namespace RXNLabs\MistletoeMatchupFantasyDraft\Service;

use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\League;
use RXNLabs\MistletoeMatchupFantasyDraft\PostTypes\Team;
use RXNLabs\MistletoeMatchupFantasyDraft\Util\Share;

class LeagueService {
	use Share;

	public function __construct( private League $league, private Team $team ) {
		$this->hooks();
	}

	private function hooks(): void {
		$this->core_hooks();
		$this->vendor_hooks();
	}

	private function core_hooks(): void {
	}

	private function vendor_hooks(): void {
		$this->gform_create_league_form_hooks();
	}

	private function gform_create_league_form_hooks(): void {
		add_filter( 'gform_field_validation_2_1', array( $this, 'gform_validate_league_owner_not_in_league_with_same_name' ), 10, 4 );
		add_action( 'gform_after_submission_2', array( $this, 'gform_create_league_after_submission' ), 10, 2 );
	}

	/**
	 * Validates that the league owner is not already part of a league with the same name.
	 *
	 * @param array $validation_result The array representing the validation result, including validity status and messages.
	 * @param string $value The input value to validate (league name).
	 * @param int $form_id The ID of the form being validated.
	 * @param object $field The field object containing form field data.
	 *
	 * @return array The updated validation result array.
	 */
	public function gform_validate_league_owner_not_in_league_with_same_name( $validation_result, $value, $form_id, $field ): array {
		$clean_value = strtolower( trim( sanitize_text_field( $value ) ) );
		$leagues     = $this->get_player_leagues( get_current_user_id() );

		if ( ! empty( $leagues ) ) {
			foreach ( $leagues as $league ) {
				if ( strtolower( $league->get_name() ) === $clean_value && $league->get_id() !== 1 ) {
					$validation_result['valid']   = false;
					$validation_result['message'] = __( 'You are already a member of a league with the same name. Choose a different name for this new league.', 'mistletoe-matchup-fantasy-draft' );
					break;
				}
			}
		}

		return $validation_result;
	}

	/**
	 * Creates a new league after the new League form is submitted.
	 *
	 * @param array $entry The entry data from the Gravity Forms submission.
	 * @param array $form The form data associated with the Gravity Forms submission.
	 *
	 * @return void
	 */
	public function gform_create_league_after_submission( $entry, $form ): void {
		$league_name            = trim( rgar( $entry, '1' ) );
		$description            = trim( sanitize_textarea_field( rgar( $entry, '3' ) ) );
		$will_commissioner_play = false;

		// Gravity forms multiple choice entries are stored with the field ID + choice ID (e.g. the string literal 5.1 to represent field 5 with choice 1)
		if ( ! empty( $this->search_partial_string_in_array( $entry, '_snowdraft_commissioner_team' ) ) ) {
			$will_commissioner_play = true;
		}

		if ( ! empty( $league_name ) ) {
			$league_data = $this->league->create_league( $league_name, $description, get_current_user_id() );

			if ( ! empty( $league_data ) && $will_commissioner_play ) {
				$this->league->add_player_to_league( get_current_user_id(), $league_data['league_id'], $league_data['league_post_id'] );
			}
		}
	}
}
