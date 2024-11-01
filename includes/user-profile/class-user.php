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
if ( ! class_exists( 'Stock_Notifier_Whatsapp_Number' ) ) {
	/**
	 * Add WhatsApp number input fields in user profile.
	 *
	 * @varsion 1.0.0
	 */
	class Stock_Notifier_Whatsapp_Number {
		/**
		 * Calling method
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'show_user_profile', [ $this, 'extra_profile_fields' ], 10 );
			add_action( 'edit_user_profile', [ $this, 'extra_profile_fields' ], 10 );
			add_action( 'personal_options_update', [ $this, 'save_extra_profile_fields' ] );
			add_action( 'edit_user_profile_update', [ $this, 'save_extra_profile_fields' ] );
		}
		/**
		 * Add Extra WhatsApp Number fields in user profile.
		 *
		 * @param Object $user current user.
		 *
		 * @version 1.0.0
		 */
		public function extra_profile_fields( $user ) {
			?>
			<h3><?php esc_html_e( 'WhatsApp number (Stock Notifier )', 'stock-notifier' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="whatsapp_number"><?php esc_html_e( 'WhatsApp number ', 'stock-notifier' ); ?></label></th>
					<td>
						<input type="hidden" name="country_code" id="country_code" class="stock_notifier_country_code">
						<input  type="tel" id="stock_notifier_phone" name="stock_notifier_phone" class="inwpstock_whatsapp_sms" value="<?php echo esc_attr( get_the_author_meta( 'stock_notifier_whatsapp_number', $user->ID ) ); ?>">
						<span id="valid-msg" class="hide">✓ Valid</span>
						<span style="size:10px" id="error-msg" class="hide"></span>
					</td>
				</tr>
				<?php wp_nonce_field( 'stock_notifier_fields_nonce', 'stock_notifier_fields_nonce' ); ?>
			</table>
			<?php
		}
		/**
		 * Add Extra WhatsApp Number fields in user profile.
		 *
		 * @param int $user_id current user id.
		 *
		 * @version 1.0.0
		 */
		public function save_extra_profile_fields( $user_id ) {
			if ( ! current_user_can( 'edit_posts', $user_id ) ) {
				return false;
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			if ( isset($_POST['stock_notifier_fields_nonce']) && ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['stock_notifier_fields_nonce']) ), 'stock_notifier_fields_nonce' ) ) {
				die( 'Invalid nonce' );
			}
			$code               = isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '+1';
			$phone_code          = isset( $_POST['stock_notifier_phone'] ) ? sanitize_text_field( wp_unslash($_POST['stock_notifier_phone']) ) : '';
			$phone               = $code . $phone_code;
			$phone_number        = trim( $phone );
			$inwp_number         = str_replace( '-', '', $phone_number );
			$phone_number_length = strlen( $inwp_number );

			if ( $phone_number_length > 4 && $phone_number_length < 16 ) {
				update_user_meta( $user_id, 'stock_notifier_whatsapp_number', $phone_number );
			}

		}
	}
	/**
	 * Kick out the __construct.
	 *
	 * @version 1.0.0
	 */
	new Stock_Notifier_Whatsapp_Number();
}
