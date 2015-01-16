<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_EBS_Order {

    public function __construct() {
        add_action('admin_enqueue_scripts', array( $this, 'wc_ebs_order_admin_scripts'));
        add_action('woocommerce_before_order_itemmeta', array($this, 'wc_ebs_order_display_product_dates'), 10, 3);
        add_action('wp_ajax_ebs_sku_order_update_product_dates', array( $this, 'wc_ebs_order_update_product_dates'));
        add_action('woocommerce_saved_order_items', array($this, 'wc_ebs_update_order_product'), 10, 2);
    }

    public function wc_ebs_order_admin_scripts() {
        global $post;

        $screen = get_current_screen();
        $this->options = get_option('wc_ebs_options');

        // Get page language in order to load Pickadate translation
        $site_language = get_bloginfo( 'language' );
        $lang = str_replace("-","_", $site_language);

        if ( in_array( $screen->id, array( 'shop_order' ) ) ) {

            // Calculation mode (Days or Nights)
            $calc_mode = $this->options['wc_ebs_calc_mode'];

            // Concatenated and minified script including picker.js, picker.date.js and legacy.js
            wp_enqueue_script( 'pickadate', plugins_url( '/js/pickadate.min.js', __FILE__ ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker', plugins_url( '/js/picker.js', __FILE__  ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker.date', plugins_url( '/js/picker.date.js', __FILE__  ), array('jquery'), '1.0', true);
           //  wp_enqueue_script( 'legacy', plugins_url( '/js/legacy.js', __FILE__  ), array('jquery'), '1.0', true);

            wp_enqueue_script( 'pickadate-custom-admin', plugins_url( '/js/admin/pickadate-custom-admin.min.js', __FILE__ ), array('jquery', 'pickadate'), '1.0', true);
            // wp_enqueue_script( 'pickadate-custom-admin', plugins_url('/js/admin/pickadate-custom-admin.js', __FILE__), array('jquery', 'pickadate'), '1.0', true );

            wp_enqueue_script( 'datepicker.language', plugins_url( '/js/translations/' . $lang . '.js', __FILE__  ), array('jquery', 'pickadate', 'pickadate-custom-admin'), '1.0', true);

            wp_register_style( 'picker', plugins_url( '/css/default.min.css', __FILE__  ), true);
            wp_enqueue_style( 'picker' );

            wp_localize_script( 'pickadate-custom-admin', 'order_ajax_info',
                array( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'order_id' => $post->ID,
                    'calc_mode' => $calc_mode
                )
            );

        }
                
    }

    public function wc_ebs_order_display_product_dates( $item_id, $item, $_product ) {
        global $woocommerce, $post;

        $product_id = intval( $item['product_id'] );

        // Is product bookable ?
        $wc_ebs_options = get_post_meta($product_id, '_booking_option', true);
        $this->options = get_option('wc_ebs_options');

        if ( isset( $wc_ebs_options ) && $wc_ebs_options == 'yes' ) {

            $start_date_set = wc_get_order_item_meta( $item_id, '_ebs_start_format' );
            $end_date_set = wc_get_order_item_meta( $item_id, '_ebs_end_format' );

            $start_date_text = $this->options['wc_ebs_start_date_text'];
            $end_date_text = $this->options['wc_ebs_end_date_text'];

            $u_start_date = empty( $start_date_set ) ? '' : strtotime( $item['ebs_start_format'] ) * 1000;
            $u_end_date = empty( $end_date_set ) ? '' : strtotime( $item['ebs_end_format'] ) * 1000;

            if ( ! empty( $start_date_set ) && ! empty( $end_date_set ) ) {

                // If ordered before 1.4, dates will be displayed in English
                $start_date = empty( $item['ebs_start_display'] ) ? date('d F Y', strtotime( $item['ebs_start_format'] ) ) : $item['ebs_start_display'];
                $end_date = empty( $item['ebs_end_display'] ) ? date('d F Y', strtotime( $item['ebs_end_format'] ) ) : $item['ebs_end_display'];

                // Please do not remove inputs' attributes (classes, ids, etc.)
                echo apply_filters( 'wc_ebs_order_booked_meta',
                    '<div class="view">
                        <p>
                            <label for="start_date" style="font-weight: bold;">' . esc_html__( $start_date_text ) . ' : </label>
                            <span class="wc_ebs_order_date">' . $start_date . '</span>
                        </p>
                        <p>
                            <label for="end_date" style="font-weight: bold;">' . esc_html__( $end_date_text ) . ' : </label>
                            <span class="wc_ebs_order_date">' . $end_date . '</span>
                        </p>
                    </div>' );

            }

            echo apply_filters( 'wc_ebs_order_picker_form',
                '<div class="edit" style="display: none;">
                    <p>
                        <label for="start_date" style="font-weight: bold;">' . esc_html__( $start_date_text ) . ' : </label>
                        <input type="hidden" class="variation_id" name="variation_id" data-item_id="' . $item_id . '" data-product_id="' . $product_id . '" value="">
                        <input type="text" id="start_date" class="datepicker datepicker_start--' . $item_id . '" data-value="' . $u_start_date .'">
                    </p>
                    <p>
                        <label for="end_date" style="font-weight: bold;">' . esc_html__( $end_date_text ) . ' : </label>
                        <input type="text" id="end_date" class="datepicker datepicker_end--' . $item_id . '" data-value="' . $u_end_date .'">
                    </p>
                </div>', $start_date_text, $end_date_text );  

        }
    }

    public function wc_ebs_get_product_taxes( $total, $item_tax_class ) {

        $tax_rates  = WC_Tax::get_rates( $item_tax_class );
        $taxes      = WC_Tax::calc_tax( $total, $tax_rates, false );

        return $taxes;
        
    }

    public function wc_ebs_get_booking_price( $product, $item_id, $start, $end, $order_item, $coupons ) {

        $this->options = get_option( 'wc_ebs_options' );
        $calc_mode = $this->options['wc_ebs_calc_mode']; // Calculation mode (Days or Nights)

        // Get booking duration
        $start_diff = strtotime($start);
        $end_diff = strtotime($end);

        $diff  = abs($start_diff - $end_diff) * 1000;

        $days = $diff / 86400000;

        $calc_mode = $this->options['wc_ebs_calc_mode']; // Calculation mode (Days or Nights)

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
        
        // If product is variable, get variation price
        if ( $product->is_type( 'variable' ) ) {

            $variation_id = wc_get_order_item_meta( $item_id, '_variation_id' );
            $variable_product = new WC_Product_Variation( $variation_id );
            $variation_price = $variable_product->get_price();
            $new_price = $variation_price * $duration;

        } else {

            $price = $product->get_price(); // Product price
            $new_price = $price * $duration; // Price for x days

        }

        if ( $product->is_taxable() ) {

            $item_tax_class = $order_item['tax_class'];
            $product_taxes = $this->wc_ebs_get_product_taxes( $new_price, $item_tax_class );

            // Product taxes without potential discounts
            foreach ( $product_taxes as $_tax_id => $_tax_value ) {
                $tax_subtotal[ $_tax_id ] = $_tax_value;
            }

            $tax_amount = WC_Tax::get_tax_total( $product_taxes );
            $new_price = $new_price - $tax_amount; // Price excluding tax

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

        $item_prices['subtotal'] = floatval( $new_price );
        $item_prices['total'] = floatval( $total );

        return $item_prices;

    }

    public function wc_ebs_order_update_product_dates() {
        global $woocommerce;

        $item_id = isset( $_POST['item_id'] ) && intval( $_POST['item_id'] ) ? $_POST['item_id'] : ''; // Item ID
        $order_id = isset( $_POST['order_id'] ) && intval( $_POST['order_id'] ) ? $_POST['order_id'] : ''; // Order ID
        $item_qty = isset( $_POST['quantity'] ) && intval( $_POST['quantity'] ) ? $_POST['quantity'] : 0; // Item quantity
 
        $product_id = wc_get_order_item_meta( $item_id, '_product_id' );
        $item_tax_class = wc_get_order_item_meta( $item_id, '_tax_class' );

        $start_date = isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : ''; // Booking start date
        $end_date = isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : ''; // Booking end date

        $start = isset( $_POST['start_format'] ) ? sanitize_text_field( $_POST['start_format'] ) : ''; // Booking start date 'yyyy-mm-dd'
        $end = isset( $_POST['end_format'] ) ? sanitize_text_field( $_POST['end_format'] ) : ''; // Booking end date 'yyyy-mm-dd'
        
        $product = get_product( $product_id ); // Product object

        // Get order coupons
        $order = new WC_Order( $order_id );
        $coupons = $order->get_used_coupons();
        $order_item['quantity'] = $item_qty;
        $order_item['tax_class'] = $item_tax_class;

        $item_prices = $this->wc_ebs_get_booking_price( $product, $item_id, $start, $end, $order_item, $coupons );

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

    public function wc_ebs_update_order_product( $order_id, $posted ) {
        global $woocommerce;

        $order = new WC_Order( $order_id );

        $item_ids = $posted['order_item_id'];

        foreach ( $item_ids as $item_id ) {

            $product_id = wc_get_order_item_meta( $item_id, '_product_id' );
            $wc_ebs_options = get_post_meta( $product_id, '_booking_option', true );

            if ( class_exists( 'WC_Session' ) ) {
                $session_data = WC()->session->get_session_data();
                $order_booking_session = ! empty( $session_data['order_booking_' . $item_id] ) ? WC()->session->get( 'order_booking_' . $item_id ) : '';
            } else {
                $order_booking_session = '';
            }

            if ( isset( $wc_ebs_options ) && $wc_ebs_options == 'yes' ) {

                $product = get_product( $product_id );
                $item_tax_class = $posted['order_item_tax_class'][$item_id];

                if ( ! empty( $order_booking_session ) ) {

                    $start = $order_booking_session['start'];
                    $end = $order_booking_session['end'];
                    $start_date = $order_booking_session['start_date'];
                    $end_date = $order_booking_session['end_date'];

                    $new_price = $order_booking_session['new_price'];

                    $subotal_price = $new_price['subtotal'];
                    $total_price = $new_price['total'];

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

                    $start = wc_get_order_item_meta( $item_id, '_ebs_start_format' );
                    $end = wc_get_order_item_meta( $item_id, '_ebs_end_format' );
                    $order_item['quantity'] = $posted['order_item_qty'][$item_id];
                    $order_item['tax_class'] = $item_tax_class;

                    $coupons = $order->get_used_coupons();

                    $item_prices = $this->wc_ebs_get_booking_price( $product, $item_id, $start, $end, $order_item, $coupons );

                    $subotal_price= $item_prices['subtotal'];
                    $total_price = $item_prices['total'];

                    if ( ! empty( $item_tax_class ) ) {
                        $tax_subtotal = isset( $new_price['tax_subtotal'] ) ? $new_price['tax_subtotal'] : '';
                        $tax_total = isset( $new_price['tax_total'] ) ? $new_price['tax_total'] : '';
                    }

                }

                // Update totals
                wc_update_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $subotal_price ) );
                wc_update_order_item_meta( $item_id, '_line_total', wc_format_decimal( $total_price ) );

                if ( ! empty( $tax_subtotal ) )
                    wc_update_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( $tax_subtotal ) );

                if ( ! empty( $tax_total ) )
                    wc_update_order_item_meta( $item_id, '_line_tax', wc_format_decimal( $tax_total ) );

                if ( ! empty( $tax_subtotal ) && ! empty ( $tax_total ) )
                    wc_update_order_item_meta( $item_id, '_line_tax_data', array( 'total' => $tax_total, 'subtotal' => $tax_subtotal ) );

            }
            
        }

    }

}

new WC_EBS_Order();