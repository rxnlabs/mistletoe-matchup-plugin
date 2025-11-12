<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft;

class Config {

	private string $plugin_file;
	private string $plugin_dir;
	private string $plugin_url;
	private string $version;

	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_dir  = plugin_dir_path( $plugin_file );
		$this->plugin_url  = plugin_dir_url( $plugin_file );
		$this->version     = defined( 'MISTLETOE_MATCHUP_FANTASY_DRAFT_VERSION' ) ? (string) MISTLETOE_MATCHUP_FANTASY_DRAFT_VERSION : '1.0.0';
	}

	public function file(): string {
		return $this->plugin_file; }
	public function dir(): string {
		return $this->plugin_dir; }
	public function url(): string {
		return $this->plugin_url; }
	public function version(): string {
		return $this->version; }

	public function build_js_url( string $name ): string {
		return $this->url() . 'build/js/' . ltrim( $name, '/' );
	}

	public function build_css_url( string $name ): string {
		return $this->url() . 'build/css/' . ltrim( $name, '/' );
	}

	public function pusher_config(): array {
		$options = get_option( 'snowdraft_settings', array() );
		return array(
			'key'     => (string) ( $options['pusher_key'] ?? getenv( 'PUSHER_APP_KEY' ) ?: '' ),
			'secret'  => (string) ( $options['pusher_secret'] ?? getenv( 'PUSHER_APP_SECRET' ) ?: '' ),
			'app_id'  => (string) ( $options['pusher_app_id'] ?? getenv( 'PUSHER_APP_ID' ) ?: '' ),
			'cluster' => (string) ( $options['pusher_cluster'] ?? getenv( 'PUSHER_APP_CLUSTER' ) ?: 'us2' ),
			'useTLS'  => true,
		);
	}
}
