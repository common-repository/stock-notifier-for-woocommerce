<?php
/**
 * Register Stock Notifier Setting
 *
 * @package STOCKNOTIFIER
 */

// if direct access than exit the file.
defined( 'ABSPATH' ) || exit;
/**
 * Check class is already exists
 *
 * @version 1.0.0
 */
if ( ! class_exists( 'Stock_Notifier_Setting' ) ) {
	/**
	 * Stock Notifier class for register setting and create submenu
	 *
	 * @package STOCKNOTIFIER
	 */
	class Stock_Notifier_Setting {
		/**
		 * API Object store
		 *
		 * @var object STOCKNOTIFIER
		 */
		public $api;
		/**
		 * Calling method
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'add_setting_submenu' ], 11 );
			$this->api = new Stock_Notifier_API();
			add_action( 'admin_init', [ $this, 'dokan_active_or_not' ] );
			add_action( 'admin_footer', [ $this, 'redirect_wppool_dev' ] );
		}
		/**
		 * Redirect wppool page
		 */
		public function redirect_wppool_dev() {
			?>
			<script type="text/javascript">
					jQuery(document).on("click", "#stock_notifier-get-pro-menu", function (e) {
						e.preventDefault();
						window.open("https://go.wppool.dev/hiE1");
					});
			</script>
			<?php
		}

		/**
		 * Check dokan active or not
		 *
		 * @version 1.0.0
		 */
		public function dokan_active_or_not() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( ( is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active_for_network( 'dokan-lite/dokan.php' ) ) || ( is_plugin_active( 'dokan-pro/dokan-pro.php' ) || is_plugin_active_for_network( 'dokan-pro/dokan-pro.php' ) ) ) {
				update_option( 'stock_notifier_dokan_active_or_not', 1 );
			} else {
				update_option( 'stock_notifier_dokan_active_or_not', 0 );
			}
			$get_select_value = get_option( 'stock_notifier_select_api_value', 1 );
			$pro_active       = get_option( 'stock_notifier_pro_active', 0 );
			$ultimate_active  = get_option( 'stock_notifier_ultimate_active', 0 );
			$premium_version = 0;
			if ( '1' == $pro_active || '1' == $ultimate_active ) {
				$premium_version = 1;
			}
			if ( 1 != $premium_version ) {
				if ( '4' == $get_select_value || '5' == $get_select_value ) {
					update_option( 'stock_notifier_select_api_value', 1 );
				}
			}
		}
		/**
		 * Register Stock Notifier Menu.
		 *
		 * @version 1.0.0
		 */
		public function add_setting_submenu() {
			add_menu_page( __( 'Stock Notifier', 'stock-notifier' ), __( 'Stock Notifier', 'stock-notifier' ), 'manage_woocommerce', 'stock_notifier', [ $this, 'overview_setting' ], STOCKNOTIFIER_URL . 'assets/img/svg.svg', 40 );
			add_submenu_page( 'stock_notifier', __( 'Dashboard', 'stock-notifier' ), __( 'Dashboard', 'stock-notifier' ), 'manage_options', 'stock_notifier', [
				$this,
				'overview_setting',
			] );
			add_submenu_page( 'stock_notifier', 'Subscribers', 'Notification', 'manage_woocommerce', 'edit.php?post_type=stock_notifier', false );

			$plugin_active_first_time = get_option( 'stock_notifier_plugin_active_first_time', 0 );
			$setting_name  = '0' == $plugin_active_first_time || false == $plugin_active_first_time ? __( 'Configure Settings', 'stock-notifier' ) : __( 'Settings', 'stock-notifier' );
			add_submenu_page( 'stock_notifier', $setting_name, $setting_name, 'manage_woocommerce', 'stock_notifier_settings', [
				$this,
				'setting_menu',
			] );
			$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );
			if ( 1 != $ultimate_active ) {
				add_submenu_page( 'stock_notifier', __( 'Get Ultimate - Stock Notifier for WooCommerce', 'stock-notifier' ), '<span id="stock_notifier-get-pro-menu">Get Stock Notifier <span class="stock_notifier_ult_link">Ultimate</span> </span>', 'manage_options', '#' );
			}
		}
		/**
		 * Stock Notifier Setting menu callback
		 *
		 * @version 1.0.0
		 */
		public function setting_menu() {
			$get_license = get_option( 'stock_notifier_premium_license_1', 0 );
			if ( 1 == $get_license ) {
				?>
					<div class="stock_notifier_license_validation">
						Please activate your license <a href="<?php echo esc_url(admin_url( 'admin.php?page=stock-notifier-premium-license') ); ?>">Activate</a>
					</div>
				<?php
			}
			printf( '<div class="wrap"><div id="stock_notifier_app"  xmlns="http://www.w3.org/1999/html"></div></div>' );
			?>
			<script>
				var HW_config = {
					selector: ".stock_notifier_changelog",
					account: "JrAL87"
				}
			</script>
			<script async src="https://cdn.headwayapp.co/widget.js"></script><?php //phpcs:ignore 
		}
		/**
		 * Stock Notifier Dashboard menu callback
		 *
		 * @version 1.0.0
		 */
		public function overview_setting() {
			$get_license = get_option( 'stock_notifier_premium_license_1', 0 );
			if ( 1 == $get_license ) {
				?>
					<div class="stock_notifier_license_validation">
						Please active your license <a href="<?php echo esc_url(admin_url( 'admin.php?page=stock-notifier-premium-license')); ?>">Activate</a>
					</div>
				<?php
			}
			printf( '<div id="stock_notifier_overview_app"></div>' );
		}
	}
	/**
	 * Kick out the constuct method
	 */
	new Stock_Notifier_Setting();
}