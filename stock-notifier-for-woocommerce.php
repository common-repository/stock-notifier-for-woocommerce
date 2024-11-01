<?php
/**
 * Plugin Name: Stock Notifier for WooCommerce
 * Plugin URI:  https://wppool.dev/
 * Description: Woocommerce plugin using which a customer can subscribe for interest on an out of stock product. When the product becomes available, subscribed customer will get an alert Whatsapp.
 * Version:     2.1.7
 * Author:      WPPOOL
 * Author URI:  http://wppool.dev
 * Text Domain:  stock-notifier
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package STOCKNOTIFIER
 */

// don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit(1);
}

// define constants.
if ( ! defined( 'STOCKNOTIFIER_VERSION' ) ) {
	define( 'STOCKNOTIFIER_VERSION', '2.1.7' );
	define( 'STOCKNOTIFIER_PATH', plugin_dir_path( __FILE__ ) );
	define( 'STOCKNOTIFIER_INCLUDES', STOCKNOTIFIER_PATH . 'includes' );
	define( 'STOCKNOTIFIER_URL', plugin_dir_url( __FILE__ ) );
	define( 'STOCKNOTIFIER_ASSETS', STOCKNOTIFIER_URL . '/assets' );
	define( 'STOCKNOTIFIER_FILE', __FILE__ );
}
require_once STOCKNOTIFIER_PATH . 'appsero/client/src/Client.php';
require_once STOCKNOTIFIER_INCLUDES . '/wppoolsdk/class-plugin.php';
// deactivation_hook.
register_deactivation_hook( STOCKNOTIFIER_FILE, function () {
	update_option( 'stock_notifier_free_active', 0 );
});

