<?php
/**
 * Plugin Name: PDF Upload Validator for Artwork Upload
 * Description: Validates that only PDF files can be uploaded through the WooCommerce Checkout Add-Ons upload field.
 * Version: 1.2
 * Author: Oliver Long
 */

defined('ABSPATH') || exit;

/**
 * Enqueue JavaScript on the checkout page
 */
add_action('wp_enqueue_scripts', 'lgp_enqueue_pdf_validator');
function lgp_enqueue_pdf_validator() {
    if (is_checkout()) {
        // Enqueue the PDF.js library
        wp_enqueue_script(
            'pdf-js',
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js',
            array(),
            null,
            true
        );
        wp_enqueue_script(
            'lgp-upload-check',
            plugin_dir_url(__FILE__) . 'js/upload-check.js',
            array('jquery'),
            time(),
            true
        );
        wp_enqueue_script(
            'pdf-password-check',
            plugin_dir_url(__FILE__) . 'js/pdf-password-check.js',
            array(),
            time(),
            true
        );
    }
}

/**
 * Server-side validation for uploaded file
 */
add_filter('woocommerce_checkout_add_ons_validate_file_upload', 'lgp_block_non_pdf_files', 10, 3);

function lgp_block_non_pdf_files($valid, $file, $field) {
    if ($field['name'] === 'db7e631') { 
        $filetype = wp_check_filetype($file['name']);
        if (strtolower($filetype['ext']) !== 'pdf') {
            wc_add_notice(__('Only PDF files are allowed for artwork uploads.', 'lgp'), 'error');
            return false;
        }
    }
    return $valid;
}
