<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div id="booking_product_data" class="panel woocommerce_options_panel">
    
    <div class="options_group show_if_variable">
        <?php woocommerce_wp_checkbox( array(
            'id' => '_manage_bookings',
            'label' => __( 'Manage bookings?', 'easy_booking' ),
            'description' => __('Check this box to manage bookings at product level.'),
            'value' => $product->product_type === 'simple' ? 'yes' : $post->_manage_bookings
        ) ); ?>

    </div>

    <div class="options_group show_if_simple show_if_manage_bookings">

        <?php woocommerce_wp_text_input( array(
            'id' => '_booking_min',
            'label' => __( 'Minimum booking duration', 'easy_booking' ),
            'desc_tip' => 'true',
            'description' => __( 'Leave zero or empty to set no duration limit', 'easy_booking' ),
            'value' => isset( $post->_booking_min ) ? $post->_booking_min : '',
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
            'value' => isset( $post->_booking_max ) ? $post->_booking_max : '',
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
            'value' => isset( $post->_first_available_date ) ? $post->_first_available_date : '',
            'type' => 'number',
            'custom_attributes' => array(
                'step'  => '1',
                'min' => '0'
            ) ) ); ?>

    </div>

    <?php do_action('easy_booking_after_simple_booking_options', $product); ?>

</div>