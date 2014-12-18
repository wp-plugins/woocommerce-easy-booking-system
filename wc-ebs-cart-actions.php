<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_EBS_Cart extends WC_Cart {

    public function __construct() {

        // get plugin options values
        $this->options = get_option('wc_ebs_options');
        
        add_filter('woocommerce_add_to_cart_validation', array($this, 'ebs_check_dates_before_add_to_cart'), 20, 2);
        add_filter('woocommerce_add_cart_item_data', array( $this, 'wc_ebs_add_cart_item_data'), 10, 2);
        add_filter('woocommerce_get_cart_item_from_session', array( $this, 'wc_ebs_get_cart_item_from_session'), 10, 2);
        add_filter('woocommerce_get_item_data', array( $this, 'wc_ebs_get_item_data'), 10, 2);
        add_filter('woocommerce_add_cart_item', array( $this, 'wc_ebs_add_cart_item'), 10, 1);
    }

    // Check if two dates are set before adding to cart
    public function ebs_check_dates_before_add_to_cart( $passed = true, $product_id ) {
        global $woocommerce;

        $booking_session = WC()->session->get( 'booking' );
        $wc_ebs_options = get_post_meta($product_id, '_booking_option', true);

        // If product is bookable
        if ( isset( $wc_ebs_options ) && $wc_ebs_options == "yes" ) {

            if ( ! empty( $booking_session ) ) {

                $start_date = $booking_session[$product_id]['start_date']; // Formated dates
                $end_date = $booking_session[$product_id]['end_date']; // Formated dates
            
                if ( isset( $start_date ) && isset( $end_date ) ) {
                    $passed = true;
                } 

            } else {
                wc_add_notice( __( 'Please choose two dates', 'wc_ebs' ), 'error' );
                $passed = false;
            }

        }

        return $passed;
    }

    function wc_ebs_add_cart_item_data($cart_item_meta, $product_id) {
        global $woocommerce;
 
        $booking_session = WC()->session->get( 'booking' );

        if ( ! empty( $booking_session ) ) {

            $booking_price = $booking_session[$product_id]['new_price'];
            $booking_duration = $booking_session[$product_id]['duration'];
            $start_date = $booking_session[$product_id]['start_date']; // Formated dates
            $end_date = $booking_session[$product_id]['end_date']; // Formated dates
            $start = $booking_session[$product_id]['start'];
            $end = $booking_session[$product_id]['end'];

            $cart_item_meta['_booking_price'] = $booking_price;
            $cart_item_meta['_start_date'] = $start_date;
            $cart_item_meta['_end_date'] = $end_date;
            $cart_item_meta['_ebs_start'] = $start;
            $cart_item_meta['_ebs_end'] = $end;

            WC()->session->set( 'booking', '' );
            
        }

        return $cart_item_meta;
    }
 
    function wc_ebs_get_cart_item_from_session($cart_item, $values) {

        // Add the form options meta to the cart item in case you want to do special stuff on the check out page.
        if (isset($values['_booking_price']))
            $cart_item['_booking_price'] = $values['_booking_price'];

        if (isset($values['_start_date']))
            $cart_item['_start_date'] = $values['_start_date'];

        if (isset($values['_end_date']))
            $cart_item['_end_date'] = $values['_end_date'];

        if (isset($values['_ebs_start']))
            $cart_item['_ebs_start'] = $values['_ebs_start'];

        if (isset($values['_ebs_end']))
            $cart_item['_ebs_end'] = $values['_ebs_end'];

        $this->wc_ebs_add_cart_item($cart_item);
     
        return $cart_item;
    }
 
    function wc_ebs_get_item_data($other_data, $cart_item) {
        global $woocommerce;

        $start_text = ! empty( $this->options['wc_ebs_start_date_text'] ) ? $this->options['wc_ebs_start_date_text'] : __('Start', 'wc_ebs');
        $end_text = ! empty( $this->options['wc_ebs_end_date_text'] ) ? $this->options['wc_ebs_end_date_text'] : __('End', 'wc_ebs');

        // Add custom data to product data
        if ( isset( $cart_item['_start_date'] ) && $cart_item['_start_date'] )
            $other_data[] = array('name' => $start_text, 'value' => $cart_item['_start_date']);

        if ( isset( $cart_item['_end_date'] ) && $cart_item['_end_date'] )
            $other_data[] = array('name' => $end_text, 'value' => $cart_item['_end_date']);

        return $other_data;
    }
 
    function wc_ebs_add_cart_item($cart_item) {
        global $woocommerce;
 
        if ( isset( $cart_item['_booking_price'] ) && $cart_item['_booking_price'] > 0 ) {
            $booking_price = $cart_item['_booking_price'];
            $cart_item['data']->set_price($booking_price);
        }
 
        return $cart_item;
    }

}

new WC_EBS_Cart();

class WC_EBS_Checkout extends WC_Checkout {

    public function __construct() {

        // Get plugin options values
        $this->options = get_option('wc_ebs_options');

        add_action('woocommerce_add_order_item_meta', array($this, 'wc_ebs_add_order_meta' ), 10, 2);
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'wc_ebs_hide_formatted_date'), 10, 1);

    }

    public function wc_ebs_add_order_meta($item_id, $values) {

        $start_text = ! empty( $this->options['wc_ebs_start_date_text'] ) ? $this->options['wc_ebs_start_date_text'] : __('Start', 'wc_ebs');
        $end_text = ! empty( $this->options['wc_ebs_end_date_text'] ) ? $this->options['wc_ebs_end_date_text'] : __('End', 'wc_ebs');

        if ( ! empty( $values['_start_date'] ) ) {
            wc_add_order_item_meta( $item_id, $start_text, $values['_start_date'] );
            wc_add_order_item_meta( $item_id, '_ebs_start_display', $values['_start_date'] );
        }

        if ( ! empty( $values['_end_date'] ) ) {
            wc_add_order_item_meta( $item_id, $end_text, $values['_end_date'] );
            wc_add_order_item_meta( $item_id, '_ebs_end_display', $values['_end_date'] );
        }

        if ( ! empty( $values['_ebs_start'] ) )
            wc_add_order_item_meta( $item_id, '_ebs_start_format', $values['_ebs_start'] );

        if ( ! empty( $values['_ebs_end'] ) )
            wc_add_order_item_meta( $item_id, '_ebs_end_format', $values['_ebs_end'] );
    }

    public function wc_ebs_hide_formatted_date( $item_meta ) {

        $start_text = ! empty( $this->options['wc_ebs_start_date_text'] ) ? $this->options['wc_ebs_start_date_text'] : __('Start', 'wc_ebs');
        $end_text = ! empty( $this->options['wc_ebs_end_date_text'] ) ? $this->options['wc_ebs_end_date_text'] : __('End', 'wc_ebs');

        $item_meta[] = $start_text;
        $item_meta[] = $end_text;
        $item_meta[] = '_ebs_start_display';
        $item_meta[] = '_ebs_end_display';
        $item_meta[] = '_ebs_start_format';
        $item_meta[] = '_ebs_end_format';

        return $item_meta;
    }

}

new WC_EBS_Checkout();