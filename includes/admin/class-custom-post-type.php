<?php
/**
 * Declaring Custom Post Types for show subscribers list
 *
 * @version 1.0.0
 * @package STOCKNOTIFIER/Free
 */

// if direct access than exit the file.
defined( 'ABSPATH' ) || exit;
/**
 * Check class is already exists
 */
if ( ! class_exists( 'Stock_Notifier_Post_Type' ) ) {
	/**
	 * Stock Notifie custom post type class
	 * Use Register Custom post type and show subscribers list
	 *
	 * @version 1.0.0
	 */
	class Stock_Notifier_Post_Type {
		/**
		 * Calling method
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'register_custom_post_type' ] );
			add_action( 'init', [ $this, 'register_post_status' ] );
			add_action( 'init', [ $this, 'subscriber_popularity_count' ] );
			add_filter( 'manage_stock_notifier_posts_columns', [ $this, 'add_columns' ] );
			add_action( 'manage_stock_notifier_posts_custom_column', [ $this, 'manage_columns' ], 10, 2 );

			add_filter( 'list_table_primary_column', [ $this, 'list_table_primary_column' ], 10, 2 );
			add_filter( 'manage_edit-stock_notifier_sortable_columns', [ $this, 'sortable_columns' ] );
			add_filter( 'post_row_actions', [ $this, 'manage_row_actions' ], 10, 2 );
			add_action( 'admin_action_stock_notifier-whatsapp', [ $this, 'send_manual_whatsapp_sms' ] );
			// Bulk action unset edit.
			add_filter( 'bulk_actions-edit-stock_notifier', [ $this, 'remove_from_bulk_actions' ] );
			add_filter( 'handle_bulk_actions-edit-stock_notifier', [ $this, 'handle_bulk_actions' ], 10, 3 );
			// Mark status to sms sent.
			add_action( 'stock_notifier_handle_action_mark_status_sent', [ $this, 'bulk_mark_status_sent' ] );
			// Mark status to subscribed.
			add_action( 'stock_notifier_handle_action_mark_status_subscribed', [ $this, 'bulk_mark_status_subscribed' ] );
			// Mark status to unsubscribed.
			add_action( 'stock_notifier_handle_action_mark_status_unsubscribed', [ $this, 'bulk_mark_status_unsubscribed' ] );
			// Send SMS in bulk.
			add_action( 'stock_notifier_handle_action_send_sms', [ $this, 'bulk_send_manual_sms' ] );
			// Add filter option in custom post type.
			add_action( 'restrict_manage_posts', [ $this, 'filter_by_subscribed_products' ] );
			add_filter( 'parse_query', [ $this, 'parse_query' ] );
			add_action( 'pre_get_posts', [ $this, 'sort_total_subscribers' ], 999 );
			add_filter( 'add_menu_classes', [ $this, 'set_transient' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'delete_transient' ] );
			add_action( 'admin_head-edit.php', [ $this, 'export_button' ] );
		}
		/**
		 * Export Button for export subscribers
		 *
		 * @version 1.0.0
		 */
		public function export_button() {
			$current_screen = get_current_screen();
			if ( 'stock_notifier' != $current_screen->post_type ) {
				return;
			}
			if ( ! current_user_can( 'publish_posts' ) ) {
				return;
			}
			?>
			<style>
				.wp-core-ui select{
				border: 3px solid #9eadba !important;
				line-height: 30px !important;
				}
				.select2-selection{
				border: 3px solid #9eadba !important;
				}
				#doaction, #doaction2, #stock_notifier_export, .search-box input{
				margin: 0 8px 0 0;
				line-height: 30px !important;
				font-size: 15px !important; ;
				border: 3px solid #9eadba !important; 
				}
				#stock_notifier_exports_free{
				margin: 0 8px 0 0;
				line-height: 30px !important;
				font-size: 15px !important; ;
				border: 3px solid #9eadba !important; 
				opacity: 0.5 !important;
				}
				#post-query-submit {
				margin: 0 8px 0 0;
				line-height: 34px !important;
				font-size: 15px !important; ;
				border: 3px solid #9eadba !important;
				}
				.wc-wp-version-gte-53 .select2-container.select2-container--open .select2-selection--multiple{
				box-shadow: 0 0 0 1px #9eadba !important;
				}
				.select2-container--default.select2-container--focus .select2-selection--multiple {
					border: 3px solid #9eadba !important;
					height: 35px !important;
				}
			</style>
			<?php
			do_action( 'stock_notifier_export_button' );
			$ultimate_role = apply_filters( 'stock_notifier_extra_cap', false );
			if ( ! $ultimate_role ) {
				do_action( 'stock_notifier_popup' );
				?>
				<script type="text/javascript">
					jQuery(document).ready( function($)
					{
						jQuery('hr.wp-header-end').before('<a id="stock_notifier_exports_free" style="font-size:14px !important" class="page-title-action"><?php esc_html_e( 'Export', 'stock-notifier' ); ?></a>');
					});
				</script>
				<?php
			}
		}
		/**
		 * Total Subscriber popularity count
		 *
		 * @version 1.0.0
		 *
		 * @return void
		 */
		public function subscriber_popularity_count() {
			$post_data  = get_posts( [
				'posts_per_page' => -1,
				'post_type' => 'stock_notifier',
				'post_status' => 'iwg_subscribed, iwg_smssent, iwg_smsnotsent',
			] );

			foreach ( $post_data as $posts ) {
				$stock_notifier_product_id       = get_post_meta( $posts->ID, 'stock_notifier_product_id', true );
				$stock_notifier_popular_product  = get_option( "stock_notifier_popular_product_$stock_notifier_product_id" );
				update_post_meta( $posts->ID, 'stock_notifier_subscriber_count', $stock_notifier_popular_product);
			}

		}
		/**
		 * Register custom post type for stock notifier
		 *
		 * @return void
		 *
		 * @version 1.0.0
		 */
		public function register_custom_post_type() {
			$labels = [
				'name'               => _x( 'Subscribers', ' Subscribers', 'stock-notifier' ),
				'singular_name'      => _x( ' Subscribers', ' Subscribers', 'stock-notifier' ),
				'menu_name'          => _x( 'Stock Notifier ', 'Stock Notifier', 'stock-notifier' ),
				'name_admin_bar'     => _x( 'Stock Notifier', 'Name in Admin Bar', 'stock-notifier' ),
				'add_new'            => _x( 'Add New Subscriber', 'add new in menu', 'stock-notifier' ),
				'add_new_item'       => __( 'Add New Subscriber', 'stock-notifier' ),
				'edit_item'          => __( 'Edit Subscriber', 'stock-notifier' ),
				'view_item'          => __( 'View Subscriber', 'stock-notifier' ),
				'all_items'          => __( ' Notifications', 'stock-notifier' ),
				'search_items'       => __( 'Search', 'stock-notifier' ),
				'parent_item_colon'  => __( 'Parent:', 'stock-notifier' ),
				'not_found'          => __( 'No Subscriber Found', 'stock-notifier' ),
				'not_found_in_trash' => __( 'No Subscriber found in Trash', 'stock-notifier' ),
			];

			$args = [
				'labels'          => $labels,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'menu_icon'       => STOCKNOTIFIER_URL . 'assets/img/svg.svg',
				'capability_type' => 'post',
				'capabilities'    => [
					'create_posts' => 'do_not_allow',
				],
				'map_meta_cap'    => true,
			];

			do_action( 'stock_notifier_register_post_type' );
			register_post_type( 'stock_notifier', $args );

			flush_rewrite_rules();
		}
		/**
		 * Register Status for stock notifier
		 *
		 * @version 1.0.0
		 */
		public function register_post_status() {
			register_post_status( 'iwg_smssent', [
				'label'                     => _x( 'SMS Sent', 'post', 'stock-notifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( "SMS Sent <span class='count'>(%s)</span>", "SMS Sent <span class='count'>(%s)</span>" ),//phpcs:ignore
			] );

			register_post_status( 'iwg_smsnotsent', [
				'label'                     => _x( 'Failed', 'post', 'stock-notifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( "Failed <span class='count'>(%s)</span>", "Failed <span class='count'>(%s)</span>" ),//phpcs:ignore
			] );

			register_post_status( 'iwg_subscribed', [
				'label'                     => _x( 'Subscribed', 'post', 'stock-notifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( "Subscribed <span class='count'>(%s)</span>", "Subscribed <span class='count'>(%s)</span>" ),//phpcs:ignore
			] );

			register_post_status( 'iwg_unsubscribed', [
				'label'                     => _x( 'Unsubscribed', 'post', 'stock-notifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( "Unsubscribed <span class='count'>(%s)</span>", "Unsubscribed <span class='count'>(%s)</span>" ),//phpcs:ignore
			] );

			register_post_status( 'iwg_converted', [
				'label'                     => _x( 'Purchased', 'post', 'stock-notifier' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( "Purchased <span class='count'>(%s)</span>", "Purchased <span class='count'>(%s)</span>" ),//phpcs:ignore
			] );
		}
		/**
		 * Add columns for show subscribers list
		 *
		 * @param array $columns register columns for subscribers list.
		 */
		public function add_columns( $columns ) {
			$newcolumns['cb']               = $columns['cb'];
			$newcolumns['whatsapp']         = __( 'WhatsApp Number', 'stock-notifier' );
			$newcolumns['status']           = __( 'Status', 'stock-notifier' );
			$newcolumns['product']          = __( 'Product', 'stock-notifier' );
			$newcolumns['author_id']        = __( 'Shop Name', 'stock-notifier' );
			$newcolumns['popular_product']  = __( 'Subscriber Count', 'stock-notifier' );
			$newcolumns['date']             = __( 'Subscribed on', 'stock-notifier' );

			return apply_filters( 'stock_notifier_add_new_columns', $newcolumns );
		}
		/**
		 * Manage columns use for show subscribers data
		 *
		 * @param array $column return all columns.
		 * @param int   $post_id return post ids.
		 * @version 1.0.0
		 */
		public function manage_columns( $column, $post_id ) {
			return $this->manage_columnss( $column, $post_id );
		}
		/**
		 * Manage columns use for show subscribers data
		 *
		 * @param array $columns return all columns.
		 * @param int   $post_id return post ids.
		 * @version 1.0.0
		 */
		protected function manage_columnss( $columns, $post_id ) {
			$whatsapp_number                      = get_post_meta( $post_id, 'stock_notifier_subscriber_phone', true );
			$product_upload_author_id             = get_post_meta( $post_id, 'stock_notifier_product_upload_author', true );
			$product_id                           = get_post_meta( $post_id, 'stock_notifier_product_id', true );
			$stock_notifier_popular_products_meta = get_post_meta( $post_id, 'stock_notifier_subscriber_count', true );
			$obj                                  = new Stock_Notifier_API( 0, 0, $whatsapp_number );

			switch ( $columns ) {
				case 'whatsapp':
					echo esc_html( $whatsapp_number );
					break;
				case 'status':
					$this->stock_notifier_display_status( $post_id );
					break;
				case 'product':
					$obj          = new Stock_Notifier_API();
					$product_name = $obj->stock_notifier_display_product_name( $post_id );
					$product_id   = get_post_meta( $post_id, 'stock_notifier_product_id', true );
					$variation_id = get_post_meta( $post_id, 'stock_notifier_variation_id', true );
					$pid          = get_post_meta( $post_id, 'stock_notifier_pid', true );
					$intvariation = intval( $variation_id );

					if ( $intvariation > 0 ) {
						$var_obj = wc_get_product( $intvariation );
						// $image = $var_obj->get_image(array(40, 40));
						$pid = $product_id;
					} else {
						$product_obj = wc_get_product( $product_id );
					}
					if ( $product_id ) {
						$permalink = esc_url_raw( admin_url( "post.php?post=$product_id&action=edit" ) );
						printf( "<a href='%s'>#%s %s</a>", esc_url( $permalink ), esc_html($pid), esc_html($product_name) );
					}
					break;
				case 'author_id':
					$obj            = new Stock_Notifier_API();
					$author_name    = $obj->stock_notifier_display_product_author_name( $product_upload_author_id );
					echo esc_html( $author_name );
					break;
				case 'popular_product':
					$permalink_two = esc_url_raw( admin_url( "post.php?post=$product_id&action=edit" ) );
					printf( "<a href='%s'>%s</a>", esc_url($permalink_two), esc_html($stock_notifier_popular_products_meta) );
					break;
				case 'date':
					echo esc_html( gmdate( 'y-m-d h:i:s' ) );
					break;
			}
		}
		/**
		 * Edit default bulk action and add stock notifier bulk action
		 *
		 * @param array $actions return default action.
		 *
		 * @return array push stock Notifier action
		 * @version 1.0.0
		 */
		public function remove_from_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			$newactions      = [];
			$list_of_actions = [
				'mark_status_sent'         => __( 'Change status to SMS Sent', 'stock-notifier' ),
				'mark_status_subscribed'   => __( 'Change status to Subscribed', 'stock-notifier' ),
				'mark_status_unsubscribed' => __( 'Change status to Unsubscribed', 'stock-notifier' ),
				'send_sms'                 => __( 'Send SMS', 'stock-notifier' ),
			];
			foreach ( $list_of_actions as $key => $each_action ) {
				$newactions[ $key ] = $each_action;
			}
			$merge_actions = array_merge( $newactions, $actions );
			return apply_filters( 'stock_notifier_bulk_actions', $merge_actions );
		}
		/**
		 * Handle stock notifier subscriber bulk actions.
		 *
		 * @param String $redirect_to  redireact url.
		 * @param array  $action action button.
		 * @param int    $post_ids return post ids.
		 *
		 * @return array
		 * @version 1.0.0
		 */
		public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
			do_action( 'stock_notifier_handle_action_' . $action, $post_ids );
			return $redirect_to;
		}
		/**
		 * Display subscriber status in Subscriber list view
		 *
		 * @param int $id single post ids.
		 * @return void
		 * @version 1.0.0
		 */
		public function stock_notifier_display_status( $id ) {
			$get_post_status     = get_post_status( $id );
			$stock_notifier_api  = new Stock_Notifier_API();
			$stock_notifier_api->stock_notifier_display_status( $get_post_status );
		}
		/**
		 * Mange Subscriber list row action
		 * Example: trush button and send image button
		 *
		 * @param array  $actions sigle subnscriber whatsApp number action.
		 * @param object $post subscriber.
		 */
		public function manage_row_actions( $actions, $post ) {
			$post_status = get_post_status( $post->ID );
			$newactions = [];
			$post_id    = intval( $post->ID );
			if ( 'stock_notifier' == $post->post_type && 'trash' != $post_status ) {
				$newactions['id'] = "<span class='id' style='color:#a0a0a0;'>" . __( 'ID:', 'stock-notifier' ) . $post_id . '</span>';
				$edit_list = admin_url( 'edit.php?post_type=stock_notifier' );
				$action    = 'stock_notifier-whatsapp';
				$nonce     = wp_create_nonce( 'stock_notifier-whatsapp-' . $post_id );

				$query_arg = esc_url_raw( add_query_arg( [
					'action'  => $action,
					'post_id' => $post_id,
					'nonce'   => $nonce,
				], $edit_list ) );
				$caption    = sprintf( '<img style="width:30px;height:20px;margin-bottom:-3px" src="%s" alt="%s" />', STOCKNOTIFIER_URL . 'assets/img/automate.svg', 'in stock sms' );
				$send_sms    = "<a href='$query_arg'>$caption</a>";
				$newactions['sendsms'] = $send_sms;
				$newactions['trash'] = $actions['trash'];
				$actions             = $newactions;
				return apply_filters( 'stock_notifier_row_actions', $actions );
			}
			return $actions;
		}
		/**
		 * List Subscriber table primary column
		 *
		 * @param String $default page name.
		 * @param Sting  $screen current page.
		 *
		 * @return String
		 */
		public function list_table_primary_column( $default, $screen ) {
			if ( 'edit-stock_notifier' === $screen ) {
				$default = 'whatsapp';
			}
			return $default;
		}
		/**
		 * Select sortable columns
		 *
		 * @param array $columns return all subscriber colums.
		 *
		 * @return array
		 */
		public function sortable_columns( $columns ) {
			$columns['popular_product'] = 'subscriber';
			return $columns;
		}
		/**
		 * Send Menual WhatsApp SMS
		 *
		 * @version 1.0.0
		 */
		public function send_manual_whatsapp_sms() {
			$nonce   = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
			$post_id = isset($_REQUEST['post_id']) ? sanitize_text_field( wp_unslash( $_REQUEST['post_id'] ) ) : '';
			$post_id = intval($post_id);
			if ( wp_verify_nonce( $nonce, 'stock_notifier-whatsapp-' . $post_id ) ) {
				// Send sms.
				$stock_notifier_api = new Stock_Notifier_API();
				$stock_notifier_api->stock_notifier_manual_whatsapp_sms( $post_id );
			} else {
				stock_notifier_add_persistent_notice( [
					'type'    => 'warning',
					'message' => __( 'Security Check Failed, Please try later', 'stock-notifier' ),
				] );
			}
			$http_server = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']));
			wp_redirect( $http_server );
			exit();
		}
		/**
		 * Change mark SMS sent status using by bulk action
		 *
		 * @param int $post_ids subscriber id.
		 *
		 * @version 1.0.0
		 */
		public function bulk_mark_status_sent( $post_ids = [] ) {
			$count     = count( $post_ids );
			$stock_api = new Stock_Notifier_API();

			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $each_id ) {
					$stock_api->stock_notifier_sms_sent_status( $each_id );
					do_action( 'stock_notifier_bulk_status_action', $each_id, 'iwg_smssent' );
					$logger = new Stock_Notifier_Logger( 'success', "Manual changed status to SMS Sent - $each_id" );
					$logger->stock_notifier_record_log();
				}
				stock_notifier_add_persistent_notice( [
					'type'    => 'success',
					'message' => sprintf( '%d - Data(s) Manually marked status to SMS Sent', $count ),
				] );
			}
		}
		/**
		 *  Change mark subsribed status using by bulk action
		 *
		 * @param int $post_ids subscriber id.
		 */
		public function bulk_mark_status_subscribed( $post_ids = [] ) {
			$count     = count( $post_ids );
			$stock_api = new Stock_Notifier_API();

			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $each_id ) {
					$stock_api->stock_notifier_subscriber_subscribed( $each_id );
					do_action( 'stock_notifier_bulk_status_action', $each_id, 'iwg_subscribed' );
					$logger = new Stock_Notifier_Logger( 'success', "Manual changed status to Subscribe - $each_id" );
					$logger->stock_notifier_record_log();
				}

				stock_notifier_add_persistent_notice( [
					'type'    => 'success',
					'message' => sprintf( '%d - Data(s) Manually marked status to Subscribe', $count ),
				] );
			}
		}
		/**
		 *  Change mark subsribed status using by bulk action
		 *
		 * @param int $post_ids subscriber id.
		 */
		public function bulk_mark_status_unsubscribed( $post_ids = [] ) {
			$count     = count( $post_ids );
			$stock_api = new Stock_Notifier_API();

			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				foreach ( $post_ids as $each_id ) {
					$stock_api->stock_notifier_subscriber_unsubscribed( $each_id );
					do_action( 'stock_notifier_bulk_status_action', $each_id, 'iwg_unsubscribed' );
					$logger = new Stock_Notifier_Logger( 'success', "Manual changed status to Unsubscribe - $each_id" );
					$logger->stock_notifier_record_log();
				}

				stock_notifier_add_persistent_notice( [
					'type'    => 'success',
					'message' => sprintf( '%d - Data(s) Manually marked status to Unsubscribe', $count),
				] );
			}

		}
		/**
		 * Send manual multipale sms using by bulk action.
		 *
		 * @param int $post_ids subscriber id.
		 */
		public function bulk_send_manual_sms( $post_ids = [] ) {
			$sent       = 0;
			$failed     = 0;
			$not_exists = 0;
			$count      = count( $post_ids );
			$stock_api  = new Stock_Notifier_API();
			$whatsapp_active_or_inactive = get_option( 'stock_notifier_whatsapp_toggle' );

			if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
				$logger = new Stock_Notifier_Logger( 'success', "Bulk SMS process started for data #$count" );
				$logger->stock_notifier_record_log();
				foreach ( $post_ids as $post_id ) {
					$get_phone      = get_post_meta( $post_id, 'stock_notifier_subscriber_phone', true );
					$send_smsler    = new Stock_Notifier_Instock_Subscribe_SMS( $post_id );
					$pid            = get_post_meta( $post_id, 'stock_notifier_pid', true );
					$api            = new Stock_Notifier_API();
					$product_exists = wc_get_product( $pid );

					if ( $product_exists ) {
						if ( '1' == $whatsapp_active_or_inactive ) {
							$send_sms = $send_smsler->send_whatsapp_sms();
							if ( $send_sms ) {
								$message    = __( 'Instock SMS sent to {whatsapp_number} successfully', 'stock-notifier' );
								$replace    = str_replace( '{whatsapp_number}', $get_phone, $message );
								$sms_status = $api->stock_notifier_sms_sent_status( $post_id );
								$logger     = new Stock_Notifier_Logger( 'success', "Bulk mail sent to #$get_phone - #$post_id" );
								$logger->stock_notifier_record_log();
								$sent++;
							} else {
								$error_msg     = __('Unable to send Instock SMS to this {whatsapp_number}', 'stock-notifier');
								$error_replace = str_replace( '{whatsapp_number}', $get_phone, $error_msg );
								$sms_status    = $api->stock_notifier_sms_not_sent_status( $post_id );
								$logger        = new Stock_Notifier_Logger( 'error', $error_replace . " #$post_id" );
								$logger->stock_notifier_record_log();
								$failed++;
							}
						} else {
							$error_msg     = __( 'Unable to send In stock SMS to this {whatsapp_number}', 'stock-notifier' );
							$error_replace = str_replace( '{whatsapp_number}', $get_phone, $error_msg );
							$sms_status    = $api->stock_notifier_sms_not_sent_status( $post_id );
							$logger        = new Stock_Notifier_Logger( 'error', $error_replace . " #$post_id" );
							$logger->stock_notifier_record_log();
							$failed++;
						}
					} else {
						$error_msg     = __( 'Unable to send Instock SMS to this {whatsapp_number} as stock product does not exists/deleted !!!', 'stock-notifier' );
						$error_replace = str_replace( '{whatsapp_number}', $get_phone, $error_msg );
						$logger        = new Stock_Notifier_Logger( 'error', $error_replace . " #$post_id" );
						$logger->stock_notifier_record_log();
						$not_exists++;
					}
				}

				$final_notice  = __( 'Bulk SMS: ', 'stock-notifier' );
				$final_notice .= $count > 0 ? "Total = $count" : '';
				$final_notice .= $sent > 0 ? " Sent = $sent" : '';
				$final_notice .= $failed > 0 ? " Failed = $failed" : '';
				$final_notice .= $not_exists > 0 ? " Product not Exists = $not_exists" : '';

				$logger = new Stock_Notifier_Logger( 'info', $final_notice );
				$logger->stock_notifier_record_log();

				stock_notifier_add_persistent_notice( [
					'type'    => 'success',
					'message' => $final_notice,
				] );
			}
		}
		/**
		 * Use transient and show awaiting when new subscribers in subscriber list
		 *
		 * @param array $menu return all dashboard menu.
		 */
		public function set_transient( $menu ) {
			$get_subscriber_count = get_transient( 'subscriber_count' ) ? get_transient( 'subscriber_count' ) : 0;
			if ( $get_subscriber_count > 0 ) {
				$i = 0;
				foreach ( $menu as $key => $sid_nav ) {
					if ( 'stock_notifier' == $sid_nav[2] ) {
						$menu[ $i ][0] = sprintf( "%s<span class='awaiting-mod'>%s</span>", __( 'Stock Notifier', 'stock-notifier' ), $get_subscriber_count );
							return $menu;
					}
					$i++;
				}
			}

			return $menu;
		}
		/**
		 * Delete transient when system admin on subscriber list
		 *
		 * @param Object $screen current screen.
		 */
		public function delete_transient( $screen ) {
			$current_screen = get_current_screen();
			if ( 'edit.php' == $screen && 'stock_notifier' == $current_screen->post_type ) {
				delete_transient( 'subscriber_count' );
			}

		}
		/**
		 * Filter out of stock request subscriber by subscriber poroduct.
		 *
		 * @version 1.0.0
		 */
		public function filter_by_subscribed_products() {
			$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ' ';
			if ( $post_type && ' ' != $post_type ) {
				$type = $post_type;
				if ( 'stock_notifier' == $type ) {
					?>
					<select style='width:320px;' data-placeholder='<?php esc_html_e( 'Filter by products', 'stock-notifier' ); ?>'
							data-allow_clear='true' tabindex='-1' aria-hidden='true' name='iwg_filter_by_products[]'
							multiple='multiple' class='wc-product-search'>
						<?php
						$current_v  = isset( $_GET['iwg_filter_by_products'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET['iwg_filter_by_products'] ) ) : [];
						if ( is_array( $current_v ) && ! empty( $current_v ) ) {
							foreach ( $current_v as $each_id ) {
								$product = wc_get_product( $each_id );
								if ( $product ) {
									printf( "<option value='%s'%s>%s</option>", wp_kses_post($each_id), wp_kses_post('selected="selected"'), wp_kses_post( $product->get_formatted_name() ) );
								}
							}
						}
						?>
					</select>
					<?php
				}
			}
		}
		/**
		 * Stock Notifier parse query
		 *
		 * @param url $query url.
		 */
		public function parse_query( $query ) {
			global $pagenow;
			if ( ! is_admin() ) {
				return;
			}
			$orderby    = $query->get( 'orderby' );
			$post_type  = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ' ';
			if ( $post_type && ' ' != $post_type ) {
				$type                    = $post_type;
				$api                     = new Stock_Notifier_API();
				$check_value_set         = isset( $_GET['iwg_filter_by_products'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET['iwg_filter_by_products'] ) ) : [];
				$sanitize_filter_product = $check_value_set;
				if ( 'stock_notifier' == $type && is_admin() && 'edit.php' == $pagenow && isset( $sanitize_filter_product ) && ! empty( $sanitize_filter_product ) && is_array( $sanitize_filter_product ) ) {
					$meta_query  = [
						'relation' => 'OR',
						[
							'key'     => 'stock_notifier_pid',
							'value'   => $sanitize_filter_product,
							'compare' => 'IN',
						],
						[
							'key'     => 'stock_notifier_product_id',
							'value'   => $sanitize_filter_product,
							'compare' => 'IN',
						],
					];
					$query->query_vars['meta_query'] = $meta_query;
				}
				if ( 'stock_notifier' == $type && is_admin() && 'edit.php' == $pagenow && 'product' == $orderby ) {
					// For orderby just order based on product id.
					$query->set( 'meta_key', 'stock_notifier_pid' );
					$query->set( 'orderby', 'meta_value_num' );
				}
			}
		}
		/**
		 * Sortabe total subscriber columns
		 *
		 * @param object $query args.
		 */
		public function sort_total_subscribers( $query ) {
			if ( ! is_admin() ) {
				return;
			}
			$orderby = $query->get( 'orderby' );
			if ( 'subscriber' == $orderby ) {
				$query->set( 'meta_key', 'stock_notifier_subscriber_count' );
				$query->set( 'orderby', 'meta_value_num' );
			}
		}
	}
	/**
	 * Kick out the __contructer
	 */
	new Stock_Notifier_Post_Type();

}
