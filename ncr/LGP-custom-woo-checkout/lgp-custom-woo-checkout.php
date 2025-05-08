<?php
/**
 * Plugin Name: LGP Custom Woo Checkout
 * Description: Custom tweaks for the WooCommerce checkout page.
 * Version: 1.2
 * Author: Oliver Long
 */

defined('ABSPATH') || exit;

// Enqueue custom CSS and JS on checkout page
add_action('wp_enqueue_scripts', 'lgp_enqueue_custom_checkout_assets');
function lgp_enqueue_custom_checkout_assets() {
    if (is_checkout()) {
        // CSS
        wp_enqueue_style(
            'lgp-custom-checkout-styles',
            plugin_dir_url(__FILE__) . 'css/custom-css-stylings.css',
            array(),
            '1.0'
        );

        // JS
        wp_enqueue_script(
            'lgp-custom-checkout-js',
            plugin_dir_url(__FILE__) . 'js/lgp-custom-checkout.js',
            array('jquery'),
            '1.0',
            true
        );

        // Localize AJAX URL
        wp_localize_script('lgp-custom-checkout-js', 'lgp_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}

// AJAX handler for custom heading
add_action('wp_ajax_lgp_load_number_heading', 'lgp_load_number_heading');
add_action('wp_ajax_nopriv_lgp_load_number_heading', 'lgp_load_number_heading');
function lgp_load_number_heading() {
    echo '
    <div class="col-md-4 col-xs-12">
        <h3>Number Sequence</h3>
    </div>
    <div class="nm-checkout-form col-md-8 col-xs-12">
        <!-- Optional: add input here -->
    </div>
    ';
    wp_die();
}
