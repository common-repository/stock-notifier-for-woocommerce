<?php
// don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit(1);
}
/**
 * WooCommerce Popup template
 *
 * @package STOCKNOTIFIER
 */
?>
<div class="stock_notifier_bg">
		<img src="<?php echo esc_url(STOCKNOTIFIER_URL . '/assets/img/settings.PNG'); ?>" alt="">
	</div>	
<div class="stock_notifier_popup_container popup_active" id="stock_notifier_pro_popup">
	<div class="popup_head_and_content">
		<div class="popupContent">
			<div class="stock_notifier_top_bg">
				<figure class="media">
						<img src="<?php echo esc_url(STOCKNOTIFIER_URL . '/assets/img/woo.svg'); ?>" alt="">
				</figure>
				<div class="stock_notifier_popup_heading">
				<h3 class="title" x-text="(is_woocommerce_installed ? '<?php esc_html_e( 'Activate', 'stock-notifier' ); ?>' : '<?php esc_html_e( 'Install and activate', 'stock-notifier' ); ?>') + ' WooCommerce'"><?php esc_html_e( 'Install and activate WooCommerce', 'stock-notifier' ); ?></h3>

				<div class="text" >
					<p><?php esc_html_e( 'The plugin only works when WooCommerce is', 'stock-notifier' ); ?> <span ><strong><?php esc_html_e( 'installed and', 'stock-notifier' ); ?></strong></span>
					<strong><?php esc_html_e( 'activated', 'stock-notifier' ); ?></strong></p>
				</div>
				<a  href="#"  class="stock_notifier-btn flex-button" x-html="(is_woocommerce_installed ? '<?php esc_html_e( 'Activate', 'stock-notifier' ); ?>' : '<?php esc_html_e( 'Install & activate', 'stock-notifier' ); ?>')"></a>
				</div>
			</div>
			<div class="stock_notifier_button">
				<button type="button" class="stock_notifier_bottom_bg" ><?php echo esc_html( 'Install & activate','stock-notifier'); ?></a>
			</div>
		</div>
	</div>
</div>