if ( ! class_exists( 'Stock_Notifier' ) ) {
	/**
	* Stock notifier base class
	*
	* @return object
	*
	* @since 1.0.0
	*/
	final class Stock_Notifier {
		/**
		 * Set php version for debug console
		 *
		 * @var string
		 */
		private static $min_php = '5.6.0';
		/**
		 * Constructor for the Stock_Notifier class
		 *
		 * Sets up all the appropriate hooks and actions
		 * within our plugin.
		 */
		private function __construct() {
			// Activation hook call When plugin run first time.
			register_activation_hook( STOCKNOTIFIER_FILE, [ $this, 'activate' ] );
			$this->appsero_init_tracker_stock_notifier_for_woocommerce();
			$this->wp_sms_active();
			$this->check_premium_active();
			// Use for woocommerce dependence active check.
			if ( self::check_dependent_true() ) {
				$this->avoid_header_sent();
				$this->include_files();
				add_filter( 'woocommerce_screen_ids', [ $this, 'screen_ids_to_woocommerce' ] );
				add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );
				add_action( 'admin_head', [ $this, 'remove_help_tab_context_plugin' ] );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
				add_action( 'activated_plugin', [ $this, 'activation_redirect' ] );
				add_action( 'stock_notifier_popup_dokan', [ $this, 'show_popup_dokan' ] );
			} else {
				$this->include_woocommerce_popup_file();
				add_action('admin_menu',[ $this, 'register_stock_notifer_menu' ] );
				add_action('admin_enqueue_scripts',[ $this, 'css_for_woocommerce_popup' ]);
				add_action( 'activated_plugin', [ $this, 'activation_redirect' ] );
			}
		}
		/**
		 * WooCommerce popup file load.
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function include_woocommerce_popup_file() {
			require_once STOCKNOTIFIER_INCLUDES . '/class-popup-ajax.php';
		}
		/**
		 * Woocommerce inactive popup css
		 *
		 * @param mixed $hook hook name.
		 *
		 * @return void
		 */
		public function css_for_woocommerce_popup( $hook ) {
			wp_enqueue_style( 'stock_notifier_admin_css', STOCKNOTIFIER_URL . 'assets/admin/admin.css', [], time(), false );
			if ( 'toplevel_page_stock_notifier_settings' === $hook ) {
				wp_enqueue_script( 'stock_notifier_popup', STOCKNOTIFIER_URL . 'assets/admin/popup.js', [ 'jquery' ], STOCKNOTIFIER_VERSION, true );
				wp_localize_script( 'stock_notifier_popup', 'stock_notifier_popup', [
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'stock_notifier_popup_ajax' ),
				] );
			}

		}
		/**
		 * Register Stock Notifier -> Woocommerce inactive Menu
		 *
		 * @return void
		 * @version 2.0.1
		 */
		public function register_stock_notifer_menu() {
			add_menu_page( __( 'Stock Notifier','stock-notifier' ), __('Stock Notifier','stock-notifier'), 'manage_options', 'stock_notifier_settings', [ $this, 'stock_notifier_setting' ],STOCKNOTIFIER_URL . 'assets/img/svg.svg', 40 );
		}
		/**
		 * Woocommerce inactive Setting page
		 *
		 * @return void
		 * @version 2.0.1
		 */
		public function stock_notifier_setting() {
			$template = __DIR__ . '/templates/woocommerce-popup.php';
			if ( file_exists( $template ) ) {
				include $template;
			}
		}

		/**
		 * Show dokan Multivendor page premium popup
		 *
		 * @since 1.0.8
		 */
		public function show_popup_dokan() {
			?>
			<div class="stock_notifier_pro_popup" id="stock_notifier_pro_popup">
			<div class="content">
				<div class="close_btn_outer">
					<div class="close_btn_inner">
						<label class="close_label"><?php esc_html_e( 'Close', 'stock-notifier' ); ?></label>
					</div>
				</div>
				<div class="Stock_lock_button" >
					<p style="margin-left: -35px; color:#fff;">Ask admin to enable this features</p>       
				</div>  
			</div>
			</div>
			<?php
		}
		/**
		 * Check premium active
		 *
		 * @version 1.0.5
		 */
		public function check_premium_active() {
			$pro_active = get_option( 'stock_notifier_pro_active', 0 );
			$ultimate_actives = get_option( 'stock_notifier_ultimate_active', 0 );
			$select_api = get_option( 'stock_notifier_select_api_value', 1 );
			$check_premium_active = false;
			// Check premium actve.
			if ( 1 == $pro_active || 1 == $ultimate_actives ) {
				$check_premium_active = true;
			}

			if ( false == $check_premium_active ) {
				if ( 4 == $select_api ) {
					update_option( 'stock_notifier_select_api_value', 1 );
				}
			}
		}
		/**
		 * Create database table
		 * When plugin active call this function and carete datebase table for Stock Notifier for wooCommerce
		 *
		 * @return void
		 *
		 * @version 1.0.0
		 */
		public function activate() {
			self::create_default_data();
			self::create_tables();
		}
		/**
		 * Check WP_SMS_active
		 *
		 * @return void
		 *
		 * @version 1.0.9
		 */
		public function wp_sms_active() {
			$select_api  = get_option( 'stock_notifier_select_api_value', 1 );
			$plugin_list = get_option( 'active_plugins' );
			$wp_sms_free = 'wp-sms/wp-sms.php';

			if ( in_array( $wp_sms_free, $plugin_list, false ) ) {
				update_option( 'stock_notifier_wp_sms_active', 1 );
			} else {
				update_option( 'stock_notifier_wp_sms_active', 0 );
				if ( '4' == $select_api ) {
					update_option( 'stock_notifier_select_api_value', 1 );
				}
			}
		}
		/**
		 * Initialize the plugin tracker
		 *
		 * @return void
		 */
		public function appsero_init_tracker_stock_notifier_for_woocommerce() {
			if ( ! class_exists( 'Appsero\Client' ) ) {
				require_once __DIR__ . '/appsero/client/src/Client.php';
			}

			$client = new Appsero\Client( '90619c56-5b1b-4e75-a681-d6acc87245d2', 'Stock Notifier for WooCommerce', STOCKNOTIFIER_FILE );

			// Active insights.
			$client->insights()->init();
			// Init WPPOOL Plugin.
			if ( function_exists( 'wppool_plugin_init' ) ) {
				$image = STOCKNOTIFIER_INCLUDES . '/wppoolsdk/background-image.png';
				wppool_plugin_init( 'stock_notifier_for_woocommerce',$image );
			}
		}
		/**
		 * Use for create datebase table
		 *
		 * @return void
		 *
		 * @version 1.0.0
		 */
		private static function create_tables() {
			global $wpdb;
			$wpdb->hide_errors();
			$table_name = $wpdb->prefix . 'stock_notifier_popular_product';
			$sql = "CREATE TABLE if not exists $table_name(
			post_id INT(11),product_id INT(11),author_id INT(11),product_name VARCHAR(255),
			phone_number VARCHAR(255),shop_name VARCHAR(255),status VARCHAR(255),
			populer_date DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY(post_id)
			)";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
		/**
		 * Save Stock Notifier for WooCommerce some important data for plugin run first time
		 *
		 * @since 1.0.0
		 */
		private static function create_default_data() {
			$version         = get_option( 'stock_notifier_version', '0' );
			$install_time    = get_option( 'stock_notifier_install_time', '' );
			$current_user_id = get_current_user_id();

			if ( empty( $version ) ) {
				update_option( 'stock_notifier_version', STOCKNOTIFIER_VERSION );
			}

			if ( ! empty( $install_time ) ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );
				update_option( 'stock_notifier_install_time', gmdate( $date_format . ' ' . $time_format ) );
			}
			update_option( 'stock_notifier_free_active', 1 );
			update_option( "stock_notifier_dokan_notifier_on_off_$current_user_id", 1 );
			flush_rewrite_rules();
		}

		/**
		 * When plugin active redirect to setting page
		 *
		 * @param array $plugin comment about this variable.
		 *
		 * @version 1.0.0
		 */
		public function activation_redirect( $plugin ) {
			$plugin_file = plugin_basename( __FILE__ );
			if ( $plugin_file == $plugin ) {
				wp_safe_redirect( admin_url( 'admin.php?page=stock_notifier_settings' ) );
				exit;
			}
		}
		/**
		 * Stock Notifier plugin action links
		 * plugin_action_links method use for show Stock Notifier GET PRO button and Setting in plugin.php
		 *
		 * @param array $links when click this button redirect wppool.dev page.
		 *
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$pro_active      = get_option( 'stock_notifier_pro_active', 0 );
			$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );

			$plugin_link1 = [
				'<a href="' . admin_url( 'admin.php?page=stock_notifier_settings' ) . '">' . __( 'Settings', 'stock-notifier' ) . '</a>',
			];
			$plugin_links = array_merge( $links, $plugin_link1 );

			if ( 1 != $pro_active && 1 != $ultimate_active ) {
				$plugin_link2 = [
					'<a  style="color: orangered;font-weight: bold;" href="https://go.wppool.dev/hiE1">' . __( 'GET PRO', 'stock-notifier' ) . '</a>',
				];
				$plugin_links = array_merge( $plugin_links, $plugin_link2 );
			}
			return $plugin_links;
		}

		/**
		 * Register screen ids This is to indicate that Stock Notifier is a wooComerce page.
		 *
		 * @param array $screen_ids stock notifier ids.
		 *
		 * @return mixed
		 */
		public function screen_ids_to_woocommerce( $screen_ids ) {
			$screen_ids[] = 'edit-stock_notifier';
			$screen_ids[] = 'stock_notifier';
			$screen_ids[] = 'stock-notifier_page_stock_notifier_settings';
			$screen_ids[] = 'toplevel_page_stock_notifier ';
			return $screen_ids;
		}
		/**
		 * Initializes singleton instance Stock Notifier class
		 *
		 * @return object
		 */
		public static function init() {
			static $instance = false;
			if ( ! $instance ) {
				$instance = new self();
			}
			return $instance;
		}
		/**
		 * Use headers to avoid troubleshooting
		 *
		 * @version 1.0.0
		 *
		 * @return void
		 */
		public function avoid_header_sent() {
			ob_start();
		}
		/**
		 * Include necessary files to load for stock notifier for wooCommerce
		 *
		 * @return void
		 */
		public function include_files() {
			require_once STOCKNOTIFIER_INCLUDES . '/admin/class-custom-post-type.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-enqueue.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-rest-api.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-api.php';
			require_once STOCKNOTIFIER_INCLUDES . '/admin/class-settings.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-ajax.php';
			require_once STOCKNOTIFIER_INCLUDES . '/user-profile/class-user.php';
			require_once STOCKNOTIFIER_INCLUDES . '/frontend/class-form.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-logger.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-subscribe-sms.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-instock-sms.php';
			require_once STOCKNOTIFIER_INCLUDES . '/library/persistent-notices.php';
			require_once STOCKNOTIFIER_INCLUDES . '/class-core-functions.php';

			$multivendor_on = get_option( 'stock_notifier_multivendor_on_off', 0 );

			if ( ( is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active_for_network( 'dokan-lite/dokan.php' ) || is_plugin_active( 'dokan-pro/dokan-pro.php' ) || is_plugin_active_for_network( 'dokan-pro/dokan-pro.php' ) ) && '1' == $multivendor_on ) {
				require_once STOCKNOTIFIER_INCLUDES . '/dokan/add-dashboard-menu.php';
				require_once STOCKNOTIFIER_INCLUDES . '/dokan/dokan-ajax.php';
			}

		}

		/**
		 * Loaded plugin text domain for translation
		 *
		 * @return bool
		 */
		public function load_plugin_textdomain() {
			$domain = 'stock-notifier';
			$dir    = untrailingslashit( WP_LANG_DIR );
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
			$exists = load_textdomain( $domain, $dir . '/plugins/' . $domain . '-' . $locale . '.mo' );
			if ( $exists ) {
				return $exists;
			} else {
				load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * Hide help context tab
		 *
		 * @version 1.0.0
		 */
		public function remove_help_tab_context_plugin() {
			$screen = get_current_screen();
			if ( 'edit-stock_notifier' == $screen->id || 'stock-notifier_page_stock_notifier_settings' == $screen->id ) {
				$screen->remove_help_tabs();
			}
		}

		/**
		 * Check Stock Notifier Dependencies
		 *
		 * @return boolen
		 *
		 * @version 1.0.0
		 */
		public static function check_dependent_true() {
			if ( self::check_environment() && self::is_woocommerce_activated() ) {
				return true;
			}
			return false;
		}

		/**
		 * Ensure theme and server variable compatibility
		 *
		 * @return boolean
		 * @since  1.0.0
		 * @access private
		 */
		private static function check_environment() {
			$return = true;
			// Check the PHP version compatibility.
			if ( version_compare( PHP_VERSION, self::$min_php, '<=' ) ) {
				$return = false;
				$notice = sprintf( esc_html__( 'Unsupported PHP version Min required PHP Version: "%s"', 'stock-notifier' ), self::$min_php ); //phpcs:ignore
			}

			// Add notice and deactivate the plugin if the environment is not compatible.
			if ( ! $return ) {
				add_action('admin_notices', function () use ( $notice ) {
					?>
					<div class="notice is-dismissible notice-error">
					<p>
						<?php
							printf( wp_kses_post( $notice ));
						?>
					</p>
					</div>
					<?php
				} );
				return $return;

			} else {
				return $return;
			}
		}

		/**
		 * Check wooCommerce active or not.
		 *
		 * @return bool
		 */
		private static function is_woocommerce_activated() {
			$active_value = true;
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$active_value = true;
			} elseif ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				$active_value = true;
			} else {
				$active_value = false;
				$in_active_notice = sprintf( __('%1$s Stock Notifier for WooCommerce is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for the %5$sStock Notifier for WooCommerce %6$s to work . Please %7$sinstall & activate WooCommerce%8$s', 'stock_notifier' ), '<strong>', '</strong>', '<strong>', '</strong>', '<strong>', '</strong>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' );//phpcs:ignore
			}

			/** Add notice and deactivate the plugin if woocommerce inactive */
			if ( ! $active_value ) {
				add_action( 'admin_notices', function () use ( $in_active_notice ) {
					?>
					<div class="notice notice-warning is-dismissible">
						<p><?php printf( wp_kses_post($in_active_notice) ); ?></p>
					</div>
					<?php
				});
					return $active_value;
			} else {
				return $active_value;
			}
		}
	}
}
/**
 * Kick-of the plugin
 */
Stock_Notifier::init();