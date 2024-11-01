<?php
namespace ChadwickMarketing\SocialLite;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use ChadwickMarketing\Utils\Activator as UtilsActivator;
use const true;
use function gmdate;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * The activator class handles the plugin relevant activation hooks: Uninstall, activation,
 * deactivation and installation. The "installation" means installing needed database tables.
 */
class Activator {
    use UtilsProvider;
    use UtilsActivator;

    const OPTION_NAME_INSTALLATION_DATE = SOCIAL_LITE_OPT_PREFIX . '-installation-date';
    const OPTION_NAME_NEEDS_DEFAULT_DATA = SOCIAL_LITE_OPT_PREFIX . '-needs-default-data';
    const OPTION_NAME_TOKEN = SOCIAL_LITE_OPT_PREFIX . '-token';

    /**
     * Method gets fired when the user activates the plugin.
     */
    public function activate() {

    }

    /**
     * Method gets fired when the user deactivates the plugin.
     */
    public function deactivate() {
        // Your implementation...
    }

    /**
     * Function to add initial data after first activation.
     */
    public function add_initial_data() {

        delete_site_option(self::OPTION_NAME_NEEDS_DEFAULT_DATA);

    }

    /**
     *  Function create a unique token, stored in the database, to identify the installation.
     */
    public function create_token() {

        $token = get_option(self::OPTION_NAME_TOKEN);

        if (empty($token)) {
            $token = [
				uniqid(mt_rand()),
				uniqid(mt_rand()),
				rand(999, time())
            ];
            add_option( self::OPTION_NAME_TOKEN, $token );
          }

    }

    /**
     * Detect first installation and conditionally create initial content.
     */
    public function detect_first_installation() {
                    // Is it the first installation?
        	        if (empty(get_option(self::OPTION_NAME_INSTALLATION_DATE))) {
        	            // Default content needs to be inserted in `init` action, and can not ensured at this time (e. g. WP CLI activation)
        	            add_site_option(self::OPTION_NAME_NEEDS_DEFAULT_DATA, true);
        	            // Add option initially of first installation date
                        add_option(self::OPTION_NAME_INSTALLATION_DATE, gmdate('Y-m-d'));
        	        }

            }


    /**
     * Install tables, stored procedures or whatever in the database.
     * This method is always called when the version bumps up or for
     * the first initial activation.
     *
     * @param boolean $errorlevel If true throw errors
     */
    public function dbDelta($errorlevel) {

        $this->create_token();

        $this->detect_first_installation();

    }
}