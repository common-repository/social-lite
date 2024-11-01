<?php

/**
 *
 * Send notifications via email.
 *
 * @since 1.1.3
 */

namespace ChadwickMarketing\SocialLite\base\notifications;

use ChadwickMarketing\SocialLite\base\UtilsProvider;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkNotifications {

    use UtilsProvider;

    /**
     * Format the email content.
     *
     * @param array $data
     *
     * @return string
     */
    public function formEmailContent($data) {

        $html = '<ul>';

        foreach ($data as $key => $value) {
            $html .= '<li><strong>' . esc_html(ucfirst($key)) . '</strong>: ' . esc_html($value) . '</li>';
        }

        $html .= '</ul>';

        return $html;

    }



    /**
     * Send email notifications.
     *
     * @param string $to
     * @param array $data
     * @return void
     */
    public function sendMailNotification($to, $data) {

        return wp_mail($to, __('New form submission', SOCIAL_LITE_TD), self::formEmailContent($data), ['Content-Type: text/html; charset=UTF-8;']);

    }

      /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkNotifications();
    }


}