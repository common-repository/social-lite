<?php

/**
 *
 * WooCommerce integration.
 *
 * @since 1.1.7
 */

namespace ChadwickMarketing\SocialLite\base\woocommerce;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
use WC_Product_Query;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkWooCommerce {

    use UtilsProvider;

    /**
     * Check if WooCommerce is active.
     *
     * @return bool
     */
    public function isWooCommerceActive() {
        return class_exists('WooCommerce');
    }


    /**
     * Get the WooCommerce shop page URL.
     *
     * @return string
     */
    public function getShopPageUrl() {
        return get_permalink(wc_get_page_id('shop'));
    }

    /**
     * Get price of a product, with currency symbol.
     *
     * @param $price float
     *
     * @return string
     */
    public function getProductPrice($price) {
            if (get_option('woocommerce_currency_pos') === 'left') {
                return get_woocommerce_currency_symbol() . $price;
            } else {
                return $price . get_woocommerce_currency_symbol();
            }
    }

    /**
     * Get thumbnail of a product, with placeholder if no image is set.
     *
     * @param $productId int
     *
     * @return string
     */
    public function getProductThumbnail($productId) {
        return get_the_post_thumbnail_url($productId, 'full') === false ? wc_placeholder_img_src() : get_the_post_thumbnail_url($productId, 'full');
    }

    /**
     * Get thumbnail of a category, with placeholder if no image is set.
     *
     * @param $categoryId int
     *
     * @return string
     */
    public function getCategoryThumbnail($categoryId) {
        return get_term_meta($categoryId, 'thumbnail_id', true) ? wp_get_attachment_url(get_term_meta($categoryId, 'thumbnail_id', true)) : wc_placeholder_img_src();
    }


    /**
     * Get WooCommerce product data.
     *
     * @param WC_Product $product
     *
     * @return array
     */
    public function getProductData($product) {
        return [
            'id' => $product->get_id(),
            'title' => $product->get_title(),
            'description' => $product->get_description(),
            'permalink' => $product->get_permalink(),
            'price' => $this->getProductPrice($product->get_price()),
            'image' => $this->getProductThumbnail($product->get_id()),
        ];
    }

    /**
     * Get WooCommerce category data.
     *
     * @param $category
     *
     * @return array
     */
    public function getCategoryData($category) {
        return [
            'id' => $category->term_id,
            'title' => $category->name,
            'items' => $category->count,
            'image' => $this->getCategoryThumbnail($category->term_id),
        ];
    }

    /**
     * Get WooCommerce categories and products.
     *
     * @param string $type 'categories' or 'products'
     * @param int $id Category IDs
     *
     * @return array Category or product objects
     */
    public function getCategories($ids = null) {
          $data =  [ 
              'categories' => [],
			  'products' => [],
			  'shop_url' => null
		  ];

          if (!$this->isWooCommerceActive()) {
            return $data;
          }

          if (is_array($ids) && count($ids) > 0) {

                $data['shop_url'] = $this->getShopPageUrl();

                $data['products'] = array_map([$this, 'getProductData'], (new WC_Product_Query([
                    'status' => 'publish',
                    'limit' => -1,
                    'category' => array_map(function ($id) {
                            return get_term($id)->slug;
                        }, $ids)
                ]))->get_products());

            } else {

                $data['categories'] = array_values(array_map([$this, 'getCategoryData'], get_terms([
                    'public' => true,
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                ])));

            }

          return $data;
      }

    /**
     * Get WooCommerce products.
     *
     * @param array $ids
     *
     * @return array Product objects
     */
    public function getProducts($ids = null) {
        $data = [
            'products' => [],
            'shop_url' => null
		];

        if (!$this->isWooCommerceActive()) {
            return $data;
        }

        $data['shop_url'] = $this->getShopPageUrl();

        if (is_array($ids) && count($ids) > 0) {
            $data['products'] = array_map([$this, 'getProductData'], (new WC_Product_Query([
                'status' => 'publish',
                'limit' => -1,
                'include' => $ids,
            ]))->get_products());
        } else {
            $data['products'] = array_map([$this, 'getProductData'], (new WC_Product_Query([
                'status' => 'publish',
                'limit' => -1,
            ]))->get_products());
        }


        return $data;

    }

    /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkWooCommerce();
    }


}