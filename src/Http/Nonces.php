<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Http;

class Nonces
{
    public function rest(): string
    {
        return wp_create_nonce('wp_rest');
    }

    public function verify(string $nonce): bool
    {
        return wp_verify_nonce($nonce, 'wp_rest') !== false;
    }
}
