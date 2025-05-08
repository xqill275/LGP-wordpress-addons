<?php
/**
 * Custom Checkout - Billing Removed (uses shipping only)
 *
 * Place this in yourtheme/woocommerce/checkout/form-billing.php
 *
 * @package WooCommerce\Templates
 * @version Custom
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Hidden Billing Section -->
<div class="woocommerce-billing-fields" style="display:none;">
	<?php
		// Ensure billing is populated with shipping values behind the scenes
		$billing_fields = $checkout->get_checkout_fields( 'billing' );
		foreach ( $billing_fields as $key => $field ) {
			// If a matching shipping field exists, populate billing with its value
			if ( isset( $checkout->checkout_fields['shipping'][ str_replace( 'billing_', 'shipping_', $key ) ] ) ) {
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $checkout->get_value( str_replace( 'billing_', 'shipping_', $key ) ) ) . '">';
			} else {
				// Fallback to blank
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="">';
			}
		}
	?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount"
					<?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?>
					type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
				</label>
			</p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
		<!-- Custom billing template loaded -->
	</div>
<?php endif; ?>
