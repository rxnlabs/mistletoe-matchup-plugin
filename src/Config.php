<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft;

class Config
{
    private string $pluginFile;
    private string $pluginDir;
    private string $pluginUrl;
    private string $version;

    public function __construct(string $pluginFile)
    {
        $this->pluginFile = $pluginFile;
        $this->pluginDir  = plugin_dir_path($pluginFile);
        $this->pluginUrl  = plugin_dir_url($pluginFile);
        $this->version    = defined('MISTLETOE_MATCHUP_FANTASY_DRAFT_VERSION') ? (string) MISTLETOE_MATCHUP_FANTASY_DRAFT_VERSION : '1.0.0';
    }

    public function file(): string { return $this->pluginFile; }
    public function dir(): string { return $this->pluginDir; }
    public function url(): string { return $this->pluginUrl; }
    public function version(): string { return $this->version; }

    public function buildJsUrl(string $name): string
    {
        return $this->url() . 'build/js/' . ltrim($name, '/');
    }

    public function buildCssUrl(string $name): string
    {
        return $this->url() . 'build/css/' . ltrim($name, '/');
    }

    public function pusherConfig(): array
    {
        $options = get_option('snowdraft_settings', []);
        return [
            'key'     => (string) ($options['pusher_key'] ?? getenv('PUSHER_APP_KEY') ?: ''),
            'secret'  => (string) ($options['pusher_secret'] ?? getenv('PUSHER_APP_SECRET') ?: ''),
            'app_id'  => (string) ($options['pusher_app_id'] ?? getenv('PUSHER_APP_ID') ?: ''),
            'cluster' => (string) ($options['pusher_cluster'] ?? getenv('PUSHER_APP_CLUSTER') ?: 'us2'),
            'useTLS'  => true,
        ];
    }
}
