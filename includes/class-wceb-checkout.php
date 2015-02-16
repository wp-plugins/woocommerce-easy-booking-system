<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Checkout' ) ) :

class WCEB_Checkout {

    public function __construct() {
        // Get plugin options values
        $this->options = get_option('easy_booking_settings');

        add_action('woocommerce_add_order_item_meta', array($this, 'easy_booking_add_order_meta' ), 10, 2);
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'easy_booking_hide_formatted_date'), 10, 1);

    }

    /**
    *
    * Adds booked dates to the order item
    *
    * @param int $item_id
    * @param array $values - 
    *
    **/
    public function easy_booking_add_order_meta($item_id, $values) {

        $start_text = ! empty( $this->options['easy_booking_start_date_text'] ) ? $this->options['easy_booking_start_date_text'] : __('Start', 'easy_booking');
        $end_text = ! empty( $this->options['easy_booking_end_date_text'] ) ? $this->options['easy_booking_end_date_text'] : __('End', 'easy_booking');

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

    /**
    *
    * Hides dates on the order page (to display a custom form instead)
    *
    * @param array $item_meta - Hidden values
    * @return array $item_meta
    *
    **/
    public function easy_booking_hide_formatted_date( $item_meta ) {

        $start_text = ! empty( $this->options['easy_booking_start_date_text'] ) ? $this->options['easy_booking_start_date_text'] : __('Start', 'easy_booking');
        $end_text = ! empty( $this->options['easy_booking_end_date_text'] ) ? $this->options['easy_booking_end_date_text'] : __('End', 'easy_booking');

        $item_meta[] = $start_text;
        $item_meta[] = $end_text;
        $item_meta[] = '_ebs_start_display';
        $item_meta[] = '_ebs_end_display';
        $item_meta[] = '_ebs_start_format';
        $item_meta[] = '_ebs_end_format';

        return $item_meta;
    }

}

return new WCEB_Checkout();

endif;