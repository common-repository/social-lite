<?php

namespace ChadwickMarketing\SocialLite\view\bio;

defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request

/**
 * Manage custom post types.
 */
class BioLinkMeta {

    /**
     * Add new post type for link in bio pages
     */
    public function add_meta() {

        // add a new meta box for the post type
        add_meta_box(
            'cms-landingpage-data',
            __('Data', SOCIAL_LITE_TD),
            [$this, 'render_meta_box'],
            'cms-landingpages',
            'normal',
            'default'
        );

        // add a new meta box for bio link analytics
        add_meta_box(
            'cms-landingpage-analytics',
            __('Analytics', SOCIAL_LITE_TD),
            [$this, 'render_meta_box'],
            'cms-landingpages',
            'normal',
            'default'
        );

    }


    public function render_meta_box($post) {
        // get the value of the meta box
        $value = get_post_meta($post->ID, 'cms-landingpage-data', true);
        // create a form to edit the value
        echo '<input type="text" id="cms-landingpages-meta-box" name="cms-landingpages-meta-box" value="' . esc_attr($value) . '" />';

    }

     /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkMeta();
    }

}