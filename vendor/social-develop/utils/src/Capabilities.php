<?php
namespace ChadwickMarketing\Utils;

defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request

class Capabilities {
    private $capability;

    /**
     * C'tor.
     */
    private function __construct() {
        $this->capability = apply_filters(SOCIAL_LITE_OPT_PREFIX . '_capability', 'manage_options');
    }

    /**
     * Get the capability.
     * 
     * @return string
     */
    public function get_capability() {
        return $this->capability;
    }

    /**
     * Get a new instance of the class.
     */
    public static function instance() {
        return new Capabilities();
    }
}