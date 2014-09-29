<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_EBS_Cart extends WC_Cart {

    public function __construct() {

        // get plugin options values
        $this->options = get_option('wc_ebs_options');
        
        add_filter('woocommerce_add_cart_item_data', array( $this, 'wc_ebs_add_cart_item_data'), 10, 2);
        add_filter('woocommerce_get_cart_item_from_session', array( $this, 'wc_ebs_get_cart_item_from_session'), 10, 2);
        add_filter('woocommerce_get_item_data', array( $this, 'wc_ebs_get_item_data'), 10, 2);
        add_filter('woocommerce_add_cart_item', array( $this, 'wc_ebs_add_cart_item'), 10, 1);
    }

    function wc_ebs_add_cart_item_data($cart_item_meta, $product_id) {
        global $woocommerce;
 
        $booking_price = get_post_meta($product_id, '_booking_price', true);
        $booking_duration = get_post_meta($product_id, '_booking_duration', true);
        $start = get_post_meta($product_id, '_start_date', true);
        $end = get_post_meta($product_id, '_end_date', true);

        $cart_item_meta['_booking_price'] = $booking_price;
        $cart_item_meta['_start_date'] = $start;
        $cart_item_meta['_end_date'] = $end;

        $this->wc_ebs_reset_product_meta( $product_id, $booking_duration, $booking_price, $start, $end );

        return $cart_item_meta;
    }
 
    function wc_ebs_get_cart_item_from_session($cart_item, $values) {

        // Add the form options meta to the cart item in case you want to do special stuff on the check out page.
        if (isset($values['_booking_price'])) {
            $cart_item['_booking_price'] = $values['_booking_price'];
        }

        if (isset($values['_start_date'])) {
            $cart_item['_start_date'] = $values['_start_date'];
        }

        if (isset($values['_end_date'])) {
            $cart_item['_end_date'] = $values['_end_date'];
        }

        $this->wc_ebs_add_cart_item($cart_item);
     
        return $cart_item;
    }

    // Reset meta data after adding to cart
    function wc_ebs_reset_product_meta( $product_id, $booking_duration, $booking_price, $start, $end ) {

        if ( get_post_meta( $product_id, '_booking_duration', true ) ) {
            delete_post_meta($product_id, '_booking_duration');
        }

        if ( get_post_meta( $product_id, '_booking_price', true ) ) {
            delete_post_meta($product_id, '_booking_price');
        }

        if ( get_post_meta( $product_id, '_start_date', true ) ) {
            delete_post_meta($product_id, '_start_date');
        }

        if ( get_post_meta( $product_id, '_end_date', true ) ) {
            delete_post_meta($product_id, '_end_date');
        }

    }
 
    function wc_ebs_get_item_data($other_data, $cart_item) {

        $start_text = ! empty( $this->options['wc_ebs_start_date_text'] ) ? $this->options['wc_ebs_start_date_text'] : __('Start', 'wc_ebs');
        $end_text = ! empty( $this->options['wc_ebs_end_date_text'] ) ? $this->options['wc_ebs_end_date_text'] : __('End', 'wc_ebs');

        if ( isset($cart_item['_start_date']) && $cart_item['_start_date'] ) {
 
            $startDate = $cart_item['_start_date'];

            // Add custom data to product data
            $other_data[] = array('name' => $start_text, 'value' => $startDate);
        }

        if ( isset($cart_item['_end_date']) && $cart_item['_end_date'] ) {

            $endDate = $cart_item['_end_date'];
            
            // Add custom data to product data
            $other_data[] = array('name' => $end_text, 'value' => $endDate);
        }

        return $other_data;
    }
 
    function wc_ebs_add_cart_item($cart_item) {
        global $woocommerce;
 
        if ( isset($cart_item['_booking_price']) && $cart_item['_booking_price'] > 0 ) {
            $booking_price = $cart_item['_booking_price'];
            $cart_item['data']->set_price($booking_price);
        }
 
        return $cart_item;
    }

}

new WC_EBS_Cart();

class WC_EBS_Checkout extends WC_Checkout {

    public function __construct() {

        // get plugin options values
        $this->options = get_option('wc_ebs_options');

        add_action('woocommerce_add_order_item_meta', array($this, 'wc_ebs_add_order_meta' ), 10, 2);

    }

    public function wc_ebs_add_order_meta($item_id, $values) {

        $start_text = ! empty( $this->options['wc_ebs_start_date_text'] ) ? $this->options['wc_ebs_start_date_text'] : __('Start', 'wc_ebs');
        $end_text = ! empty( $this->options['wc_ebs_end_date_text'] ) ? $this->options['wc_ebs_end_date_text'] : __('End', 'wc_ebs');

        if ( ! empty( $values['_start_date'] ) )
            wc_add_order_item_meta( $item_id, $start_text, $values['_start_date'] );

        if ( ! empty( $values['_end_date'] ) )
            wc_add_order_item_meta( $item_id, $end_text, $values['_end_date'] );
    }

}

new WC_EBS_Checkout();