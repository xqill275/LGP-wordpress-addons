<?php
/**
 * Plugin Name: LGP Custom Woo Checkout
 * Description: Custom tweaks for the WooCommerce checkout page.
 * Version: 1.0
 * Author: Oliver Long
 */

defined('ABSPATH') || exit;

// Example: Add heading above artwork number inputs
add_action('woocommerce_checkout_before_order_notes', function() {
    time();
    echo '<h3 class="artwork-section-title">Artwork Number Sequence</h3>';
});
