<?php
namespace ChadwickMarketing\SocialLite;

use ChadwickMarketing\SocialLite\base\Core as BaseCore;
use ChadwickMarketing\SocialLite\rest\BioLinkEndpoints;
use ChadwickMarketing\SocialLite\view\bio\BioLinkMeta;
use ChadwickMarketing\SocialLite\view\menu\Page;
use ChadwickMarketing\SocialLite\base\CPT;
use ChadwickMarketing\SocialLite\view\bio\BioLink;
use ChadwickMarketing\SocialLite\Activator;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use ChadwickMarketing\Utils\PluginReceiver;
use ChadwickMarketing\Utils\Constants;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Singleton core class which handles the main system for plugin. It includes
 * registering of the autoload, all hooks (actions & filters) (see BaseCore class).
 */
class Core extends BaseCore {
    /**
     * Singleton instance.
     */
    private static $me;


     /**
     * The plugins activator class.
     *
     * @see Activator
     */
    private $activator;

    /**
     * Application core constructor.
     */
    protected function __construct() {
        parent::__construct();

        // Register immediate actions and filters
        $this->activator = $this->getPluginClassInstance(
            Constants::PLUGIN_CLASS_ACTIVATOR
        );

        add_action('init', [CPT::instance(), 'add_cpt']);
        add_action('init', [BioLink::instance(), 'register']);

    }


      /**
     * Getter.
     *
     * @return Activator
     * @codeCoverageIgnore
     */
    public function get_activator() {
        return $this->activator;
    }

    /**
     * The init function is fired even the init hook of WordPress. If possible
     * it should register all hooks to have them in one place.
     */
    public function init() {
        // Register all your hooks here
        add_action('rest_api_init', [BioLinkEndpoints::instance(), 'rest_api_init']);
        add_action('admin_enqueue_scripts', [$this->getAssets(), 'admin_enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this->getAssets(), 'wp_enqueue_scripts']);
        add_action('style_loader_tag', [$this->getAssets(), 'preload_frontend_styles'], 10, 2);
        add_action('admin_menu', [Page::instance(), 'admin_menu']);
        add_action('add_meta_boxes', [BioLinkMeta::instance(), 'add_meta']);
        add_filter('cmplz_site_needs_cookiewarning', function() {
            return !$this->getAssets()->isBioLinkPage();
        });

        // Create default content
        if (get_site_option(Activator::OPTION_NAME_NEEDS_DEFAULT_DATA) || !BioLinkData::instance()->isBioLinkAvailable()) {

            $this->get_activator()->add_initial_data();

        }

    }


    /**
     * Get singleton core class.
     *
     * @return Core
     */
    public static function getInstance() {
        return !isset(self::$me) ? (self::$me = new Core()) : self::$me;
    }
}