<?php
/**
 * Plugin Name: LGP-CCTA-Checkout-plugin
 * Description: Removes all billing fields from the WooCommerce checkout.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_filter( 'woocommerce_checkout_fields', 'nbc_remove_billing_fields' );

function nbc_remove_billing_fields( $fields ) {
    unset( $fields['billing'] );
    return $fields;
}