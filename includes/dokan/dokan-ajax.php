<?php
/**
 * Don't call the file directly.
 *
 * @package STOCKNOTIFIER
 */

defined( 'ABSPATH' ) || exit;
/**
 * Check class already exists
 *
 * @varsion 1.0.0
 */
if ( ! class_exists( 'Stock_Notifier_Dokan_Ajax_Save' ) ) {
	/**
	 * Save and get Vendor Information
	 *
	 * @varsion 1.0.0
	 */
	class Stock_Notifier_Dokan_Ajax_Save {
		/**
		 * Object Calling method.
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_stock_notifier_product_popularity2', [ $this, 'stock_notifier_product_popularity' ] );
		}
		/**
		 * Count venodor product subscription and show product popularity
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_product_popularity() {
			global $wpdb;
			$product_name             = [];
			$product_subscriber_count = [];
			if ( isset( $_POST ) ) {
				$security     = isset( $_POST['security']) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
				$option_value = isset( $_POST['option_value']) ? sanitize_text_field( wp_unslash($_POST['option_value']) ) : '';
				$dokander_ids = isset( $_POST['dokander_id']) ? sanitize_text_field( wp_unslash($_POST['dokander_id']) ) : '';

				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_overview2' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'publish_products' ) ) {
					return false;
				}

				if ( ! is_user_logged_in() ) {
					return false;
				}

				if ( 'month' == $option_value ) {
					$posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stock_notifier_popular_product WHERE populer_date >= (LAST_DAY(NOW()) + INTERVAL 1 DAY - INTERVAL 1 MONTH)AND populer_date <  (LAST_DAY(NOW()) + INTERVAL 1 DAY) AND author_id=%s",$dokander_ids ) );
					foreach ( $posts as $post ) {
						$product_name[ $post->product_id ] = $post->product_name;
						$product_subscriber_count[]      = $post->product_id;
					}
				} elseif ( 'last_30_days' == $option_value ) {
					$posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stock_notifier_popular_product WHERE DATE(populer_date) >= DATE(NOW()) - INTERVAL 30 DAY AND author_id=%d", $dokander_ids ) );
					foreach ( $posts as $post ) {
						$product_name[ $post->product_id ] = $post->product_name;
						$product_subscriber_count[]      = $post->product_id;
					}
				} elseif ( 'last_seven_days' == $option_value ) {
					$postss = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stock_notifier_popular_product WHERE DATE(populer_date) >= DATE(NOW()) - INTERVAL 7 DAY AND author_id=%d", $dokander_ids ) );
					foreach ( $postss as $post ) {
						$product_name[ $post->product_id ] = $post->product_name;
						$product_subscriber_count[]      = $post->product_id;
					}
				}
				$product_unique_name  = array_unique( $product_name );
				$product_count        = array_count_values( $product_subscriber_count );
				wp_send_json(
					[
						'product_name'  => $product_unique_name,
						'product_count' => $product_count,
					]
				);
			}
			die();
		}
	}
	/**
	 * Kick out the __construct
	 *
	 * @varsion 1.0.0
	 */
	new Stock_Notifier_Dokan_Ajax_Save();
}
