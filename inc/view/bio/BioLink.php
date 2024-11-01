<?php


namespace ChadwickMarketing\SocialLite\view\bio;

use ChadwickMarketing\SocialLite\base\seo\BioLinkSEO;
use ChadwickMarketing\SocialLite\base\analytics\BioLinkAnalytics;
use ChadwickMarketing\SocialLite\base\shortcodes\BioLinkShortcodes;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;


use WP_Query;

class BioLink {

	public function register() {

        add_filter('template_include', [
            $this,
            'renderSocialTemplate'
        ]);
        add_filter('post_type_link', [
            $this,
            'removeCPTLandingpageSlug'
        ], 10, 3);
        add_filter('pre_get_posts', [
            $this,
            'parseRequest'
        ]);
        add_filter( 'pre_handle_404',
            function ($value, $query ) {
                return $this->parseRequestOnError( $value, $query );
            },
        10, 2 ); 

    }

    /**
    * Render landing page template
    *
    * @return $single_template
    * @since 1.0.0
    */
    public function renderSocialTemplate($single_template) {

        if (!BioLinkData::instance()->isBioLinkAvailable()) {
          return $single_template;
        }

        $id = get_the_ID();

        $bio_link = BioLinkData::instance()->getBioLink($id);

        if (is_front_page() || is_home()) {

            $homepage_bio_link = BioLinkData::instance()->getHomepageBioLink();

            if ($homepage_bio_link) {

               if (BioLinkShortcodes::instance()->shouldRenderShortcode()) {
                    return SOCIAL_LITE_INC . 'base/shortcodes/view/BioLinkShortcodesTemplate.php';
               }

               BioLinkSEO::instance()->generateSEOMeta($homepage_bio_link);
               
               return SOCIAL_LITE_INC . 'view/bio/BioLinkTemplate.php';

            }
        } elseif (isset($GLOBALS['post']->post_type) && $GLOBALS['post']->post_type === 'cms-landingpages' && !is_search()) {

            if (BioLinkShortcodes::instance()->shouldRenderShortcode()) {
                     return SOCIAL_LITE_INC . 'base/shortcodes/view/BioLinkShortcodesTemplate.php';
            }

            if (!$bio_link[0]['data']['meta']['homepage']) {

                BioLinkSEO::instance()->generateSEOMeta($bio_link[0]);

                return SOCIAL_LITE_INC . 'view/bio/BioLinkTemplate.php';

            } else {
                wp_redirect(home_url());
                exit;
            }

        }


        return $single_template;
      }





    /**
    * Remove the slug thats automatically being generated for cpts, in this case 'links-shortened' or 'landingpage'
    *
    * @return $post_link
    * @since 1.0.0
    */

    public function removeCPTLandingpageSlug($post_link, $post, $leavename) {

        if ( 'cms-landingpages' != $post->post_type || 'publish' != $post->post_status ) {
            return $post_link;
        }

        return get_home_url() . '/' . $post->post_name . '/';

    }

    /**
    * Parse request for link in bio pages
    *
    * @since 1.0.0
    */
    public function parseRequest($query) {

        if ( ! $query->is_main_query() ||  ! isset( $query->query['page']) || isset( $query->query['post_type']) ) {
            return;
        }

        if ( ! empty( $query->query['name']  ) ) {

            $query->set('post_type', [ 'post', 'page', 'cms-landingpages' ] );

        } elseif ( ! empty( $query->query['pagename'] ) && false === strpos( $query->query['pagename'], '/' ) && ! $query->is_posts_page ) {

            $query->set( 'post_type', [ 'post', 'page', 'cms-landingpages' ] );

            $query->set( 'name', $query->query['pagename'] );

        }
       
    }

    /**
    * Handle 404
    *
    * This method runs after a page is not found in the database, but before a page is returned as a 404.
    * These cases are handled in this filter callback, that runs on the 'pre_handle_404' filter.
    *
    * @since 1.0.0
    */

    public function parseRequestOnError($value, $query) {

        global $wp_query;

        if ( $value ) { 
            return $value;
        }

        if ( is_admin() ) {
            return false;
        }

        if ( ! empty($query->query['name']) ) {

            $new_query = new WP_Query( [
                'post_type' => ['cms-landingpages'],
                'name' => $query->query['name'],
            ] );

        } else {

            $name = strtolower(preg_replace('/[\W\s\/]+/', '-', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')));

            if (empty($name)) {
                return false;
            }

            $new_query = new WP_Query( [
                'post_type' => ['cms-landingpages'],
                'name' => $name,
            ] );

        }

        if ( ! empty( $new_query->posts ) ) {
            $wp_query = $new_query;
        }  

        return false;
    }


    /**
     * New instance.
     */
    public static function instance() {
        return new BioLink();
    }


}