<?php
namespace RXNLabs\MistletoeMatchupFantasyDraft\Repository;

class LeagueRepo
{
    public function get(int $id): ?\WP_Post
    {
        $post = get_post($id);
        return ($post && $post->post_type === 'snowdraft_league') ? $post : null;
    }
}
