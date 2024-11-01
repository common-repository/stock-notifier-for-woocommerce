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
if ( ! class_exists( 'Stock_Notifier_Core' ) ) {
	/**
	 * Store stock Notifier in stock action hook.
	 * These hooks are used to complete the plugin's goal.
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Core {
		/**
		 * Calling method
		 *
		 * @varsion 1.0.0
		 */
		public function __construct() {
			add_action( 'woocommerce_product_set_stock_status', [ $this, 'action_based_on_stock_status' ], 999, 3 );
			add_action( 'woocommerce_variation_set_stock_status', [ $this, 'action_based_on_stock_status' ], 999, 3 );
			add_action( 'stock_notifier_trigger_status', [ $this, 'trigger_instock_status' ], 999, 3 );
			add_filter( 'stock_notifier_trigger_status_product', [ $this, 'get_product_bundle_subscribers' ], 10, 2 );
			add_filter( 'stock_notifier_trigger_status_variation', [ $this, 'get_product_bundle_subscribers' ], 10, 2 );
			add_filter( 'stock_notifier_trigger_status_phone_product', [ $this, 'get_phone_product_bundle_subscribers' ], 10, 2 );
			add_filter( 'stock_notifier_trigger_status_phone_variation', [ $this, 'get_phone_product_bundle_subscribers' ], 10, 2 );
			add_action( 'stock_notifier_sms_sent_success', [ $this, 'update_sms_sent_status' ] );
			add_action( 'stock_notifier_sms_sent_failure', [ $this, 'sms_failed_status' ] );
			add_action( 'woocommerce_product_set_stock', [ $this, 'set_product_qt' ],10,1 );
		
		}
		public function set_product_qt($products) {
			if ( $products->is_type( 'variable' ) ) {
				$product_qt = $products->get_stock_quantity();
				if ($product_qt) {
					$id = $products->get_id();
					$main_obj  = $products->is_type( 'variable' ) ? new Stock_Notifier_API( $id, 0 ) : new Stock_Notifier_API( 0, $id, '', 0 );
					$get_posts = apply_filters( 'stock_notifier_trigger_status_variation', $main_obj->stock_notifier_get_list_of_subscribers(), $id );
					$this->stock_notifier_background_process_core( $get_posts, true, $id );
				}
			}
		}
		/**
		 * When product back in stock but sms not sent then this method is called and update status field
		 *
		 * @param int $subscriber_id post id.
		 */
		public function sms_failed_status( $subscriber_id ) {
			$api = new Stock_Notifier_API();
			$api->sms_failed_status_update( $subscriber_id );
		}
		/**
		 * When product back in stock but sms  sent then this method is called and update status sent
		 *
		 * @param int $subscriber_id post id.
		 */
		public function update_sms_sent_status( $subscriber_id ) {
			$api = new Stock_Notifier_API();
			$api->stock_notifier_sms_sent_status_db( $subscriber_id );
		}
		/**
		 * This method is called when product back instock.
		 *
		 * @param int    $id subscriber id.
		 * @param String $stockstatus current status.
		 * @param Object $obj reference.
		 *
		 * @version 1.0.0
		 */
		public function action_based_on_stock_status( $id, $stockstatus, $obj = '' ) {
			if ( 'instock' == $stockstatus ) {
				do_action( 'stock_notifier_trigger_status', $id, $stockstatus, $obj );
			} elseif ( 'outofstock' == $stockstatus ) {
				update_post_meta( $id, 'stock_notifier_set_quantity', 0 );
			}
		}
		/**
		 * Trigger this hook when change status out of stock to in stock
		 *
		 * @param int    $id subscriber id.
		 * @param String $stockstatus current status.
		 * @param Object $obj reference object.
		 */
		public function trigger_instock_status( $id, $stockstatus, $obj ) {
			if ( ! $obj ) {
				$obj = wc_get_product( $id );
			}

			if ( $obj->is_type( 'variation' )) {
				$main_obj  = $obj->is_type( 'variable' ) ? new Stock_Notifier_API( $id, 0 ) : new Stock_Notifier_API( 0, $id, '', 0 );
				$get_posts = apply_filters( 'stock_notifier_trigger_status_variation', $main_obj->stock_notifier_get_list_of_subscribers(), $id );
				$this->stock_notifier_background_process_core( $get_posts, true, $id );
			} else if( ! $obj->is_type( 'variable' ) ) {
				$main_obj  = new Stock_Notifier_API( $id, 0 );
				$get_posts = apply_filters( 'stock_notifier_trigger_status_product', $main_obj->stock_notifier_get_list_of_subscribers(), $id );
				$this->stock_notifier_background_process_core( $get_posts, false, $id );
			}
		}
		/**
		 * This function sends serial SMS when there are multiple subscribers
		 *
		 * @param array $get_posts subscriber id.
		 * @param array $is_variation check product is variation.
		 * @param int   $id product id.
		 *
		 * @version 1.0.0
		 */
		private function stock_notifier_background_process_core( $get_posts, $is_variation, $id ) {
			if ( is_array( $get_posts ) && ! empty( $get_posts ) ) {
				foreach ( $get_posts as $post_id ) {
					$this->stock_notifier_task( $post_id );
				}
			} else {
				$this->stock_notifier_task( $get_posts );
			}
		}
		/**
		 * Trigger Whatsapp API method for sent SMS.
		 *
		 * @param int $each_id post id.
		 */
		protected function stock_notifier_task( $each_id ) {
			$get_post_status = get_post_status( $each_id );

			if ( 'iwg_subscribed' == $get_post_status ) {
				$get_enable_instock          = get_option( 'stock_notifier_enable_instock_sms' );
				$whatsapp_active_or_inactive = get_option( 'stock_notifier_whatsapp_toggle' );

				if ( '1' == $whatsapp_active_or_inactive ) {
					if ( '1' == $get_enable_instock ) {
						$ge_phone = get_post_meta( $each_id, 'stock_notifier_subscriber_phone', true );
						$send_sm  = new Stock_Notifier_Instock_Subscribe_SMS( $each_id );
						// sms sent.
						$send_sms = $send_sm->send_whatsapp_sms();
						if ( $send_sms ) {
							$api = new Stock_Notifier_API();
							// update sms sent status.
							$sms_status = $api->stock_notifier_sms_sent_status( $each_id );
							$logger     = new Stock_Notifier_Logger( 'info', "Automatic Instock SMS Triggered for ID #$each_id with #$ge_phone" );
							$logger->stock_notifier_record_log();
						} else {
							$api        = new Stock_Notifier_API();
							$sms_status = $api->stock_notifier_sms_not_sent_status( $each_id );
							$logger     = new Stock_Notifier_Logger( 'error', "Failed to send Automatic Instock sms for ID #$each_id with #$ge_phone" );
							$logger->stock_notifier_record_log();
						}
					}
				}
			}
			return false;
		}
		/**
		 * Sent bundle product SMS.
		 *
		 * @param int $subscribers subscribers id.
		 * @param int $product_id product_id id.
		 *
		 * @version 1.0.0
		 */
		public function get_product_bundle_subscribers( $subscribers, $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product && ! $product->is_type( 'bundle' ) ) {
				if ( function_exists( 'wc_pb_is_bundled_cart_item' ) ) {
					$product_ids = [
						$product_id,
					];
					$results = WC_PB_DB::query_bundled_items( [
						'return' => 'id=>bundle_id',
						'product_id' => $product_ids,
					] );
					if ( is_array( $results ) && ! empty( $results ) ) {
						foreach ( $results as $each_item_key => $bundle_id ) {
							$bundle = wc_get_product( $bundle_id );
							if ( $bundle->is_in_stock() ) {
								$main_obj                = new Stock_Notifier_API( $bundle_id, 0 );
								$get_list_of_subscribers = $main_obj->stock_notifier_get_list_of_subscribers();
								if ( is_array( $get_list_of_subscribers ) && ! empty( $get_list_of_subscribers ) ) {
									$subscribers = array_merge( $subscribers, $get_list_of_subscribers );
								}
							}
						}
					}
				}
			}
			return $subscribers;
		}
		/**
		 * Sent bundle product SMS.
		 *
		 * @param int $subscribers subscribers id.
		 * @param int $product_id product_id id.
		 *
		 * @version 1.0.0
		 */
		public function get_phone_product_bundle_subscribers( $subscribers, $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product && ! $product->is_type( 'bundle' ) ) {
				if ( function_exists( 'wc_pb_is_bundled_cart_item' ) ) {
					$product_ids = [
						$product_id,
					];
					$results = WC_PB_DB::query_bundled_items( [
						'return'     => 'id=>bundle_id',
						'product_id' => $product_ids,
					] );
					if ( is_array( $results ) && ! empty( $results ) ) {
						foreach ( $results as $each_item_key => $bundle_id ) {
							$bundle = wc_get_product( $bundle_id );
							if ( $bundle->is_in_stock() ) {
								$main_obj                = new Stock_Notifier_API( $bundle_id, 0 );
								$get_list_of_subscribers = $main_obj->stock_notifier_get_list_of_subscribers();
								if ( is_array( $get_list_of_subscribers ) && ! empty( $get_list_of_subscribers ) ) {
									$subscribers = array_merge( $subscribers, $get_list_of_subscribers );
								}
							}
						}
					}
				}
			}
			return $subscribers;
		}
	}
	new Stock_Notifier_Core();
}
