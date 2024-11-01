<?php
/**
 * WooCoommerce Popup ajax request.
 *
 * @package STOCKNOTIFER
 */
if ( ! class_exists('Stock_Notifier_Popup_Ajax') ) {
	/**
	 * Show Woocommerce popup.
	 */
	class Stock_Notifier_Popup_Ajax {
		/**
		 * Instance of Stock_Notifier.
		 */
		public function __construct() {
			add_action('wp_ajax_stock_notifier_popup_ajax',[ $this, 'active_woocommerce' ] );
		}
		/**
		 * Activate woocommerce
		 */
		public function active_woocommerce() {
			if ( isset( $_POST ) ) {
				$nonce = isset($_POST['nonce_validation']) ? sanitize_text_field( wp_unslash($_POST['nonce_validation']) ) : '';
				if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'stock_notifier_popup_ajax' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				define('WP_ADMIN', true);
				define('WP_NETWORK_ADMIN', true);
				define('WP_USER_ADMIN', true);
				$woocoomerce = ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';

				if ( file_exists( $woocoomerce ) ) {
					require_once ABSPATH . 'wp-admin/includes/admin.php';
					require_once ABSPATH . 'wp-admin/includes/upgrade.php';
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
					activate_plugin($woocoomerce);
					wp_send_json(
						[
							'success' => true,
						]
					);
				} else {
					require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
					require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
					require_once(ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php');
					require_once(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
					// Get Plugin Info.
					$api = plugins_api(
						'plugin_information',
						[
							'slug' => 'woocommerce',
							'fields' => [
								'short_description' => false,
								'sections' => false,
								'requires' => false,
								'rating' => false,
								'ratings' => false,
								'downloaded' => false,
								'last_updated' => false,
								'added' => false,
								'tags' => false,
								'compatibility' => false,
								'homepage' => false,
								'donate_link' => false,
							],
						]
					);
					$skin     = new WP_Ajax_Upgrader_Skin();
					$upgrader = new Plugin_Upgrader($skin);
					$upgrader->install($api->download_link);
					activate_plugin($woocoomerce);
					wp_send_json(
						[
							'success' => true,
						]
					);
				}
			}
			die();
		}
	}
	new Stock_Notifier_Popup_Ajax();
}
