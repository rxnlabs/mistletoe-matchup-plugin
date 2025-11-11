<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Repository;

class TropeRepo
{
    public function get(int $id): ?\WP_Post
    {
        $post = get_post($id);
        return ($post && $post->post_type === 'snowdraft_trope') ? $post : null;
    }
}
