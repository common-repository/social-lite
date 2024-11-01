<?php
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request

if (!function_exists('social_lite_skip_rest_admin_notice')) {
    /**
     * Show an admin notice to administrators when the minimum WP version
     * could not be reached. The error message is only in english available.
     */
    function social_lite_skip_rest_admin_notice() {
        if (current_user_can('install_plugins')) {
            $data = get_plugin_data(SOCIAL_LITE_FILE, true, false);
            global $wp_version;
            echo '';
        }
    }
}
add_action('admin_notices', 'social_lite_skip_rest_admin_notice');
