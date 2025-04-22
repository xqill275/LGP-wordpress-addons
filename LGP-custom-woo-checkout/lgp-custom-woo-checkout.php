<?php
/**
 * Plugin Name: LGP Custom Woo Checkout
 * Description: Custom tweaks for the WooCommerce checkout page.
 * Version: 1.0
 * Author: Oliver Long
 */

defined('ABSPATH') || exit;


// Enqueue the custom CSS for checkout page
add_action( 'wp_enqueue_scripts', 'lgp_enqueue_custom_checkout_styles' );
function lgp_enqueue_custom_checkout_styles() {
    if ( is_checkout() ) {
        wp_enqueue_style(
            'lgp-custom-checkout-styles',
            plugin_dir_url( __FILE__ ) . 'css/custom-css-stylings.css',
            array(), // No dependencies
            '1.0',    // Version
            'all'     // Media type
        );
    }
}

add_action('woocommerce_checkout_after_customer_details', function() {
    echo '<h3 class="artwork-section-title">Artwork Number Sequence</h3>';
});