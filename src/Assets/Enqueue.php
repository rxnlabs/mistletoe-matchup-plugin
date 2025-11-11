<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Assets;

use RXNLabs\MistletoeMatchupFantasyDraft\Config;

class Enqueue
{
    public function __construct(private Config $config)
    {
    }

    public function admin(): void
    {
        $this->enqueueAssets('admin');
    }

    public function public(): void
    {
        $this->enqueueAssets('public');
    }

    private function enqueueAssets(string $context): void
    {
        $handleJs  = 'snowdraft-' . $context . '-js';
        $handleCss = 'snowdraft-' . $context . '-css';

        // In development you could point to Vite dev server; for now always enqueue built assets
        $js  = $this->config->buildJsUrl($context . '.js');
        $css = $this->config->buildCssUrl($context . '.css');

        wp_register_script($handleJs, $js, ['wp-api-fetch'], $this->config->version(), true);
        wp_register_style($handleCss, $css, [], $this->config->version());

        wp_enqueue_script($handleJs);
        wp_enqueue_style($handleCss);

        wp_localize_script($handleJs, 'SnowDraft', [
            'restBase' => esc_url_raw( rest_url('snowdraft/v1/') ),
            'nonce'    => wp_create_nonce('wp_rest'),
            'pusher'   => [
                'key'     => $this->config->pusherConfig()['key'] ?? '',
                'cluster' => $this->config->pusherConfig()['cluster'] ?? 'us2',
            ],
        ]);
    }
}
