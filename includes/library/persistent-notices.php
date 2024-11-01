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

if ( ! class_exists( 'Stock_Notifier_Persistent_Notices' ) ) {
	/**
	 * Implements a Standardized messaging system that allows admin messages to be passed across page redirects.
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Persistent_Notices {
		/**
		 * Session Notice
		 *
		 * @var string
		 */
		private $session_var = 'Stock_Notifier_Persistent_Notices';
		/**
		 * Store Notices.
		 *
		 * @var string
		 */
		private $notices;
		/**
		 * Calling method
		 *
		 * @param array $notice notifications.
		 * @version 1.0.0
		 */
		public function __construct( $notice = [] ) {
			$this->notices = $notice;
			if ( is_admin() ) {
				setcookie( $this->session_var, json_encode( $this->notices ), time() + ( 10 * 365 * 24 * 60 * 60 ) );
				$cookie = isset( $_COOKIE ) ? array_map( 'sanitize_text_field', wp_unslash( $_COOKIE ) ) : [];
				if ( isset( $cookie[ $this->session_var ] ) ) {
					$cookie = $cookie[ $this->session_var ];
					$this->show_notices( $cookie);
				}
			}
		}
		/**
		 * Show Notices
		 *
		 * @param string $cookie Cookie.
		 *
		 * @version 1.1.6
		 */
		public function show_notices( $cookie ) {
			if ( '[]' == $cookie ) {
				return;
			} else {
				$json_decode = json_decode($cookie);
				$message     = $json_decode->message;
				$type        = $json_decode->type;
				add_action( 'admin_notices', function () use ( $message, $type ) {
					?>
				<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
					<?php
				});
			}
		}
	}
	/**
	 * Call Notices constructor
	 *
	 * @version 1.0.0
	 */
	new Stock_Notifier_Persistent_Notices();

	if ( ! function_exists( 'stock_notifier_add_persistent_notice' ) ) {
		/**
		 * Add Notices
		 *
		 * @param String $notice notice object.
		 * @version 1.0.0
		 */
		function stock_notifier_add_persistent_notice( $notice ) {
			new Stock_Notifier_Persistent_Notices( $notice );
		}
	}
}