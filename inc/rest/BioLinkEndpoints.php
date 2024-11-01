<?php

namespace ChadwickMarketing\SocialLite\rest;

use ChadwickMarketing\SocialLite\base\analytics\BioLinkAnalytics;
use ChadwickMarketing\Utils\Service;
use ChadwickMarketing\Utils\Capabilities;
use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use ChadwickMarketing\SocialLite\base\forms\BioLinkForms;
use ChadwickMarketing\SocialLite\base\spotlight\BioLinkSpotlight;
use ChadwickMarketing\SocialLite\base\templates\BioLinkTemplates;
use ChadwickMarketing\SocialLite\base\woocommerce\BioLinkWooCommerce;
use ChadwickMarketing\SocialLite\base\newsletter\BioLinkNewsletter;
use WP_REST_Request;
use WP_REST_Response;
// @codeCoverageIgnoreStart
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create rest service for bio link data.
 *
 * @codeCoverageIgnore Example implementations gets deleted the most time after plugin creation!
 */
class BioLinkEndpoints {
    use UtilsProvider;
    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        $namespace = Service::getNamespace( $this );
        $capability = Capabilities::instance()->get_capability();
        // Bio link options.
        register_rest_route( $namespace, '/bio-link/options', [
            'methods'             => ['POST', 'GET', 'DELETE'],
            'callback'            => [$this, 'handleBioLinkOptions'],
            'args'                => [
                'data' => [
                    'required'    => false,
                    'type'        => 'object',
                    'description' => 'The data to push to the bio link.',
                ],
            ],
            'permission_callback' => function () use($capability) {
                return current_user_can( $capability );
            },
        ] );
        // Edit slug endpoint.
        register_rest_route( $namespace, '/bio-link/options/slug', [
            'methods'             => ['POST'],
            'callback'            => [$this, 'handleBioLinkSlug'],
            'args'                => [
                'slug' => [
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'The slug to change to.',
                ],
                'id'   => [
                    'required'    => true,
                    'type'        => 'integer',
                    'description' => 'The id of the bio link.',
                ],
            ],
            'permission_callback' => function () use($capability) {
                return current_user_can( $capability );
            },
        ] );
        // Add bio link endpoint.
        register_rest_route( $namespace, '/bio-link/add', [
            'methods'             => ['POST'],
            'callback'            => [$this, 'handleBioAdd'],
            'args'                => [
                'data' => [
                    'required'    => true,
                    'type'        => 'object',
                    'description' => 'The data to push to the bio link.',
                ],
            ],
            'permission_callback' => function () use($capability) {
                return current_user_can( $capability );
            },
        ] );
        // Duplicate bio link endpoint.
        register_rest_route( $namespace, '/bio-link/duplicate', [
            'methods'             => ['POST'],
            'callback'            => [$this, 'handleBioDuplicate'],
            'args'                => [
                'id' => [
                    'required'    => true,
                    'type'        => 'integer',
                    'description' => 'The id of the bio link.',
                ],
            ],
            'permission_callback' => function () use($capability) {
                return current_user_can( $capability );
            },
        ] );
        // Bio link analytics.
        register_rest_route( $namespace, '/bio-link/analytics', [
            'methods'             => ['GET'],
            'callback'            => [$this, 'handleBioLinkAnalytics'],
            'permission_callback' => function () use($capability) {
                return current_user_can( $capability );
            },
        ] );
        register_rest_route( $namespace, '/bio-link/analytics', [
            'methods'             => ['POST'],
            'callback'            => [$this, 'handleBioLinkAnalytics'],
            'permission_callback' => '__return_true',
        ] );
        // Bio link forms.
        register_rest_route( $namespace, '/bio-link/forms/token', [
            'methods'             => ['GET'],
            'callback'            => [$this, 'handleBioLinkFormToken'],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( $namespace, '/bio-link/forms/send', [
            'methods'             => ['POST'],
            'callback'            => [$this, 'handleBioLinkFormSubmission'],
            'args'                => [
                'data' => [
                    'required'    => true,
                    'type'        => 'object',
                    'description' => 'The form data.',
                ],
            ],
            'permission_callback' => '__return_true',
        ] );
        // WooCommerce integration.
        register_rest_route( $namespace, '/bio-link/wc/products', [
            'methods'             => ['GET', 'POST'],
            'callback'            => [$this, 'handleBioLinkWooCommerceProducts'],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( $namespace, '/bio-link/wc/categories', [
            'methods'             => ['GET', 'POST'],
            'callback'            => [$this, 'handleBioLinkWooCommerceCategories'],
            'permission_callback' => '__return_true',
        ] );
        // Spotlight integration.
        register_rest_route( $namespace, '/bio-link/sli/feed', [
            'methods'             => ['GET'],
            'callback'            => [$this, 'handleBioLinkSpotlightFeeds'],
            'permission_callback' => '__return_true',
        ] );
        // Bio link templates.
        register_rest_route( $namespace, '/bio-link/templates', [
            'methods'             => ['GET'],
            'callback'            => [$this, 'handleBioLinkTemplates'],
            'permission_callback' => function () use($capability) {
                return current_user_can( $capability );
            },
        ] );
    }

    /**
     * Handle bio link Spotlight feeds request.
     * @api {get} social-lite/v1/bio-link/sli/feed Get Bio Link Spotlight feeds.
     * @apiName GetBioLinkSpotlightFeeds
     * @apiGroup BioLink
     * @apiVersion 1.0.0
     *
     * @apiParam {String} The id of the feed.
     *
     * @apiSuccess {Object} The feed data.
     */
    public function handleBioLinkSpotlightFeeds( WP_REST_Request $request ) {
        return new WP_REST_Response(BioLinkSpotlight::instance()->getFeedById( sanitize_text_field( $request->get_param( 'id' ) ) ));
    }

    /**
     * Handle bio link duplicate request.
     * 
     * @api {post} social-lite/v1/bio-link/duplicate Duplicate bio link.
     * @apiName DuplicateBioLink
     * @apiGroup BioLink
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} The id of the bio link.
     * 
     * @apiSuccess {Object} The duplicated bio link id.
     */
    public function handleBioDuplicate( WP_REST_Request $request ) {
        return new WP_REST_Response(BioLinkData::instance()->duplicateBioLink( sanitize_text_field( $request->get_param( 'id' ) ) ));
    }

    /**
     * Handle bio link add request.
     *
     * @api {post} /wp-json/chadwick-marketing/social-lite/bio-link/add Add bio link
     * @apiName AddBioLink
     * @apiGroup BioLink
     * @apiVersion 1.0.0
     *
     * @apiParam {Object} data The data to push to the bio link.
     *
     * @apiSuccess {Object} Bio link id.
     * {
     *    "id": 1
     * }
     *
     */
    public function handleBioAdd( WP_REST_Request $request ) {
        return new WP_REST_Response(BioLinkData::instance()->addBioLink( rest_sanitize_object( $request->get_param( 'data' ) ) ));
    }

    /**
     * Handle bio link templates request.
     *
     * @api {get} social-lite/v1/bio-link/templates Get Bio Link Templates.
     * @apiHeader {String} X-WP-Nonce
     * @apiName GetBioLinkTemplates
     * @apiGroup BioLinkTemplates
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *   {data: []}
     * }
     *
     * @apiVersion 1.1.8
     */
    public function handleBioLinkTemplates( WP_REST_Request $request ) {
        return new WP_REST_Response([
            'data' => BioLinkTemplates::instance()->getTemplatesData(),
        ]);
    }

    /**
     * Handle bio link WooCommerce products request.
     *
     * @api {get} social-lite/v1/bio-link/wc/products Get WooCommerce Products.
     * @apiHeader {String} X-WP-Nonce
     * @apiName GetWooCommerceProducts
     * @apiGroup BioLinkWooCommerce
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *    {data: []}
     * }
     *
     * @apiVersion 1.1.7
     */
    public function handleBioLinkWooCommerceProducts( WP_REST_Request $request ) {
        $capability = Capabilities::instance()->get_capability();
        if ( $request->get_method() === 'GET' && !current_user_can( $capability ) ) {
            return new WP_REST_Response([
                'data' => [
                    'products' => [],
                ],
            ]);
        }
        return new WP_REST_Response([
            'data' => BioLinkWooCommerce::instance()->getProducts( ( $request->get_method() === 'POST' ? rest_sanitize_array( $request->get_json_params()['ids'] ) : null ) ),
        ]);
    }

    /**
     * Handle bio link WooCommerce categories request.
     *
     * @api {get} social-lite/v1/bio-link/wc/categories Get WooCommerce Categories.
     * @apiHeader {String} X-WP-Nonce
     * @apiName GetWooCommerceCategories
     * @apiGroup BioLinkWooCommerce
     *
     * @apiSuccessExample {json} Success-Response:
     *  {
     *     {data: []}
     * }
     *
     * @apiVersion 1.1.7
     */
    public function handleBioLinkWooCommerceCategories( WP_REST_Request $request ) {
        return new WP_REST_Response([
            'data' => [
                'categories' => [],
                'products'   => [],
            ],
        ]);
    }

    /**
     * Handle bio link form submission.
     *
     * @api {post} social-lite/v1/bio-link/forms/send Send Bio Link Form.
     * @apiHeader {String} X-WP-Nonce
     * @apiName Send
     * @apiGroup BioLinkForms
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     {success: true}
     * }
     * @apiVersion 1.1.3
     *
     * */
    public function handleBioLinkFormSubmission( WP_REST_Request $request ) {
        return new WP_REST_Response([
            'success' => BioLinkForms::instance()->handleFormSubmission( rest_sanitize_object( $request->get_json_params()['data'] ) ),
        ]);
    }

    /**
     * Handle bio form token request.
     *
     * @api {get} social-lite/v1/bio-link/forms/token Get bio link form token.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Token
     * @apiGroup BioLinkForms
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  data: {
     *  token: 'token'
     *  }
     * }
     * @apiVersion 1.1.3
     */
    public function handleBioLinkFormToken() {
        return new WP_REST_Response([
            'data' => [
                'token' => BioLinkForms::instance()->getToken(),
            ],
        ]);
    }

    /**
     * See API docs.
     *
     * @api {get} /social-lite/v1/bio-link/slug Edit bio link slug.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Options
     * @apiGroup BioLink
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     {data: []}
     * }
     * @apiVersion 0.1.0
     */
    public function handleBioLinkSlug( WP_REST_Request $request ) {
        return new WP_REST_Response([
            'data' => BioLinkData::instance()->updateBioLinkSlug( sanitize_text_field( $request->get_json_params()['id'] ), sanitize_text_field( $request->get_json_params()['slug'] ) ),
        ]);
    }

    /**
     * See API docs.
     *
     * @api {get} /social-lite/v1/bio-link/options Get bio link options.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Options
     * @apiGroup BioLink
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     {data: success}
     * }
     * @apiVersion 0.1.0
     */
    public function handleBioLinkOptions( WP_REST_Request $request ) {
        // check if request is get
        if ( $request->get_method() === 'GET' ) {
            return new WP_REST_Response([
                'data' => BioLinkAnalytics::instance()->supplyLinkAnalyticsData( BioLinkData::instance()->getBioLinks( sanitize_text_field( $request->get_param( 'id' ) ) ) ),
            ], 200);
        }
        // check if request is post
        if ( $request->get_method() === 'POST' ) {
            // update the bio link
            BioLinkData::instance()->updateBioLinkData( rest_sanitize_object( $request->get_json_params()['data'] ), sanitize_text_field( $request->get_json_params()['id'] ) );
            // return success response
            return new WP_REST_Response([
                'data' => 'success',
            ], 200);
        }
        // check if request is delete
        if ( $request->get_method() === 'DELETE' ) {
            // delete the bio link
            BioLinkData::instance()->deleteBioLink( sanitize_text_field( $request->get_param( 'id' ) ) );
            return new WP_REST_Response([
                'data' => 'success',
            ], 200);
        }
    }

    /**
     * See API docs.
     *
     * @api {post} /social-lite/v1/bio-link/analytics Send analytics.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Analytics
     * @apiGroup BioLink
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     * {data: success}
     * }
     * @apiVersion 0.1.0
     */
    public function handleBioLinkAnalytics( WP_REST_Request $request ) {
        if ( $request->get_method() === 'GET' ) {
            return new WP_REST_Response([
                'data' => BioLinkAnalytics::instance()->getAnalytics(
                    sanitize_text_field( $request->get_param( 'start' ) ),
                    sanitize_text_field( $request->get_param( 'end' ) ),
                    sanitize_text_field( $request->get_param( 'id' ) ),
                    sanitize_text_field( $request->get_param( 'linkId' ) )
                ),
            ], 200);
        }
        if ( $request->get_method() === 'POST' ) {
            if ( empty( $request->get_json_params()['data']['id'] ) ) {
                return new WP_REST_Response([
                    'data' => 'No valid id supplied.',
                ], 400);
            }
            BioLinkAnalytics::instance()->handleAnalyticsAction( sanitize_text_field( $request->get_json_params()['data']['id'] ), sanitize_text_field( $request->get_json_params()['data']['linkId'] ), sanitize_text_field( $request->get_json_params()['data']['referrer'] ) );
            return new WP_REST_Response([
                'data' => 'success',
            ], 200);
        }
    }

    /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkEndpoints();
    }

}
