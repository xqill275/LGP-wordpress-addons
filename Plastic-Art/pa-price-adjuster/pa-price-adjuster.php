<?php
/**
 * Plugin Name: PA Price Adjuster
 * Description: Admin UI for increasing the price of products by a certain amount in CPO product forms
 * Version: 1.4
 * Author: Oliver Long
 */

add_action('admin_menu', 'pa_price_adjuster_menu');
add_action('admin_enqueue_scripts', 'pa_price_adjuster_admin_scripts');
add_action('wp_enqueue_scripts', 'pa_price_adjuster_frontend_scripts');

function pa_price_adjuster_menu() {
    add_menu_page(
        'PA Price Adjuster',
        'PA Price Adjuster',
        'manage_options',
        'pa-price-adjuster',
        'pa_price_adjuster_page',
        'dashicons-money-alt',
        26
    );
}

function pa_price_adjuster_page() {
    if (isset($_POST['pa_increase_price'])) {
        $matt = floatval($_POST['matt_increase']);
        $gloss = floatval($_POST['gloss_increase']);
        update_option('pa_price_adjuster_matt', $matt);
        update_option('pa_price_adjuster_gloss', $gloss);
        echo '<div class="updated"><p>Prices updated successfully.</p></div>';
    }

    $matt = get_option('pa_price_adjuster_matt', '');
    $gloss = get_option('pa_price_adjuster_gloss', '');

    ?>
    <div class="wrap">
        <h1>PA Price Adjuster</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="matt_increase">Matt Price Increase (%)</label></th>
                    <td><input type="number" step="0.01" name="matt_increase" id="matt_increase" value="<?php echo esc_attr($matt); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="gloss_increase">Gloss Price Increase (%)</label></th>
                    <td><input type="number" step="0.01" name="gloss_increase" id="gloss_increase" value="<?php echo esc_attr($gloss); ?>" /></td>
                </tr>
            </table>
            <p><input type="submit" name="pa_increase_price" class="button button-primary" value="Increase Price" /></p>
        </form>
    </div>
    <?php
}

function pa_price_adjuster_frontend_scripts() {
    $site_type = strpos($_SERVER['REQUEST_URI'], 'matt') !== false ? 'matt' : 'gloss';
    $adjustment = $site_type === 'matt'
        ? floatval(get_option('pa_price_adjuster_matt', 0))
        : floatval(get_option('pa_price_adjuster_gloss', 0));

    wp_enqueue_script(
        'pa-price-adjuster',
        plugin_dir_url(__FILE__) . 'js/price-adjuster.js',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('pa-price-adjuster', 'paAdjuster', [
        'percent' => $adjustment,
    ]);
}

function pa_price_adjuster_admin_scripts() {}

add_filter('uni_cpo_in_cart_calculated_price', 'pa_adjust_uni_cpo_cart_price', 10, 3);
function pa_adjust_uni_cpo_cart_price($price_calculated, $product, $filtered_form_data) {
    if (!is_a($product, 'WC_Product')) {
        return $price_calculated;
    }

    $slug = $product->get_slug(); // e.g. 'encapsulation-matt-film' or 'encapsulation'
    $is_matt = strpos($slug, 'matt') !== false;

    $adjustment = $is_matt
        ? floatval(get_option('pa_price_adjuster_matt', 0))
        : floatval(get_option('pa_price_adjuster_gloss', 0));

    $adjusted_price = $price_calculated + ($price_calculated * ($adjustment / 100));
    return $adjusted_price;
}

