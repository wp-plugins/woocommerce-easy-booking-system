<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Order' ) ) :

class WCEB_Order {

    public function __construct() {
        add_action('woocommerce_before_order_itemmeta', array($this, 'easy_booking_order_display_product_dates'), 10, 3);
        add_action('wp_ajax_ebs_sku_order_update_product_dates', array( $this, 'easy_booking_order_update_product_dates'));
        add_action('woocommerce_saved_order_items', array($this, 'easy_booking_update_order_product'), 10, 2);
    }

    /**
    *
    * Displays booked dates and a picker form on the order page
    *
    * @param int $item_id
    * @param object $item
    * @param WC_Product $_product
    *
    **/
    public function easy_booking_order_display_product_dates( $item_id, $item, $_product ) {
        $product_id = intval( $item['product_id'] );

        // Is product bookable ?
        $is_bookable = get_post_meta( $product_id, '_booking_option', true );
        $settings = get_option( 'easy_booking_settings' );

        $start_date_set = wc_get_order_item_meta( $item_id, '_ebs_start_format' );
        $end_date_set = wc_get_order_item_meta( $item_id, '_ebs_end_format' );

        $start_date_text = $settings['easy_booking_start_date_text'];
        $end_date_text = $settings['easy_booking_end_date_text'];

        if ( ! empty( $start_date_set ) && ! empty( $end_date_set ) ) {

            // If ordered before 1.4, dates will be displayed in English
            $start_date = empty( $item['ebs_start_display'] ) ? date('d F Y', strtotime( $start_date_set ) ) : $item['ebs_start_display'];
            $end_date = empty( $item['ebs_end_display'] ) ? date('d F Y', strtotime( $end_date_set ) ) : $item['ebs_end_display'];

            // Please do not remove inputs' attributes (classes, ids, etc.)
            echo '<div class="view">';
            echo apply_filters( 'easy_booking_order_booked_meta',
                '<p>
                        <label for="start_date" style="font-weight: bold;">' . esc_html__( $start_date_text ) . ' : </label>
                        <span class="wc_ebs_order_date">' . $start_date . '</span>
                    </p>
                    <p>
                        <label for="end_date" style="font-weight: bold;">' . esc_html__( $end_date_text ) . ' : </label>
                        <span class="wc_ebs_order_date">' . $end_date . '</span>
                    </p>',
                $start_date_text, $end_date_text, $start_date, $end_date );

            echo '</div>';

        }

        $u_start_date = empty( $start_date_set ) ? '' : strtotime( $start_date_set ) * 1000;
        $u_end_date = empty( $end_date_set ) ? '' : strtotime( $end_date_set ) * 1000;

        if ( ( ! empty( $start_date_set ) && ! empty( $end_date_set ) ) || ( isset( $is_bookable ) && $is_bookable === 'yes' ) ) {

            echo '<div class="edit" style="display: none;">';
            echo apply_filters( 'easy_booking_order_picker_form',
                '<p>
                    <label for="start_date" style="font-weight: bold;">' . esc_html__( $start_date_text ) . ' : </label>
                    <input type="hidden" class="variation_id" name="variation_id" data-item_id="' . $item_id . '" data-product_id="' . $product_id . '" value="">
                    <input type="text" id="start_date" class="datepicker datepicker_start--' . $item_id . '" data-value="' . $u_start_date .'">
                </p>
                <p>
                    <label for="end_date" style="font-weight: bold;">' . esc_html__( $end_date_text ) . ' : </label>
                    <input type="text" id="end_date" class="datepicker datepicker_end--' . $item_id . '" data-value="' . $u_end_date .'">
                </p>', $start_date_text, $end_date_text, $product_id, $item_id );
            echo '</div>';

        }

    }

    /**
    *
    * Gets product taxes for custom price
    *
    * @param int $total - custom price excluding taxes
    * @param str $item_tax_class
    * @return array $taxes
    *
    **/
    public function easy_booking_get_product_taxes( $total, $item_tax_class ) {
        $tax_rates  = WC_Tax::get_rates( $item_tax_class );
        $taxes      = WC_Tax::calc_tax( $total, $tax_rates, false );

        return $taxes;
        
    }

    /**
    *
    * Gets custom price on the order page
    *
    * @param WC_Product $product
    * @param int $item_id
    * @param str $start - Start date
    * @param str $end - End date
    * @param array $order_item
    * @param array $coupons
    * @return array $item_prices - Item prices (subtotal, total, tax subtotal and tax total)
    *
    **/
    public function easy_booking_get_booking_price( $product, $item_id, $start, $end, $order_item, $coupons ) {

        if ( ! $product )
            return false;

        $this->options = get_option( 'easy_booking_settings' );
        $calc_mode = $this->options['easy_booking_calc_mode']; // Calculation mode (Days or Nights)

        // Get booking duration
        $start_diff = strtotime( $start );
        $end_diff = strtotime( $end );

        $diff  = abs( $start_diff - $end_diff ) * 1000;

        $days = $diff / 86400000;

        if ( $days == 0 )
            $days = 1;

        // If calculation mode is set to "Days", add one day
        if ( $calc_mode == "days" && ( $start != $end ) ) {
            $duration = $days + 1;
        } elseif ( ( $calc_mode == "days" ) && ( $start == $end ) ) {
            $duration = $days;
        } else {
            $duration = $days;
        }

        if ( $product->is_taxable() ) {
            $price = $product->get_price_excluding_tax(); // Product price excluding tax
        } else {
            $price = $product->get_price(); // Product price
        }

        // Price for x days
        $new_price = apply_filters( 'easy_booking_get_order_item_price', $price * $duration, $product, $item_id, $duration );

        if ( $product->is_taxable() ) {
            
            $item_tax_class = $order_item['tax_class'];
            $product_taxes = $this->easy_booking_get_product_taxes( $new_price, $item_tax_class );

            // Product taxes without potential discounts
            foreach ( $product_taxes as $_tax_id => $_tax_value ) {
                $tax_subtotal[ $_tax_id ] = $_tax_value;
            }

            $tax_amount = WC_Tax::get_tax_total( $product_taxes );

        }

        if ( $coupons ) {

            foreach ( $coupons as $code ) {

                $coupon = new WC_Coupon( $code );

                if ( $coupon->is_valid_for_product( $product ) ) {

                    $coupon_amount = $coupon->get_discount_amount( $new_price, $order_item, true ); // Discounted amount for item price
                    $total = $new_price - $coupon_amount; // New price with discount

                    if ( ! empty( $product_taxes ) ) {

                        foreach ($product_taxes as $_tax_id => $_tax_value ) {

                            $tax_discount[ $_tax_id ] = $coupon->get_discount_amount( $_tax_value, $order_item, true ); // Discounted amount for item taxes
                            $tax_total[ $_tax_id ] = $_tax_value - $tax_discount[ $_tax_id ]; //  Product taxes with discount

                        }

                    }

                } else {

                    if ( ! empty( $product_taxes ) ) foreach ($product_taxes as $_tax_id => $_tax_value ) {
                        $tax_total[ $_tax_id ] = $_tax_value; // No valid coupon - Product taxes unchanged
                    }

                    $total = $new_price; // No valid coupon - Product  price unchanged
                }

            }

        } else {

            if ( ! empty( $product_taxes ) ) {
                foreach ($product_taxes as $_tax_id => $_tax_value ) {
                    $tax_total[$_tax_id] = $_tax_value; // No coupon - Product taxes unchanged
                }
            }

            $total = $new_price; // No coupon - Product  price unchanged
        }

        $new_price = $new_price * $order_item['quantity']; // Multiply subtotal by item quantity
        $total = $total * $order_item['quantity']; // Multiply total by item quantity

        if ( ! empty( $product_taxes ) ) {

            foreach ( $tax_subtotal as $tax_subtotal_id => $tax_subtotal_amnt )
                $tax_subtotal[$tax_subtotal_id] = $tax_subtotal_amnt * $order_item['quantity']; // Multiply tax subtotal by item quantity

            foreach ( $tax_total as $tax_total_id => $tax_total_amnt )
                $tax_total[$tax_total_id] = $tax_total_amnt * $order_item['quantity']; // Multiply tax total by item quantity

            // Format taxes
            $line_taxes          = array_map( 'wc_format_decimal', $tax_total );
            $line_subtotal_taxes = array_map( 'wc_format_decimal', $tax_subtotal );

            $item_prices['tax_subtotal'] = $line_subtotal_taxes;
            $item_prices['tax_total'] = $line_taxes;

        }

        $item_prices['subtotal'] = floatval( wc_format_decimal( $new_price, 0 ) );
        $item_prices['total'] = floatval( wc_format_decimal( $total, 0 ) );

        return $item_prices;

    }

    /**
    *
    * Sets new product data to session
    *
    **/
    public function easy_booking_order_update_product_dates() {
        $item_id = isset( $_POST['item_id'] ) && intval( $_POST['item_id'] ) ? $_POST['item_id'] : ''; // Item ID
        $order_id = isset( $_POST['order_id'] ) && intval( $_POST['order_id'] ) ? $_POST['order_id'] : ''; // Order ID
        $item_qty = isset( $_POST['quantity'] ) && intval( $_POST['quantity'] ) ? $_POST['quantity'] : 0; // Item quantity

        $item_tax_class = wc_get_order_item_meta( $item_id, '_tax_class' );

        $start_date = isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : ''; // Booking start date
        $end_date = isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : ''; // Booking end date

        $start = isset( $_POST['start_format'] ) ? sanitize_text_field( $_POST['start_format'] ) : ''; // Booking start date 'yyyy-mm-dd'
        $end = isset( $_POST['end_format'] ) ? sanitize_text_field( $_POST['end_format'] ) : ''; // Booking end date 'yyyy-mm-dd'

        $order = wc_get_order( $order_id );
        $order_items = $order->get_items();

        if ( $order && ! empty( $order_items ) && ! empty( $item_id ) ) {

            foreach ( $order_items as $order_item_id => $item ) {

                if ( $order_item_id != $item_id )
                    continue;

                $product = $order->get_product_from_item( $item ); // Product object

            }

        }

        if ( ! $product )
            return;

        // Get order coupons
        $coupons = $order->get_used_coupons();

        // Store data
        $order_item_data = array(
            'quantity' => $item_qty,
            'tax_class' => $item_tax_class
        );

        $item_prices = $this->easy_booking_get_booking_price( $product, $item_id, $start, $end, $order_item_data, $coupons );

        $booking_data = array(
            'new_price' => $item_prices,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'start' => $start,
            'end' => $end
        );

        // Update session data
        WC()->session->set( 'order_booking_' . $item_id, $booking_data );

        wp_send_json( $item_prices );

        die();
    }

    /**
    *
    * Update order item product when changing booking dates
    *
    * @param int $order_id
    * @param array $posted - Order content
    *
    **/
    public function easy_booking_update_order_product( $order_id, $posted ) {
        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        if ( $items ) foreach ( $items as $item_id => $item ) {

            $product = $order->get_product_from_item( $item );

            if ( ! $product )
                continue;

            $start = wc_get_order_item_meta( $item_id, '_ebs_start_format' );
            $end = wc_get_order_item_meta( $item_id, '_ebs_end_format' );

            $product_id = wc_get_order_item_meta( $item_id, '_product_id' );

            // Is product bookable ?
            $is_bookable = get_post_meta( $product_id, '_booking_option', true );

            if ( ( ! empty( $start ) && ! empty( $end ) ) || ( isset( $is_bookable ) && $is_bookable === 'yes' ) ) {

                if ( class_exists( 'WC_Session' ) ) {
                    $session_data = WC()->session->get_session_data();
                    $order_booking_session = ! empty( $session_data['order_booking_' . $item_id] ) ? WC()->session->get( 'order_booking_' . $item_id ) : '';
                } else {
                    $order_booking_session = '';
                }

                $item_tax_class = $posted['order_item_tax_class'][$item_id];
                $order_item['quantity'] = $posted['order_item_qty'][$item_id];

                if ( ! empty( $order_booking_session ) ) {

                    $start = $order_booking_session['start'];
                    $end = $order_booking_session['end'];
                    $start_date = $order_booking_session['start_date'];
                    $end_date = $order_booking_session['end_date'];

                    if ( $order_booking_session['new_price'] ) {
                        $new_price = $order_booking_session['new_price'];
                        $subtotal_price = $new_price['subtotal'];
                        $total_price = $new_price['total'];
                    }

                    if ( ! empty( $item_tax_class ) ) {
                        $tax_subtotal = isset( $new_price['tax_subtotal'] ) ? $new_price['tax_subtotal'] : '';
                        $tax_total = isset( $new_price['tax_total'] ) ? $new_price['tax_total'] : '';
                    }

                    wc_update_order_item_meta( $item_id, '_ebs_start_format', $start );
                    wc_update_order_item_meta( $item_id, '_ebs_end_format', $end );
                    wc_update_order_item_meta( $item_id, '_ebs_start_display', $start_date );
                    wc_update_order_item_meta( $item_id, '_ebs_end_display', $end_date );

                    WC()->session->set( 'order_booking_' . $item_id, '' );

                } else {
                    
                    $order_item['tax_class'] = $item_tax_class;

                    $coupons = $order->get_used_coupons();

                    $item_prices = $this->easy_booking_get_booking_price( $product, $item_id, $start, $end, $order_item, $coupons );

                    if ( $item_prices ) {
                        $subtotal_price = $item_prices['subtotal'];
                        $total_price = $item_prices['total']; 
                    }

                    if ( ! empty( $item_tax_class ) ) {
                        $tax_subtotal = isset( $new_price['tax_subtotal'] ) ? $new_price['tax_subtotal'] : '';
                        $tax_total = isset( $new_price['tax_total'] ) ? $new_price['tax_total'] : '';
                    }

                }

                // Update totals
                if ( isset( $subtotal_price ) )
                    wc_update_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $subtotal_price ) );

                if ( isset( $total_price ) )
                    wc_update_order_item_meta( $item_id, '_line_total', wc_format_decimal( $total_price ) );

                if ( ! empty( $tax_subtotal ) )
                    wc_update_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( $tax_subtotal ) );

                if ( ! empty( $tax_total ) )
                    wc_update_order_item_meta( $item_id, '_line_tax', wc_format_decimal( $tax_total ) );

                if ( ! empty( $tax_subtotal ) && ! empty ( $tax_total ) )
                    wc_update_order_item_meta( $item_id, '_line_tax_data', array( 'total' => $tax_total, 'subtotal' => $tax_subtotal ) );

                do_action( 'easy_booking_update_order_product', $order, $item_id, $order_item );

            }
            
        }

    }

}

return new WCEB_Order();

endif;