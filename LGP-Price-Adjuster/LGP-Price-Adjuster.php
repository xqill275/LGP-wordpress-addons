<?php
/**
 * Plugin Name: LGP Price Adjuster
 * Description: Admin UI for controlling the "increase" Product field in multiple Gravity Forms.
 * Version: 1.4
 * Author: Oliver Long
 */

add_action('admin_menu', 'lgp_price_adjuster_menu');

function lgp_price_adjuster_menu() {
    add_menu_page(
        'LGP Price Adjuster',
        'LGP Price Adjuster',
        'manage_options',
        'lgp-price-adjuster',
        'lgp_price_adjuster_page',
        'dashicons-money-alt',
        26
    );
}

function lgp_price_adjuster_page() {
    // Grouped by type
    $forms_grouped = [
        'Books' => [31, 22, 34, 33, 32],
        'Pads'  => [47, 26, 27, 30, 28, 29], // if you want to add a new form, just add the form id here. make sure the form has the "increase" field
        'Sets'  => [46, 1, 25, 23, 20, 24]
    ];
    $increase_field_label = 'increase';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('lgp_price_adjuster_save');

        foreach ($forms_grouped as $group => $form_ids) {
            foreach ($form_ids as $form_id) {
                if (isset($_POST['lgp_increase_value_' . $form_id])) {
                    $new_value = sanitize_text_field($_POST['lgp_increase_value_' . $form_id]);
                    $form = GFAPI::get_form($form_id);

                    if ($form && !empty($form['fields'])) {
                        foreach ($form['fields'] as &$field) {
                            if ($field->type === 'product' && ($field->label === $increase_field_label || $field->adminLabel === $increase_field_label)) {
                                $field->basePrice = $new_value;
                                break;
                            }
                        }
                        GFAPI::update_form($form);
                    }
                }
            }
        }
        echo '<div class="updated"><p>Increase values updated.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>LGP Price Adjuster</h1>
        <form method="post">
            <?php wp_nonce_field('lgp_price_adjuster_save'); ?>

            <style>
                .accordion-section { margin-bottom: 10px; border: 1px solid #ccc; border-radius: 6px; }
                .accordion-header { padding: 10px; cursor: pointer; background: #f1f1f1; font-weight: bold; }
                .accordion-content { display: none; padding: 10px; background: #fff; }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.accordion-header').forEach(header => {
                        header.addEventListener('click', () => {
                            const content = header.nextElementSibling;
                            content.style.display = content.style.display === 'block' ? 'none' : 'block';
                        });
                    });
                });
            </script>

            <?php foreach ($forms_grouped as $group => $form_ids): ?>
                <div class="accordion-section">
                    <div class="accordion-header"><?php echo esc_html($group); ?></div>
                    <div class="accordion-content">
                        <table class="form-table">
                        <?php foreach ($form_ids as $form_id): ?>
                            <?php
                            $form = GFAPI::get_form($form_id);
                            $form_title = $form['title'];
                            $default = '';
                            foreach ($form['fields'] as $field) {
                                if ($field->type === 'product' && ($field->label === $increase_field_label || $field->adminLabel === $increase_field_label)) {
                                    $default = $field->basePrice;
                                    break;
                                }
                            }
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="lgp_increase_value_<?php echo esc_attr($form_id); ?>">
                                        <?php echo esc_html($form_title); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="lgp_increase_value_<?php echo esc_attr($form_id); ?>"
                                           id="lgp_increase_value_<?php echo esc_attr($form_id); ?>"
                                           value="<?php echo esc_attr($default); ?>" class="regular-text" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php submit_button('Update Increase Values'); ?>
        </form>
    </div>
    <?php
}
