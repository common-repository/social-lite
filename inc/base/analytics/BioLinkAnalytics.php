<?php

namespace ChadwickMarketing\SocialLite\base\analytics;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use DatePeriod;
use DateInterval;
use DateTime;
// @codeCoverageIgnoreStart
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Analytics class for bio link.
 */
class BioLinkAnalytics {
    use UtilsProvider;
    private static $ipLookupApis = ['http://api.ipify.org/', 'http://ipecho.net/plain', 'http://ident.me'];

    private static $ipGeoApis = ['https://ipapi.co/%s/json', 'http://ip-api.com/json/%s'];

    private static $reffererSocialNetworks = [
        'Instagram' => 'instagram.com',
        'Instagram' => 'l.instagram.com',
        'Twitter'   => 't.co',
        'TikTok'    => 'tiktok.com',
        'YouTube'   => 'youtube.com',
        'Facebook'  => 'facebook.com',
        'LinkedIn'  => 'linkedin.com',
        'Pinterest' => 'pinterest.com',
        'Reddit'    => 'reddit.com',
        'Snapchat'  => 'snapchat.com',
        'Twitch'    => 'twitch.tv',
        'Vimeo'     => 'vimeo.com',
        'Xing'      => 'xing.com',
    ];

    /**
     * Function to get an IP address.
     *
     * @return string
     */
    public static function getIp( $includePrivate = false ) {
        $ip = '';
        // Possible headers to check for an IP address.
        $fields = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        // Loop through possible headers and return the first one that contains an IP address.
        foreach ( $fields as $ip_field ) {
            if ( !empty( $_SERVER[$ip_field] ) ) {
                $ipAddresses[] = sanitize_text_field( $_SERVER[$ip_field] );
                if ( $includePrivate ) {
                    return sanitize_text_field( $_SERVER[$ip_field] );
                } else {
                    $ip = sanitize_text_field( $_SERVER[$ip_field] );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                        return $ip;
                    }
                }
            }
        }
        // Return the default IP address.
        return ( $includePrivate ? $ip : self::getExternalIp() );
    }

    /**
     * Function to get IP address externally.
     *
     * @return string
     */
    public static function getExternalIp() {
        $transientName = SOCIAL_LITE_OPT_PREFIX . '_external_ip_' . self::getIp( true );
        $externalIp = get_transient( $transientName );
        if ( empty( $externalIp ) ) {
            $externalIp = '0.0.0.0';
            foreach ( self::$ipLookupApis as $ipLookupApi ) {
                $response = wp_safe_remote_get( $ipLookupApi, [
                    'timeout' => 2,
                ] );
                if ( !is_wp_error( $response ) && rest_is_ip_address( wp_remote_retrieve_body( $response ) ) ) {
                    $externalIp = wp_remote_retrieve_body( $response );
                    break;
                }
            }
            set_transient( $transientName, $externalIp, WEEK_IN_SECONDS );
        }
        return $externalIp;
    }

    /**
     * Function to geolocate an IP address.
     *
     * @param string $ipAddress
     *
     * @return array
     */
    public static function getGeoLocation( $ipAddress = '' ) {
        if ( empty( $ipAddress ) ) {
            $ipAddress = self::getIp();
        }
        return self::geoLocateIpApi( $ipAddress );
    }

    /** Function to create a date range.
     *
     * @param string $start Start date
     * @param string $end End date
     * @param string $format Output format (Default: Y-m-d)
     *
     * @return array
     */
    public static function getDatesFromRange( $start, $end, $format = 'Y-m-d' ) {
        if ( new DateTime($start) > new DateTime($end) ) {
            return [];
        }
        $array = [];
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($end);
        $realEnd->add( $interval );
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
        foreach ( $period as $date ) {
            $array[] = $date->format( $format );
        }
        return $array;
    }

    /**
     * Function to get geo data from ip address.
     *
     * @param string $ipAddress
     *
     * @return array
     */
    private static function geoLocateIpApi( $ipAddress ) {
        $geoData = ( !empty( get_transient( SOCIAL_LITE_OPT_PREFIX . '_geo_data_' . $ipAddress ) ) ? get_transient( SOCIAL_LITE_OPT_PREFIX . '_geo_data_' . $ipAddress ) : [
            'country_name' => '',
            'country_code' => '',
            'latitude'     => '',
            'longitude'    => '',
            'state'        => '',
            'city'         => '',
            'postal_code'  => '',
        ] );
        if ( $geoData['country_name'] == '' ) {
            foreach ( self::$ipGeoApis as $ipGeoApi ) {
                $ipGeoApiEndpoint = sprintf( $ipGeoApi, $ipAddress );
                $response = wp_safe_remote_get( $ipGeoApiEndpoint, [
                    'timeout' => 2,
                ] );
                if ( !is_wp_error( $response ) && wp_remote_retrieve_body( $response ) ) {
                    switch ( $ipGeoApi ) {
                        case 'https://ipapi.co/%s/json':
                            $data = json_decode( wp_remote_retrieve_body( $response ), true );
                            $geoData['country_name'] = ( isset( $data['country_name'] ) ? sanitize_text_field( $data['country_name'] ) : '' );
                            $geoData['country_code'] = ( isset( $data['country_code'] ) ? sanitize_text_field( $data['country_code'] ) : '' );
                            $geoData['latitude'] = ( isset( $data['latitude'] ) ? sanitize_text_field( $data['latitude'] ) : '' );
                            $geoData['longitude'] = ( isset( $data['longitude'] ) ? sanitize_text_field( $data['longitude'] ) : '' );
                            $geoData['state'] = ( isset( $data['region'] ) ? sanitize_text_field( $data['region'] ) : '' );
                            $geoData['city'] = ( isset( $data['city'] ) ? sanitize_text_field( $data['city'] ) : '' );
                            $geoData['postal_code'] = ( isset( $data['postal'] ) ? sanitize_text_field( $data['postal'] ) : '' );
                            break;
                        case 'http://ip-api.com/json/%s':
                            $data = json_decode( wp_remote_retrieve_body( $response ), true );
                            $geoData['country_name'] = ( isset( $data['country'] ) ? sanitize_text_field( $data['country'] ) : '' );
                            $geoData['country_code'] = ( isset( $data['countryCode'] ) ? sanitize_text_field( $data['countryCode'] ) : '' );
                            $geoData['latitude'] = ( isset( $data['lat'] ) ? sanitize_text_field( $data['lat'] ) : '' );
                            $geoData['longitude'] = ( isset( $data['lon'] ) ? sanitize_text_field( $data['lon'] ) : '' );
                            $geoData['state'] = ( isset( $data['regionName'] ) ? sanitize_text_field( $data['regionName'] ) : '' );
                            $geoData['city'] = ( isset( $data['city'] ) ? sanitize_text_field( $data['city'] ) : '' );
                            $geoData['postal_code'] = ( isset( $data['zip'] ) ? sanitize_text_field( $data['zip'] ) : '' );
                            break;
                    }
                    if ( $geoData['country_name'] != '' ) {
                        break;
                    }
                }
            }
            set_transient( SOCIAL_LITE_OPT_PREFIX . '_geo_data_' . $ipAddress, $geoData, WEEK_IN_SECONDS );
        }
        return $geoData;
    }

    /**
     * Function to get the device type from the user agent.
     *
     * @param string $userAgent
     *
     * @return string Mobile, Tablet, Desktop
     */
    private static function getDeviceType() {
        // Get the user agent.
        $userAgent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '' );
        if ( empty( $userAgent ) ) {
            return 'Other';
        }
        if ( preg_match( '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\\.browser|up\\.link|webos|wos)/i', $userAgent ) ) {
            return 'Mobile';
        } elseif ( preg_match( '/(ipad|playbook|silk|tablet|kindle|gt-p1000)/i', $userAgent ) ) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Retrieves the referring source.
     *
     * @param string $referrer - The referring URL.
     *
     * @return string - The categorized referring source.
     */
    private static function getReferrer( $referrer = '' ) {
        if ( empty( $referrer ) || is_null( $referrer ) ) {
            return 'Direct';
        }
        $trimmedReferrer = preg_replace( '/^www\\./', '', preg_replace( '/^https?:\\/\\/(www\\.)?/', '', rtrim( $referrer, '/' ) ) );
        $socialNetwork = array_search( $trimmedReferrer, self::$reffererSocialNetworks );
        return ( $socialNetwork ? $socialNetwork : $trimmedReferrer );
    }

    /**
     * Function to check whether a request is unique.
     *
     * @param string $ipAddress
     *
     * @return boolean
     */
    private static function isUniqueRequest( $ipAddress = '' ) {
        if ( empty( $ipAddress ) ) {
            $ipAddress = self::getIp();
        }
        $transientName = SOCIAL_LITE_OPT_PREFIX . '_ip_addresses';
        $ipAddresses = ( !empty( get_transient( $transientName ) ) ? get_transient( $transientName ) : [] );
        $updatedIpAddresses = $ipAddresses;
        array_push( $updatedIpAddresses, $ipAddress );
        set_transient( $transientName, array_unique( $updatedIpAddresses, SORT_REGULAR ), WEEK_IN_SECONDS );
        return ( in_array( $ipAddress, $ipAddresses ) ? false : true );
    }

    /**
     * Function to insert analytics data.
     *
     * @param string $action
     *
     * @return void
     */
    public static function handleAnalyticsAction( $id, $linkId = null, $referrer = null ) {
        $analyticsData = BioLinkData::instance()->getBioLinkAnalyticsData( $id );
        if ( empty( $linkId ) ) {
            $updatedAnalyticsData = $analyticsData['page'];
        } else {
            $updatedAnalyticsData = $analyticsData['content'][$linkId];
            $updatedAnalyticsData['title'] = current( array_filter( BioLinkData::instance()->getBioLinkDataById( $id )['data']['content'], function ( $item ) use($linkId) {
                return $item['id'] === $linkId;
            } ) )['title'];
        }
        if ( empty( $updatedAnalyticsData[current_time( 'Y-m-d' )] ) ) {
            $updatedAnalyticsData[current_time( 'Y-m-d' )] = [
                'visits'        => 1,
                'unique_visits' => 1,
                'referrers'     => [
                    self::getReferrer( $referrer ) => [
                        'visits'        => 1,
                        'unique_visits' => 1,
                    ],
                ],
                'devices'       => [
                    self::getDeviceType() => [
                        'visits'        => 1,
                        'unique_visits' => 1,
                    ],
                ],
                'locations'     => [
                    self::getGeoLocation()['postal_code'] => [
                        'visits'          => 1,
                        'unique_visits'   => 1,
                        'additional_data' => [
                            'country_name' => self::getGeoLocation()['country_name'],
                            'country_code' => self::getGeoLocation()['country_code'],
                            'latitude'     => self::getGeoLocation()['latitude'],
                            'longitude'    => self::getGeoLocation()['longitude'],
                            'state'        => self::getGeoLocation()['state'],
                            'city'         => self::getGeoLocation()['city'],
                        ],
                    ],
                ],
            ];
        } else {
            $isUniqueRequest = self::isUniqueRequest();
            $updatedAnalyticsData[current_time( 'Y-m-d' )]['visits']++;
            $updatedAnalyticsData[current_time( 'Y-m-d' )]['unique_visits'] = ( $isUniqueRequest ? $updatedAnalyticsData[current_time( 'Y-m-d' )]['unique_visits'] + 1 : $updatedAnalyticsData[current_time( 'Y-m-d' )]['unique_visits'] );
            if ( isset( $updatedAnalyticsData[current_time( 'Y-m-d' )]['referrers'][self::getReferrer( $referrer )] ) ) {
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['referrers'][self::getReferrer( $referrer )]['visits']++;
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['referrers'][self::getReferrer( $referrer )]['unique_visits'] = ( $isUniqueRequest ? $updatedAnalyticsData[current_time( 'Y-m-d' )]['referrers'][self::getReferrer( $referrer )]['unique_visits'] + 1 : $updatedAnalyticsData[current_time( 'Y-m-d' )]['referrers'][self::getReferrer( $referrer )]['unique_visits'] );
            } else {
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['referrers'][self::getReferrer( $referrer )] = [
                    'visits'        => 1,
                    'unique_visits' => 1,
                ];
            }
            if ( isset( $updatedAnalyticsData[current_time( 'Y-m-d' )]['devices'][self::getDeviceType()] ) ) {
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['devices'][self::getDeviceType()]['visits']++;
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['devices'][self::getDeviceType()]['unique_visits'] = ( $isUniqueRequest ? $updatedAnalyticsData[current_time( 'Y-m-d' )]['devices'][self::getDeviceType()]['unique_visits'] + 1 : $updatedAnalyticsData[current_time( 'Y-m-d' )]['devices'][self::getDeviceType()]['unique_visits'] );
            } else {
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['devices'][self::getDeviceType()] = [
                    'visits'        => 1,
                    'unique_visits' => 1,
                ];
            }
            if ( isset( $updatedAnalyticsData[current_time( 'Y-m-d' )]['locations'][self::getGeoLocation()['postal_code']] ) ) {
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['locations'][self::getGeoLocation()['postal_code']]['visits']++;
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['locations'][self::getGeoLocation()['postal_code']]['unique_visits'] = ( $isUniqueRequest ? $updatedAnalyticsData[current_time( 'Y-m-d' )]['locations'][self::getGeoLocation()['postal_code']]['unique_visits'] + 1 : $updatedAnalyticsData[current_time( 'Y-m-d' )]['locations'][self::getGeoLocation()['postal_code']]['unique_visits'] );
            } else {
                $updatedAnalyticsData[current_time( 'Y-m-d' )]['locations'][self::getGeoLocation()['postal_code']] = [
                    'visits'          => 1,
                    'unique_visits'   => 1,
                    'additional_data' => [
                        'country_name' => self::getGeoLocation()['country_name'],
                        'country_code' => self::getGeoLocation()['country_code'],
                        'latitude'     => self::getGeoLocation()['latitude'],
                        'longitude'    => self::getGeoLocation()['longitude'],
                        'state'        => self::getGeoLocation()['state'],
                        'city'         => self::getGeoLocation()['city'],
                    ],
                ];
            }
        }
        BioLinkData::instance()->updateBioLinkAnalyticsData( $updatedAnalyticsData, $id, $linkId );
    }

    /**
     * Function to get analytics, based on a time period.
     *
     * @param string $dateStart
     * @param string $dateEnd
     *
     * @return array
     */
    public static function getAnalytics(
        $dateStart,
        $dateEnd,
        $id,
        $linkId = null
    ) {
        $pageAnalyticsData = $contentAnalyticsData = [];
        if ( empty( $linkId ) ) {
            // If no link ID is provided, get analytics for all links and the bio link page.
            $pageAnalyticsData = BioLinkData::instance()->getBioLinkAnalyticsData( $id )['page'];
            $contentAnalyticsData = BioLinkData::instance()->getBioLinkAnalyticsData( $id )['content'];
        } else {
            // If a link ID is provided, get analytics for that link only.
            $contentAnalyticsData = array_filter( BioLinkData::instance()->getBioLinkAnalyticsData( $id )['content'], function ( $item ) use($linkId) {
                return $item === $linkId;
            }, ARRAY_FILTER_USE_KEY );
            $filteredPageAnalyticsData['lifetime']['top_device'] = ( !empty( array_values( $contentAnalyticsData ) ) ? array_reduce( array_map( function ( $item ) {
                return array_column( $item, 'devices' );
            }, array_values( $contentAnalyticsData ) )[0], function ( $carry, $item ) {
                $device = key( $item );
                if ( array_key_exists( $device, $carry ) ) {
                    $carry[$device]['visits'] += $item[$device]['visits'];
                } else {
                    $carry = $item;
                }
                return $carry;
            }, [] ) : [] );
            $filteredPageAnalyticsData['lifetime']['top_location'] = ( !empty( array_values( $contentAnalyticsData ) ) ? array_reduce( array_reduce( array_map( function ( $item ) {
                return array_column( $item, 'locations' );
            }, array_values( $contentAnalyticsData ) )[0], function ( $carry, $item ) {
                $item = current( $item );
                $location = $item['additional_data']['city'];
                if ( array_key_exists( $location, $carry ) ) {
                    $carry[$location]['visits'] += $item['visits'];
                } else {
                    $carry[$location] = $item;
                }
                return $carry;
            }, [] ), function ( $carry, $item ) {
                // Sort by visits, only keep the first 1.
                if ( count( $carry ) < 1 ) {
                    $carry[] = $item;
                } else {
                    if ( $item['visits'] > $carry[0]['visits'] ) {
                        $carry[0] = $item;
                    }
                }
                return $carry;
            }, [] ) : [] );
        }
        if ( isset( $pageAnalyticsData ) || isset( $contentAnalyticsData ) ) {
            // Get dates between the two dates.
            $dateRange = self::getDatesFromRange( $dateStart, $dateEnd );
            // Referrers, devices, locations, and trending links for the date range.
            $referrers = $devices = $locations = $trendingLinks = [];
            // Summarize all visits and clicks, lifetime.
            $totalVisits = array_sum( array_column( $pageAnalyticsData, 'visits' ) );
            $totalClicks = array_sum( array_map( function ( $item ) {
                return array_sum( array_column( $item, 'visits' ) );
            }, $contentAnalyticsData ) );
            // Summarize all visits and clicks, for the selected time period.
            foreach ( $dateRange as $date ) {
                $clicks = $uniqueClicks = 0;
                $visits = $uniqueVisits = 0;
                if ( isset( $pageAnalyticsData[$date] ) ) {
                    $visits = $pageAnalyticsData[$date]['visits'];
                    $uniqueVisits = $pageAnalyticsData[$date]['unique_visits'];
                }
                foreach ( array_values( $contentAnalyticsData ) as $contentAnalytics ) {
                    if ( isset( $contentAnalytics[$date] ) ) {
                        $clicks += $contentAnalytics[$date]['visits'];
                        $uniqueClicks += $contentAnalytics[$date]['unique_visits'];
                    }
                }
                $visits = rand( 10, 100 );
                $clicks = rand( 20, 50 );
                $filteredPageAnalyticsData['overview']['range'][$date] = [
                    'visits'             => $visits,
                    'unique_visits'      => rand( 50, 70 ),
                    'clicks'             => $clicks,
                    'unique_clicks'      => rand( 10, 40 ),
                    'click_through_rate' => ( $visits > 0 ? round( $clicks / $visits * 100, 2 ) : 0 ),
                ];
            }
            $filteredPageAnalyticsData['locations'] = [
                '31234' => [
                    'location'        => 'United States',
                    'visits'          => 50,
                    'clicks'          => 0,
                    'additional_data' => [
                        'country'      => 'United States',
                        'country_code' => 'US',
                        'region'       => 'California',
                        'region_code'  => 'CA',
                        'city'         => 'San Francisco',
                        'latitude'     => 37.7749,
                        'longitude'    => -122.4194,
                    ],
                ],
                '31235' => [
                    'location'        => 'United Kingdom',
                    'visits'          => 30,
                    'clicks'          => 0,
                    'additional_data' => [
                        'country'      => 'United Kingdom',
                        'country_code' => 'GB',
                        'region'       => 'England',
                        'region_code'  => 'ENG',
                        'city'         => 'London',
                        'latitude'     => 51.5074,
                        'longitude'    => -0.1278,
                    ],
                ],
                '31236' => [
                    'location'        => 'India',
                    'visits'          => 10,
                    'clicks'          => 0,
                    'additional_data' => [
                        'country'      => 'India',
                        'country_code' => 'IN',
                        'region'       => 'Maharashtra',
                        'region_code'  => 'MH',
                        'city'         => 'Mumbai',
                        'latitude'     => 19.076,
                        'longitude'    => 72.8777,
                    ],
                ],
                '31238' => [
                    'location'        => 'Australia',
                    'visits'          => 80,
                    'clicks'          => 0,
                    'additional_data' => [
                        'country'      => 'Australia',
                        'country_code' => 'AU',
                        'region'       => 'New South Wales',
                        'region_code'  => 'NSW',
                        'city'         => 'Sydney',
                        'latitude'     => -33.8688,
                        'longitude'    => 151.2093,
                    ],
                ],
            ];
            $filteredPageAnalyticsData['devices'] = [
                'Mobile'  => [
                    'device' => 'Mobile',
                    'visits' => 100,
                    'clicks' => 0,
                ],
                'Desktop' => [
                    'device' => 'Desktop',
                    'visits' => 50,
                    'clicks' => 0,
                ],
                'Others'  => [
                    'device' => 'Others',
                    'visits' => 50,
                    'clicks' => 0,
                ],
            ];
            $filteredPageAnalyticsData['referrers'] = [
                'Direct'    => [
                    'referrer' => 'Direct',
                    'visits'   => 50,
                    'clicks'   => 0,
                ],
                'Twitter'   => [
                    'referrer' => 'Twitter',
                    'visits'   => 100,
                    'clicks'   => 0,
                ],
                'TikTok'    => [
                    'referrer' => 'TikTok',
                    'visits'   => 50,
                    'clicks'   => 0,
                ],
                'Instagram' => [
                    'referrer' => 'Instagram',
                    'visits'   => 50,
                    'clicks'   => 0,
                ],
            ];
            $filteredPageAnalyticsData['trending'] = [[
                'title'  => __( 'Berlin\'s best coffee shops', SOCIAL_LITE_TD ),
                'visits' => 75,
            ], [
                'title'  => __( 'Recipe: Yummy cookies', SOCIAL_LITE_TD ),
                'visits' => 60,
            ], [
                'title'  => __( 'Read my latest book', SOCIAL_LITE_TD ),
                'visits' => 35,
            ]];
            $filteredPageAnalyticsData['lifetime']['total'] = [
                'visits'             => 1250,
                'clicks'             => 650,
                'click_through_rate' => round( 650 / 1250 * 100, 2 ),
            ];
            $filteredPageAnalyticsData['overview']['total'] = [
                'visits'        => array_sum( array_column( $filteredPageAnalyticsData['overview']['range'], 'visits' ) ),
                'unique_visits' => array_sum( array_column( $filteredPageAnalyticsData['overview']['range'], 'unique_visits' ) ),
                'clicks'        => array_sum( array_column( $filteredPageAnalyticsData['overview']['range'], 'clicks' ) ),
                'unique_clicks' => array_sum( array_column( $filteredPageAnalyticsData['overview']['range'], 'unique_clicks' ) ),
            ];
            $filteredPageAnalyticsData['overview']['total']['click_through_rate'] = ( $filteredPageAnalyticsData['overview']['total']['visits'] > 0 ? round( $filteredPageAnalyticsData['overview']['total']['clicks'] / $filteredPageAnalyticsData['overview']['total']['visits'] * 100, 2 ) : 0 );
        }
        return $filteredPageAnalyticsData;
    }

    /**
     * Function to supply individual link analytics data to $bioLinkData array
     *
     * @param array $bioLinkData
     *
     * @return array $bioLinkData
     */
    public function supplyLinkAnalyticsData( $bioLinkData ) {
        if ( isset( $bioLinkData[0] ) && isset( $bioLinkData[0]['data'] ) && isset( $bioLinkData[0]['data']['content'] ) && is_array( $bioLinkData[0]['data']['content'] ) ) {
            foreach ( $bioLinkData[0]['data']['content'] as $key => $content ) {
                // Get analytics and assign clicks value
                $bioLinkData[0]['data']['content'][$key]['clicks'] = $this->getAnalytics(
                    null,
                    null,
                    $bioLinkData[0]['id'],
                    $content['id']
                )['lifetime']['total']['clicks'];
            }
        }
        return $bioLinkData;
    }

    /**
     * New instance.
     *
     */
    public static function instance() {
        return new BioLinkAnalytics();
    }

}
