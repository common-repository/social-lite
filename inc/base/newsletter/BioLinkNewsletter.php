<?php
/**
 *
 * Manage newsletter subscriptions to Mailchimp.
 *
 * @since 1.3.7
 */

namespace ChadwickMarketing\SocialLite\base\newsletter;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;

// @codeCoverageIgnoreStart
defined("ABSPATH") or die("No script kiddies please!"); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkNewsletter
{
    use UtilsProvider;

    private static $supportedProviders = ["mailchimp", "activecampaign"];

    /**
     * Get email lists.
     *
     * @param string $apiKey
     * @param string $accountId
     * 
     * @return array
     */
    public function getEmailLists($provider, $apiKey, $accountId)
    {
        if (!in_array($provider, self::$supportedProviders)) {
            return [
                "error" => "Invalid provider.",
                "lists" => [],
            ];
        }

        if ($provider === "mailchimp") {
            return $this->getMailchimpLists($apiKey);
        } else if ($provider === "activecampaign") {
            return $this->getActiveCampaignLists($apiKey, $accountId);
        }
       
    }

    /**
     * Get email list from Mailchimp.
     * @param string $apiKey
     * 
     * @return array
     */
    public function getMailchimpLists($apiKey)
    {
        $apiKeyArr = explode("-", $apiKey);
        $domain = end($apiKeyArr);

        if (empty($domain)) {
            return [
                "error" => "Invalid API key.",
                "lists" => [],
            ];
        }

        $response = wp_remote_get(
            sprintf("https://%s.api.mailchimp.com/3.0/lists", $domain),
            [
                "headers" => [
                    "Authorization" => "Basic " . base64_encode("a:" . $apiKey),
                ],
            ]
        );

        if (is_wp_error($response)) {
            return [
                "error" => "Invalid API key.",
                "lists" => [],
            ];
        }

        $reponseBody = json_decode(wp_remote_retrieve_body($response), true);

        if (
            200 !== wp_remote_retrieve_response_code($response) ||
            empty($reponseBody["lists"])
        ) {
            return [
                "error" => "Invalid API key.",
                "lists" => [],
            ];
        }

        return [
            "error" => "",
            "lists" => array_map(function ($list) {
                return [
                    "id" => $list["id"],
                    "name" => $list["name"],
                    "stats" => [
                        "member_count" => $list["stats"]["member_count"],
                    ],
                ];
            }, $reponseBody["lists"]),
        ];

    }


    /**
     * Get email lists from ActiveCampaign.
     * @param string $apiKey
     * @param string $accountId
     * 
     * @return array
     */
    public function getActiveCampaignLists($apiKey, $accountId)
    {
        $response = wp_remote_get(
            sprintf(
                "https://%s.api-us1.com/api/3/lists",
                $accountId
            ),
            [
                "headers" => [
                    "Api-Token" => $apiKey,
                ],
            ]
        );

        if (is_wp_error($response)) {
            return [
                "error" => "Invalid API key.",
                "lists" => [],
            ];
        }

        $reponseBody = json_decode(wp_remote_retrieve_body($response), true);

        if (
            200 !== wp_remote_retrieve_response_code($response) ||
            empty($reponseBody["lists"])
        ) {
            return [
                "error" => "Invalid API key.",
                "lists" => [],
            ];
        }


        return [
            "error" => "",
            "lists" => array_map(function ($list) {
                return [
                    "id" => $list["id"],
                    "name" => $list["name"],
                ];
            }, $reponseBody["lists"]),
        ];
    }

    /**
     * Subscribe an email to a Mailchimp list.
     * @param string $apiKey
     * @param string $listId
     * @param string $email
     * 
     * @return array
     */
    public function subscribeToMailchimpList($apiKey, $listId, $email) {

        if (
            !isset($apiKey) ||
            !isset($listId)
        ) {
            return [
                "error" => "Invalid API key or list ID.",
            ];
        }

        $apiKeyArr = explode("-", $apiKey);

        $domain = end($apiKeyArr);

        if (empty($domain)) {
            return [
                "error" => "Invalid API key.",
            ];
        }

        $response = wp_remote_post(
            sprintf(
                "https://%s.api.mailchimp.com/3.0/lists/%s/members",
                $domain,
                $listId
            ),
            [
                "method" => "POST",
                "headers" => [
                    "Authorization" => "Basic " . base64_encode("a:" . $apiKey),
                ],
                "body" => json_encode([
                    "email_address" => $email,
                    "status" => "subscribed",
                ]),
            ]
        );

        if (is_wp_error($response)) {
            return [
                "error" => "Error subscribing to list.",
            ];
        }

        $reponseBody = json_decode(wp_remote_retrieve_body($response), true);

        if (
            200 !== wp_remote_retrieve_response_code($response) ||
            empty($reponseBody["id"])
        ) {
            return [
                "error" => "Error subscribing to list.",
            ];
        }

        return [
            "error" => "",
        ];
    }

    /**
     * Subscribe an email to an ActiveCampaign list.
     * @param string $apiKey
     * @param string $listId
     * @param string $accountId
     * @param string $email
     * 
     * @return array
     */
     public function subscribeToActiveCampaignList($apiKey, $listId, $accountId, $email) {

        if (
            !isset($apiKey) ||
            !isset($listId) ||
            !isset($accountId)
        ) {
            return [
                "error" => "Invalid API key or list ID.",
            ];
        }

        $addContactResponse = wp_remote_post(
            sprintf(
                "https://%s.api-us1.com/api/3/contacts",
                $accountId
            ),
            [
                "method" => "POST",
                "headers" => [
                    "Api-Token" => $apiKey,
                ],
                "body" => json_encode([
                    "contact" => [
                        "email" => $email,
                    ],
                ]),
            ]
        );

      
        if (wp_remote_retrieve_response_code($addContactResponse) === 422) {
            $findContactResponse = wp_remote_get(
                sprintf(
                    "https://%s.api-us1.com/api/3/contacts?email=%s",
                    $accountId,
                    $email
                ),
                [
                    "headers" => [
                        "Api-Token" => $apiKey,
                    ],
                ]
            );

            $findContactResponseBody = json_decode(wp_remote_retrieve_body($findContactResponse), true);

            if (
                200 !== wp_remote_retrieve_response_code($findContactResponse) ||
                empty($findContactResponseBody["contacts"])
            ) {
                return [
                    "error" => "Error subscribing to list.",
                ];
            }

            $contactId = $findContactResponseBody["contacts"][0]["id"];
        } else {
            $addContactResponseBody = json_decode(wp_remote_retrieve_body($addContactResponse), true);

            if (
                201 !== wp_remote_retrieve_response_code($addContactResponse) ||
                empty($addContactResponseBody["contact"]["id"])
            ) {
                return [
                    "error" => "Error subscribing to list.",
                ];
            }

            $contactId = $addContactResponseBody["contact"]["id"];
        }

        $addToListResponse = wp_remote_post(
            sprintf(
                "https://%s.api-us1.com/api/3/contactLists",
                $accountId
            ),
            [
                "method" => "POST",
                "headers" => [
                    "Api-Token" => $apiKey,
                ],
                "body" => json_encode([
                    "contactList" => [
                        "list" => $listId,
                        "contact" => $contactId,
                        "status" => 1,
                    ],
                ]),
            ]
        );

        if (
            is_wp_error($addToListResponse)
        ) {
            return [
                "error" => "Error subscribing to list.",
            ];
        }

        return [
            "error" => "",
        ];

    }


    /**
     * Subscribe an email to a list.
     *
     * @param string $linkId
     * @param string $bioLinkId
     * @param string $email
     *
     * @return array
     */
    public function subscribeToMailList($linkId, $bioLinkId, $email)
    {
        $link = current(
            array_filter(
                BioLinkData::instance()->getBioLinkDataById($bioLinkId)["data"][
                    "content"
                ],
                function ($item) use ($linkId) {
                    return $item["id"] === $linkId;
                }
            )
        )["content"]["EditEmailSignup"];


        if ( ! in_array( $link["providerId"], self::$supportedProviders ) ) {
            return [
                "error" => "Invalid provider.",
            ];
        }

        if ( $link["providerId"] === "mailchimp" ) {
            return $this->subscribeToMailchimpList( $link["__private__apiKey"], $link["__private__listId"], $email );
        } else if ( $link["providerId"] === "activecampaign" ) {
            return $this->subscribeToActiveCampaignList( $link["__private__apiKey"], $link["__private__listId"], $link["__private__accountId"], $email );
        }
      
    }

    /**
     * New instance.
     */
    public static function instance()
    {
        return new BioLinkNewsletter();
    }
}
