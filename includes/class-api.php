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
if ( ! class_exists( 'Stock_Notifier_API' ) ) {
	/**
	 * Helper class
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_API {
		/**
		 * Store porduct id.
		 *
		 * @var mixed
		 */
		public $product_id;
		/**
		 * Store porduct id.
		 *
		 * @var mixed
		 */
		public $variation_id;
		/**
		 * Store Phone.
		 *
		 * @var mixed
		 */
		public $subscriber_phone;
		/**
		 * Store user id.
		 *
		 * @var mixed
		 */
		public $user_id;
		/**
		 * Store author_id id.
		 *
		 * @var mixed
		 */
		public $author_id;
		/**
		 * Store language id.
		 *
		 * @var mixed
		 */
		public $language;
		/**
		 * Object calling method
		 *
		 * @param int    $product_id deafult value 0.
		 * @param int    $variation_id deafult value 0.
		 * @param String $user_phone deafult value empty.
		 * @param int    $user_id  deafult value 0.
		 * @param int    $author_id deafult value 0.
		 * @param String $language deafult value en_US.
		 * @version 1.0.0
		 */
		public function __construct( $product_id = 0, $variation_id = 0, $user_phone = '', $user_id = 0, $author_id = 0, $language = 'en_US' ) {
			$this->product_id       = $product_id;
			$this->variation_id     = $variation_id;
			$this->subscriber_phone = $user_phone;
			$this->user_id          = $user_id;
			$this->author_id        = $author_id;
			$this->language         = $language;
		}
		/**
		 * Use for Post data validation
		 *
		 * @param array $post all subscribers post.
		 * @version 1.0.0
		 */
		public function stock_notifier_post_data_validation( $post ) {
			$post_data = [];
			if ( is_array( $post ) && ! empty( $post ) ) {
				foreach ( $post as $key => $value ) {

					if ( is_array( $value ) && ! empty( $value ) ) {
						foreach ( $value as $newkey => $newvalue ) {
							$post_data[ $key ][ $newkey ] = $this->stock_notifier_format_field( $newkey, $newvalue );
						}
					} else {
						$post_data[ $key ] = $this->stock_notifier_format_field( $key, $value );
					}
				}
			}
			return $post_data;
		}
		/**
		 * Update Subscriber meta value
		 *
		 * @param int $id subscriber post id.
		 * @version 1.0.0
		 */
		public function stock_notifier_insert_data( $id ) {
			$default_data = [
				'stock_notifier_product_id'            => $this->product_id,
				'stock_notifier_variation_id'          => $this->variation_id,
				'stock_notifier_subscriber_phone'      => $this->subscriber_phone,
				'stock_notifier_product_upload_author' => $this->author_id,
				'stock_notifier_user_id'               => $this->user_id,
				'stock_notifier_language'              => $this->language,
				'stock_notifier_pid'                   => $this->variation_id > '0' || $this->variation_id > 0 ? $this->variation_id : $this->product_id,
			];
			foreach ( $default_data as $key => $value ) {
				update_post_meta( $id, $key, $value );
			}
		}
		/**
		 * Check Subscriber number already exists
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_is_already_subscribed() {
			$args = [
				'post_type'      => 'stock_notifier',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'iwg_subscribed',
			];
			$meta_query = [
				'relation' => 'AND',
				[
					'key'   => 'stock_notifier_pid',
					'value' => $this->variation_id > '0' || $this->variation_id > 0 ? $this->variation_id : $this->product_id,
				],
				[
					'key'   => 'stock_notifier_subscriber_phone',
					'value' => $this->subscriber_phone,
				],
			];
			$args['meta_query'] = $meta_query;
			$get_posts          = get_posts( $args );
			return $get_posts;
		}
		/**
		 * Insert new subscriber
		 *
		 * @version 1.0.0
		 *
		 * @return boolean value
		 */
		public function stock_notifier_insert_subscriber() {
			$args = [
				'post_title'  => $this->subscriber_phone,
				'post_type'   => 'stock_notifier',
				'post_status' => 'iwg_subscribed',
			];
			$id = wp_insert_post( $args );
			if ( ! is_wp_error( $id ) ) {
				return $id;
			} else {
				return false;
			}
		}
		/**
		 * Check how many subscribers are there single product.
		 *
		 * @param int    $product_id pass product id and how many subscriber are there in this product.
		 * @param String $status get value by status.
		 * @return boolean subcriber count
		 */
		public function stock_notifier_get_subscribers_count( $product_id, $status = 'any' ) {
			$args = [
				'post_type'   => 'stock_notifier',
				'post_status' => $status,
				'meta_query' => [
					[
						'key'   => 'stock_notifier_product_id',
						'value' => [
							$product_id,
						],
						'compare' => 'IN',
					],
				],
				'numberposts' => - 1,
			];
			$query = get_posts( $args );
			return count( $query );
		}
		/**
		 * Sanitize array value.
		 *
		 * @param String $key subscriber key value.
		 *
		 * @param int    $value subscriber value.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_format_field( $key, $value ) {
			$list_of_fields = [
				'product_id'    => intval( sanitize_text_field( $value ) ),
				'variation_id'  => intval( sanitize_text_field( $value ) ),
				'user_id'       => intval( sanitize_text_field( $value ) ),
				'user_phone'    => $this->sanitize_text_field( $value ),
			];
			if ( isset( $list_of_fields[ $key ] ) ) {
				return $list_of_fields[ $key ];
			} else {
				return sanitize_text_field( $value );
			}
		}
		/**
		 * Display variation product name in subscriber list.
		 *
		 * @param int $id single post id.
		 *
		 * @return string
		 */
		public function stock_notifier_display_product_name( $id ) {
			$variation_id = get_post_meta( $id, 'stock_notifier_variation_id', true );
			$product_id = get_post_meta( $id, 'stock_notifier_product_id', true );
			if ( $product_id ) {
				$val = intval( $variation_id );
				if ( $val > 0 ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation ) {
						$formatted_name = $variation->get_name() . '(#' . $product_id . ')';
						return $formatted_name;
					}
				} else {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						return $product->get_formatted_name();
					}
				}
				return false;
			}
		}
		/**
		 * Display product link in subscriber list.
		 *
		 * @param int $id single post id.
		 *
		 * @return string
		 */
		public function stock_notifier_display_product_link( $id ) {
			$variation_id = get_post_meta( $id, 'stock_notifier_variation_id', true );
			$product_id = get_post_meta( $id, 'stock_notifier_product_id', true );
			if ( $product_id ) {
				$val = intval( $variation_id );
				if ( $val > 0 ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation ) {
						$link = $variation->get_permalink();
						return $link;
					}
				} else {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						return $product->get_permalink();
					}
				}
			}
			return '';
		}
		/**
		 * Display simple product name in subscriber list.
		 *
		 * @param int $id single post id.
		 *
		 * @return string
		 */
		public function stock_notifier_display_only_product_name( $id ) {
			$variation_id = get_post_meta( $id, 'stock_notifier_variation_id', true );
			$product_id = get_post_meta( $id, 'stock_notifier_product_id', true );
			if ( $product_id ) {
				$val = intval( $variation_id );
				if ( $val > 0 ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation ) {
						$formatted_name = $variation->get_name();
						return $formatted_name;
					}
				} else {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						return $product->get_name();
					}
				}
				return false;
			}
		}
		/**
		 * Get product sku.
		 *
		 * @param int $id single post id.
		 *
		 * @return string
		 */
		public function stock_notifier_get_product_sku( $id ) {
			$variation_id = get_post_meta( $id, 'stock_notifier_variation_id', true );
			$product_id = get_post_meta( $id, 'stock_notifier_product_id', true );
			if ( $product_id ) {
				$val = intval( $variation_id );
				if ( $val > 0 ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation ) {
						$formatted_name = $variation->get_sku();
						return $formatted_name;
					}
				} else {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						return $product->get_sku();
					}
				}
				return false;
			}
		}
		/**
		 * Get product image by id for show subscribers list now it is not used in stock notifier for woocommerce.
		 *
		 * @param int    $id single post id.
		 * @param String $size image size default value woocommerce_thumbnail.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_get_product_image( $id, $size = 'woocommerce_thumbnail' ) {
			$variation_id = get_post_meta( $id, 'stock_notifier_variation_id', true );
			$product_id = get_post_meta( $id, 'stock_notifier_product_id', true );
			if ( $product_id ) {
				$val = intval( $variation_id );
				if ( $val > 0 ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation ) {
						return $variation->get_image( $size );
					}
				} else {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						return $product->get_image( $size );
					}
				}
				return false;
			}
		}
		/**
		 * Update SMS sent status
		 *
		 * @param int $subscribe_id single post id.
		 */
		public function stock_notifier_sms_sent_status( $subscribe_id ) {
			$args = [
				'ID'          => $subscribe_id,
				'post_type'   => 'stock_notifier',
				'post_status' => 'iwg_smssent',
			];
			$id = wp_update_post( $args );
			return $id;
		}
		/**
		 * Update SMS sent status in mysql database
		 *
		 * @param int $subscribe_id single post id.
		 */
		public function stock_notifier_sms_sent_status_db( $subscribe_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'stock_notifier_popular_product';
			$wpdb->query( $wpdb->prepare( "UPDATE `$table_name` SET status='Sent' WHERE post_id=%d", $subscribe_id ) );
		}
		/**
		 * Update SMS Failed status in mysql database
		 *
		 * @param int $subscribe_id single post id.
		 */
		public function sms_failed_status_update( $subscribe_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'stock_notifier_popular_product';
			$wpdb->query( $wpdb->prepare( "UPDATE `$table_name` SET status='Failed' WHERE post_id=%d", $subscribe_id ) );
		}
		/**
		 * Update SMS not sent status in subscriber list
		 *
		 * @param int $subscribe_id single post id.
		 */
		public function stock_notifier_sms_not_sent_status( $subscribe_id ) {
			$args = [
				'ID'          => $subscribe_id,
				'post_type'   => 'stock_notifier',
				'post_status' => 'iwg_smsnotsent',
			];
			$id = wp_update_post( $args );
			return $id;
		}
		/**
		 * Update subscribe status in subscriber list useing by bulk action
		 *
		 * @param int $subscribe_id single post id.
		 */
		public function stock_notifier_subscriber_subscribed( $subscribe_id ) {
			$args = [
				'ID'          => $subscribe_id,
				'post_type'   => 'stock_notifier',
				'post_status' => 'iwg_subscribed',
			];
			$id = wp_update_post( $args );
			return $id;
		}
		/**
		 * Update unsubscribed status in subscriber list useing by bulk action
		 *
		 * @param int $subscribe_id single post id.
		 */
		public function stock_notifier_subscriber_unsubscribed( $subscribe_id ) {
			$args = [
				'ID'          => $subscribe_id,
				'post_type'   => 'stock_notifier',
				'post_status' => 'iwg_unsubscribed',
			];
			$id = wp_update_post( $args );
			return $id;
		}
		/**
		 * Get all subscribers->status subscriber.
		 */
		public function stock_notifier_get_list_of_subscribers() {
			$args = [
				'post_type'      => 'stock_notifier',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'iwg_subscribed',
			];
			$meta_query = [
				'realtion' => 'AND',
				[
					'relation' => 'OR',
					[
						'key'   => 'stock_notifier_product_id',
						'value' => ( $this->product_id > '0' || $this->product_id ) ? $this->product_id : 'no_data_found',
					],
					[
						'key'   => 'stock_notifier_variation_id',
						'value' => ( $this->variation_id > '0' || $this->variation_id > 0 ) ? $this->variation_id : 'no_data_found',
					],
				],

			];

			$args['meta_query'] = apply_filters( 'stock_notifier_instock_metaquery', $meta_query );
			$get_posts = get_posts( $args );
			return $get_posts;
		}
		/**
		 * Sanitize_text_field
		 *
		 * @param String $value string.
		 */
		public function sanitize_text_field( $value ) {
			return sanitize_text_field( $value );
		}

		/**
		 * Export all subscribers
		 *
		 * @param array  $array all subscriber.
		 * @param String $filename default name subscribers csv.
		 * @param String $delimiter default value :.
		 */
		public function stock_notifier_csv_download( $array, $filename = 'subscribers.csv', $delimiter = ':' ) {
			$f = fopen( $filename, 'w' );
			foreach ( $array as $line ) {
				fputcsv( $f, $line, $delimiter );
			}
			fseek( $f, 0 );
			fpassthru( $f );
		}
		/**
		 * Delete Subscriber in subscriber list
		 *
		 * @param int $id single post id.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_delete_subscribe( $id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'posts';
			return $wpdb->delete( $table_name, [ 'ID' => sanitize_key( $id ) ], [ '%d' ] );

		}

		/**
		 * Total calculation subscriber
		 *
		 * @param int $total_subscribers all subscribers.
		 */
		public function stock_notifier_total_subscriber_cal( $total_subscribers ) {
			if ( $total_subscribers >= 0 && $total_subscribers < 1000 ) {
				return $total_subscribers;
			} elseif ( $total_subscribers >= 1000 && $total_subscribers < 1000000 ) {
				$number = $total_subscribers / 1000;
				if ( is_float( $number ) ) {
					$value = number_format( (float) $number, 3, '.', '' );
					return $value . 'K';
				} else {
					return $number . 'K';
				}
			} elseif ( $total_subscribers >= 1000000 && $total_subscribers < 1000000000 ) {
				$number = $total_subscribers / 1000000;
				if ( is_float( $number ) ) {
					$value = number_format( (float) $number, 3, '.', '' );
					return $value . 'M';
				} else {
					return $number . 'M';
				}
			} elseif ( $total_subscribers >= 1000000000 ) {
				$number = $total_subscribers / 1000000000;
				if ( is_float( $number ) ) {
					$value = number_format( (float) $number, 3, '.', '' );
					return $value . 'Bil';
				} else {
					return $number . 'Bil';
				}
			}
		}

		/**
		 * Format Time
		 *
		 * @param string $time_stamp .
		 *
		 * @return string
		 */
		public function format_time_string( $time_stamp ) {
			$str_time   = $time_stamp;
			$time       = strtotime( $str_time );
			$d          = new DateTime( $str_time );

			$week_days = [ 'Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun' ];
			$months   = [ 'Jan', 'Feb', 'Mar', 'Apr', ' May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec' ];

			if ( $time > strtotime( '-2 minutes' ) ) {
				return 'Just now';
			} elseif ( $time > strtotime( '-59 minutes' ) ) {
				$min_diff = floor( ( strtotime( 'now' ) - $time ) / 60 );
				return $min_diff . ' minute' . ( ( 1 != $min_diff ) ? 's' : '' ) . ' ago';
			} elseif ( $time > strtotime( '-23 hours' ) ) {
				$hour_diff = floor( ( strtotime( 'now' ) - $time ) / ( 60 * 60 ) );
				return $hour_diff . ' hour' . ( ( 1 != $hour_diff ) ? 's' : '' ) . ' ago';
			} elseif ( $time > strtotime( 'today' ) ) {
				return $d->format( 'G:i' );
			} elseif ( $time > strtotime( 'yesterday' ) ) {
				return 'Yesterday at ' . $d->format( 'G:i' );
			} elseif ( $time > strtotime( 'this week' ) ) {
				return $week_days[ $d->format( 'N' ) - 1 ] . ' at ' . $d->format( 'G:i' );
			} else {
				return $d->format( 'j' ) . ' ' . $months[ $d->format( 'n' ) - 1 ] . ( ( $d->format( 'Y' ) != gmdate( 'Y' ) ) ? $d->format( 'Y' ) : '' ) . ' at ' . $d->format( 'G:i' );
			}
		}

		/**
		 * Compare subscriber % prev month and current month
		 *
		 * @param mixed $old_number prv month total subscriber.
		 * @param mixed $new_number current month total subscriber.
		 */
		public function get_percentage_change( $old_number, $new_number ) {
			$percentage = '0';
			if ( $old_number > 0 ) {
				$decrease_value = $new_number - $old_number;
				return ( $decrease_value / $old_number ) * 100;
			} else {
				return $percentage;
			}
		}
		/**
		 * Sent Manual Whatsapp SMS.
		 *
		 * @param int $post_id single post id.
		 * @param int $dokan SMS sent request in dokan vendor subscriber list.
		 */
		public function stock_notifier_manual_whatsapp_sms( $post_id, $dokan = 0 ) {
			$get_number                  = get_post_meta( $post_id, 'stock_notifier_subscriber_phone', true );
			$whatsapp_active_or_inactive = get_option( 'stock_notifier_whatsapp_toggle' );
			$whatsapp                    = new Stock_Notifier_Instock_Subscribe_SMS( $post_id );
			$pid                         = get_post_meta( $post_id, 'stock_notifier_pid', true );
			$product_exists              = wc_get_product( $pid );

			if ( $product_exists ) {
				if ( '1' == $whatsapp_active_or_inactive ) {
					$send_sms = $whatsapp->send_whatsapp_sms();
					if ( $send_sms ) {
						$message    = __( 'In stock SMS sent to {whatsapp_number} successfully', 'stock-notifier' );
						$replace    = str_replace( '{whatsapp_number}', $get_number, $message );
						$sms_status = $this->stock_notifier_sms_sent_status( $post_id );
						$logger     = new Stock_Notifier_Logger( 'success', "Manual Instock SMS sent to #$get_number - #$post_id" );
						$logger->stock_notifier_record_log();
						stock_notifier_add_persistent_notice( [
							'type' => 'success',
							'message' => $replace,
						] );
					} else {
						$error_msg     = __( 'Unable to send In stock SMS to this {whatsapp_number}', 'stock-notifier' );
						$error_replace = str_replace( '{whatsapp_number}', $get_number, $error_msg );
						$sms_status    = $this->stock_notifier_sms_not_sent_status( $post_id );
						$logger        = new Stock_Notifier_Logger( 'error', $error_replace . " #$post_id" );
						$logger->stock_notifier_record_log();
						stock_notifier_add_persistent_notice( [
							'type'    => 'warning',
							'message' => $error_replace,
						] );
					}
				} else {
					$error_msg     = __( 'Unable to send In stock SMS to this {whatsapp_number}', 'stock-notifier' );
					$error_replace = str_replace( '{whatsapp_number}', $get_number, $error_msg );
					$sms_status    = $this->stock_notifier_sms_not_sent_status( $post_id );
					$logger        = new Stock_Notifier_Logger( 'error', $error_replace . " #$post_id" );
					$logger->stock_notifier_record_log();
					stock_notifier_add_persistent_notice( [
						'type' => 'warning',
						'message' => $error_replace,
					] );
				}
			} else {
				$error_msg     = __( 'Unable to send In stock SMS to this {whatsapp_number} as stock product does not exists/deleted !!!', 'stock-notifier' );
				$error_replace = str_replace( '{whatsapp_number}', $get_number, $error_msg );
				$logger        = new Stock_Notifier_Logger( 'error', $error_replace . " #$post_id" );
				$logger->stock_notifier_record_log();
				stock_notifier_add_persistent_notice( [
					'type'    => 'warning',
					'message' => $error_replace,
				] );
			}
			if ( '1' == $dokan ) {
				$http_server = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']));
				wp_safe_redirect($http_server);
				exit;
			}
		}
		/**
		 * Display Product Author Name in Subscriber list
		 *
		 * @param int $product_upload_author_id author id.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_display_product_author_name( $product_upload_author_id ) {
			$wp_user = new WP_User( $product_upload_author_id );
			return $wp_user->user_nicename;
		}
		/**
		 * Insert new subscriber in stock notifier mysql table.
		 *
		 * @param int    $id post id.
		 * @param String $product_name product name.
		 * @param String $shop_name shop name.
		 */
		public function stock_notifier_insert_data_sql( $id, $product_name, $shop_name ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'stock_notifier_popular_product';
			$wpdb->insert( $table_name, [
				'post_id'      => $id,
				'product_id'   => $this->product_id,
				'author_id'    => $this->author_id,
				'product_name' => $product_name,
				'phone_number' => $this->subscriber_phone,
				'shop_name'    => $shop_name,
				'status'       => 'Subscribed',
			] );

				update_option( 'stock_notifier_subscriber_time', gmdate( 'Y-m-d H:i:sP', time() ) );
				update_option( "stock_notifier_subscriber_time_$this->author_id", gmdate( 'Y-m-d H:i:sP', time() ) );

		}
		/**
		 * Display Status in subscriber list status columns
		 *
		 * @param String $get_post_status post staus.
		 */
		public function stock_notifier_display_status( $get_post_status ) {
			switch ( $get_post_status ) {
				case 'iwg_subscribed':
					$subscribed = __( 'Subscribed', 'stock-notifier' );
					printf( "<mark class='iwgmark iwgsubscribed'>%s</mark>", wp_kses_post($subscribed) );
					break;
				case 'iwg_smssent':
					$smssent = __('SMS Sent', 'stock-notifier' );
					printf( "<mark class='iwgmark iwgsmssent'>%s</mark>", wp_kses_post($smssent) );
					break;
				case 'iwg_unsubscribed':
					$unsubscribed = __( 'Unsubscribed', 'stock-notifier' );
					printf( "<mark class='iwgmark iwgunsubscribed'>%s</mark>", wp_kses_post($unsubscribed) );
					break;
				case 'iwg_converted':
					$converted = __( 'Purchased', 'stock-notifier' );
					printf( "<mark class='iwgmark iwgpurchased'>%s</mark>", wp_kses_post($converted) );
					break;
				case 'iwg_smsnotsent':
					$notsent = __( 'Failed', 'stock-notifier' );
					printf( "<mark class='iwgmark iwgfailed'>%s</mark>", wp_kses_post($notsent) );
					break;
				default:
					$otherstatus = $get_post_status;
					printf( "<mark class='iwgmark'>%s</mark>", wp_kses_post($otherstatus) );
					break;
			}
		}
		/**
		 * Sanitize all array value
		 *
		 * @param array $array .
		 *
		 * @return mixed
		 */
		public function _recursive_sanitize_text_field( $array ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = $this->_recursive_sanitize_text_field( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
			}
			return $array;
		}
	}
}
