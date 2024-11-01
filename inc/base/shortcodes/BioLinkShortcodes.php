<?php

/**
 * Handle bio link shortcodes.
 * 
 * @since 1.5.6
 */

namespace ChadwickMarketing\SocialLite\base\shortcodes;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use ReflectionFunction;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkShortcodes {

    use UtilsProvider;

    /**
     * Check if shortcode should be executed on the current page.
     * 
     * @return bool
     */
    public function shouldRenderShortcode() {

        if (BioLinkData::instance()->isBioLinkAvailable()) {

           $bioLinkId = is_home() || is_front_page() ? BioLinkData::instance()->getHomepageBioLink()['id'] ?? false : get_the_ID();

           return isset( $_GET['shortcode'] ) && $this->getShortcode(sanitize_text_field($_GET['shortcode']), $bioLinkId);

        }

        return false;

    }

    /**
     * Get shortcode by id.
     * 
     * @param string $linkId
     * @param int $bioLinkId
     * 
     * @return array
     */
    public function getShortcode($linkId, $bioLinkId) {

        $shortcode = current(array_filter(BioLinkData::instance()->getBioLinkDataById($bioLinkId)['data']['content'], function ($item) use ($linkId) {
            return $item['id'] === $linkId;
        }))['content']['EditShortcode'];

        if (empty($shortcode)) {
            return false;
        }

        return $shortcode;

    }


    /**
     * Execute shortcode.
     * 
     * @param string $linkId
     * @param int $bioLinkId
     * 
     * @return string
     */
    public function executeShortcode($linkId, $bioLinkId) {

        $shortcode = $this->getShortcode($linkId, $bioLinkId);

        if (empty($shortcode) || !isset($shortcode['__private__tag'])) {
            return false;
        }

        return do_shortcode($shortcode['__private__tag']);

    }


    /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkShortcodes();
    }

}