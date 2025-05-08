<?php
if ( !defined( 'ABSPATH' ) ) exit;
?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        color: #222;
        margin: 10mm;
        font-size: 13px;
        line-height: 1.6;
    }

    h1, h3, h4 {
        margin: 0 0 6px;
        padding-bottom: 4px;
        font-weight: 600;
        border-bottom: 1px solid #ccc;
        font-size: 15px;
    }

    .order-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .branding {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }

    .company-logo img {
        max-height: 50px;
    }

    .company-info {
        text-align: right;
        font-size: 13px;
    }

    .company-name {
        font-size: 18px;
        font-weight: 700;
    }

    .info-notes, .addresses {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .order-info, .order-notes, .shipping-address, .billing-address {
        flex: 1;
        min-width: 200px;
    }

    .order-info ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .order-info li {
        margin-bottom: 4px;
    }

    .order-items table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .order-items th,
    .order-items td {
        padding: 6px 8px;
        border-bottom: 1px solid #ddd;
        vertical-align: top;
        text-align: left;
    }

    .product-name {
        font-weight: 600;
    }

    .extras {
        margin-top: 4px;
        font-size: 12px;
    }

    .extras dt {
        font-weight: 600;
        display: inline;
    }

    .extras dd {
        display: inline;
        margin: 0 10px 0 5px;
    }

    .total-name {
        font-weight: bold;
        padding-top: 8px;
        text-align: right;
        font-size: 13px;
    }


    address span {
        display: block;
        margin-left: 0;
        padding-left: 0;
    }

    .order-shipping-footer {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        font-size: 12px;
        border-top: 1px dashed #aaa;
        padding-top: 10px;
        margin-top: 20px;
    }

    .no-shipping-address .shipping-address {
        display: none;
    }
</style>

<div class="order-container">

    <div class="branding">
        <div class="company-logo">
            <?php if ( wcdn_get_company_logo_id() ) : ?>
                <?php wcdn_company_logo(); ?>
            <?php endif; ?>
        </div>
        <div class="company-info">
            <?php if ( !wcdn_get_company_logo_id() ) : ?>
                <div class="company-name"><?php wcdn_company_name(); ?></div>
            <?php endif; ?>
            <div class="company-address"><?php wcdn_company_info(); ?></div>
        </div>
    </div>

    <?php do_action( 'wcdn_after_branding', $order ); ?>

    <div class="info-notes">
        <div class="order-info">
            <h3><?php _e( 'Order Info', 'woocommerce-delivery-notes' ); ?></h3>
            <ul>
                <li><strong><?php _e( 'Customer Name', 'woocommerce-delivery-notes' ); ?>:</strong>
                    <?php 
                        echo $order->get_shipping_first_name() ? 
                            esc_html( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ) : 
                            esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
                    ?>
                </li>
                <?php 
                    $fields = apply_filters( 'wcdn_order_info_fields', wcdn_get_order_info( $order ), $order ); 
                    foreach ( $fields as $field ) : 
                ?>
                    <li><strong><?php echo $field['label']; ?>:</strong> <?php echo $field['value']; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php do_action( 'wcdn_after_info', $order ); ?>
        </div>

        <div class="order-notes">
            <?php if ( wcdn_has_customer_notes( $order ) ) : ?>
                <h4><?php _e( 'Artwork Title', 'woocommerce-delivery-notes' ); ?></h4>
                <?php wcdn_customer_notes( $order ); ?>
            <?php endif; ?>
            <?php do_action( 'wcdn_after_notes', $order ); ?>
        </div>
    </div>

    <div class="order-items">
        <table>
            <tbody>
                <?php if ( count( $order->get_items() ) > 0 ) : ?>
                    <?php foreach ( $order->get_items() as $item ) :
                        $product = apply_filters( 'wcdn_order_item_product', $order->get_product_from_item( $item ), $item );
                        $item_meta = new WC_Order_Item_Meta( $item['item_meta'], $product );
                    ?>
                        <tr>
                            <td class="product-name">
                                <?php do_action( 'wcdn_order_item_before', $product, $order ); ?>
                                <?php echo apply_filters( 'wcdn_order_item_name', $item['name'], $item ); ?>
                                <div style="font-size:11px;"><?php $item_meta->display(); ?></div> 
                                <dl class="extras">
                                    <?php if ( $product && $product->exists() && $product->is_downloadable() && $order->is_download_permitted() ) : ?>
                                        <dt><?php _e( 'Download:', 'woocommerce-delivery-notes' ); ?></dt>
                                        <dd><?php printf( __( '%s Files', 'woocommerce-delivery-notes' ), count( $order->get_item_downloads( $item ) ) ); ?></dd>
                                    <?php endif; ?>
                                    <?php foreach ( apply_filters( 'wcdn_order_item_fields', array(), $product, $order ) as $field ) : ?>
                                        <dt><?php echo $field['label']; ?></dt><dd><?php echo $field['value']; ?></dd>
                                    <?php endforeach; ?>
                                </dl>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ( $totals = $order->get_order_item_totals() ) : ?>
                    <?php foreach ( $totals as $total ) :
                        $label = strip_tags( $total['label'] );
                        if ( wcdn_get_template_type() === 'delivery-note' && (
                            strpos( $label, 'Artwork Upload' ) !== false ||
                            in_array( $label, [ 'Subtotal', 'Shipping', 'VAT', 'Total' ] )
                        ) ) continue;
                    ?>
                        <tr><td class="total-name"><?php echo $total['label']; ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php do_action( 'wcdn_after_items', $order ); ?>
            </tbody>
        </table>
    </div>

    <div class="addresses<?php if ( !wcdn_has_shipping_address( $order ) ) : ?> no-shipping-address<?php endif; ?>">
        <div class="shipping-address">
            <h3><?php _e( 'Shipping Address', 'woocommerce-delivery-notes' ); ?></h3>
            <address>
                <?php 
                    echo $order->get_formatted_shipping_address() ?: __( 'N/A', 'woocommerce-delivery-notes' );
                ?>
            </address>
        </div>
    </div>

    <?php do_action( 'wcdn_after_addresses', $order ); ?>

    <div class="order-shipping-footer">
        <span>Printed by ___________</span>
        <span>Guillotine Operator ___________</span>
        <span>Collated by ___________</span>
        <span>Padded by ___________</span>
        <span>Drilled by ___________</span>
        <span>Packed by ___________</span>
        <span>Consignment No. ____________________________</span>
        <span>Despatch Date ______ / _____ / 2025</span>
    </div>

</div>
