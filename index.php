<?php
/**
 * Main file for WordPress.
 *
 * @wordpress-plugin
 * Plugin Name: 	Social
 * Plugin URI:		https://socialwp.io
 * Description: 	Turn your site into a launchpad for your links, products, and more. Build a place your fans will love, with Social.
 * Author:          Chadwick Marketing
 * Author URI:		https://socialwp.io
 * Version: 		1.6.3
 * Text Domain:		social-lite
 * Domain Path:		/languages
 */

defined('ABSPATH') or die( 'No script kiddies please!' ); // Avoid direct file request

if ( function_exists( 'social_fs') ) {

    social_fs()->set_basename( false, __FILE__ );

} else {

    /**
     * Plugin constants. This file is procedural coding style for initialization of
     * the plugin core and definition of plugin configuration.
     */
    if (defined('SOCIAL_LITE_PATH')) {
        return;
    }

    define('SOCIAL_LITE_FILE', __FILE__);
    define('SOCIAL_LITE_PATH', dirname(SOCIAL_LITE_FILE));
    define('SOCIAL_LITE_ROOT_SLUG', 'social-develop');
    define('SOCIAL_LITE_SLUG', 'social-lite');
    define('SOCIAL_LITE_INC', trailingslashit(path_join(SOCIAL_LITE_PATH, 'inc')));
    define('SOCIAL_LITE_MIN_PHP', '7.0.0'); // Minimum of PHP 5.3 required for autoloading and namespacing
    define('SOCIAL_LITE_MIN_WP', '5.0'); // Minimum of WordPress 5.0 required
    define('SOCIAL_LITE_NS', 'ChadwickMarketing\\SocialLite');
    define('SOCIAL_LITE_DB_PREFIX', 'social_lite'); // The table name prefix wp_{prefix}
    define('SOCIAL_LITE_OPT_PREFIX', 'social_lite'); // The option name prefix in wp_options
    define('SOCIAL_LITE_SLUG_CAMELCASE', lcfirst(str_replace('-', '', ucwords(SOCIAL_LITE_SLUG, '-'))));

    // Check PHP Version and print notice if minimum not reached, otherwise start the plugin core
    require_once SOCIAL_LITE_INC .
        'base/others/' .
        (version_compare(phpversion(), SOCIAL_LITE_MIN_PHP, '>=') ? 'start.php' : 'fallback-php-version.php');

    // Require the Freemius SDK
    require_once SOCIAL_LITE_PATH .
        '/freemius.php';


}