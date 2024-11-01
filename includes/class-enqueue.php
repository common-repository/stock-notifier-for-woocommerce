<?php
/**
 * Don't call the file directly.
 *
 * @package STOCKNOTIFIER
 */

defined( 'ABSPATH' ) || exit;
/**
 * Enqueue all admin or frontend scripts
 *
 * @version 1.0.0
 */

if ( ! class_exists( 'Stock_Notifier_Scripts' ) ) {
	/**
	 * Enqueue all admin or frontend scripts
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Scripts {
		/**
		 * Store product subscribers Name
		 *
		 * @var array
		 */
		public $product_default_unique_name_2 = [];
		/**
		 * Store product subscribers Name
		 *
		 * @var array
		 */
		public $product_default_count_2 = [];
		/**
		 * Stock_Notifier_Scripts constructor.
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
			$dokan_active = self::check_dokan_active();
		}
		/**
		 * Check Dokan plugin active or not active
		 *
		 * @version 1.0.0
		 *
		 * @return boolean
		 */
		public static function check_dokan_active() {
			$dokan_active = false;
			if ( ( is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active_for_network( 'dokan-lite/dokan.php' ) ) || ( is_plugin_active( 'dokan-pro/dokan-pro.php' ) || is_plugin_active_for_network( 'dokan-pro/dokan-pro.php' ) ) ) {
					$dokan_active = true;
			}
			return $dokan_active;
		}


		/**
		 * Check scripts already exist
		 *
		 * @param String $handle return current page.
		 *
		 * @param String $list list out scripts.
		 * @version 1.0.0
		 */
		public function check_script_is_already_load( $handle, $list = 'enqueued' ) {
			return wp_script_is( $handle, $list );
		}
		/**
		 * Check stock notifier active url in dokan.
		 *
		 * @return url dokan url
		 * @version 1.0.0
		 */
		public static function check_dokan_url() {
			global $wp;
			$current_page = $wp->query_vars;
			$dokan_active_url = false;
			if ( isset( $current_page['stock_notifier'] ) && ( 'all_subscriber' == $current_page['stock_notifier'] || 'overview' == $current_page['stock_notifier'] ) ) {
				$dokan_active_url = true;
			}
			return $dokan_active_url;
		}

		/**
		 * Stock Notifier Frontend css and js
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function frontend_scripts() {
			/* enqueue frontend styles */
			$get_hide_form_guests = get_option( 'stock_notifier_hide_sub_non_log' );
			$default_country = get_option( 'stock_notifier_default_country_code', 'us' );
			$check_wp_visibility = isset( $get_hide_form_guests ) && ! empty( $get_hide_form_guests ) && ! is_user_logged_in() ? false : true;
			if ( $check_wp_visibility ) {
				$check_already_enqueued = $this->check_script_is_already_load( 'jquery-blockui' );
				if ( ! $check_already_enqueued ) {
					wp_enqueue_script( 'jquery-blockui', STOCKNOTIFIER_URL . 'assets/frontend/jquery.blockUI.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, false );
				}
				wp_enqueue_style( 'stock_notifier_frontend_css', STOCKNOTIFIER_URL . 'assets/frontend/frontend.css', [ 'dashicons' ],time(),false );
				wp_enqueue_script( 'stock_notifier_intlTrlInput_js', STOCKNOTIFIER_URL . 'assets/frontend/intlTelInput-jquery.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, true );
				wp_localize_script( 'stock_notifier_intlTrlInput_js', 'stock_notifier_default_country', [
					'default_country' => $default_country,
				] );
				wp_enqueue_script( 'stock_notifier_js', STOCKNOTIFIER_URL . 'assets/frontend/frontend1.js', [ 'jquery', 'jquery-blockui', 'stock_notifier_intlTrlInput_js' ], STOCKNOTIFIER_VERSION, true );
				wp_localize_script( 'stock_notifier_js', 'stock_notifier_form', [
					'ajax_url'       => admin_url( 'admin-ajax.php' ),
					'url'            => STOCKNOTIFIER_URL,
					'user_id'        => get_current_user_id(),
					'security'       => wp_create_nonce( 'stock_notifier_product_subscribe' ),
					'security_error' => __( 'Something went wrong, please try after sometime', 'stock-notifier' ),
				] );
				do_action( 'stock_notifier_fronted_js' );
			}

			$multivendor_on = get_option( 'stock_notifier_multivendor_on_off' );
			$dokan_active = self::check_dokan_active();
			if ( ! ( $dokan_active && $multivendor_on ) ) {
				return;
			}
			if ( ! self::check_dokan_url() ) {
				return;
			}
			$localizer = apply_filters('stock_notifier_localization_array',
				[
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'user_id'   => get_current_user_id(),
					'security2' => wp_create_nonce( 'stock_notifier_vendor_notifier_on_off' ),
				]
			);
			wp_enqueue_style( 'stock_notifier_dokan_css', STOCKNOTIFIER_URL . 'assets/dokan/dokan.css',[],time(), false );
			wp_enqueue_script( 'stock_notifier_dokan_js', STOCKNOTIFIER_URL . 'assets/dokan/dokan.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, true );
			wp_localize_script( 'stock_notifier_dokan_js', 'stock_notifier', $localizer );
			do_action( 'stock_notifier_dokan_js' );
		}
		/**
		 * Stock Notifier admin css and js
		 *
		 * @param Object $hook return screen ids.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function admin_enqueue( $hook ) {
			$default_country = get_option( 'stock_notifier_default_country_code', 'us' );
			wp_enqueue_style( 'stock_notifier_admin_css', STOCKNOTIFIER_URL . 'assets/admin/admin.css', [], time(), false );
			$screen = get_current_screen();
			if ( 'stock-notifier_page_stock_notifier_settings' == $screen->id || 'edit-stock_notifier' == $screen->id || 'toplevel_page_stock_notifier' == $screen->id ) {
				wp_enqueue_script( 'stock_notifier_syotimer', STOCKNOTIFIER_URL . 'assets/admin/jquery.syotimer.min.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, false );
				wp_enqueue_script( 'stock_notifier_admin_js', STOCKNOTIFIER_URL . 'assets/admin/admin.js', [ 'jquery', 'stock_notifier_syotimer' ], STOCKNOTIFIER_VERSION, false );
				$plugin_active_first_time  = get_option( 'stock_notifier_plugin_active_first_time', 0 );
				$pro_active                = get_option( 'stock_notifier_pro_active', 0 );
				$ultimate_active           = get_option( 'stock_notifier_ultimate_active', 0 );
				$wp_sms_active             = get_option( 'stock_notifier_wp_sms_active', 0 );
				wp_enqueue_script( 'stock_notifier_settings_js', STOCKNOTIFIER_URL . 'assets/setting/setting.js', [ 'jquery', 'wp-element' ], STOCKNOTIFIER_VERSION, false );
				wp_localize_script('stock_notifier_settings_js', 'stock_notifier_appLocalizer',
					[
						'stock_notifier_rest_apiUrl'      => home_url( '/wp-json' ),
						'stock_notifier_rest_nonce'       => wp_create_nonce( 'wp_rest' ),
						'stock_notifier_ajax_url'         => admin_url( 'admin-ajax.php' ),
						'stock_notifier_security'         => wp_create_nonce( 'stock_notifier_setting_nonce' ),
						'stock_notifier_firstTime_active' => $plugin_active_first_time,
						'stock_notifier_pro_active'       => $pro_active,
						'stock_notifier_ultimate_active'  => $ultimate_active,
						'stock_notifier_wp_sms'           => $wp_sms_active,
						'stock_notifier_admin_url'        => admin_url( '/admin.php?page=stock_notifier_settings' ),
					]
				);
				if ( '1' == $ultimate_active || '1' == $pro_active ) {
					do_action( 'stock_notifier_admin_assets' );
				} else {
					wp_enqueue_script( 'stock_notifier_overview_js', STOCKNOTIFIER_URL . 'assets/overviewFree/overview.js', [ 'jquery', 'wp-element' ], STOCKNOTIFIER_VERSION, false );
				}
			}
			if ( 'profile.php' == $hook || 'user-edit.php' == $hook ) {
				wp_enqueue_style( 'stock_notifier_user_css', STOCKNOTIFIER_URL . 'assets/user/user.css', [], time(), false );
				wp_enqueue_script( 'stock_notifier_intlTellnput_js', STOCKNOTIFIER_URL . 'assets/user/intlTelInput.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, true );
				wp_localize_script( 'stock_notifier_intlTellnput_js', 'stock_notifier_default_country', [
					'default_country' => $default_country,
				] );
				wp_enqueue_script( 'stock_notifier_user_js', STOCKNOTIFIER_URL . 'assets/user/isValidNumber.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, true );
				wp_localize_script( 'stock_notifier_user_js', 'stock_notifier_isValidnumber', [ 'plugin_Urls' => STOCKNOTIFIER_URL ] );
			}
		}
	}
	/**
	 * Kick out Stock_Notifier_Scripts constructor
	 *
	 * @version 1.0.0
	 */
	new Stock_Notifier_Scripts();
}
