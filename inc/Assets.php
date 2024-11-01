<?php

namespace ChadwickMarketing\SocialLite;

use ChadwickMarketing\SocialLite\view\menu\Page;
use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\Utils\Assets as UtilsAssets;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use ChadwickMarketing\SocialLite\base\spotlight\BioLinkSpotlight;
use ChadwickMarketing\SocialLite\base\woocommerce\BioLinkWooCommerce;
use ChadwickMarketing\SocialLite\base\shortcodes\BioLinkShortcodes;
use ChadwickMarketing\Utils\Capabilities;
// @codeCoverageIgnoreStart
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Asset management for frontend scripts and styles.
 */
class Assets {
    use UtilsProvider;
    use UtilsAssets;
    /**
     * Get the dependencies for the frontend and backend.
     *
     * @return array
     */
    public function getDependencies() {
        $dependencies = [[
            'id'     => 'woocommerce',
            'active' => BioLinkWooCommerce::instance()->isWooCommerceActive(),
        ], [
            'id'     => 'spotlight',
            'active' => BioLinkSpotlight::instance()->isSpotlightActive(),
        ]];
        return $dependencies;
    }

    /**
     * Function to check if the current page is a bio link page.
     * 
     * @return bool
     */
    public function isBioLinkPage() {
        global $post;
        if ( BioLinkData::instance()->isBioLinkAvailable() ) {
            if ( BioLinkData::instance()->getHomepageBioLink() && (is_front_page() || is_home()) || isset( $post->post_type ) && $post->post_type === 'cms-landingpages' && !is_search() ) {
                if ( BioLinkShortcodes::instance()->shouldRenderShortcode() ) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Enqueue scripts and styles depending on the type. This function is called
     * from both admin_enqueue_scripts and wp_enqueue_scripts. You can check the
     * type through the $type parameter. In this function you can include your
     * external libraries from src/public/lib, too.
     *
     * @param string $type The type (see utils Assets constants)
     * @param string $hook_suffix The current admin page
     */
    public function enqueue_scripts_and_styles( $type, $hook_suffix = null ) {
        // Generally check if an entrypoint should be loaded
        if ( !in_array( $type, [self::$TYPE_ADMIN, self::$TYPE_FRONTEND], true ) ) {
            return;
        }
        // Your assets implementation here... See utils Assets for enqueue* methods
        // $useNonMinifiedSources = $this->useNonMinifiedSources(); // Use this variable if you need to differ between minified or non minified sources
        // Our utils package relies on jQuery, but this shouldn't be a problem as the most themes still use jQuery (might be replaced with https://github.com/github/fetch)
        // Enqueue external utils package
        $scriptDeps = $this->enqueueUtils();
        // Enqueue plugin entry points
        if ( $type === self::$TYPE_ADMIN ) {
            $handle = $this->enqueueScript(
                'admin',
                'admin.js',
                $scriptDeps,
                array(
                    'in_footer' => true,
                    'strategy'  => 'async',
                )
            );
            if ( $this->isScreenBase( 'toplevel_page_social-lite' ) || $this->isScreenBase( 'social_page_social-lite-pricing' ) || $this->isScreenBase( 'admin_page_social-lite-network' ) || $this->isScreenBase( 'social_page_social-lite-account-network' ) || $this->isScreenBase( 'social_page_social-lite-account' ) ) {
                $this->enqueueStyle( 'admin', 'admin.css' );
                $this->enqueueStyle( 'bio-link', 'bio-link.css' );
                if ( array_filter( $this->getDependencies(), function ( $dependency ) {
                    return $dependency['id'] === 'spotlight' && $dependency['active'];
                } ) ) {
                    do_action( 'spotlight/instagram/enqueue_front_app' );
                }
                wp_enqueue_media();
            }
        } elseif ( $type === self::$TYPE_FRONTEND ) {
            global $post;
            if ( $this->isBioLinkPage() ) {
                foreach ( wp_scripts()->registered as $wp_script ) {
                    // only dequeue if this script isn't from social-lite
                    if ( strpos( $wp_script->src, SOCIAL_LITE_SLUG ) === false ) {
                        wp_dequeue_script( $wp_script->handle );
                    }
                }
                foreach ( wp_styles()->registered as $wp_style ) {
                    // only dequeue if this style isn't from social-lite
                    if ( strpos( $wp_style->src, SOCIAL_LITE_SLUG ) === false ) {
                        wp_dequeue_style( $wp_style->handle );
                    }
                }
                if ( array_filter( $this->getDependencies(), function ( $dependency ) {
                    return $dependency['id'] === 'spotlight' && $dependency['active'];
                } ) ) {
                    do_action( 'spotlight/instagram/enqueue_front_app' );
                }
                // dequeue block styles
                wp_dequeue_style( 'global-styles' );
                wp_dequeue_style( 'wc-block-style' );
                wp_dequeue_style( 'wp-block-library' );
                wp_dequeue_style( 'wp-block-library-theme' );
                // remove admin bar styles
                remove_action( 'wp_head', '_admin_bar_bump_cb' );
                remove_action( 'wp_head', 'wp_generator' );
                // third party plugins
                remove_all_actions( 'rank_math/head' );
                add_filter( 'pre_get_rocket_option_delay_js', '__return_zero' );
                // enqueue our scripts and styles
                $handle = $this->enqueueScript(
                    'frontend',
                    'frontend.js',
                    $scriptDeps,
                    array(
                        'in_footer' => true,
                        'strategy'  => 'async',
                    )
                );
            }
            if ( BioLinkShortcodes::instance()->shouldRenderShortcode() || $this->isBioLinkPage() ) {
                add_filter( 'show_admin_bar', '__return_false' );
                $this->enqueueStyle( 'frontend', 'frontend.css' );
                if ( !$this->isBioLinkPage() ) {
                    $this->enqueueIframeResizer();
                }
            }
        }
        // Localize script with server-side variables
        if ( isset( $handle ) ) {
            wp_localize_script( $handle, SOCIAL_LITE_SLUG_CAMELCASE, $this->localizeScript( $type ) );
        }
    }

    /**
     * Preload the frontend styles.
     * @param string $html
     * @param string $handle
     * 
     * @return string
     */
    public function preload_frontend_styles( $html, $handle ) {
        if ( strcmp( $handle, 'social-lite-frontend' ) == 0 ) {
            $fallback = '<noscript>' . $html . '</noscript>';
            $preload = str_replace( "rel='stylesheet'", "rel='preload' as='style' onload='this.rel=\"stylesheet\"'", $html );
            $html = $preload . $fallback;
        }
        return $html;
    }

    /**
     * Localize the WordPress backend and frontend. If you want to provide URLs to the
     * frontend you have to consider that some JS libraries do not support umlauts
     * in their URI builder. For this you can use utils Assets#getAsciiUrl.
     *
     * Also, if you want to use the options typed in your frontend you should
     * adjust the following file too: src/public/ts/store/option.tsx
     *
     * @param string $context
     * @return array
     */
    public function overrideLocalizeScript( $context ) {
        if ( $context === self::$TYPE_ADMIN ) {
            $optionsBackend = [
                'siteURL'                      => get_home_url(),
                'upgradeURL'                   => 'https://socialwp.io/pricing?utm_source=plugin&utm_medium=backend&utm_campaign=upgrade',
                'socialBadgeEnabled'           => apply_filters( SOCIAL_LITE_OPT_PREFIX . '_social_badge_enabled', true ),
                'setHomepageBioLinkAsDefault'  => apply_filters( SOCIAL_LITE_OPT_PREFIX . '_set_homepage_bio_link_as_default', false ),
                'shareButtonText'              => Page::instance()->get_share_button_text(),
                'shareButtonOnboardingContent' => Page::instance()->get_share_button_onboarding_content(),
                'additionalShareButtons'       => Page::instance()->get_additional_share_buttons(),
                'helpMenuEnabled'              => Page::instance()->is_help_menu_enabled(),
                'aiThemeGeneratorEnabled'      => Page::instance()->is_ai_theme_generator_enabled(),
                'fullScreenEditorEnabled'      => Page::instance()->is_full_screen_enabled(),
                'supportURL'                   => social_fs()->contact_url(),
                'dependencies'                 => $this->getDependencies(),
                'isMultiUserMode'              => false,
                'playground'                   => false,
            ];
            return $optionsBackend;
        } elseif ( $context === self::$TYPE_FRONTEND ) {
            $optionsFrontend = [
                '__INITIAL_STATE__' => BioLinkData::instance()->getBioLinkDataFrontEnd( ( BioLinkData::instance()->getHomepageBioLink() && (is_front_page() || is_home()) ? BioLinkData::instance()->getHomepageBioLink()['id'] : get_the_id() ) ),
                'dependencies'      => $this->getDependencies(),
            ];
            return $optionsFrontend;
        }
        return [];
    }

}
