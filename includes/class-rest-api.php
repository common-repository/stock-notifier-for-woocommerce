<?php
/**
 * This file will create Custom Rest API End Points.
 *
 * @package STOCKNOTIFIER
 */

defined( 'ABSPATH' ) || exit;
/**
 * Ensure WP_React_Settings_Rest_Route class exists or not
 *
 * @version 1.0.0
 */
if ( ! class_exists( 'Stock_Notifier_Rest_Route' ) ) {
	/**
	 * Register Stock Notifier end points for save setting data.
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Rest_Route {
		/**
		 * Calling method
		 */
		public function __construct() {
			add_action( 'rest_api_init', [ $this, 'create_rest_api_routes' ] );
		}
		/**
		 * Register rest Route for get and post.
		 *
		 * @version 1.0.0
		 */
		public function create_rest_api_routes() {
			register_rest_route('stock_notifier/v1', '/GatewaySettings', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_stock_notifier_gateway_settings' ],
				'permission_callback' => [ $this, 'save_stock_notifier_gateway_settings_permission' ],
			]);
			register_rest_route('stock_notifier/v1', '/GeneralSettings', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_stock_notifier_general_settings' ],
				'permission_callback' => [ $this, 'save_stock_notifier_general_settings_permission' ],
			]);
			register_rest_route('stock_notifier/v1', '/NotificationSettings', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_stock_notifier_notification_settings' ],
				'permission_callback' => [ $this, 'save_stock_notifier_notification_settings_permission' ],
			]);
			register_rest_route('stock_notifier/v1', '/MultiVendorSetting', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_stock_notifier_multi_vendor_setting' ],
				'permission_callback' => [ $this, 'save_stock_notifier_multi_vendor_setting_permission' ],
			]);
		}

		/**
		 * Save all gateway setting value
		 *
		 * @param array $request all submit data.
		 *
		 * @version 1.0.0
		 */
		public function save_stock_notifier_gateway_settings( $request ) {
			$this->stock_notifier_whatsapp_toggle( $request );
			$this->stock_notifier_select_api( $request );
			$this->stock_notifier_twilio_sms( $request );
			$this->stock_notifier_twilio_info( $request );
			$this->stock_notifier_chatapi_info( $request );
			$this->stock_notifier_ultramsg_info( $request );
			return rest_ensure_response( 'success' );
		}
		/**
		 * Set Twilio SMS API information
		 *
		 * @param array $request Twilio SMS.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_twilio_sms( $request ) {
			$sms_account_sid   = trim( sanitize_text_field( $request['twilio_SMS_SID'] ), ' ' );
			$sms_auth_token    = trim( sanitize_text_field( $request['twilio_SMS_token'] ), ' ' );
			$sms_sender_number = trim( sanitize_text_field( $request['twilio_SMS_senderNumber'] ), ' ' );
			update_option( 'stock_notifier_twilio_SMS_SID', $sms_account_sid );
			update_option( 'stock_notifier_twilio_SMS_token', $sms_auth_token );
			update_option( 'stock_notifier_twilio_SMS_senderNumber', $sms_sender_number );
		}
		/**
		 * Save all general setting value
		 *
		 * @param array $request all submit data.
		 *
		 * @version 1.0.0
		 */
		public function save_stock_notifier_general_settings( $request ) {
			$this->stock_notifier_update_general_setting( $request );
			return rest_ensure_response( 'success' );
		}
		/**
		 * Sava all multivendor setting value
		 *
		 * @param array $request all submit data.
		 */
		public function save_stock_notifier_multi_vendor_setting( $request ) {
			$this->update_value_mutlivendor_on_off( $request );
			return rest_ensure_response( 'success' );
		}
		/**
		 * Save all notification setting value
		 *
		 * @param array $request all submit data.
		 */
		public function save_stock_notifier_notification_settings( $request ) {
			$this->stock_notifier_notification_settings( $request );
			return rest_ensure_response( 'success' );
		}
		/**
		 * Notification settings all method
		 *
		 * @param array $request all submit data.
		 */
		private function stock_notifier_notification_settings( $request ) {
			$this->stock_notifier_general_option_data_save( $request );
			$this->stock_notifier_color( $request );
			$this->stock_notifier_sms_settings_option_data_save( $request );
			$this->stock_notifier_default_country_code( $request );
		}
		/**
		 * Update default country code value
		 *
		 * @param array $request all submit data.
		 */
		public function stock_notifier_default_country_code( $request ) {
			$value = sanitize_text_field( $request['default_country'] );
			$country_code = isset( $value ) && ! empty( $value ) ? $value : 'us';
			update_option( 'stock_notifier_default_country_code', $country_code );
		}
		/**
		 * Call all color method for save color option fields data
		 *
		 * @param array $request all submit data.
		 */
		private function stock_notifier_color( $request ) {
			$this->button_color( $request );
			$this->button_border_color( $request );
			$this->button_text_color( $request );
		}
		/**
		 * Save button Color data
		 *
		 * @version 1.0.0
		 * @param array $request all submit data.
		 */
		public function button_color( $request ) {
			$default_button_color = '#4caf50';
			$button_color         = $request['button_color'];
			$not_set_color        = isset( $button_color ) && ! empty( $button_color ) ? $button_color : $default_button_color;
			$sanitize_color       = sanitize_text_field( $not_set_color );
			update_option( 'stock_notifier_button_color', $sanitize_color );
		}
		/**
		 * Save button border color data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		public function button_border_color( $request ) {
			$default_border_color = '#fff';
			$border_color         = $request['border_color'];
			$not_set_color        = isset( $border_color ) && ! empty( $border_color ) ? $border_color : $default_border_color;
			$sanitize_color       = sanitize_text_field( $not_set_color );
			update_option( 'stock_notifier_button_border_color', $sanitize_color );
		}
		/**
		 * Save button text color data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		public function button_text_color( $request ) {
			$default_button_text_color = '#fff';
			$button_text_color         = $request['button_text_color'];
			$not_set_color             = isset( $button_text_color ) && ! empty( $button_text_color ) ? $button_text_color : $default_button_text_color;
			$sanitize_color            = sanitize_text_field( $not_set_color );
			update_option( 'stock_notifier_button_text_color', $sanitize_color );
		}
		/**
		 * Call all notifications setting method for saving data.
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_general_option_data_save( $request ) {
			$this->update_option_frontend_placeholders( $request );
			$this->update_option_frontend_button( $request );
			$this->update_option_subscription_description( $request );
		}
		/**
		 * Update success subscription description option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_option_subscription_description( $request ) {
			$inwp_frontent_empty_error_message = __( 'Get notified on WhatsApp when the product comes back in stock', 'stock-notifier' );
			$inwp_frontent_post_empty          = trim( $request['success_subscription_description'], ' ' );
			$field_empty_errors                = isset( $inwp_frontent_post_empty ) && ! empty( $inwp_frontent_post_empty ) ? $inwp_frontent_post_empty : $inwp_frontent_empty_error_message;
			$field_empty_error                 = sanitize_text_field( $field_empty_errors );
			update_option( 'stock_notifier_success_subscription_description', $field_empty_error );
		}
		/**
		 * Update frontend button option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_option_frontend_button( $request ) {
			$inwp_frontent_button_lable = __( 'Request stock', 'stock-notifier' );
			$inwp_frontent_post_button  = trim( $request['frontent_form_button'], ' ' );
			$frontent_form_buttons      = isset( $inwp_frontent_post_button ) && ! empty( $inwp_frontent_post_button ) ? $inwp_frontent_post_button : $inwp_frontent_button_lable;
			$frontent_form_button       = sanitize_text_field( $frontent_form_buttons );
			update_option( 'stock_notifier_frontent_form_button', $frontent_form_button );
		}
		/**
		 * Update frontend placeholder option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_option_frontend_placeholders( $request ) {
			$inwp_frontent_form_placeholder = __( 'Enter Your Whatsapp Number', 'stock-notifier' );
			$inwp_frontent_post_placeholder = trim( $request['frontent_form_placeholder'], ' ' );
			$frontent_form_placeholders     = isset( $inwp_frontent_post_placeholder ) && ! empty( $inwp_frontent_post_placeholder ) ? $inwp_frontent_post_placeholder : $inwp_frontent_form_placeholder;
			$frontent_form_placeholder      = sanitize_text_field( $frontent_form_placeholders );
			update_option( 'stock_notifier_frontent_form_placeholder', $frontent_form_placeholder );
		}

		/**
		 * Call Notifiecation Message setting all method for save data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_sms_settings_option_data_save( $request ) {
			$this->enable_disable_subscription_sms( $request );
			$this->update_success_subscription_subject( $request );
			$this->update_success_subscription_message( $request );
			$this->enable_disable_in_stock_sms( $request );
			$this->update_success_is_stock_sms_subject( $request );
			$this->update_success_is_stock_sms_message( $request );
		}
		/**
		 * Update success in stock sms message option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_success_is_stock_sms_message( $request ) {
			$instock_message                  = 'Hello {whatsapp_number},{br}Thank you for your patience. Your requested product {product_name} is now back in stock! Hurry up and purchase this product now before stock ends.
			To add this product directly to your cart, follow this link: {cart_link}';
			$inwp_enable_instock_message_post = trim( $request['instock_sub_message'], ' ' );
			$inwp_enable_instock_message      = isset( $inwp_enable_instock_message_post ) && ! empty( $inwp_enable_instock_message_post ) ? $inwp_enable_instock_message_post : $instock_message;
			$inwp_enable_instock_messages     = sanitize_text_field( $inwp_enable_instock_message );
			update_option( 'stock_notifier_instock_sub_message', $inwp_enable_instock_messages );
		}

		/**
		 * Update success in stock sms subject option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_success_is_stock_sms_subject( $request ) {
			$instock_sms_subject              = __( 'Product {product_name} is now back in stock.', 'stock-notifier' );
			$inwp_enable_instock_subject_post = trim( $request['instock_sub_subject'], ' ' );
			$inwp_enable_instock_subject      = isset( $inwp_enable_instock_subject_post ) && ! empty( $inwp_enable_instock_subject_post ) ? $inwp_enable_instock_subject_post : $instock_sms_subject;
			$inwp_enable_instock_subjects     = sanitize_text_field( $inwp_enable_instock_subject );
			update_option( 'stock_notifier_instock_sub_subject', $inwp_enable_instock_subjects );
		}
		/**
		 * Enable disable in stock sms
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function enable_disable_in_stock_sms( $request ) {
			$inwp_enable_instock_success   = isset( $request['enable_instock_sms'] ) ? $request['enable_instock_sms'] : 0;
			$inwp_enable_instock_successes = sanitize_text_field( $inwp_enable_instock_success );
			update_option( 'stock_notifier_enable_instock_sms', $inwp_enable_instock_successes );
		}
		/**
		 * Update success subscription message option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_success_subscription_message( $request ) {
			$success_subscribe_message   = __( 'Dear {subscriber_number}, {br}" . "Thank you for subscribing to the #{product_name}. We will notify you via WhatsApp once the product is back in stock. Thank you.', 'stock-notifier' );
			$inwp_success_sub_message_to = trim( $request['success_sub_message'], ' ' );
			$inwp_success_sub_message    = isset( $inwp_success_sub_message_to ) && ! empty( $inwp_success_sub_message_to ) ? $inwp_success_sub_message_to : $success_subscribe_message;
			$inwp_success_sub_messages   = sanitize_text_field( $inwp_success_sub_message );
			update_option( 'stock_notifier_success_sub_message', $inwp_success_sub_messages );
		}

		/**
		 * Update success subscription subject option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_success_subscription_subject( $request ) {
			$success_sub_subject       = __( 'You have been subscribed to {product_name} at {shopname}.{br}', 'stock-notifier' );
			$inwp_subject_post         = trim( $request['success_sub_subject'], ' ' );
			$inwp_success_sub_subjects = isset( $inwp_subject_post ) && ! empty( $inwp_subject_post ) ? $inwp_subject_post : $success_sub_subject;
			$inwp_success_sub_subject  = sanitize_text_field( $inwp_success_sub_subjects );
			update_option( 'stock_notifier_success_sub_subject', $inwp_success_sub_subject );
		}

		/**
		 * Enable disable subscription message
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function enable_disable_subscription_sms( $request ) {
			$inwp_enable_success   = isset( $request['enable_success_subscription'] ) ? $request['enable_success_subscription'] : 0;
			$inwp_enable_successes = sanitize_text_field( $inwp_enable_success );
			update_option( 'stock_notifier_enable_success_subscription', $inwp_enable_successes );
		}

		/**
		 * Update Whatsapp toggle option value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_whatsapp_toggle( $request ) {
			$whatsapp_toggle = sanitize_text_field( $request['whatsapp_toggle'] );
			$whatsapp_toggle = isset( $whatsapp_toggle ) ? $whatsapp_toggle : 0;
			update_option( 'stock_notifier_whatsapp_toggle', $whatsapp_toggle );
		}
		/**
		 * Update Select API Value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_select_api( $request ) {
			$api_value = sanitize_text_field( $request['select_api'] );
			$api_value = isset( $api_value ) ? $api_value : 1;
			update_option( 'stock_notifier_select_api_value', $api_value );
		}

		/**
		 * Update Twilio all option fields data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_twilio_info( $request ) {
			$whatsapp_account_sid   = trim( sanitize_text_field( $request['twilio_SID'] ), ' ' );
			$whatsapp_auth_token    = trim( sanitize_text_field( $request['twilio_token'] ), ' ' );
			$whatsapp_sender_number = trim( sanitize_text_field( $request['twilio_senderNumber'] ), ' ' );
			update_option( 'stock_notifier_twilio_SID', $whatsapp_account_sid );
			update_option( 'stock_notifier_twilio_token', $whatsapp_auth_token );
			update_option( 'stock_notifier_twilio_senderNumber', $whatsapp_sender_number );
		}
		/**
		 * Call Ultramsg api all method for save data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_ultramsg_info( $request ) {
			$this->whatsapp_ultramsg_instance_id( $request );
			$this->whatsapp_ultramsg_auth_token( $request );
		}
		/**
		 * Update ultramsg instance_id option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function whatsapp_ultramsg_instance_id( $request ) {
			$whatsapp_ultramsg_insatnce = trim( sanitize_text_field( $request['ultramsg_insatnceID'] ), ' ' );
			update_option( 'stock_notifier_ultramsg_insatnceID', $whatsapp_ultramsg_insatnce );
		}
		/**
		 * Update ultramsg auth token option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function whatsapp_ultramsg_auth_token( $request ) {
			$whatsapp_ultramsg_token = trim( sanitize_text_field( $request['ultramsg_token'] ), ' ' );
			update_option( 'stock_notifier_ultramsg_token', $whatsapp_ultramsg_token );
		}

		/**
		 * Call chat api instance id or auth token method for save data.
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function stock_notifier_chatapi_info( $request ) {
			$this->whatsapp_chatapi_auth_token( $request );
		}
		/**
		 * Update  Chat api auth token option data.
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function whatsapp_chatapi_auth_token( $request ) {
			$whatsapp_chat_tokens = trim( sanitize_text_field( $request['chatapi_token'] ), ' ' );
			update_option( 'stock_notifier_chatapi_token', $whatsapp_chat_tokens );
		}
		/**
		 * Call general settings all method for save data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		public function stock_notifier_update_general_setting( $request ) {
			$this->update_value_stock_progress_bar( $request );
			$this->update_value_stock_progress_bar_style( $request );
			$this->update_value_loop_product_visibility( $request );
			$this->update_value_non_logdin_user( $request );
			$this->update_value_logdin_user( $request );
			$this->update_value_backorders_option( $request );
			$this->update_value_hide_reguler_product( $request );
			$this->update_value_regular_sale_product_hide_show( $request );
			$this->update_value_ignore_disabled_variations( $request );
			$this->update_value_specific_tags( $request );
			$this->update_value_specific_tags_visibility_hide_show( $request );
			$this->update_value_specific_categories( $request );
			$this->update_value_specific_categories_visibility( $request );
			$this->update_value_specific_products( $request );
			$this->update_value_specific_products_visibility( $request );
		}

		/**
		 * Update Specific products visibility option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_specific_products_visibility( $request ) {
			$stock_notifier_specific_products_visibility  = isset( $request['specific_porduct_visibility'] ) ? $request['specific_porduct_visibility'] : 1;
			$stock_notifier_specific_products_visibilitys = sanitize_text_field( $stock_notifier_specific_products_visibility );
			update_option( 'stock_notifier_specific_products_visibility', $stock_notifier_specific_products_visibilitys );
		}
		/**
		 * Update  specific products option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_specific_products( $request ) {
			$api                              = new Stock_Notifier_API();
			$stock_notifier_specific_products = isset( $request['specific_products'] ) ? $request['specific_products'] : [];
			$stock_notifier_specific_product  = $api->_recursive_sanitize_text_field( $stock_notifier_specific_products );
			update_option( 'stock_notifier_specific_products', $stock_notifier_specific_product );
		}

		/**
		 * Update specific_categories visibility option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_specific_categories_visibility( $request ) {
			$stock_notifier_specific_categories_visibility  = isset( $request['specific_categories_visibility'] ) ? $request['specific_categories_visibility'] : 1;
			$stock_notifier_specific_categories_visibilitys = sanitize_text_field( $stock_notifier_specific_categories_visibility );
			update_option( 'stock_notifier_specific_categories_visibility', $stock_notifier_specific_categories_visibilitys );
		}

		/**
		 * Update specific_categories option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_specific_categories( $request ) {
			$api                                = new Stock_Notifier_API();
			$stock_notifier_specific_categories = isset( $request['specific_categories'] ) ? $request['specific_categories'] : [];
			$stock_notifier_specific_category   = $api->_recursive_sanitize_text_field( $stock_notifier_specific_categories );
			update_option( 'stock_notifier_specific_categories', $stock_notifier_specific_category );
		}

		/**
		 * Update  specific_tags_visibility_hide_show option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_specific_tags_visibility_hide_show( $request ) {
			$stock_notifier_specific_tags_visibility  = isset( $request['specific_tags_visibility'] ) ? $request['specific_tags_visibility'] : 1;
			$stock_notifier_specific_tags_visibilitys = sanitize_text_field( $stock_notifier_specific_tags_visibility );
			update_option( 'stock_notifier_specific_tags_visibility', $stock_notifier_specific_tags_visibilitys );
		}
		/**
		 * Update  specific_tags option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_specific_tags( $request ) {
			$api                          = new Stock_Notifier_API();
			$stock_notifier_specific_tags = isset( $request['specific_tags'] ) ? $request['specific_tags'] : [];
			$stock_notifier_specific_tag  = $api->_recursive_sanitize_text_field( $stock_notifier_specific_tags );
			update_option( 'stock_notifier_specific_tags', $stock_notifier_specific_tag );
		}
		/**
		 * Update multivendor on /off option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_mutlivendor_on_off( $request ) {
			$multivendor_on_off = isset( $request['multivendor_on_off'] ) ? $request['multivendor_on_off'] : 0;
			$multivendor_on_off = sanitize_text_field( $multivendor_on_off );
			update_option( 'stock_notifier_multivendor_on_off', $multivendor_on_off );
		}

		/**
		 * Update  ignore_disabled_variations option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_ignore_disabled_variations( $request ) {
			$ignore_disabled_variations = isset( $request['ignore_disabled_variation'] ) ? $request['ignore_disabled_variation'] : 0;
			$ignore_disabled_variation  = sanitize_text_field( $ignore_disabled_variations );

			update_option( 'stock_notifier_ignore_disabled_variation', $ignore_disabled_variation );
		}

		/**
		 * Update regular sale product hide show option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_regular_sale_product_hide_show( $request ) {
			$hide_regular_sale_products = isset( $request['hide_subscribe_sale_product'] ) ? $request['hide_subscribe_sale_product'] : 0;
			$hide_regular_sale_product  = sanitize_text_field( $hide_regular_sale_products );
			update_option( 'stock_notifier_hide_subscribe_sale_product', $hide_regular_sale_product );
		}

		/**
		 * Update  regular product hide option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_hide_reguler_product( $request ) {
			$hide_regular_products = isset( $request['hide_subscribe_regular_product'] ) ? $request['hide_subscribe_regular_product'] : 0;
			$hide_regular_product  = sanitize_text_field( $hide_regular_products );
			update_option( 'stock_notifier_hide_subscribe_regular_product', $hide_regular_product );
		}

		/**
		 * Update backorder on/off option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_backorders_option( $request ) {
			$show_backorders = isset( $request['show_subscribe_on_backorder'] ) ? $request['show_subscribe_on_backorder'] : 0;
			$show_backorder  = sanitize_text_field( $show_backorders );
			update_option( 'stock_notifier_show_subscribe_on_backorder', $show_backorder );
		}

		/**
		 * Update logdin_user option toggle option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_logdin_user( $request ) {
			$subscribe_logdins = isset( $request['hide_subscribe_loggedin'] ) ? $request['hide_subscribe_loggedin'] : 0;
			$subscribe_logdin  = sanitize_text_field( $subscribe_logdins );
			update_option( 'stock_notifier_hide_subscribe_loggedin', $subscribe_logdin );
		}

		/**
		 * Update  loop product visibility toggle data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_loop_product_visibility( $request ) {
			$loop_product_visibility = isset( $request['loop_product_visibility'] ) ? $request['loop_product_visibility'] : 0;
			$loop_product_visibility = sanitize_text_field( $loop_product_visibility );
			update_option( 'stock_notifier_loop_product_visibility', $loop_product_visibility );
		}
		/**
		 * Update stock progress bar value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_stock_progress_bar( $request ) {
			$stock_progress_bar = isset( $request['stock_progress_bar'] ) ? $request['stock_progress_bar'] : 0;
			$stock_progress_bar = sanitize_text_field( $stock_progress_bar );
			update_option( 'stock_notifier_stock_progress_bar', $stock_progress_bar );
		}
		/**
		 * Update stock progress bar Style value
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_stock_progress_bar_style( $request ) {
			$stock_progress_bar_style = isset( $request['stock_progress_bar_style'] ) ? $request['stock_progress_bar_style'] : 1;
			$stock_progress_bar_style = sanitize_text_field( $stock_progress_bar_style );
			update_option( 'stock_notifier_stock_progress_bar_style', $stock_progress_bar_style );
		}
		/**
		 * Update non-logdin for user option data
		 *
		 * @param array $request all submit data.
		 * @version 1.0.0
		 */
		private function update_value_non_logdin_user( $request ) {
			$non_logdins_user = isset( $request['non_logdins_user'] ) ? $request['non_logdins_user'] : 0;
			$non_logdins_users = sanitize_text_field( $non_logdins_user );
			update_option( 'stock_notifier_hide_sub_non_log', $non_logdins_users );
		}
		/**
		 * Check user role and capabiltiy.
		 *
		 * @return boolean
		 */
		public function save_stock_notifier_gateway_settings_permission() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			return true;
		}
		/**
		 * Check user role and capabiltiy.
		 *
		 * @return boolean
		 */
		public function save_stock_notifier_notification_settings_permission() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			if ( ! is_user_logged_in() ) {
				return false;
			}
			return true;

		}

		/**
		 * Check user Use Permissions
		 *
		 * @return boolean
		 * @version 1.1.0
		 */
		public function save_stock_notifier_general_settings_permission() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			return true;
		}

		/**
		 * Check user Use Permissions
		 *
		 * @return boolean
		 * @version 1.1.0
		 */
		public function save_stock_notifier_multi_vendor_setting_permission() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			return true;
		}
	}
	/**
	 * Kick out the contructor
	 */
	new Stock_Notifier_Rest_Route();
}
