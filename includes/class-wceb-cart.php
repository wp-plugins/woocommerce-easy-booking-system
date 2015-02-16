<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Cart' ) ) :

class WCEB_Cart {

    public function __construct() {

        // get plugin options values
        $this->options = get_option('easy_booking_settings');
        
        add_filter('woocommerce_add_to_cart_validation', array($this, 'easy_booking_check_dates_before_add_to_cart'), 20, 2);
        add_filter('woocommerce_add_cart_item_data', array( $this, 'easy_booking_add_cart_item_data'), 10, 2);
        add_filter('woocommerce_get_cart_item_from_session', array( $this, 'easy_booking_get_cart_item_from_session'), 10, 2);
        add_filter('woocommerce_get_item_data', array( $this, 'easy_booking_get_item_data'), 10, 2);
        add_filter('woocommerce_add_cart_item', array( $this, 'easy_booking_add_cart_item'), 10, 1);
    }

    /**
    *
    * Checks if two dates are set before adding to cart
    *
    * @param bool $passed
    * @param int $product_id
    * @return bool $passed
    *
    **/
    public function easy_booking_check_dates_before_add_to_cart( $passed = true, $product_id ) {
        $booking_session = WC()->session->get( 'booking' );
        $is_bookable = get_post_meta($product_id, '_booking_option', true);

        // If product is bookable
        if ( isset( $is_bookable ) && $is_bookable === 'yes' ) {

            if ( isset( $booking_session[$product_id] ) && ! empty( $booking_session[$product_id] ) ) {

                $start_date = $booking_session[$product_id]['start_date']; // Formated dates
                $end_date = $booking_session[$product_id]['end_date']; // Formated dates
            
                if ( isset( $start_date ) && isset( $end_date ) ) {
                    $passed = true;
                }

            } else {
                wc_add_notice( __( 'Please choose two dates', 'easy_booking' ), 'error' );
                $passed = false;
            }

        }

        return $passed;
    }

    /**
    *
    * Adds session data to cart item
    *
    * @param array $cart_item_meta
    * @param int $product_id
    * @return array $cart_item_meta
    *
    **/
    function easy_booking_add_cart_item_data( $cart_item_meta, $product_id ) {
        // Get session
        $booking_session = WC()->session->get( 'booking' );

        if ( isset( $booking_session[$product_id] ) && ! empty( $booking_session[$product_id] ) ) {

            $cart_item_meta['_booking_price'] = $booking_session[$product_id]['new_price'];
            $cart_item_meta['_booking_duration'] = $booking_session[$product_id]['duration'];
            $cart_item_meta['_start_date'] = $booking_session[$product_id]['start_date']; // Formatted dates
            $cart_item_meta['_end_date'] = $booking_session[$product_id]['end_date']; // Formatted dates
            $cart_item_meta['_ebs_start'] = $booking_session[$product_id]['start'];
            $cart_item_meta['_ebs_end'] = $booking_session[$product_id]['end'];

            // Reset session
            WC()->session->set( 'booking', '' );
            
        }

        return $cart_item_meta;
    }

    /**
    *
    * Adds data to cart item
    *
    * @param array $cart_item
    * @param array $values - cart_item_meta
    * @return array $cart_item
    *
    **/
    function easy_booking_get_cart_item_from_session( $cart_item, $values ) {

        if ( isset( $values['_booking_price'] ) )
            $cart_item['_booking_price'] = $values['_booking_price'];

        if ( isset( $values['_booking_duration'] ) )
            $cart_item['_booking_duration'] = $values['_booking_duration'];

        if ( isset( $values['_start_date'] ) )
            $cart_item['_start_date'] = $values['_start_date'];

        if ( isset( $values['_end_date'] ) )
            $cart_item['_end_date'] = $values['_end_date'];

        if ( isset( $values['_ebs_start'] ) )
            $cart_item['_ebs_start'] = $values['_ebs_start'];

        if ( isset( $values['_ebs_end'] ) )
            $cart_item['_ebs_end'] = $values['_ebs_end'];

        $this->easy_booking_add_cart_item( $cart_item );
     
        return $cart_item;
    }
 
    /**
    *
    * Adds formatted dates to the cart item
    *
    * @param array $other_data
    * @param array $cart_item
    * @return array $other_data
    *
    **/
    function easy_booking_get_item_data( $other_data, $cart_item ) {
        $start_text = ! empty( $this->options['easy_booking_start_date_text'] ) ? $this->options['easy_booking_start_date_text'] : __('Start', 'easy_booking');
        $end_text = ! empty( $this->options['easy_booking_end_date_text'] ) ? $this->options['easy_booking_end_date_text'] : __('End', 'easy_booking');

        if ( isset( $cart_item['_start_date'] ) && ! empty ( $cart_item['_start_date'] ) )
            $other_data[] = array('name' => $start_text, 'value' => $cart_item['_start_date']);

        if ( isset( $cart_item['_end_date'] ) && ! empty ( $cart_item['_end_date'] ) )
            $other_data[] = array('name' => $end_text, 'value' => $cart_item['_end_date']);

        return $other_data;
    }
 
    /**
    *
    * Sets custom price to the cart item
    *
    * @param array $cart_item
    * @return array $cart_item
    *
    **/
    function easy_booking_add_cart_item( $cart_item ) {

        if ( isset( $cart_item['_booking_price'] ) && $cart_item['_booking_price'] > 0 ) {
            $booking_price = $cart_item['_booking_price'];
            $cart_item['data']->set_price( $booking_price );
        }
 
        return $cart_item;
    }

}

new WCEB_Cart();

endif;