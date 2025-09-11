<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );

if ( $has_orders ) : ?>

    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th class="woocommerce-orders-table__header"><span class="nobr"><?php esc_html_e( 'Order', 'woocommerce' ); ?></span></th>
                <th class="woocommerce-orders-table__header"><span class="nobr"><?php esc_html_e( 'Date', 'woocommerce' ); ?></span></th>
                <th class="woocommerce-orders-table__header"><span class="nobr"><?php esc_html_e( 'Status', 'woocommerce' ); ?></span></th>
                <th class="woocommerce-orders-table__header"><span class="nobr"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span></th>
                <th class="woocommerce-orders-table__header"><span class="nobr"><?php esc_html_e( 'Actions', 'woocommerce' ); ?></span></th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ( $customer_orders->orders as $customer_order ) :
            $order = wc_get_order( $customer_order );
            $item_count = $order->get_item_count();
            ?>
            <tr class="woocommerce-orders-table__row order">
                <td class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'Order', 'woocommerce' ); ?>">
                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
                        <?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number(); ?>
                    </a>
                </td>
                <td class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'Date', 'woocommerce' ); ?>">
                    <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
                </td>
                <td class="woocommerce-orders-table__cell order-status-<?php echo esc_attr( $order->get_status() ); ?>" data-title="<?php esc_attr_e( 'Status', 'woocommerce' ); ?>">
                    <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                </td>
                <td class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
                    <?php
                    printf(
                        /* translators: 1: formatted order total 2: total order items */
                        esc_html__( '%1$s for %2$s item(s)', 'woocommerce' ),
                        $order->get_formatted_order_total(),
                        $item_count
                    );
                    ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell--actions" data-title="<?php esc_attr_e( 'Actions', 'woocommerce' ); ?>">
                    <?php
                    $order_id = $order->get_id();
                    $actions = wc_get_account_orders_actions( $order );

                    // Add Cancel button if order is processing
                    if ( $order->get_status() === 'processing' ) {
                        $cancel_url = $order->get_cancel_order_url();
                        $redirect_url = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
                        $cancel_url = add_query_arg( 'redirect', urlencode( $redirect_url ), $cancel_url );

                        $actions['cancel'] = array(
                            'url'        => esc_url( $cancel_url ),
                            'name'       => __( 'Cancel', 'woocommerce' ),
                            'aria-label' => sprintf( __( 'Cancel order number %s', 'woocommerce' ), $order->get_order_number() ),
                            'data-order-id' => $order_id,
                        );
                    }

                    if ( ! empty( $actions ) ) {
                        foreach ( $actions as $key => $action ) {
                            $extra_attrs = '';
                            if ( $key === 'cancel' ) {
                                $extra_attrs = ' data-cancel="true" data-url="' . esc_url( $action['url'] ) . '"';
                            }

                            echo '<a href="' . ( $key === 'cancel' ? '#' : esc_url( $action['url'] ) ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '"' . $extra_attrs . ' aria-label="' . esc_attr( $action['aria-label'] ?? $action['name'] ) . '">' . esc_html( $action['name'] ) . '</a>';
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

    <?php if ( 1 < $customer_orders->max_num_pages ) : ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"
               href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
                <?php esc_html_e( 'Previous', 'woocommerce' ); ?>
            </a>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"
               href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
                <?php esc_html_e( 'Next', 'woocommerce' ); ?>
            </a>
        </div>
    <?php endif; ?>

<?php else : ?>
    <p><?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?></p>
    <a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
        <?php esc_html_e( 'Browse products', 'woocommerce' ); ?>
    </a>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-cancel="true"]').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const url = button.getAttribute('data-url');
            const confirmCancel = confirm("Are you sure you want to cancel this order?");
            if (confirmCancel && url) {
                window.location.href = url;
            }
        });
    });
});
</script>
