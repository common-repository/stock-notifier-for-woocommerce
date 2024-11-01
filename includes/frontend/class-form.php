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
if ( ! class_exists( 'Stock_Notifier_Frontend_Product' ) ) {
	/**
	 * Frontend class
	 *
	 * @varsion 1.0.0
	 */
	class Stock_Notifier_Frontend_Product {
		/**
		 * Calling method
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'woocommerce_simple_add_to_cart', [ $this, 'display_in_simple_product' ], 31 );
			add_action( 'woocommerce_bundle_add_to_cart', [ $this, 'display_in_simple_product' ], 31 );
			add_action( 'woocommerce_woosb_add_to_cart', [ $this, 'display_in_simple_product' ], 31 );
			add_action( 'woocommerce_after_variations_form', [ $this, 'display_in_no_variation_product' ] );
			add_action( 'woocommerce_grouped_add_to_cart', [ $this, 'display_in_simple_product' ], 32 );
			add_filter( 'woocommerce_available_variation', [ $this, 'display_in_variation' ], 10, 3 );
			// Some theme variation disabled by default if it is out of stock so for that workaround solution.
			add_filter( 'woocommerce_variation_is_active', [ $this, 'enable_disabled_variation_dropdown' ], 100, 2 );
			add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'stock_notifier_woocommerce_template_loop_add_to_cart' ], 10, 3 );
			add_action( 'wp_head', [ $this, 'stock_notifier_style' ] );
			// Check progress bar visibility.
			$visibility_progress_bar = get_option( 'stock_notifier_stock_progress_bar', 0 );
			if ( '1' == $visibility_progress_bar ) {
				add_filter( 'woocommerce_get_stock_html', [ $this, 'woocommerce_get_availability_class' ], 9999, 2 );
			}
		}
		/**
		 * Push stock notifier class for progress par.
		 *
		 * @param string $html The HTML tag.
		 * @param object $product The product object.
		 *
		 * @return string The modified HTML tag.
		 */
		public function woocommerce_get_availability_class( $html, $product ) {
			if ( is_product() ) {
				$get_progress_bar_style = get_option( 'stock_notifier_stock_progress_bar_style' );
				$product_avail  = $product->get_availability();
				$product_id     = $product->get_id();
				$stock_quantity = $product->get_stock_quantity();
				$total_sold     = $this->get_qountity_sale_counter( $product_id, $stock_quantity );
				$total_quantity = get_post_meta( $product_id, 'stock_notifier_set_quantity', true );
				$availability   = $product_avail['availability'];
				$avail_class    = $product_avail['class'];
				if ( ! empty( $availability ) && $stock_quantity ) {
					ob_start();
					if ( '1' == $get_progress_bar_style ) {
						$this->stock_notifier_progress_bar_1( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity );
					} elseif ( '2' == $get_progress_bar_style ) {
						$this->stock_notifier_progress_bar_2( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity );
					} elseif ( '3' == $get_progress_bar_style ) {
						$this->stock_notifier_progress_bar_3( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity );
					} elseif ( '4' == $get_progress_bar_style ) {
						$this->stock_notifier_progress_cricular( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity );
					} elseif ( '5' == $get_progress_bar_style ) {
						$this->stock_notifier_progress_bar_4( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity );
					}
					$html = ob_get_clean();
				}
			}
			return $html;
		}
		/**
		 * Stock Progress Bar 1
		 *
		 * @return void
		 * @param string $avail_class attribute.
		 * @param string $availability total quantity.
		 * @param int    $stock_quantity stock quantity.
		 * @param int    $total_sold total sold.
		 * @param int    $total_quantity total quantity.
		 * @version 1.0.0
		 */
		public function stock_notifier_progress_bar_1( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity ) {
			$percentage_value = $this->calculate_percentage( $total_sold, $total_quantity );
			?>
			<div class="stock_notifier_progress_bar">
				<span class="stock_notifier_sold_item" style="color:#484848 !important;"><?php echo esc_attr( 'Sold: ' . $total_sold ); ?></span>
				<span class="stock_notifier_due_item" style="color:#484848 !important;"><?php echo esc_attr( 'Avilable Stock: ' . $stock_quantity ); ?></span>
				<div class="stock_notifier_progress_1">
					<div class="stock_notifier_progress_bar_1" style="width:<?php echo esc_attr( $percentage_value ); ?>%">
						<p class="stock_notifier_progress_percent_1"></p>
					</div>
				</div>
			</div>
			<?php
		}
		/**
		 * Calculate decimal to percentage
		 *
		 * @return integer
		 * @param int $minvalue max.
		 * @param int $tota_value totalvalue.
		 * @version 1.1.3
		 */
		public function calculate_percentage( $minvalue, $tota_value ) {
			$new_value = ( $minvalue / $tota_value ) * 100;
			return $new_value;
		}
		/**
		 * Convart percentage to degree
		 *
		 * @return integer
		 * @param int $value max.
		 * @version 1.1.3
		 */
		public function conavart_degree( $value ) {
			$degree = ( $value / 100 ) * 180;
			return $degree;
		}
		/**
		 * Stock Progress Bar 2
		 *
		 * @return void
		 * @param string $avail_class attribute.
		 * @param string $availability total quantity.
		 * @param int    $stock_quantity stock quantity.
		 * @param int    $total_sold total sold.
		 * @param int    $total_quantity total quantity.
		 * @version 1.0.0
		 */
		public function stock_notifier_progress_bar_2( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity ) {
			$percentage_value = $this->calculate_percentage( $total_sold, $total_quantity );
			?>
			<div class="stock_notifier_progress_bar">
				<span class="stock_notifier_sold_item" style="color:#484848 !important;"><?php echo esc_attr( 'Avilable Stock: ' . $stock_quantity ); ?></span>
				<div class="stock_notifier_bar_border_2">
					<div class="stock_notifier_progress_2">
						<div class="stock_notifier_progress_bar_2" style="width:<?php echo esc_attr( $percentage_value ); ?>%">
						</div>
					</div>
					<div class="stock_notifier_sold-item" style="color:#484848 !important;" ><?php echo esc_attr( ' ' . $total_sold . ' Sold'); ?></div>
				</div>
			</div>
			<?php
		}
		/**
		 * Stock Progress Bar 3
		 *
		 * @return void
		 * @param string $avail_class attribute.
		 * @param string $availability total quantity.
		 * @param int    $stock_quantity stock quantity.
		 * @param int    $total_sold total sold.
		 * @param int    $total_quantity total quantity.
		 * @version 1.0.0
		 */
		public function stock_notifier_progress_bar_3( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity ) {
			$percentage_value = $this->calculate_percentage( $total_sold, $total_quantity );
			?>
			<div class="stock_notifier_progress_bar">
			<span class="stock_notifier_sold_item" style="color:#484848 !important;"><?php echo esc_attr( $stock_quantity . ' Avilable in stock '  ); ?></span>
				<div class="stock_notifier_progressbar3">
					<div class="stock_notifier_progress-in3 stock_notifier_progressbar3_tooltip" style="width:<?php echo esc_attr( $percentage_value ); ?>%;" >
						<div class="stock_notifier_progressbar3_p-head-value" ><p></p></div>
						<div class="">
							<span class="stock_notifier_progressbar3_tooltiptext">
								<?php echo esc_attr( 'Sold: ' . $total_sold ); ?>
							</span>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		/**
		 * Stock Progress Bar 4
		 *
		 * @return void
		 * @param string $avail_class attribute.
		 * @param string $availability total quantity.
		 * @param int    $stock_quantity stock quantity.
		 * @param int    $total_sold total sold.
		 * @param int    $total_quantity total quantity.
		 * @version 1.0.0
		 */
		public function stock_notifier_progress_bar_4( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity ) {
			$percentage_value = $this->calculate_percentage( $total_sold, $total_quantity );
			?>
			<div class="stock_notifier_progress_bar_4">
				<div class="stock_notifier_sold_item_4" style="color:#484848 !important;"><?php echo esc_attr( $total_sold . ' items sold' ); ?></div>
				<div class="stock_notifier_progress_4_wrap">
					<div class="stock_notifier_progress_4">
						<div class="stock_notifier_progress_bar4" style="width:<?php echo esc_attr( $percentage_value ); ?>%">
							<div class="stock_notifier_progress_percent_4"><img src="<?php echo esc_url( STOCKNOTIFIER_ASSETS . '/img/Group-logo.png'); ?>" /></div>
						</div>
					</div>
				</div>
				<div class="stock_notifier_avaiable_stock_button"><?php echo esc_attr( 'Available Stock: ' . $stock_quantity ); ?> </div>
			</div>
			<?php
		}
		/**
		 * Stock circular Progress Bar
		 *
		 * @return void
		 * @param string $avail_class attribute.
		 * @param string $availability total quantity.
		 * @param int    $stock_quantity stock quantity.
		 * @param int    $total_sold total sold.
		 * @param int    $total_quantity total quantity.
		 * @version 1.0.0
		 */
		public function stock_notifier_progress_cricular( $avail_class, $availability, $stock_quantity, $total_sold, $total_quantity ) {
			$percentage_value = $this->calculate_percentage( $total_sold, $total_quantity );
			$conavart_degree  = $this->conavart_degree( $percentage_value );
			?>
			<style>
			.stock_notifier-mask.stock_notifier-full,
			.stock_notifier-circle .stock_notifier-fill {
				animation: fill ease-in-out 3s;
				transform: rotate(<?php echo esc_attr( $conavart_degree ); ?>deg );
			}
			</style>
			<div class="stock_notifier-circle-wrap">
				<div class="stock_notifier-circle">
					<div class="stock_notifier-mask stock_notifier-full">
						<div class="stock_notifier-fill" ></div>
					</div>
					<div class="stock_notifier-mask stock_notifier-half">
						<div class="stock_notifier-fill"></div>
					</div>
					<div class="stock_notifier-inside-circle"><?php echo esc_attr( $total_sold ); ?> Sold</div>
				</div>
			</div>
			<div class="stock_notifier_circular_quantity"><?php echo esc_attr( $stock_quantity . ' Avilable in stock' ); ?></div>
			<div class="stock_notifier_divider"></div>
			<?php
		}
		/**
		 * Custom css Method
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_style() {
			$pro_active = get_option( 'stock_notifier_pro_active', 0 );
			$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );
			?>
			<style>
					.stock_notifier_hide_error{
						display: none !important;
					}
			</style>
			<?php
			if ( 1 == $pro_active || 1 == $ultimate_active ) {
				$custom_css = get_option( 'stock_notifier_customs_csss' );
				?>
					<style>
						<?php echo wp_kses_post( $custom_css ); ?>
					</style>
				<?php
			}
		}
		/**
		 * Show Request Stock Button in store page
		 *
		 * @param html   $html Html button.
		 * @param Object $product Product.
		 * @param array  $args args.
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_woocommerce_template_loop_add_to_cart( $html, $product, $args ) {
			global $product;
			$loop_product_visibility = get_option( 'stock_notifier_loop_product_visibility', 0 );

			if ( '1' != $loop_product_visibility ) {
				if ( $product ) {
					$product_type = $product->get_type();
					$product_id   = $product->get_id();
					if ( 'variable' == $product_type ) {
						$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
						$get_variations = $get_variations ? $product->get_available_variations() : false;
					} else {
						$get_variations = 0;
					}
					$get_option_backorder  = get_option( 'stock_notifier_show_subscribe_on_backorder' );
					$visibility_backorder  = isset( $get_option_backorder ) && '1' == $get_option_backorder ? true : false;
					$hide_for_gets         = get_option( 'stock_notifier_hide_sub_non_log' );
					$hide_for_member       = get_option( 'stock_notifier_hide_subscribe_loggedin' );
					$check_guest_visibility = isset( $hide_for_gets ) && ! empty( $hide_for_gets ) && ! is_user_logged_in() ? false : true;
					$check_member_visibility = isset( $hide_for_member ) && ! empty( $hide_for_member ) && is_user_logged_in() ? false : true;
					$porduct_upload_author   = get_post( $product_id );
					$current_user_id  = $porduct_upload_author->post_author;
					$multivendor_on_off = get_option( 'stock_notifier_multivendor_on_off' );
					$dokan_active_or_not = get_option( 'stock_notifier_dokan_active_or_not' );
					$get_dokanders_notfier_on_off = get_option( "stock_notifier_dokan_notifier_on_off_$current_user_id" );
					if ( '1' != $get_dokanders_notfier_on_off ) {
						if ( '1' != $multivendor_on_off ) {
							update_option( "stock_notifier_dokan_notifier_on_off_$current_user_id", 1 );
						}
						if ( '1' != $dokan_active_or_not ) {
							update_option( "stock_notifier_dokan_notifier_on_off_$current_user_id", 1 );
						}
						$pro_active = get_option( 'stock_notifier_pro_active', 0 );
						$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );
						$stock_pemium = 0;
						if ( 1 == $pro_active || 1 == $ultimate_active ) {
							$stock_pemium = 1;
						}
						if ( 0 == $stock_pemium ) {
							update_option( "stock_notifier_dokan_notifier_on_off_$current_user_id", 1 );
						}
					}
					$get_dokander_notfier_on_off = get_option( "stock_notifier_dokan_notifier_on_off_$current_user_id" );
					$inwp_whatsapp_on_option     = get_option( 'stock_notifier_whatsapp_toggle' );
					if ( ! $get_variations && ! $product->is_in_stock() || ( ( ! $get_variations && ( ( $product->managing_stock() && $product->backorders_allowed() && $product->is_on_backorder( 1 ) ) || $product->is_on_backorder( 1 ) ) && $visibility_backorder ) ) ) {
						if ( $check_guest_visibility && $check_member_visibility && ( $this->is_viewable( $product_id, $get_variations ) && $this->is_viewable_for_category( $product_id ) ) && $this->visibility_on_regular_or_sale( $product, $get_variations ) && $this->is_viewable_for_product_tag( $product_id ) && $get_dokander_notfier_on_off ) {
							if ( ( $product->managing_stock() && $product->backorders_allowed() && $product->is_on_backorder( 1 ) ) || $product->is_on_backorder( 1 ) ) {
								if ( 'variable' != $product_type ) {
									?>
									<div class="stock_notifier_btn">
										<?php
										return $html . $this->html_subscribe_form( $product, null, $html, 1 );
										?>
									</div>
									<?php
								} else {
									?>
									<div class="stock_notifier_loop_wrap">
										<?php return $this->html_subscribe_form( $product, null, $html, 1 ); ?>
									</div>
									<?php
								}
							} else {
								?>
								<div class="stock_notifier_loop_wrap">
									<?php return $this->html_subscribe_form( $product, null, $html, 1 ); ?>
								</div>
								<?php
							}
						}
					}
				}
			}
			return $html;
		}
		/**
		 * Display Request Stock Button for simple product
		 *
		 * @version 1.0.0
		 */
		public function display_in_simple_product() {
			global $product;
			echo esc_html( _e( $this->display_subscribe_box( $product ) ) ); //phpcs:ignore
		}
		/**
		 * Display Reuest Stock Button in no variation product
		 *
		 * @version 1.0.0
		 */
		public function display_in_no_variation_product() {
			global $product;
			$product_type = $product->get_type();
			// Get Available variations?
			if ( 'variable' == $product_type ) {
				$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
				$get_variations = $get_variations ? $product->get_available_variations() : false;
				if ( ! $get_variations ) {
					echo esc_html( _e( $this->display_subscribe_box( $product ) ) ); //phpcs:ignore
				}
			}
		}
		/**
		 * Display Subscribe from in shop page and single product page.
		 *
		 * @param object $product all product.
		 * @param object $variation all Variabtion product.
		 *
		 * @return html $html
		 */
		public function display_subscribe_box( $product, $variation = null ) {
			$get_option_backorder = get_option( 'stock_notifier_show_subscribe_on_backorder' );
			$visibility_backorder = isset( $get_option_backorder ) && '1' == $get_option_backorder ? true : false;

			if ( ! $variation && ! $product->is_in_stock() || ( ( ! $variation && ( ( $product->managing_stock() && $product->backorders_allowed() && $product->is_on_backorder( 1 ) ) || $product->is_on_backorder( 1 ) ) && $visibility_backorder ) ) ) {
				return $this->html_subscribe_form( $product );
			} elseif ( $variation && ! $variation->is_in_stock() || ( ( $variation && ( ( $variation->managing_stock() && $variation->backorders_allowed() && $variation->is_on_backorder( 1 ) ) || $variation->is_on_backorder( 1 ) ) && $visibility_backorder ) ) ) {
				return $this->html_subscribe_form( $product, $variation );
			}
		}
		/**
		 * Specific product Total sale counter.
		 *
		 * @return int $sold_count.
		 * @param int $product_id product id.
		 * @param int $product_qountity qountity.
		 * @version 1.0.0
		 */
		public function get_qountity_sale_counter( $product_id, $product_qountity ) {
			$set_quantity = get_post_meta( $product_id, 'stock_notifier_set_quantity', true );
			if ( $product_qountity > 0 && $set_quantity < 1 ) {
				update_post_meta( $product_id, 'stock_notifier_set_quantity', $product_qountity );
			}
			$total_quantity     = get_post_meta( $product_id, 'stock_notifier_set_quantity', true );
			$sold_count         = $this->get_sold_count( $total_quantity, $product_qountity );
			return $sold_count;
		}
		/**
		 * Check how much is sold.
		 *
		 * @param int $total_quantity total quantity.
		 * @param int $current_qountity current qountity.
		 *
		 * @return int $sold.
		 * @version 1.1.4
		 */
		public function get_sold_count( $total_quantity, $current_qountity ) {
			$sold = 0;
			if ( $total_quantity != $current_qountity && $total_quantity > $current_qountity ) {
				$sold = $total_quantity - $current_qountity;
			}
			return $sold;
		}
		/**
		 * Display Subscribe from in shop page and single product page.
		 *
		 * @param object $product all Product.
		 * @param object $variation Variabtion product.
		 * @param string $html prev html button.
		 * @param int    $loop_active check loop_active.
		 *
		 * @version 1.0.0
		 */
		public function html_subscribe_form( $product, $variation = [], $html = '', $loop_active = 0 ) {
			ob_start();
			$stock_notifier_random_code = bin2hex( random_bytes( 12 ) );
			$hide_for_gets              = get_option( 'stock_notifier_hide_sub_non_log' );
			$hide_for_member            = get_option( 'stock_notifier_hide_subscribe_loggedin' );
			$check_guest_visibility     = isset( $hide_for_gets ) && ! empty( $hide_for_gets ) && ! is_user_logged_in() ? false : true;
			$check_member_visibility    = isset( $hide_for_member ) && ! empty( $hide_for_member ) && is_user_logged_in() ? false : true;
			$product_id                 = $product->get_id();
			$product_upload_author      = get_post( $product_id );
			$current_user_id            = $product_upload_author->post_author;

			$get_dokanders_notfier_on_off = get_option( "stock_notifier_dokan_notifier_on_off_$current_user_id" );

			if ( '1' != $loop_active ) {
				$multivendor_on_off  = get_option( 'stock_notifier_multivendor_on_off', 0 );
				$dokan_active_or_not = get_option( 'stock_notifier_dokan_active_or_not' );
				if ( '1' != $get_dokanders_notfier_on_off ) {
					if ( '1' != $multivendor_on_off ) {
						$get_dokanders_notfier_on_off = 1;
					}
					if ( '1' != $dokan_active_or_not ) {
						$get_dokanders_notfier_on_off = 1;
					}
					$pro_active = get_option( 'stock_notifier_pro_active', 0 );
					$ultimate_active = get_option( 'stock_notifier_ultimate_active', 0 );
					$stock_pemium = 0;
					if ( 1 == $pro_active || 1 == $ultimate_active ) {
						$stock_pemium = 1;
					}
					if ( 0 == $stock_pemium ) {
						$get_dokanders_notfier_on_off = 1;
					}
				}
			}
			$variation_class = '';
			if ( $variation ) {
				$variation_id = $variation->get_id();
				$variation_class = "stock_notifier-subscribe-form_$stock_notifier_random_code . '-' . $variation_id";
			} else {
				$variation_id = 0;
			}
			if ( $check_guest_visibility && $check_member_visibility && ( $this->is_viewable( $product_id, $variation_id ) && $this->is_viewable_for_category( $product_id ) ) && $this->visibility_on_regular_or_sale( $product, $variation ) && $this->is_viewable_for_product_tag( $product_id ) && $get_dokanders_notfier_on_off ) {
				do_action( 'stock_notifier_instock_before_subscribe_form' );
				$get_placeholder         = get_option( 'stock_notifier_frontent_form_placeholder' );
				$get_button_label        = get_option( 'stock_notifier_frontent_form_button' );
				$placeholder             = isset( $get_placeholder ) && '' != $get_placeholder ? $get_placeholder : __( 'Enter Your Whatsapp Number.', 'stock-notifier' );
				$button_label            = isset( $get_button_label ) && '' != $get_button_label ? $get_button_label : __( 'Request stock', 'stock-notifier' );
				if ( is_user_logged_in() ) {
					$whatsapp_number = get_the_author_meta( 'stock_notifier_whatsapp_number', get_current_user_id() );
				} else {
					$whatsapp_number = '';
				}
				$button_color      = get_option( 'stock_notifier_button_color', '#4caf50' );
				$border_color      = get_option( 'stock_notifier_button_border_color', '#fff' );
				$button_text_color = get_option( 'stock_notifier_button_text_color', '#fff' );
				// Success message.
				?>
				<div class="stock_notifier_main_form_wrap">
					<?php
					$this->stock_notifier_success_message();
					?>
					<div class="stock_notifier_tooltip stock_notifier_btn_wrap" id="stock_notifier_tooltip">
						<p class="stock_notifier_button_ button" style="background-color:<?php echo esc_attr( $button_color ); ?>;border: 1px solid <?php echo esc_attr( $border_color ); ?>;color:<?php echo esc_attr( $button_text_color ); ?>;margin:auto;"><?php echo esc_html( $button_label ); ?></p>
						<span class="stock_notifier_tooltip_wrap">
							<span style="display: none" class="stock_notifier_tooltip_tooltiptext stock_notifier_popup_wrap">
								<div id="stock_notifier_main_form" style="border-radius:10px;" class="stock_notifier-subscribe-form <?php echo esc_attr( $variation_class ); ?>">
									<div class="panel panel-primary stock_notifier-panel-primary">
										<div class="panel-heading stock_notifier-panel-heading">
											<div class="inwpstock_output"></div>
										</div>
										<div class="panel-body stock_notifier-panel-body">               
											<div class="stock_notifier_intel_bg">
												<form id="stock_notifier_enter_number">
													<div class="form-group center-block">
														<input type="hidden" class="stock_notifier-product-id" id="stock_notifier-product-id" name="stock_notifier-product-id" value="<?php echo esc_attr( $product_id ); ?>" />
														<input type="hidden" class="stock_notifier-rand-code" id="stock_notifier-rand-code" name="stock_notifier-rand-code" value="<?php echo esc_attr( $stock_notifier_random_code ); ?>" />
														<span class="stock_notifier_mobile-text" ><i class="fa fa-whatsapp"></i><?php esc_html_e( $placeholder, 'stock-notifer' ); //phpcs:ignore ?></span>
														<input type="hidden" name="country_code" id="country_code_<?php echo esc_attr( $stock_notifier_random_code ); ?>" class="stock_notifier_country_code" >
														<input type="tel" id="stock_notifier_phone" name="stock_notifier_phone" class="inwpstock_whatsapp_sms" value="<?php echo esc_attr( $whatsapp_number ); ?>" >
															<?php do_action( 'stock_notifier_instock_after_whatsapp_field', $product_id, $variation_id ); ?>
														<input type="hidden" class="stock_notifier-variation-id" name="stock_notifier-variation-id" value="<?php echo esc_attr( $variation_id ); ?>"/>
														<span id="inwpstock_button" style="background-color:<?php echo esc_attr( $button_color ); ?>;border: 2px solid <?php echo esc_attr( $border_color ); ?>" class="dashicons dashicons-saved stock_notifier_submit"></span>
													</div>
												</form>
											</div> 
										</div>
									</div>
								</div>
							</span>
						</span>
					</div>
				</div>
				<?php
			} else {
				if ( '1' == $loop_active ) {
					return $html;
				} else {
					return '';
				}
			}
			return ob_get_clean();
		}
		/**
		 * Stock Notifier Success message
		 *
		 * @version 1.0.0
		 */
		public function stock_notifier_success_message() {
			?>
			<div class="stock_notifier_tooltip_subscribed stock_notifier_subscribe_wrap" id="stock_notifier_tooltip_subscribed" style="display: none">
				<p class="btn-subcribe button stock_notifier_subscribe_btn"><?php esc_html_e( 'Subscribed!', 'stock-notifier' ); ?></p>
				<span class="stock_notifier_tooltip_tooltiptext_subscribed stock_notifier_subscribe_popup_wrap">
					<div id="stock_notifier_main_form_text" style="border-radius:10px;" class="stock_notifier-subscribe-form_text">
						<div class="panel panel-primary stock_notifier-panel-primary">
							<div class="panel-heading stock_notifier-panel-heading">
								<div class="inwpstock_output"></div>
							</div>
							<div class="panel-body stock_notifier-panel-body">
								<div class="stock_notifier_intel_bg">
									<div class="stock_notifier_successfully_head" id="stock_notifier_successfully_head"></div>
								</div>
							</div>
						</div>
					</div>
				</span>
			</div>
			<?php
		}
		/**
		 * Display in variation product request stock button
		 *
		 * @param string $atts default attributes.
		 * @param object $product all product.
		 * @param object $variation variation product.
		 *
		 * @version 1.0.0
		 */
		public function display_in_variation( $atts, $product, $variation ) {
			$get_stock                 = $atts['availability_html'];
			$atts['availability_html'] = $get_stock . $this->display_subscribe_box( $product, $variation );
			return $atts;
		}
		/**
		 * Enable disabled variation dropdown
		 *
		 * @param int   $active default 0.
		 * @param array $variation variation product.
		 *
		 * @version 1.0.0
		 */
		public function enable_disabled_variation_dropdown( $active, $variation ) {
			$get_disabled_variation    = get_option( 'stock_notifier_ignore_disabled_variation' );
			$ignore_disabled_variation = isset( $get_disabled_variation ) && '1' == $get_disabled_variation ? true : false;
			if ( ! $ignore_disabled_variation ) {
				$active = true;
			}
			return $active;
		}
		/**
		 * Disable specific product visibility.
		 *
		 * @param int $product_id all product id.
		 * @param int $variation_id variation product id.
		 *
		 * @version 1.0.0
		 */
		public function is_viewable( $product_id, $variation_id = 0 ) {
			$stock_notifier_specific_products = get_option( 'stock_notifier_specific_products' );
			$specific_product_visibility      = get_option( 'stock_notifier_specific_products_visibility' );
			$selected_products                = isset( $stock_notifier_specific_products ) ? $stock_notifier_specific_products : [];
			$product_visibility_mode          = isset( $specific_product_visibility ) ? $specific_product_visibility : '';
			if ( ( is_array( $selected_products ) && ! empty( $selected_products ) ) && '' != $product_visibility_mode ) {
				if ( $variation_id > 0 ) {
					if ( '0' == $product_visibility_mode && ! in_array( $variation_id, $selected_products ) ) {
						return false;
					} elseif ( '1' == $product_visibility_mode && in_array( $variation_id, $selected_products ) ) {
						return false;
					}
				} else {
					if ( '0' == $product_visibility_mode && ! in_array( $product_id, $selected_products ) ) {
						return false;
					} elseif ( '1' == $product_visibility_mode && in_array( $product_id, $selected_products ) ) {
						return false;
					}
				}
			}
			return true;
		}
		/**
		 * Disable product visibility for specific categories.
		 *
		 * @param int $product_id product id.
		 *
		 * @version 1.0.0
		 */
		public function is_viewable_for_category( $product_id ) {
			$stock_notifier_specific_categories            = get_option( 'stock_notifier_specific_categories' );
			$stock_notifier_specific_categories_visibility = get_option( 'stock_notifier_specific_categories_visibility' );
			$selected_categories                           = isset( $stock_notifier_specific_categories ) ? $stock_notifier_specific_categories : [];
			$categories_visibility_mode                    = isset( $stock_notifier_specific_categories_visibility ) ? $stock_notifier_specific_categories_visibility : '';
			if ( ( is_array( $selected_categories ) && ! empty( $selected_categories ) ) && '' != $categories_visibility_mode ) {
				$terms = wp_get_post_terms( $product_id, [ 'product_cat' ], [ 'fields' => 'slugs' ] );
				if ( $terms ) {
					$intersect = array_intersect( $terms, $selected_categories );
					if ( '0' == $categories_visibility_mode && empty( $intersect ) ) {
						return false;
					} elseif ( '1' == $categories_visibility_mode && ! empty( $intersect ) ) {
						return false;
					}
				}
			}
			return true;
		}
		/**
		 * Disable product visibility for specific Tags.
		 *
		 * @param int $product_id product id.
		 *
		 * @version 1.0.0
		 */
		public function is_viewable_for_product_tag( $product_id ) {
			$all_product_tags         = get_option( 'stock_notifier_specific_tags' );
			$specific_tags_visibility = get_option( 'stock_notifier_specific_tags_visibility' );
			$selected_tags            = isset( $all_product_tags ) ? $all_product_tags : [];
			$tags_visibility_mode     = isset( $specific_tags_visibility ) ? $specific_tags_visibility : '';

			if ( ( is_array( $selected_tags ) && ! empty( $selected_tags ) ) && '' != $tags_visibility_mode ) {
				$terms = wp_get_post_terms( $product_id, [ 'product_tag' ], [ 'fields' => 'slugs' ] );
				if ( $terms ) {
					$intersect = array_intersect( $terms, $selected_tags );
					if ( '0' == $tags_visibility_mode && empty( $intersect ) ) {
						return false;
					} elseif ( '1' == $tags_visibility_mode && ! empty( $intersect ) ) {
						return false;
					}
				} elseif ( empty( $terms ) && '0' == $tags_visibility_mode ) {
					return false;
				}
			}
			return true;
		}
		/**
		 * Disable regular sale product and current sale product visibility
		 *
		 * @param Object $product all product.
		 * @param Object $variation variation product.
		 */
		public function visibility_on_regular_or_sale( $product, $variation ) {
			$get_on_sale      = get_option( 'stock_notifier_hide_subscribe_sale_product' );
			$get_on_regular   = get_option( 'stock_notifier_hide_subscribe_regular_product' );
			$hide_on_regular  = isset( $get_on_regular ) && '1' == $get_on_regular ? true : false;
			$hide_on_sale     = isset( $get_on_sale ) && '1' == $get_on_sale ? true : false;
			$check_is_on_sale = $variation ? $variation->is_on_sale() : $product->is_on_sale();
			$visibility       = ( ( $hide_on_regular && ! $check_is_on_sale ) || ( $hide_on_sale && $check_is_on_sale ) ) ? false : true;
			return $visibility;
		}
	}
	/**
	 * Kick out the __Contructor
	 *
	 * @version 1.0.0
	 */
	new Stock_Notifier_Frontend_Product();
}
