<?php
/**
 * Don't call the file directly.
 *
 * @package STOCKNOTIFIER
 */

defined( 'ABSPATH' ) || exit;
/**
 * Check Stock_Notifier_Ajax class is already exists
 *
 * @version 1.0.0
 */
if ( ! class_exists( 'Stock_Notifier_Ajax' ) ) {
	/**
	 * Stock_Notifier_Ajax class use for ajax request
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Ajax {
		/**
		 * Call all ajax request
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_stock_notifier_product_subscribe', [ $this, 'stock_notifier_ajax_subscription' ] );
			add_action( 'wp_ajax_nopriv_stock_notifier_product_subscribe', [ $this, 'stock_notifier_ajax_subscription' ] );
			add_action( 'stock_notifier_ajax_data', [ $this, 'stock_notifier_success_message' ] );
			add_action( 'stock_notifier_after_insert_subscriber', [ $this, 'stock_notifier_perform_action_after_insertion' ], 10, 2 );
			// select 2 button react development.
			add_action( 'wp_ajax_stock_notifier_product_tags_category', [ $this, 'product_tags_category' ] );

			add_action( 'wp_ajax_stock_notifier_Init_skip', [ $this, 'stock_notifier_init_skip' ] );
			add_action( 'wp_ajax_stock_notifier_all_setting', [ $this, 'get_all_setting_data' ] );
			add_action( 'wp_ajax_stock_notifier_vendor_notifier_on_off', [ $this, 'stock_notifier_vendor_shop_notfier_on_off' ] );
			add_action( 'wp_ajax_stock_notifier_custom_css_hook', [ $this, 'stock_notifier_custom_css_hook' ] );
		}
		/**
		 * Save Custom css option data
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_custom_css_hook() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['security']) ? sanitize_text_field( wp_unslash($_POST['security']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_setting_nonce' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				$stock_notifier_custom_css = isset($_POST['stock_custom_css']) ? sanitize_text_field( wp_unslash($_POST['stock_custom_css']) ) : '';
				update_option( 'stock_notifier_customs_csss', $stock_notifier_custom_css );
			}
			die();
		}
		/**
		 * Update specific venodor request stock button on off setting
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_vendor_shop_notfier_on_off() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['security']) ? sanitize_text_field( wp_unslash($_POST['security']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_vendor_notifier_on_off' ) ) {
					wp_die( -1, 403 );
				}

				if ( ! current_user_can( 'publish_products' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				$current_user_id      = isset($_POST['current_user_id']) ? intval(sanitize_text_field( wp_unslash($_POST['current_user_id']))) : '';
				$shop_notifier_on_off = isset($_POST['notifier_value']) ? intval(sanitize_text_field( wp_unslash($_POST['notifier_value'] ) ) ) : '';
				$inwp_option_name     = "stock_notifier_dokan_notifier_on_off_$current_user_id";
				update_option( $inwp_option_name, $shop_notifier_on_off );
			}
			die;

		}
		/**
		 * Get all setting data
		 *
		 * @return json
		 * @version 1.0.0
		 */
		public function get_all_setting_data() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['security']) ? sanitize_text_field( wp_unslash($_POST['security']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_setting_nonce' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				// Gateway setting all option.
				$gateway = $this->get_gateway_option_data();
				// General setting all options.
				$general = $this->get_general_option_data();
				// Notification.
				$notification = $this->get_notification_option_data();
				// multivendor.
				$multivendor  = $this->get_multivendor_option_data();
				$custom_css   = $this->get_custom_css();
				wp_send_json(
					[
						'gateway'          => $gateway,
						'general'          => $general,
						'notification'     => $notification,
						'multivendor'      => $multivendor,
						'stock_custom_css' => $custom_css,
					]
				);
			}
			die();
		}

		/**
		 * Get Custom css value
		 */
		public function get_custom_css() {
			$stock_custom_css = get_option( 'stock_notifier_customs_csss', '.stock_notifier_btn_wrap {} .stock_notifier_popup_wrap {} .stock_notifier_button_ {} .stock_notifier_subscribe_wrap { } .stock_notifier_subscribe_popup_wrap { }  .stock_notifier_subscribe_btn { }' );
			return $stock_custom_css;
		}

		/**
		 * Get all gateway option data
		 */
		public function get_gateway_option_data() {
			$gateway_setting = [];
			$gateway_setting['whatsapp_toggle']     = (bool) get_option( 'stock_notifier_whatsapp_toggle', 0 );
			$gateway_setting['select_api']          = get_option( 'stock_notifier_select_api_value', 1 );

			$gateway_setting['twilio_SMS_SID']          = get_option( 'stock_notifier_twilio_SMS_SID', '' );
			$gateway_setting['twilio_SMS_token']        = get_option( 'stock_notifier_twilio_SMS_token', '' );
			$gateway_setting['twilio_SMS_senderNumber'] = get_option( 'stock_notifier_twilio_SMS_senderNumber', '' );

			$gateway_setting['twilio_SID']          = get_option( 'stock_notifier_twilio_SID', '' );
			$gateway_setting['twilio_token']        = get_option( 'stock_notifier_twilio_token', '' );
			$gateway_setting['twilio_senderNumber'] = get_option( 'stock_notifier_twilio_senderNumber', '' );
			$gateway_setting['chatapi_token']       = get_option( 'stock_notifier_chatapi_token', '' );
			$gateway_setting['ultramsg_insatnceID']  = get_option( 'stock_notifier_ultramsg_insatnceID', '' );
			$gateway_setting['ultramsg_token']       = get_option( 'stock_notifier_ultramsg_token', '' );
			return $gateway_setting;
		}

		/**
		 * Get all general setting data
		 *
		 * @version 1.0.0
		 */
		public function get_general_option_data() {
			$general_setting = [];
			$general_setting['stock_progress_bar']             = (bool) get_option( 'stock_notifier_stock_progress_bar', 0 );
			$general_setting['stock_progress_bar_style']       = get_option( 'stock_notifier_stock_progress_bar_style', 1 );
			$general_setting['loop_product_visibility']        = (bool) get_option( 'stock_notifier_loop_product_visibility', 0 );
			$general_setting['non_logdins_user']               = (bool) get_option( 'stock_notifier_hide_sub_non_log', 0 );
			$general_setting['hide_subscribe_loggedin']        = (bool) get_option( 'stock_notifier_hide_subscribe_loggedin', 0 );
			$general_setting['show_subscribe_on_backorder']    = (bool) get_option( 'stock_notifier_show_subscribe_on_backorder', 0 );
			$general_setting['hide_subscribe_regular_product'] = (bool) get_option( 'stock_notifier_hide_subscribe_regular_product', 0 );
			$general_setting['hide_subscribe_sale_product']    = (bool) get_option( 'stock_notifier_hide_subscribe_sale_product', 0 );
			$general_setting['ignore_disabled_variation']      = (bool) get_option( 'stock_notifier_ignore_disabled_variation', 0 );
			$general_setting['specific_tags_visibility']       = (bool) get_option( 'stock_notifier_specific_tags_visibility', 0 );
			$general_setting['specific_tags']                  = get_option( 'stock_notifier_specific_tags', [] );
			$general_setting['specific_categories_visibility'] = (bool) get_option( 'stock_notifier_specific_categories_visibility', 0 );
			$general_setting['specific_categories']            = get_option( 'stock_notifier_specific_categories', [] );
			$general_setting['specific_porduct_visibility']    = (bool) get_option( 'stock_notifier_specific_products_visibility', 0 );
			$general_setting['specific_products']              = get_option( 'stock_notifier_specific_products', [] );
			return $general_setting;
		}

		/**
		 * Get all notification option data
		 *
		 * @version 1.0.0
		 */
		public function get_notification_option_data() {
			// Default value for notification setting.
			$inwp_frontent_form_placeholder      = __( 'Enter Your Whatsapp Number.', 'stock-notifier' );
			$inwp_frontent_button_lable          = __( 'Request stock', 'stock-notifier' );
			$inwp_frontent_empty_error_message   = __( 'Get notified on WhatsApp when the product comes back in stock.', 'stock-notifier' );
			$instock_message                     = __( 'Hello {whatsapp_number}, Thanks for your patience and finally the wait is over! Your Subscribed Product {product_name} is now back in stock! We only have a limited amount of stock,and this sms is not a guarantee you will get one, so hurry to be one of the lucky shoppers who do Add this product {product_name} directly to your cart {cart_link}','stock-notifier');
			$instock_sms_subject                 = __( 'Product {product_name} has back in stock', 'stock-notifier' );
			$success_subscribe_message           = __( 'Dear {subscriber_number}, Thank you for subscribing to the #{product_name}. We will sms you once product back in stock', 'stock-notifier' );
			$success_sub_subject                 = __( 'You subscribed to {product_name} at {shopname}', 'stock-notifier' );
			$notification_setting                 = [];
			$notification_setting['stock_notifier_pro']               = get_option( 'stock_notifier_pro_active', 0 );
			$notification_setting['stock_notifier_ultimate']          = get_option( 'stock_notifier_ultimate_active', 0 );

			$notification_setting['frontent_form_placeholder']        = get_option( 'stock_notifier_frontent_form_placeholder', $inwp_frontent_form_placeholder );
			$notification_setting['frontent_form_button']             = get_option( 'stock_notifier_frontent_form_button', $inwp_frontent_button_lable );
			$notification_setting['success_subscription_description'] = get_option( 'stock_notifier_success_subscription_description', $inwp_frontent_empty_error_message );

			$notification_setting['button_color'] = get_option( 'stock_notifier_button_color', '#4caf50' );
			$notification_setting['border_color'] = get_option( 'stock_notifier_button_border_color', '#fff' );
			$notification_setting['button_text_color'] = get_option( 'stock_notifier_button_text_color', '#fff' );
			$notification_setting['default_country'] = get_option( 'stock_notifier_default_country_code', 'us' );
			$notification_setting['enable_success_subscription']      = (bool) get_option( 'stock_notifier_enable_success_subscription', 0 );
			$notification_setting['success_sub_subject']              = get_option( 'stock_notifier_success_sub_subject', $success_sub_subject );
			$notification_setting['success_sub_message']              = get_option( 'stock_notifier_success_sub_message', $success_subscribe_message );
			$notification_setting['enable_instock_sms']               = (bool) get_option( 'stock_notifier_enable_instock_sms', 0 );
			$notification_setting['instock_sub_subject']              = get_option( 'stock_notifier_instock_sub_subject', $instock_sms_subject );
			$notification_setting['instock_sub_message']              = get_option( 'stock_notifier_instock_sub_message', $instock_message );
			return $notification_setting;
		}

		/**
		 * Get all multivendor option data
		 *
		 * @version 1.0.0
		 */
		public function get_multivendor_option_data() {
			$multivendor_setting                       = [];
			$multivendor_setting['multivendor_on_off'] = (bool) get_option( 'stock_notifier_multivendor_on_off', 0 );
			return $multivendor_setting;
		}

		/**
		 * Onboard page all setting Skip
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_init_skip() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['security']) ? sanitize_text_field( wp_unslash($_POST['security']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_setting_nonce' ) ) {
					wp_die( -1, 403 );
				}

				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}

				if ( ! is_user_logged_in() ) {
					return false;
				}
				update_option( 'stock_notifier_plugin_active_first_time', 1 );
			}
			die();
		}

		/**
		 * Get Dahboard page all data
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_ajax_subscription() {
			if ( isset( $_POST ) ) {
				$obj         = new Stock_Notifier_API();
				$post_data  = isset( $_POST ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) : [];
				$post_data   = $obj->stock_notifier_post_data_validation( $post_data );
				$product_id  = $post_data['product_id'];
				$security    = $post_data['security'];
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_product_subscribe' ) ) {
					wp_die( -1, 403 );
				}
				// For success.
				$description                           = __( 'Get notified on WhatsApp when the product comes back in stock.', 'stock-notifier' );
				$get_success_subscription_description  = get_option( 'stock_notifier_success_subscription_description' );
				do_action( 'stock_notifier_ajax_data', $post_data );
				$get_description                   = isset( $get_success_subscription_description ) && $get_success_subscription_description ? $get_success_subscription_description : $description;
				$success                           = __( 'Your stock request was successful', 'stock-notifier' );
				echo wp_kses_post("<span class='dashicons dashicons-yes-alt stock_notifier_success_logo'></span> <span  class = 'stock_notifier_success_message'><div class = 'stock_notifiersuccess' style = 'color:green;'>$success</div></span><p  class = 'stock_notifier_message_description'>" . $get_description . '</p>');
			}
			die();
		}
		/**
		 * Sent Stock Notifier successfully message.
		 *
		 * @param array $post_data all.
		 * @version 1.0.0
		 */
		public function stock_notifier_success_message( $post_data ) {
			$get_phone       = $post_data['user_phone'];
			$str_trim_phone  = trim( $get_phone, '' );
			$str_trim_phone  = str_replace( '-', '', $str_trim_phone );
			$get_user_id     = $post_data['user_id'];
			$product_id      = $post_data['product_id'];
			$variation_id    = $post_data['variation_id'];
			
			$author_object   = get_post( $product_id );
			$author_id       = $author_object->post_author;
			$obj             = new Stock_Notifier_API( $product_id, $variation_id, $str_trim_phone, $get_user_id, $author_id );

			$check_is_already_subscribed  = $obj->stock_notifier_is_already_subscribed();
			$subscriber_count             = get_transient( 'subscriber_count' ) ? get_transient( 'subscriber_count') : 0;
			if ( ! $check_is_already_subscribed ) {
				$id = $obj->stock_notifier_insert_subscriber();
				$subscriber_count++;
				set_transient( 'subscriber_count', $subscriber_count, 0 );

				if ( $id ) {
					$obj->stock_notifier_insert_data( $id );
					$product_name = $obj->stock_notifier_display_product_name( $id );
					$_shop_name   = $obj->stock_notifier_display_product_author_name( $author_id );

					$obj->stock_notifier_insert_data_sql( $id, $product_name, $_shop_name );
					$get_popular_product = get_option( "stock_notifier_popular_product_$product_id" ) ? get_option( "stock_notifier_popular_product_$product_id" ) : 0;
					$get_popular_product++;
					update_option( "stock_notifier_popular_product_$product_id", $get_popular_product );
					do_action( 'stock_notifier_after_insert_subscriber', $id, $post_data );
					// Logger.
					$logger = new Stock_Notifier_Logger( 'success', "Subscriber #$get_phone successfully subscribed - #$id" );
					$logger->stock_notifier_record_log();
				}
			} else {
				$description                           = __( 'Get notified on WhatsApp when the product comes back in stock.', 'stock-notifier' );
				$get_success_subscription_description  = get_option( 'stock_notifier_success_subscription_description' );
				$error                                 = __( 'You have already subscribed', 'stock-notifier' );
				$get_description                       = isset( $get_success_subscription_description ) && $get_success_subscription_description ? $get_success_subscription_description : $description;
				echo wp_kses_post("<span class='dashicons dashicons-dismiss stock_notifier_success_logo_err'></span> <span  class = 'stock_notifier_success_message'><div class = 'stock_notifiererror' style = 'color:coral;'>$error</div></span><p  class = 'stock_notifier_message_description'>" . $get_description . '</p>');
				die();
			}
		}
		/**
		 * Send Subscriber sms when user request for stock
		 *
		 * @param int   $id post ids.
		 * @param array $post_data all.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_perform_action_after_insertion( $id, $post_data ) {
			$get_subscribe_enabled       = get_option( 'stock_notifier_enable_success_subscription' );
			$is_enabled                  = isset( $get_subscribe_enabled ) ? $get_subscribe_enabled : 0;
			$get_phone                   = $post_data['user_phone'];
			$whatsapp_active_or_inactive = get_option( 'stock_notifier_whatsapp_toggle' );

			if ( '1' == $whatsapp_active_or_inactive ) {
				if ( '1' == $is_enabled ) {
					$whtsapp_sms = new Stock_Notifier_Subscribe_SMS( $id );
					$whtsapp_sms->send_whatsapp_sms();
					$logger = new Stock_Notifier_Logger( 'success', "SMS sent to #$get_phone for successful subscription - #$id");
					$logger->stock_notifier_record_log();
				}
			}
		}
		/**
		 * Get all tags ana category
		 *
		 * @version 1.0.0
		 */
		public function product_tags_category() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['security']) ? sanitize_text_field( wp_unslash($_POST['security']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'stock_notifier_setting_nonce' ) ) {
					wp_die( -1, 403 );
				}

				if ( ! current_user_can( 'edit_products' ) ) {
					wp_die( -1 );
				}

				if ( ! is_user_logged_in() ) {
					return false;
				}

				$found_tags     = [];
				$found_category = [];
				$found_product  = [];
				/**
				 * Tags args
				 */
				$args = [
					'taxonomy'   => [
						'product_tag',
					],
					'orderby'    => 'id',
					'order'      => 'ASC',
					'hide_empty' => true,
					'fields'     => 'all',
				];
				/**
				 * Category args
				 */
				$args2 = [
					'taxonomy' => [
						'product_cat',
					],
					'orderby'    => 'id',
					'order'      => 'ASC',
					'hide_empty' => true,
					'fields'     => 'all',
				];
				/**
				 * Products args
				 */
				$args3  = [
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'post_status'    => [ 'publish' ],
				];

				/**
				* Get all tags
				*/
				$terms  = get_terms( $args );

				if ( $terms ) {
					foreach ( $terms as $term ) {
						$found_tags[ $term->term_id ]['value'] = $term->term_id;
						$found_tags[ $term->term_id ]['label'] = $term->slug;
					}
				}

				/**
				 * Get all category
				*/
				$terms2 = get_terms( $args2 );
				if ( $terms2 ) {
					foreach ( $terms2 as $term2 ) {
						$found_category[ $term2->term_id ]['value'] = $term2->term_id;
						$found_category[ $term2->term_id ]['label'] = $term2->slug;
					}
				}
				/**
				 * Get all products
				 */
				$products = wc_get_products( $args3 );
				if ( is_array( $products ) && ! empty( $products ) ) {
					$i = 0;
					foreach ( $products as $product ) {
						$found_product[ $i ]['value'] = $product->get_id();
						$found_product[ $i ]['label'] = $product->get_name();
						$i++;
					}
				}
				wp_send_json( [
					'product_Tags'     => $found_tags,
					'product_category' => $found_category,
					'all_product'      => $found_product,
				] );
			}
			die();
		}
	}
	/**
	 * Kick out the __construct method
	 */
	new Stock_Notifier_Ajax();
}