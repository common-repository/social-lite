<?php
/**
 *
 * Manage the SEO for the Bio Link.
 *
 * @since 1.5.1
 */

namespace ChadwickMarketing\SocialLite\base\seo;

use ChadwickMarketing\SocialLite\base\UtilsProvider;

// @codeCoverageIgnoreStart
defined("ABSPATH") or die("No script kiddies please!"); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkSEO
{
    use UtilsProvider;


    /**
     * Check if one of the common SEO plugins is active.
     *
     * @return bool
     */

    public function isSEOPluginActive()
    {
        return defined("WPSEO_VERSION") ||
            defined("AIOSEO_VERSION") ||
            defined("SEOPRESS_VERSION") ||
            defined("SLIM_SEO_VER");
    }

    /**
     * Generate SEO meta tags for bio link pages
     * @param array $bio_link_data
     *
     * @since 1.5.1
     */
    public function generateSEOMeta($bio_link_data)
    {
        if (
            isset($bio_link_data["data"]["meta"]["index"]) &&
            !$bio_link_data["data"]["meta"]["index"]
        ) {
            add_filter("wp_robots", "wp_robots_no_robots");
        }

        if (
            isset($bio_link_data["data"]["meta"]["title"]) &&
            !empty($bio_link_data["data"]["meta"]["title"])
        ) {
            add_filter(
                "pre_get_document_title",
                function () use ($bio_link_data) {
                    return $bio_link_data["data"]["meta"]["title"];
                },
                999,
                1
            );

            if ($this->isSEOPluginActive()) {
                add_filter(
                    "wpseo_title",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["title"];
                    },
                    999,
                    1
                );

                add_filter(
                    "wpseo_schema_graph",
                    function ($graph) use ($bio_link_data) {
                        foreach ($graph as $key => $value) {
                            if ($value['@type'] === "WebPage") {
                                $graph[$key]["name"] =
                                    $bio_link_data["data"]["meta"]["title"];
                            }
                          
                        }
                        return $graph;
                    },

                );

                add_filter(
                    "wpseo_opengraph_title",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["title"];
                    },
                    999,
                    1
                );

                add_filter(
                    "aioseo_title",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["title"];
                    },
                    999,
                    1
                );

                add_filter(
                    "seopress_titles_title",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["title"];
                    },
                    999,
                    1
                );

                add_filter(
                    "slim_seo_meta_title",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["title"];
                    },
                    999,
                    1
                );

            }
        }

        if ($this->isSEOPluginActive()) {
            if (
                isset($bio_link_data["data"]["meta"]["image"]) &&
                !empty($bio_link_data["data"]["meta"]["image"])
            ) {
                add_filter(
                    "wpseo_opengraph_image",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["image"];
                    },
                    999,
                    1
                );

                add_filter(
                    "aioseo_twitter_tags",
                    function ($tags) use ($bio_link_data) {
                        $tags["twitter:image"] =
                            $bio_link_data["data"]["meta"]["image"];
                        return $tags;
                    },
                    999,
                    1
                );

                add_filter(
                    "aioseo_facebook_tags",
                    function ($tags) use ($bio_link_data) {
                        $tags["og:image"] =
                            $bio_link_data["data"]["meta"]["image"];
                        return $tags;
                    },
                    999,
                    1
                );

                add_filter(
                    "seopress_social_og_thumb",
                    function () use ($bio_link_data) {
                        return '<meta property="og:image" content="' .
                            esc_attr($bio_link_data["data"]["meta"]["image"]) .
                            '">';
                    },
                    999,
                    1
                );

                add_filter(
                    "seopress_social_twitter_card_thumb",
                    function () use ($bio_link_data) {
                        return '<meta property="twitter:image" content="' .
                            esc_attr($bio_link_data["data"]["meta"]["image"]) .
                            '">';
                    },
                    999,
                    1
                );


                add_filter(
                    "slim_seo_open_graph_image",
                    function () use ($bio_link_data) {
                        return $bio_link_data["data"]["meta"]["image"];
                    },
                    999,
                    1
                );


                if (
                    isset($bio_link_data["data"]["meta"]["description"]) &&
                    !empty($bio_link_data["data"]["meta"]["description"])
                ) {
                    add_filter(
                        "wpseo_metadesc",
                        function () use ($bio_link_data) {
                            return $bio_link_data["data"]["meta"][
                                "description"
                            ];
                        },
                        999,
                        1
                    );

                     add_filter(
                        "wpseo_schema_graph",
                        function ($graph) use ($bio_link_data) {
                            foreach ($graph as $key => $value) {
                                if ($value['@type'] === "WebPage") {
                                    $graph[$key]["description"] =
                                        $bio_link_data["data"]["meta"]["description"];
                                }
                              
                            }
                            return $graph;
                        },
                    );

                    add_filter(
                        "wpseo_opengraph_desc",
                        function () use ($bio_link_data) {
                            return $bio_link_data["data"]["meta"][
                                "description"
                            ];
                        },
                        999,
                        1
                    );

                    add_filter(
                        "aioseo_description",
                        function () use ($bio_link_data) {
                            return $bio_link_data["data"]["meta"][
                                "description"
                            ];
                        },
                        999,
                        1
                    );

                    add_filter(
                        "seopress_titles_desc",
                        function () use ($bio_link_data) {
                            return $bio_link_data["data"]["meta"][
                                "description"
                            ];
                        },
                        999,
                        1
                    );

                    add_filter(
                        "slim_seo_meta_description",
                        function () use ($bio_link_data) {
                            return $bio_link_data["data"]["meta"][
                                "description"
                            ];
                        },
                        999,
                        1
                    );

                }
            }
        }
    }

    /**
     * New instance.
     */
    public static function instance()
    {
        return new BioLinkSEO();
    }
}
