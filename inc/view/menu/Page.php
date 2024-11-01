<?php
namespace ChadwickMarketing\SocialLite\view\menu;
use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\Utils\Capabilities;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Creates a WordPress backend menu page and demontrates a React component (public/ts/admin.tsx).
 *
 */
class Page {
    use UtilsProvider;

    const ROOT_ID = SOCIAL_LITE_SLUG;
    const UPGRADE_ID = SOCIAL_LITE_SLUG . '-upgrade';


    /**
     * Add new menu page.
     */
    public function admin_menu() {  

        $capability = Capabilities::instance()->get_capability();

        add_menu_page('Social', 'Social', $capability, self::ROOT_ID, [
            $this,
            'render_component_library'
        ], 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHZpZXdCb3g9IjAgMCAxOCAxOCI+CiAgPGRlZnM+CiAgICA8Y2xpcFBhdGggaWQ9ImNsaXAtWmVpY2hlbmZsw6RjaGVfMSI+CiAgICAgIDxyZWN0IHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+CiAgPGcgaWQ9IlplaWNoZW5mbMOkY2hlXzEiIGRhdGEtbmFtZT0iWmVpY2hlbmZsw6RjaGUg4oCTIDEiIGNsaXAtcGF0aD0idXJsKCNjbGlwLVplaWNoZW5mbMOkY2hlXzEpIj4KICAgIDxyZWN0IGlkPSJSZWNodGVja18zIiBkYXRhLW5hbWU9IlJlY2h0ZWNrIDMiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgcng9IjQiIGZpbGw9IiNmZmYiLz4KICAgIDxwYXRoIGlkPSJQZmFkXzEiIGRhdGEtbmFtZT0iUGZhZCAxIiBkPSJNMS41NjYuMDMxQzIuNDc5LjAzMSwyLjktLjQxOCwyLjktLjk0M2MwLTEuMy0yLjItLjcwOS0yLjItMS42ODgsMC0uMzU3LjI5MS0uNjQ4Ljk0NC0uNjQ4YTEuODM4LDEuODM4LDAsMCwxLC45NzkuM2wuMTI4LS4zQTIuMDE1LDIuMDE1LDAsMCwwLDEuNjQyLTMuNmMtLjkwOCwwLTEuMzE2LjQ1NC0xLjMxNi45NzksMCwxLjMyMSwyLjIuNzE5LDIuMiwxLjcsMCwuMzUyLS4yOTEuNjMyLS45NTkuNjMyQTEuNzUzLDEuNzUzLDAsMCwxLC4zODgtLjcyNEwuMjQtLjQzM0ExLjkzNiwxLjkzNiwwLDAsMCwxLjU2Ni4wMzFaTTQuNDU3LjAyNUExLjMyLDEuMzIsMCwwLDAsNS44MTktMS4zNDEsMS4zMTUsMS4zMTUsMCwwLDAsNC40NTctMi43LDEuMzE5LDEuMzE5LDAsMCwwLDMuMDkxLTEuMzQxLDEuMzI0LDEuMzI0LDAsMCwwLDQuNDU3LjAyNVptMC0uMzIxYS45ODIuOTgyLDAsMCwxLTEtMS4wNDYuOTgyLjk4MiwwLDAsMSwxLTEuMDQ1Ljk3OC45NzgsMCwwLDEsLjk5NSwxLjA0NUEuOTc4Ljk3OCwwLDAsMSw0LjQ1Ny0uM1pNNy4zOS4wMjVBMS4xODMsMS4xODMsMCwwLDAsOC40NDEtLjVMOC4xNy0uNjg4QS45MDcuOTA3LDAsMCwxLDcuMzktLjMuOTgzLjk4MywwLDAsMSw2LjM3NS0xLjM0MS45ODYuOTg2LDAsMCwxLDcuMzktMi4zODdhLjkxMi45MTIsMCwwLDEsLjc4LjRsLjI3LS4xODRBMS4xNzIsMS4xNzIsMCwwLDAsNy4zOS0yLjcsMS4zMiwxLjMyLDAsMCwwLDYuMDA4LTEuMzQxLDEuMzI1LDEuMzI1LDAsMCwwLDcuMzkuMDI1Wk05LjA1My0zLjI2OWEuMjYuMjYsMCwwLDAsLjI2NS0uMjY1LjI1Ny4yNTcsMCwwLDAtLjI2NS0uMjUuMjYxLjI2MSwwLDAsMC0uMjY1LjI1NUEuMjYyLjI2MiwwLDAsMCw5LjA1My0zLjI2OVpNOC44NjksMGguMzYyVi0yLjY4M0g4Ljg2OVpNMTAuOS0yLjdhMS43LDEuNywwLDAsMC0xLjA5MS4zNjJsLjE2My4yN2ExLjM2NywxLjM2NywwLDAsMSwuODkyLS4zMTZjLjUsMCwuNzYuMjUuNzYuNzA5di4xNjNoLS44NTJjLS43NywwLTEuMDM1LjM0Ny0xLjAzNS43NiwwLC40NjQuMzcyLjc4Ljk3OS43OGExLjAwNywxLjAwNywwLDAsMCwuOTIzLS40NDRWMGguMzQ3Vi0xLjY2M0EuOTUzLjk1MywwLDAsMCwxMC45LTIuN1pNMTAuNzcxLS4yNmMtLjQyOCwwLS42NzgtLjE5NC0uNjc4LS41LDAtLjI3NS4xNjgtLjQ3OS42ODktLjQ3OWguODQydi40MzlBLjg1OC44NTgsMCwwLDEsMTAuNzcxLS4yNlpNMTIuNjg5LDBoLjM2MlYtMy43ODRoLS4zNjJaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyIDEwLjcpIi8+CiAgPC9nPgo8L3N2Zz4K');
       

        if (social_fs()->is_free_plan()) {

            add_submenu_page(
                self::ROOT_ID,
                'Upgrade',
                'Upgrade ➤',
                $capability,
                self::UPGRADE_ID,
                [
                    $this,
                    'render_upgrade_page'
                ]
            );

        }

    }

    /**
     * Gets the additional navigation buttons shown in the top right editor menu.
     * 
     * @return array
     */
    public function get_additional_share_buttons() {
        return apply_filters(SOCIAL_LITE_OPT_PREFIX . '_additional_share_buttons', []);
    }

    /**
     * Gets the button text of the share button.
     * 
     * @return string
     */
    public function get_share_button_text() {
        return apply_filters(SOCIAL_LITE_OPT_PREFIX . '_share_button_text', '');
    }

    /**
     * Gets the onboarding content that is shown for the share button.
     * 
     * @return array
     */
    public function get_share_button_onboarding_content() {
        return apply_filters(SOCIAL_LITE_OPT_PREFIX . '_share_button_onboarding_content', [
            'title' => '',
            'description' => ''
        ]);
    }


    /**
     * Checks if the help menu is enabled.
     * 
     * @return bool
     */
    public function is_help_menu_enabled() {
        return apply_filters(SOCIAL_LITE_OPT_PREFIX . '_help_menu_enabled', true);
    }

    /**
     * Checks if the AI theme generator is enabled.
     * 
     * @return bool
     */
    public function is_ai_theme_generator_enabled() {
        return apply_filters(SOCIAL_LITE_OPT_PREFIX . '_ai_theme_generator_enabled', true);
    }

    /**
     * Check if the full screen editor is enabled.
     * 
     * @return bool
     */
    public function is_full_screen_enabled() {
        return apply_filters(SOCIAL_LITE_OPT_PREFIX . '_full_screen_editor_enabled', false);
    }

    /**
     * Check if the full screen mode is active.
     * 
     * @return bool
     */
    public function is_full_screen_active() {
        return $this->is_full_screen_enabled() ? ( !isset($_COOKIE['social_lite_full_screen']) || ( isset($_COOKIE['social_lite_full_screen']) && rest_sanitize_boolean($_COOKIE['social_lite_full_screen']) ) ) : false;
    }

    /**
     * Render the content of the menu page.
     */
    public function render_component_library() {
        $full_screen_enabled = $this->is_full_screen_active();

        echo '<div id="' . self::ROOT_ID . '-root' . '" class="wrap ' . ($full_screen_enabled ? "social-lite-full-screen-page" : "") . '"></div>';
    }

    /**
     * Render the content of the upgrade page.
     */
    public function render_upgrade_page() {
        echo '<script>window.location.href = "https://socialwp.io/pricing?utm_source=plugin&utm_medium=backend&utm_campaign=upgrade";</script>';
    }

    /**
     * New instance.
     */
    public static function instance() {
        return new Page();
    }
}