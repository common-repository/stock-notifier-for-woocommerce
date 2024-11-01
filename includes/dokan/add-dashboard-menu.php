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

if ( ! class_exists( 'Stock_Notifier_Dokan_All_Subscriber_Menu' ) ) {
	/**
	 * Add submenu in the dokan  dashboard
	 *
	 * @varsion 1.0.0
	 */
	class Stock_Notifier_Dokan_All_Subscriber_Menu {
		/**
		 * Object Calling method
		 *
		 * @varsion 1.0.0
		 */
		public function __construct() {
			add_filter( 'dokan_query_var_filter', [ $this, 'stock_notifier_dokan_load_document_menu' ] );
			add_filter( 'dokan_get_dashboard_nav', [ $this, 'stock_notifier_dokan_add_subscriber_menu' ] );
			add_action( 'dokan_load_custom_template', [ $this, 'stock_notifier_dokan_load_template' ] );
			add_filter( 'dokan_dashboard_nav_settings_key', [ $this, 'stock_notifier_key' ], 10, 1 );
		}
		/**
		 * Push stock notifier key in dokan dashboard menu
		 *
		 * @param array $menu dokan menu key list.
		 * @varsion 1.0.0
		 */
		public function stock_notifier_key( $menu ) {
			global $wp;
			$current_page       = $wp->query_vars;
			$in_stock_pge_now   = ( array_keys( $current_page ) );

			if ( 'stock_notifier' == $in_stock_pge_now[1] ) {
				return 'stock_notifier';
			} else {
				return $menu;
			}
		}
		/**
		 * Load stock notifier document menu in dokan multivendor
		 *
		 * @param array $query_vars push stock notifier url.
		 * @varsion 1.0.0
		 */
		public function stock_notifier_dokan_load_document_menu( $query_vars ) {
			$query_vars['stock_notifier'] = 'stock_notifier';

			return $query_vars;
		}
		/**
		 * Add Stock Menu in dokan multivendor.
		 *
		 * @param array $menus dokan multivendor urls.
		 * @varsion 1.0.0
		 */
		public function stock_notifier_dokan_add_subscriber_menu( $menus ) {
			$custom_menus = [
				'stock-notifer' => [
					'title'   => __( 'Stock Notifier', 'stock-notifier' ),
					'icon'    => sprintf( '<img style="width: 20px;margin-right: 8px;" src="%s/img/svg.svg" />', STOCKNOTIFIER_ASSETS ),
					'url'     => dokan_get_navigation_url( 'stock_notifier/all_subscriber' ),
					'pos'     => 60,
					'submenu' => [
						'subscriber' => [
							'title'      => __( 'Subscriber', 'stock-notifer' ),
							'icon'       => '<i class="fa fa-user"></i>',
							'url'        => dokan_get_navigation_url( 'stock_notifier/all_subscriber' ),
							'pos'        => 10,
							'permission' => 'dokan_view_product_menu',
						],
						'overview' => [
							'title'      => __( 'Overview', 'stock-notifier' ),
							'icon'       => '<span class="dashicons dashicons-chart-bar"></span>',
							'url'        => dokan_get_navigation_url( 'stock_notifier/overview' ),
							'pos'        => 20,
							'permission' => 'dokan_view_product_menu',
						],
					],
				],
			];
			return array_merge( $menus, $custom_menus );
		}
		/**
		 * Load Stock Notifier Template.
		 *
		 * @param array $query_vars dokan query vars list.
		 */
		public function stock_notifier_dokan_load_template( $query_vars ) {
			if ( isset( $query_vars['stock_notifier'] ) && 'all_subscriber' == $query_vars['stock_notifier'] ) {
				?>
				<?php
				/**
				 *  Dokan Dashboard Template
				 *
				 *  Dokan Main Dahsboard template for Fron-end
				 *
				 * @since 2.4
				 *
				 * @package dokan
				 */
				?>
				<div class="dokan-dashboard-wrap" xmlns="http://www.w3.org/1999/html">
					<?php
					/**
					 * Dokan_dashboard_content_before hook
					 *
					 * @hooked get_dashboard_side_navigation
					 *
					 * @since 2.4
					 */
					do_action( 'dokan_dashboard_content_before' );
					?>

					<div class="dokan-dashboard-content">

						<?php
						/**
						 * Dokan_dashboard_content_before hook
						 *
						 * @hooked show_seller_dashboard_notice
						 *
						 * @since 2.4
						 */

						do_action( 'stock_notifier_dokan_all_subscriber_content_inside_before' );
						if ( current_user_can( 'publish_products' ) ) {
							$current_user_id = get_current_user_id();
						} else {
							return false;
						}

						if ( ! is_user_logged_in() ) {
							return false;
						}
						$get_stock_notifier_on_off = get_option( "stock_notifier_dokan_notifier_on_off_$current_user_id", 0 );
						if ( '1' == $get_stock_notifier_on_off ) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
						$pro_active = get_option( 'stock_notifier_pro_active', 0 );
						$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );
						$stock_pemium = 0;
						if ( 1 == $pro_active || 1 == $ultimate_active ) {
							$stock_pemium = 1;
						}
						$stock_notifier_id_name = 'stock_notifier_ON_OFF_vendor_Setting';
						if ( 0 == $stock_pemium ) {
							$stock_notifier_id_name = 'stock_notifier_ON_OFF_Setting_free';
						}
						do_action( 'stock_notifier_popup_dokan' );
						?>
						<article class="help-content-area">                            
							<div class="stock_notifier_ON_OFF_from">
								<form class="stock_notifier_Active_form">
									<span style="color: #f05025"><?php esc_html_e( 'OFF', 'stock-notifier' ); ?></span>/<span style="color: #4CAF50"><?php esc_html_e( 'ON', 'stock-notifier' ); ?></span>
									<input type="hidden" name="stock_notifier_shop_user_id" id="stock_notifier_shop_user_id" value="<?php echo esc_attr( $current_user_id ); ?>">
									<span class="stock_notifier_ON_OFF_Settings <?php echo esc_attr( $stock_notifier_id_name ); ?>">
										<label class="stock_notifier_email_switch">
											<input type="checkbox" name="stock_notifier_ON_OFF_Setting" class="stock_notifier_email_toggle"
												id="<?php echo esc_attr( $stock_notifier_id_name ); ?>"
												value="<?php echo esc_attr( $get_stock_notifier_on_off ); ?>" <?php echo esc_attr( $checked ); ?>>
											<span class="stock_notifier_slider stock_notifier_slider_round"></span>
										</label>
									</span>

									<span class="stock_notifier_tooltip_vendor_on"><span class="dashicons dashicons-info stock_notifier_dokan_dash_icon"></span>
										<span class="stock_notifier_tooltip_vendor_on_tooltiptext">Turn this option ON to allow your customers to get stock notifications.</span>
									</span>
									<div class="stock_notifier_line_break"></div>
								</form>
							</div>
							<br/>
							<?php
							if ( isset( $_GET['post_id'] ) || isset( $_GET['delete_id'] ) ) {
								$delete_id  = isset( $_GET['delete_id'] ) ? sanitize_text_field( wp_unslash( $_GET['delete_id'] )) : 0;
								$post_id    = isset( $_GET['post_id'] ) ? sanitize_text_field( wp_unslash($_GET['post_id'] )) : 0;

								if ( ! isset( $_GET['inwp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['inwp_nonce']) ), 'stock_notifier_nonce' ) ) {
									wp_die( 'sorry you are not authorizid' );
								}

								if ( isset( $_GET['action'] ) ) {
									$action = sanitize_text_field( wp_unslash($_GET['action']) );
									if ( 'stock_notifier-whatsapp' == $action ) {
										if ( $post_id ) {
											$stock_notifier_api = new Stock_Notifier_API();
											$stock_notifier_api->stock_notifier_manual_whatsapp_sms( $post_id, $dokan = 1 );
										}
									} elseif ( 'stock_notifier_delete' == $action ) {
										if ( $delete_id ) {
											$stock_notifier_api = new Stock_Notifier_API();
											$inwp_success       = $stock_notifier_api->stock_notifier_delete_subscribe( $delete_id );

											if ( $inwp_success ) {
												?>
												<p class="stock_notifier_delete"><?php esc_html_e( 'Delete Successfully!', 'stock-notifier' ); ?></p>
												<?php
											}
										}
									}
								}
							}
							if ( 1 == $ultimate_active ) {
								do_action( 'stock_notifier_vendor_subscriber_list' );
							} else {
								?>
								<div class="container stock_notifier_free_vendor_list" id="stock_notifier_vendor_list_free" >
									<div class="table-responsive" style="opacity: 0.5">
										<table style="max-width: 965px" id="stock_notifier_employee_data"
												class="table table-striped table-bordered">
											<thead>
											<tr>
												<td><?php esc_html_e( 'Number', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Status', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Product', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Subscriber', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Date', 'stock-notifier' ); ?></td>
											</tr>
											</thead>
											<tbody>
												<?php
												for ( $i = 0; $i < 8; $i++ ) {
													?>
													<tr>
														<td>
															<?php
															$caption      = sprintf( '<img style="width:30px;height:20px;margin-bottom:-3px" src="%s" alt="%s" />', STOCKNOTIFIER_URL . 'assets/img/automate.svg', 'in stock sms' );
															$deleteimg    = sprintf( '<img style="width:30px;height:20px;margin-bottom:-3px" src="%s" alt="%s" />', STOCKNOTIFIER_URL . 'assets/img/Delete.svg', 'Delete sms' );
															printf( '+8801712323243' );
															echo '<br/>';
															printf( '<a style="color: #4CAF50;display:inline-block !important;" href="#">%s</a>', wp_kses_post($caption) );
															printf( "<span style='font-size: 14px;display:inline-block !important;'>|</span>" );
															printf( '<a style="display: inline-block !important" href="#">%s</a>', wp_kses_post($deleteimg) );
															?>
														</td>
														<td>
															<?php
																$stock_notifier_api = new Stock_Notifier_API();
																$stock_notifier_api->stock_notifier_display_status( 'iwg_subscribed' );
															?>
														</td>
														<td>
															Product2 (#16)
														</td>
														<td>
															14
														</td>
														<td>
															<?php
															$get_data = get_the_date();
															echo esc_html( $get_data );
															?>
														</td>
													</tr>
													<?php
												}
												?>
											</tbody>
											<tfoot>
											<tr>
												<td><?php esc_html_e( 'Number', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Status', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Product', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Subscriber', 'stock-notifier' ); ?></td>
												<td><?php esc_html_e( 'Date', 'stock-notifier' ); ?></td>
											</tr>
											</tfoot>
										</table>

									</div>
									<div class="stock_notifier_over_unlock">
										<div class="stock_notifier_over_unlock_button">
											<span class="dashicons dashicons-lock stock_notifier_over_lock"></span>
											<h6 class="pro_feature">This feature is Locked</h6>
											<a href="#" class="stock_notifier_over_button1 stock_notifier_over_button">Unlock</a>
										</div>
									</div>
								</div>
								<?php
							}
							?>
							<script>
								jQuery(document).ready(function () {
									jQuery('#stock_notifier_employee_data').DataTable({
										"searching":true,
										"paging":true,
										"order": [[ 4, "desc" ]],
											"ordering":true
									});
								});
							</script>
						</article><!-- .dashboard-content-area -->

						<?php
						/**
						 * Dokan_dashboard_content_inside_after hook
						 *
						 * @since 2.4
						 */
						do_action( 'stock_notifier_dokan_dashboard_content_inside_after' );
						?>
					</div><!-- .dokan-dashboard-content -->
					<?php
					/**
					 * Dokan_dashboard_content_after hook
					 *
					 * @since 2.4
					 */
					do_action( 'stock_notifier_dokan_dashboard_content_after' );
					?>
				</div><!-- .dokan-dashboard-wrap -->

				<?php
			} elseif ( isset( $query_vars['stock_notifier'] ) && 'overview' == $query_vars['stock_notifier'] ) {
				?>
				<?php
				/**
				 *  Dokan Dashboard Template
				 *  Dokan Main Dahsboard template for Fron-end
				 *
				 *  @since 2.4
				 *
				 *  @package dokan
				 */

				?>
				<div class="dokan-dashboard-wrap">
					<?php
					/**
					 *  Dokan_dashboard_content_before hook
					 *
					 *  @hooked get_dashboard_side_navigation
					 *
					 *  @since 2.4
					 */
					do_action( 'dokan_dashboard_content_before' );
					?>

					<div class="dokan-dashboard-content">

						<?php
						/**
						 * Dokan_dashboard_content_before hook
						 *
						 *  @hooked show_seller_dashboard_notice
						 *
						 *  @since 2.4
						 */
						do_action( 'dokan_help_content_inside_before' );
						?>
						<article class="help-content-area">
						<?php
							$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );
						if ( 1 == $ultimate_active ) {
							do_action( 'stock_notifier_vendor_overview' );
						} else {
							do_action( 'stock_notifier_popup_dokan' );
							?>
							<div class="stock_notifier_overview_container_dokan stock_notifier_pageBackground stock_notifier_free_s">
								<div class="in_stock_overview_product_popularity_list_dokan stock_notifier_free_">
									<div class="stock_notifier_overview_canvas_dokan_free">
										<div>
											<img  class="stock_notifier_dokan_img" src="<?php echo esc_url( STOCKNOTIFIER_URL . '/assets/img/most2.png' ); ?>">
										</div>
									</div>
								</div>

								<div class="stock_notifier_overview_2_dokan">
									<div class="stock_notifier_overview_total_subscriber_dokan_free">
										<div class="stock_notifier_overview_total_heading_dokan">
											<p class="stock_notifier_overview_total_view_dokan"><?php esc_html_e( 'Total Subscribers', 'stock-notifier' ); ?></p>
											<div class="stock_notifier_overview_total_span_dokan">
												<p class="stock_notifier_overview_month_name_dokan"><span class="dashicons dashicons-arrow-left-alt2 stock_notifier_overview_dash_dokan"></span><span class="stock_notifier_date_dokan"><?php echo esc_html( gmdate('F') ); ?> </span> <span class="dashicons dashicons-arrow-right-alt2 stock_notifier_overview_dash_dokan"></span></p>
											</div>
										</div>
											<div class="stock_notifier_total_count_value_dokan">
											<h6 class="stock_notifier_show_total_data_dokan">100</h6>
											<div class="stock_notifier_product_percentage_dokan" style="background-color: #E5F8F3; color:#56D8B3; ">
												<span class="stock_notifier_percentage_text_dokan" style="color: #00C486;">
												10%
													<span class="dashicons dashicons-arrow-up-alt stock_notifier_up_icon_dokan"></span>
												</span>
											</div>
											<p class="stock_notifier_t_text_dokan" ><?php esc_html_e( 'Total Views', 'stock-notifier' ); ?></p>
											<p class="stock_notifier_t_text_dokan"><?php esc_html_e( 'Last Update', 'stock-notifier' ); ?> 3minutes ago</p>
										</div>
									</div>
									<div class="stock_notifier_notification_sent_percentage_dokan_free">
										<div class="stock_notifier_notification_sent_text_dokan">
											<div class="stock_notifier_subscriber_percentage_empty_dokan">
												<img class="stock_notifier_image_notification_dokan_free" src="<?php echo esc_url( STOCKNOTIFIER_URL . '/assets/img/notification.PNG' ); ?>" height="300px">
											</div>
										</div>
									</div>
								</div>
								<div class="stock_notifier_over_unlock">
									<div class="stock_notifier_over_unlock_button">
									<span class="dashicons dashicons-lock stock_notifier_over_lock"></span>
									<h6 class="pro_feature">This feature is locked.</h6>
									<a href="#" class="stock_notifier_over_button1 stock_notifier_over_button">Unlock</a>
									</div>
								</div>
							</div> 
							<?php
						}
						?>
						</article><!-- .dashboard-content-area -->
						<?php
						/**
						 * Dokan_dashboard_content_inside_after hook
						 *
						 *  @since 2.4
						 */
						do_action( 'dokan_dashboard_content_inside_after' );
						?>


					</div><!-- .dokan-dashboard-content -->
					<?php
					/**
					 * Dokan_dashboard_content_after hook
					 *
					 *  @since 2.4
					 */
					do_action( 'dokan_dashboard_content_after' );
					?>
				</div><!-- .dokan-dashboard-wrap -->
				<?php
			}
		}

	}
	new Stock_Notifier_Dokan_All_Subscriber_Menu();
}