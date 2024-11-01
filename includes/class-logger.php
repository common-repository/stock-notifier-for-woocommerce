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
if ( ! class_exists( 'Stock_Notifier_Logger' ) ) {
	/**
	 * Show Seccess and failed message
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Logger {
		/**
		 * Store status.
		 *
		 * @var mixed
		 */
		public $status;
		/**
		 * Store message.
		 *
		 * @var mixed
		 */
		public $message;
		/**
		 * Object Calling method
		 *
		 * @param string $status sms status.
		 * @param string $message message.
		 *
		 * @version 1.0.0
		 */
		public function __construct( $status = '', $message = '' ) {
			$this->status  = $status;
			$this->message = $message;
		}
		/**
		 * Add Stock Notifier context name
		 *
		 * @version 1.0.0
		 */
		private function stock_notifier_context_name() {
			$context_name = [
				'source' => dirname( __FILE__ ),
			];
			return $context_name;
		}
		/**
		 * Formating message
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_format_message() {
			$replace = str_replace( '#', '', $this->message );
			$arr     = explode( ' ', $replace );
			foreach ( $arr as $key => $val ) {
				if ( preg_match( '/^[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4})$/', $val ) ) {
					$arr_phone  = explode( '@', $val );
					$first_data = $arr_phone[0];
					if ( strlen( $first_data ) > 1 ) {
						$first_character  = $first_data[0];
						$last_character   = substr( $first_data, -1, '1' );
						$string_length    = strlen( $first_data );
						$hidden_character = substr( $first_data, 1, $string_length - 2 );
						$hidden           = '';
						$hidden_strln = strlen( $hidden_character );
						if ( $hidden_strln > 0 ) {
							for ( $i = 1; $i <= $hidden_strln; $i++ ) {
								$hidden .= 'x';
							}
						}
						$arr_phone[0] = $first_character . $hidden . $last_character;
					} else {
						$arr_phone[0] = 'xxxxx';
					}
					$val_new   = implode( '@', $arr_phone );
					$arr[ $key ] = $val_new;
				}
			}
			$new_msg = implode( ' ', $arr );
			return $new_msg;
		}
		/**
		 * Stock Notifier message
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_message() {
			return $this->stock_notifier_format_message();
		}
		/**
		 * Stock Notifier logger
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_logger() {
			if ( function_exists( 'wc_get_logger' ) ) {
				return wc_get_logger();
			} else {
				return new WC_Logger();
			}
		}
		/**
		 * Stock Notifier Record log
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_record_log() {
			$logger = $this->stock_notifier_logger();
			$status = $this->status;
			if ( ! function_exists( 'wc_get_logger' ) ) {
				$this->status = '';
			}
			switch ( $this->status ) {
				case 'debug':
					$logger->debug( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'info':
					$logger->info( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'notice':
					$logger->notice( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'warning':
					$logger->warning( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'error':
					$logger->error( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'critical':
					$logger->critical( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'success':
					$logger->log( 'info', $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'alert':
					$logger->alert( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				case 'emergency':
					$logger->emergency( $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					break;
				default:
					if ( function_exists( 'wc_get_logger' ) ) {
						$logger->log( $this->status, $this->stock_notifier_message(), $this->stock_notifier_context_name() );
					} else {
						$logger->add( 'stock_notifier', $this->stock_notifier_message() . ' ' . $status );
					}
					break;
			}
		}
	}
}