<?php

namespace ChadwickMarketing\SocialLite\base\data;

use ChadwickMarketing\Utils\Capabilities;

use WP_Query;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Manage bio link data.
 */

class BioLinkData {

    private $bioLinks;

    /**
     * C'tor.
     */
    public function __construct() {

        $query = [
            'post_type' => 'cms-landingpages',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $this->bioLinks = get_posts($query);

    }

    /**
     * Check if bio link is available.
     *
     * @return bool
     */
    public function isBioLinkAvailable() {
        return count($this->bioLinks) > 0;
    }

    
    /**
     * Get bio link data by id.
     *
     * @param int $id
     * @return array
     */
    public function getBioLinkDataById($id) {

        $bioLink = get_post_meta($id, 'cms-landingpage-data', true);

        return [
            'id' => $id,
            'data' => $bioLink ?? [],
        ];

    }

    /**
     * Get bio link.
     *
     * @return int
     */
    public function getBioLink($id) {

        $bioLink = $this->getBioLinks($id);

        return $bioLink;

    }

    /**
     * Get bio links.
     *
     * @return array
     */
    public function getBioLinks($id = null) {

        if ($id == null) {
            $bioLink = array_map([$this, 'getBioLinkDataById'], array_column($this->bioLinks, 'ID'));
        } else {
            $bioLink = array_map([$this, 'getBioLinkDataById'], array_column( array_filter(
                $this->bioLinks,
                function ($bioLink) use ($id) {
                    return $bioLink->ID == $id;
                }
            ), 'ID'));

        }

        return $bioLink;

    }

    /**
     * Get bio link set as homepage.
     *
     * @return int
     */
    public function getHomepageBioLink() {

        $bioLink = $this->getBioLinks();

        foreach ($bioLink as $item) {
            if ($item['data']['meta']['homepage']) {
                return $item;
            }
        }

        return false;

    }

    /**
     * Delete bio link by id.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBioLink($id) {

        if (get_post_type($id) !== 'cms-landingpages') {
            return false;
        }

        return wp_delete_post($id, true);

    }


    /**
     * Get bio link data for the front end.
     *
     * @param int $id
     *
     * @return array
     */
    public function getBioLinkDataFrontEnd($id) {

        $bioLinkData = $this->getBioLinkDataById($id)['data'];

        foreach ($bioLinkData['content'] as $key => $item) {

            // Get edit action key.
            $editActionKey = current(array_filter(array_keys($item['content']), function ($item) {
                return strpos($item, 'Edit') !== false;
            }));

            // Remove every property with __private__ prefix.
            if (isset($item['content'][$editActionKey])) {
               $bioLinkData['content'][$key]['content'][$editActionKey] = array_filter($bioLinkData['content'][$key]['content'][$editActionKey], function ($key) {
                    return strpos($key, '__private__') === false;
                }, ARRAY_FILTER_USE_KEY);
            }

            // Remove clicks from the front end.
            if (isset($item['clicks'])) {
                unset($bioLinkData['content'][$key]['clicks']);
            }

        }

        return $bioLinkData;

    }


    /**
     * Get bio link analytics.
     *
     *  @param int $id
     *
     * @return array
     */
    public function getBioLinkAnalyticsData($id) {

        $analyticsData = get_post_meta($id, 'cms-landingpage-analytics', true);

        if (get_post_type($id) !== 'cms-landingpages' || empty($analyticsData) || !isset($analyticsData['page']) || !isset($analyticsData['content']) ) {
            $analyticsData = [
                'page' => [],
                'content' => []
            ];

        }

        return $analyticsData;

    }

    /**
     * Update the slug of the bio link.
     *
     * @param int $bioLinkId
     * @param string $slug
     *
     * return string $slug
     */

    public function updateBioLinkSlug($bioLinkId, $slug) {

          if (get_post_type($bioLinkId) !== 'cms-landingpages') {
                return false;
          }

          if (strtolower($this->getBioLinkDataById($bioLinkId)['data']['meta']['slug']) == strtolower($slug)) {
                return $slug;
          }

          if (empty($slug)) {
              return $this->getBioLinkDataById($bioLinkId)['data']['meta']['slug'];
          }

          // generate a slug
          $new_slug = wp_unique_post_slug( $slug, $bioLinkId, 'publish', 'post', 0 );

          return get_post_field('post_name', wp_update_post([
              'ID' => $bioLinkId,
              'post_name' => wp_unique_post_slug( $new_slug, $bioLinkId, 'publish', 'page', 0 ),
          ]));

    }


    /**
     * Update bio link data.
     *
     * @param array $data
     * @param int $id.
     * @param bool $initial
     */

    public function updateBioLinkData($data, $id, $initial = false) {

        // Check if the post type is correct
        if (get_post_type($id) !== 'cms-landingpages') {
            return false;
        }

        // Make sure only one bio link can be homepage
        if ($data['meta']['homepage']) {

                $bioLinks = $this->getBioLinks();

                foreach ($bioLinks as $bioLink) {

                    if ($bioLink['id'] !== $id) {

                        $bioLinkData = $bioLink['data'];

                        $bioLinkData['meta']['homepage'] = false;

                        update_post_meta($bioLink['id'], 'cms-landingpage-data', $bioLinkData);

                    }

                }

        }

        // Remove action current for each item in the date content array
        foreach ($data['content'] as $key => $value) {

                // set action active to false
                $data['content'][$key]['action']['active'] = false;

                // unset action current
                unset($data['content'][$key]['action']['current']);

        }

        // Set last updated date. Only if any of the content has changed.
        if (isset($id) && $this->getBioLinkDataById($id)['data'] !== $data) {
                $data['meta']['updated'] = gmdate('Y-m-d H:i:s');
        }


        update_post_meta($id, 'cms-landingpage-data', $data);

    }

    /**
     * Update bio link analytics data.
     *
     * @param array $data
     */
    public function updateBioLinkAnalyticsData($data, $id, $linkId = null) {

        $analyticsData = $this->getBioLinkAnalyticsData($id);

        if ( empty($linkId) ) {

            $analyticsData['page'] = array_unique(array_merge( $analyticsData['page'], $data ), SORT_REGULAR);

        } else {

            $analyticsData['content'][ $linkId ] = $data;

        }

        update_post_meta($id, 'cms-landingpage-analytics', $analyticsData);

    }


    /**
     * Duplicate a bio link page by id.
     * 
     * @param int $id
     * @return int $bioLinkId
     */
    public function duplicateBioLink($id) {

        $bioLink = $this->getBioLink($id);

        if (get_post_type($id) !== 'cms-landingpages') {
            return false;
        }

        $bioLinkData = $bioLink[0]['data'];

        $bioLinkData['meta']['name'] = $bioLinkData['meta']['name'] . ' Copy';

        $bioLinkData['meta']['slug'] = $bioLinkData['meta']['slug'] . '-copy';

        $bioLinkData['meta']['homepage'] = false;

        $bioLinkData['meta']['created'] = gmdate('Y-m-d H:i:s');

        $bioLinkData['meta']['updated'] = gmdate('Y-m-d H:i:s');

        $bioLinkData['meta']['author'] = wp_get_current_user()->ID;

        $bioLinkData['content'] = array_map(
            function($contentItem) {
                return array_merge($contentItem, [
                    'clicks' => 0,
                    'action' => [
                        'active' => false,
                        'current' => '',
                    ],
                    'error' => [
                        'error' => false,
                        'message' => '',
                    ],
                ]);
            },
            $bioLinkData['content']
        );

        $bioLinkId = wp_insert_post([
            'post_title' => $bioLinkData['meta']['name'],
            'post_name' => $bioLinkData['meta']['slug'],
            'post_status' => 'publish',
            'post_type' => 'cms-landingpages',
        ]);

        $this->updateBioLinkData($bioLinkData, $bioLinkId, true);

        flush_rewrite_rules();

        return [
            'id' => $bioLinkId,
            'data' => $bioLinkData,
        ];

    }


    /**
     * Add a new bio link page.
     *
     * @param array $bioLinkData
     *
     * @return int $bioLinkId
     */
    public function addBioLink($bioLinkData) {

        $bioLinkId = wp_insert_post([
            'post_title' => 'Links',
            'post_name' => $bioLinkData['meta']['slug'],
            'post_status' => 'publish',
            'post_type' => 'cms-landingpages',
        ]);


        // Make badge optional
        $bioLinkData['options']['badge'] = false;

        // Add meta data
        $bioLinkData['meta'] = [
            'name' => 'Links',
            'slug' => get_post_field('post_name', $bioLinkId),
            'homepage' => $bioLinkData['meta']['homepage'],
            'index' => true,
            'author' => wp_get_current_user()->ID,
            'created' => gmdate('Y-m-d H:i:s'),
            'updated' => gmdate('Y-m-d H:i:s'),
            'title' => '',
            'description' => '',
            'image' => '',
        ];

        // Add content data
        $bioLinkData['content'] = array_map(
            function($contentItem) {
                return array_merge($contentItem, [
                    'clicks' => 0,
                    'action' => [
                        'active' => false,
                        'current' => '',
                    ],
                    'error' => [
                        'error' => false,
                        'message' => '',
                    ],
                ]);
            },
            $bioLinkData['content']
        );

        $this->updateBioLinkData($bioLinkData, $bioLinkId, true);

        flush_rewrite_rules();

        return [
            'id' => $bioLinkId,
            'data' => $bioLinkData,
        ];

    }

    /**
     * New instance.
     *
     */
    public static function instance() {
        return new BioLinkData();
    }

}
