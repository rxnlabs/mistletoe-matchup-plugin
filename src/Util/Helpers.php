<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Util;

class Helpers
{
    public static function update_json_meta(int $postId, string $key, $value): bool
    {
        $encoded = is_array($value)
            ? wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $value;
        return update_post_meta($postId, $key, $encoded);
    }

    public static function get_json_meta(int $postId, string $key): array
    {
        $raw = get_post_meta($postId, $key, true);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($raw) ? $raw : [];
    }
}
