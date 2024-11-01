<?php

/**
 *
 * Spotlight integration.
 *
 * @since 1.1.9
 */

namespace ChadwickMarketing\SocialLite\base\spotlight;

use ChadwickMarketing\SocialLite\base\UtilsProvider;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkSpotlight {

    use UtilsProvider;


    /**
     * Check if Spotlight is active.
     *
     * @return bool
     */

    public function isSpotlightActive() {
        return is_plugin_active('spotlight-social-photo-feeds/plugin.php');
    }

    /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkSpotlight();
    }

    /**
     * Get a feed by ID.
     * @param $feed_id
     * @return array
     */
    public function getFeedById($feed_id) {

        $data = [
            'feed' => [],
            'accounts' => [],
        ];

        $data['feed'] = get_post_meta($feed_id, '_sli_options', true);

        if (empty($data['feed'])) {
            return $data;
        }

        $data['accounts'] = $this->getAccountsByIds($data['feed']['accounts']);

        return $data;
    }


    /**
     * Get accounts by id
     * @param array $account_ids
     * @return array
     */
    public function getAccountsByIds(array $account_ids) {
        $accounts = [];

        foreach ($account_ids as $account_id) {
            $accounts[] = [
				'id' => $account_id,
				'type' => get_post_meta($account_id, '_sli_account_type', true),
				'userId' => get_post_meta($account_id, '_sli_user_id', true),
				'username' => get_post_meta($account_id, '_sli_username', true),
				'bio' => get_post_meta($account_id, '_sli_bio', true),
				'customBio' => get_post_meta($account_id, '_sli_custom_bio', true),
				'profilePicUrl' => get_post_meta($account_id, '_sli_profile_pic_url', true),
				'customProfilePicUrl' => get_post_meta($account_id, '_sli_custom_profile_pic', true),
				'mediaCount' => get_post_meta($account_id, '_sli_media_count', true),
				'followersCount' => get_post_meta($account_id, '_sli_follows_count', true),
            ];
        }

        return $accounts;
    }



}
