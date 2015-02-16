<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Product_Settings' ) ) :

class WCEB_Product_Settings {

	public function __construct() {
		add_action( 'product_type_options', array( $this, 'easy_booking_add_product_option_pricing' ));
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'easy_booking_add_booking_tab' ), 10, 1);
        add_action( 'woocommerce_product_data_panels', array($this, 'easy_booking_add_booking_data_panel'));
        add_filter( 'woocommerce_process_product_meta', array( $this, 'easy_booking_add_custom_price_fields_save' ));
	}

    /**
    *
    * Adds a checkbox to the product admin page to set the product as bookable
    *
    * @param array $product_type_options
    * @return array $product_type_options
    *
    **/
    public function easy_booking_add_product_option_pricing( $product_type_options ) {

        $product_type_options['booking_option'] = array(
            'id'            => '_booking_option',
            'wrapper_class' => 'show_if_simple show_if_variable',
            'label'         => __( 'Bookable', 'easy_booking' ),
            'description'   => __( 'Bookable products can be rent or booked on a daily schedule', 'easy_booking' ),
            'default'       => 'no'
        );

        return $product_type_options;
    }

    /**
    *
    * Adds a booking tab to the product admin page for booking options
    *
    * @param array $product_data_tabs
    * @return array $product_data_tabs
    *
    **/
    public function easy_booking_add_booking_tab( $product_data_tabs ) {

        $product_data_tabs['WCEB'] = array(
                'label'  => __( 'Bookings', 'easy_booking' ),
                'target' => 'booking_product_data',
                'class'  => array( 'show_if_bookable' ),
        );

        return $product_data_tabs;
    }

    /**
    *
    * Adds booking options in the booking tab
    *
    **/
    public function easy_booking_add_booking_data_panel() {
        global $post;

        $product = get_product( $post->ID );

        echo '<div id="booking_product_data" class="panel woocommerce_options_panel">

        <div class="options_group">';

            woocommerce_wp_text_input( array(
                'id' => '_booking_min',
                'label' => __( 'Minimum booking duration', 'easy_booking' ),
                'desc_tip' => 'true',
                'description' => __( 'Leave zero or empty to set no duration limit', 'easy_booking' ),
                'value' => intval( $post->_booking_min ),
                'type' => 'number',
                'custom_attributes' => array(
                    'step'  => '1',
                    'min' => '0'
                ) ) );

            woocommerce_wp_text_input( array(
                'id' => '_booking_max',
                'label' => __( 'Maximum booking duration', 'easy_booking' ),
                'desc_tip' => 'true',
                'description' => __( 'Leave zero or empty to set no duration limit', 'easy_booking' ),
                'value' => intval( $post->_booking_max ),
                'type' => 'number',
                'custom_attributes' => array(
                    'step'  => '1',
                    'min' => '0'
                ) ) );

            woocommerce_wp_text_input( array(
                'id' => '_first_available_date',
                'label' => __( 'First available date', 'easy_booking' ),
                'desc_tip' => 'true',
                'description' => __( 'First available date, relative to today. I.e. : today + 5 days. Leave zero or empty for today.', 'easy_booking' ),
                'value' => intval( $post->_first_available_date ),
                'type' => 'number',
                'custom_attributes' => array(
                    'step'  => '1',
                    'min' => '0'
                ) ) );

        echo '</div>';

        echo '<div class="options_group">';

        do_action('easy_booking_before_admin_picker', $product);

        echo '<p class="form-field">
            <span class="admin-picker"></span>
        </p>';

        do_action('easy_booking_after_admin_picker', $product);

        echo '</div></div>';

    }

    /**
    *
    * Saves checkbox value and booking options for the product
    *
    * @param int $post_id
    *
    **/
    public function easy_booking_add_custom_price_fields_save( $post_id ) {
        $woocommerce_checkbox = isset( $_POST['_booking_option'] ) ? 'yes' : '';
        $booking_min = isset( $_POST['_booking_min'] ) && intval( $_POST['_booking_min'] ) ? $_POST['_booking_min'] : 0;
        $booking_max = isset( $_POST['_booking_max'] ) && intval( $_POST['_booking_max'] ) ? $_POST['_booking_max'] : 0;
        $first_available_date = isset( $_POST['_first_available_date'] ) && intval( $_POST['_first_available_date'] ) ? $_POST['_first_available_date'] : 0;

        if ( $booking_min != 0 && $booking_max != 0 && $booking_min > $booking_max ) {
            WC_Admin_Meta_Boxes::add_error( __( 'Minimum booking duration must be inferior to maximum booking duration', 'easy_booking' ) );
        } else {
            update_post_meta( $post_id, '_booking_min', $booking_min );
            update_post_meta( $post_id, '_booking_max', $booking_max );
        }

        update_post_meta( $post_id, '_first_available_date', $first_available_date );
        update_post_meta( $post_id, '_booking_option', $woocommerce_checkbox );

    }

}

return new WCEB_Product_Settings();

endif;