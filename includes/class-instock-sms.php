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
if ( ! class_exists( 'Stock_Notifier_Instock_Subscribe_SMS' ) ) {
	/**
	 * Sent In stock SMS
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Instock_Subscribe_SMS {
		/**
		 * Store WP SMS global object.
		 *
		 * @var mixed
		 */
		public $sms;
		/**
		 * Store WP SMS is actve.
		 *
		 * @var int
		 */
		public $wp_sms_active;
		/**
		 * Store subscriber_id.
		 *
		 * @var int
		 */
		public $subscriber_id;
		/**
		 * Store whatsapp_number.
		 *
		 * @var int
		 */
		public $whatsapp_number;
		/**
		 * Store get_wp_subject.
		 *
		 * @var int
		 */
		public $get_wp_subject;
		/**
		 * Store get_wp_message.
		 *
		 * @var int
		 */
		public $get_wp_message;
		/**
		 * Calling method
		 *
		 * @param int $subscriber_id subscriber id.
		 *
		 * @version 1.0.0
		 */
		public function __construct( $subscriber_id ) {
			$this->wp_sms_active = get_option( 'stock_notifier_wp_sms_active', 0 );
			if ( '1' == $this->wp_sms_active ) {
				global $sms;
				$this->sms = $sms;
			}
			$this->subscriber_id    = $subscriber_id;
			$this->whatsapp_number  = get_post_meta( $subscriber_id, 'stock_notifier_subscriber_phone', true );
			do_action( 'stock_notifier_before_instock_sms', $this->whatsapp_number, $this->subscriber_id );
			$get_instock_subject     = get_option( 'stock_notifier_instock_sub_subject' );
			$get_instock_message     = get_option( 'stock_notifier_instock_sub_message' );
			$this->get_wp_subject    = apply_filters( 'stock_notifier_raw_subject', $get_instock_subject, $subscriber_id );
			$this->get_wp_message    = apply_filters( 'stock_notifier_raw_message', nl2br( $get_instock_message ), $subscriber_id );

		}
		/**
		 * Get blog Name.
		 *
		 * @version 1.0.0
		 */
		public function from_name() {
			$from_name = get_bloginfo( 'name' );
			return apply_filters( 'stock_notifier_from_name', $from_name );
		}
		/**
		 * Fromat Message
		 *
		 * @param String $message Instock message.
		 * @version 1.0.0
		 */
		public function stock_notifier_format_data( $message ) {
			$replace = html_entity_decode( $message );
			return $replace;
		}
		/**
		 * Formating Message Subject.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_get_subject() {
			return apply_filters( 'stock_notifier_subject', $this->stock_notifier_format_data( do_shortcode( $this->stock_notifier_replace_shortcode( $this->get_wp_subject ) ) ), $this->subscriber_id );
		}
		/**
		 * Formating Description Message.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_get_message() {
			return apply_filters( 'stock_notifier_message', do_shortcode( $this->stock_notifier_replace_shortcode( $this->get_wp_message ) ), $this->subscriber_id );
		}
		/**
		 * Applay Shortcode in message
		 *
		 * @param string $content message.
		 *
		 * @version 1.0.0
		 */
		private function stock_notifier_replace_shortcode( $content ) {
			$obj                = new Stock_Notifier_API();
			$pid                = get_post_meta( $this->subscriber_id, 'stock_notifier_pid', true );
			$product_name       = $obj->stock_notifier_display_product_name( $this->subscriber_id );
			$only_product_name  = $obj->stock_notifier_display_only_product_name( $this->subscriber_id );
			$product_link       = $obj->stock_notifier_display_product_link( $this->subscriber_id );
			$only_product_sku   = $obj->stock_notifier_get_product_sku( $this->subscriber_id );
			$product_image      = $obj->stock_notifier_get_product_image( $this->subscriber_id );
			$cart_url           = esc_url_raw( add_query_arg( 'add-to-cart', $pid, get_permalink( wc_get_page_id( 'cart' ) ) ) );
			$blogname           = get_bloginfo( 'name' );
			$br                 = nl2br( '\n', false );
			$find_array         = [
				'{product_name}',
				'{product_id}',
				'{product_link}',
				'{shopname}',
				'{whatsapp_number}',
				'{subscriber_number}',
				'{cart_link}',
				'{only_product_name}',
				'{only_product_sku}',
				'{product_image}',
				'{br}',
			];
			$replace_array      = [
				strip_tags( $product_name ),
				$pid,
				$product_link,
				$blogname,
				$this->whatsapp_number,
				$this->whatsapp_number,
				$cart_url,
				$only_product_name,
				$only_product_sku,
				$product_image,
				$br,
			];
			$formatted_content   = str_replace( $find_array, $replace_array, $content );
			$formatted_content   = str_replace( '<br>', '', $formatted_content );
			return apply_filters( 'stock_notifier_instock_replace_shortcode', $formatted_content, $this->subscriber_id );
		}
		/**
		 * Send Instock SMS
		 *
		 * @version 1.0.0
		 */
		public function send_whatsapp_sms() {
			$w_number           = $this->whatsapp_number;
			$subject_sms        = $this->stock_notifier_get_subject();
			$get_message_sms    = $this->stock_notifier_get_message();
			$in_stock_messages  = $subject_sms . ' ' . $get_message_sms;
			$which_api_active   = get_option( 'stock_notifier_select_api_value' );
			if ( '3' == $which_api_active ) {
				$sid            = get_option( 'stock_notifier_twilio_SID' );
				$token          = get_option( 'stock_notifier_twilio_token' );
				$form           = get_option( 'stock_notifier_twilio_senderNumber' );
				$in_stock_urls  = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
				$data = [
					'To'   => "whatsapp:$w_number",
					'From' => "whatsapp:$form",
					'Body' => $in_stock_messages,
				];
				$headers = [
					'Authorization' => 'Basic ' . base64_encode( $sid . ':' . $token ),
				];

				$result = wp_remote_post( $in_stock_urls, [
					'body' => $data,
					'headers' => $headers,
				] );

				do_action( 'stock_notifier_after_instock_sms', $w_number, $this->subscriber_id );
				if ( is_wp_error( $result ) ) {
					do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
					return false;
				} else {
					$response = json_decode( wp_remote_retrieve_body( $result ) );

					if ( is_object( $response ) ) {
						$f_curl = $response->status;

						if ( 'queued' == $f_curl ) {
							do_action( 'stock_notifier_sms_sent_success', $this->subscriber_id );
							return true;
						} else {
							do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
							return false;
						}
					} else {
						do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
						return false;
					}
				}
			} elseif ( '2' == $which_api_active ) {
				$data = [
					'phone' => $w_number,
					'body' => $in_stock_messages,
				];
				$json                       = json_encode( $data );
				// URL for request POST /message.

				$whatsapp_chat_token        = get_option( 'stock_notifier_chatapi_token' );
				$token                      = isset( $whatsapp_chat_token ) && ! empty( $whatsapp_chat_token ) ? $whatsapp_chat_token : '';
				$url                        = 'https://api.chat-api.com/message?token=' . $token;
				// Make a POST request.
				$options = stream_context_create( [
					'http' => [
						'method' => 'POST',
						'header' => 'Content-type: application/json',
						'content' => $json,
					],
				] );

				// Send a request.
				$result        = file_get_contents( $url, false, $options );
				$decode        = json_decode( $result );
				$send_success  = $decode->sent;

				do_action( 'stock_notifier_after_instock_sms', $w_number, $this->subscriber_id );
				if ( $send_success ) {
					do_action( 'stock_notifier_sms_sent_success', $this->subscriber_id );
					return true;
				} else {
					do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
					return false;
				}
			} elseif ( '1' == $which_api_active ) {
				$ulta_instance_id   = get_option( 'stock_notifier_ultramsg_insatnceID' );
				$ultramsg_token   = get_option( 'stock_notifier_ultramsg_token' );

				$ultr_url = "https://api.ultramsg.com/$ulta_instance_id/messages/chat";
				$query = [
					'token'       => $ultramsg_token,
					'to'          => $w_number,
					'body'        => $in_stock_messages,
					'priority'    => 1,
					'referenceId' => 0,
				];

				$responses = wp_remote_post( $ultr_url, [
					'method' => 'POST',
					'timeout' => 30,
					'httpversion' => '1.0',
					'headers' => [
						'content-type: application/x-www-form-urlencoded',
					],
					'body' => $query,
					'cookies' => [],
				] );
				if ( is_wp_error( $responses ) ) {
					do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
					return false;
				} else {
					$decodes = json_decode( wp_remote_retrieve_body( $responses ), true );
					if ( is_array( $decodes ) ) {
						if ( array_key_exists( 'sent', $decodes ) ) {
							if ( 'true' == $decodes['sent'] ) {
								do_action( 'stock_notifier_sms_sent_success', $this->subscriber_id );
								return true;
							}
						} elseif ( array_key_exists( 'error', $decodes ) ) {
							if ( $decodes['error'] ) {
								do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
								return false;
							}
						}
					} else {
						do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
						return false;
					}
				}
			} elseif ( '4' == $which_api_active ) {
				if ( '1' == $this->wp_sms_active ) {
					$this->sms->to  = [ $w_number ];
					$this->sms->msg = $in_stock_messages;
					$response       = $this->sms->SendSMS();
					if ( is_wp_error( $response ) ) {
						do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
						return false;
					} else {
						do_action( 'stock_notifier_sms_sent_success', $this->subscriber_id );
						return true;
					}
				}
			} else if ( '5' == $which_api_active ) {
				$sid           = get_option( 'stock_notifier_twilio_SMS_SID','' );
				$token         = get_option( 'stock_notifier_twilio_SMS_token','' );
				$form          = get_option( 'stock_notifier_twilio_SMS_senderNumber' );

				$in_stock_urls  = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
				$data = [
					'To'   => $w_number,
					'From' => $form,
					'Body' => $in_stock_messages,
				];
				$headers = [
					'Authorization' => 'Basic ' . base64_encode( $sid . ':' . $token ),
				];

				$result = wp_remote_post( $in_stock_urls, [
					'body' => $data,
					'headers' => $headers,
				] );

				do_action( 'stock_notifier_after_instock_sms', $w_number, $this->subscriber_id );
				if ( is_wp_error( $result ) ) {
					do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
					return false;
				} else {
					$response = json_decode( wp_remote_retrieve_body( $result ) );

					if ( is_object( $response ) ) {
						$f_curl = $response->status;

						if ( 'queued' == $f_curl ) {
							do_action( 'stock_notifier_sms_sent_success', $this->subscriber_id );
							return true;
						} else {
							do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
							return false;
						}
					} else {
						do_action( 'stock_notifier_sms_sent_failure', $this->subscriber_id );
						return false;
					}
				}
			}
		}
	}
}
