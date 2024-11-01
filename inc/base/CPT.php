<?php

namespace ChadwickMarketing\SocialLite\base;

use ChadwickMarketing\Utils\Capabilities;

defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request

/**
 * Manage custom post types.
 */
class CPT {

    /**
     * Add new post type for link in bio pages
     */
    public function add_cpt() {

        register_post_type('cms-landingpages',
        [
            'labels'      => [
                'name'          => __('Bio Links', SOCIAL_LITE_TD),
            ],
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
			'public'      => true,
			'has_archive' => false,
            'capabilities' => [
                'edit_post' => false,
                'edit_posts' => false,
                'edit_others_posts' => false,
                'publish_posts' => false,
                'read_post' => false,
                'read_private_posts' => false,
                'delete_post' => false,
            ],
        ]
        );
    }



     /**
     * New instance.
     */
    public static function instance() {
        return new CPT();
    }



}