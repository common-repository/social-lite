<?php

/**
 *
 * Manage the bio link template library.
 *
 * @since 1.1.8
 */
namespace ChadwickMarketing\SocialLite\base\templates;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
// @codeCoverageIgnoreStart
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Avoid direct file request
// @codeCoverageIgnoreEnd
class BioLinkTemplates {
    use UtilsProvider;
    const TEMPLATES_OPTION_NAME = SOCIAL_LITE_OPT_PREFIX . '_templates';

    const TRANSIENT_KEY_PREFIX = SOCIAL_LITE_OPT_PREFIX . '_templates_';

    public static $apiUrl = 'https://templates.socialwp.io/api/';

    /**
     * Get the templates from the API.
     *
     * @param bool $forceUpdate
     *
     * @return array
     */
    private static function receiveTemplates( $forceUpdate = false ) {
        $cacheKey = self::TRANSIENT_KEY_PREFIX . SOCIAL_LITE_VERSION;
        $templates = get_transient( $cacheKey );
        if ( $forceUpdate || false === $templates ) {
            $timeout = ( $forceUpdate ? 30 : 8 );
            $response = wp_remote_get( self::$apiUrl . 'templates', [
                'timeout' => $timeout,
                'body'    => [
                    'version' => SOCIAL_LITE_VERSION,
                ],
            ] );
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                set_transient( $cacheKey, [], 2 * HOUR_IN_SECONDS );
                return false;
            }
            $templates = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( !is_array( $templates ) || empty( $templates ) ) {
                set_transient( $cacheKey, [], 2 * HOUR_IN_SECONDS );
                return false;
            }
            update_option( self::TEMPLATES_OPTION_NAME, $templates, 'no' );
            set_transient( $cacheKey, $templates, 12 * HOUR_IN_SECONDS );
        }
        return $templates;
    }

    /**
     * Get templates data.
     *
     * @param bool $forceUpdate
     *
     * @return array
     */
    public function getTemplatesData( $forceUpdate = false ) {
        self::receiveTemplates( $forceUpdate );
        $templateData = get_option( self::TEMPLATES_OPTION_NAME, [] );
        if ( !is_array( $templateData ) || empty( $templateData ) ) {
            return self::receiveTemplates( true );
        }
        return $templateData;
    }

    /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkTemplates();
    }

}
