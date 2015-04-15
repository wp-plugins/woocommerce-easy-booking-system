<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Product_Settings' ) ) :

class WCEB_Product_Settings {

	public function __construct() {
		add_action( 'product_type_options', array( $this, 'easy_booking_add_product_option_pricing' ));
        add_action( 'woocommerce_variation_options', array( $this, 'easy_booking_set_variation_booking_option' ), 10, 3);
        add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'easy_booking_add_variation_booking_options' ), 10, 3);
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'easy_booking_add_booking_tab' ), 10, 1);
        add_action( 'woocommerce_product_data_panels', array($this, 'easy_booking_add_booking_data_panel'));
        add_filter( 'woocommerce_process_product_meta_simple', array( $this, 'easy_booking_add_custom_price_fields_save' ));
        add_action( 'woocommerce_save_product_variation', array( $this, 'easy_booking_save_booking_options' ), 10, 2);
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
            'wrapper_class' => 'show_if_simple',
            'label'         => __( 'Bookable', 'easy_booking' ),
            'description'   => __( 'Bookable products can be rent or booked on a daily schedule', 'easy_booking' ),
            'default'       => 'no'
        );

        return $product_type_options;
    }

    /**
    *
    * Adds a checkbox to the product variation to set it as bookable
    *
    * @param int $loop
    * @param array $variation_data
    * @param obj $variation
    *
    **/
    public function easy_booking_set_variation_booking_option( $loop, $variation_data, $variation ) {
        global $post;

        // Backwards compatibility
        $meta_data = array( '_booking_option', '_booking_min', '_booking_max', '_first_available_date' );
        if ( $meta_data ) foreach ( $meta_data as $meta ) {
            $meta_value = get_post_meta( $post->ID, $meta, true );

            if ( ! empty( $meta_value ) ) {

                update_post_meta( $variation->ID, $meta, $meta_value );
                delete_post_meta( $post->ID, $meta );

            }
        }
        
        $is_bookable = get_post_meta( $variation->ID, '_booking_option', true ); ?>
        
            <label><input type="checkbox" class="checkbox variable_is_bookable" name="_var_booking_option[<?php echo $loop; ?>]" <?php checked( $is_bookable, 'yes' ) ?> /> <?php _e( 'Bookable', 'woocommerce' ); ?></label>
        
        <?php
    }

    public function easy_booking_add_variation_booking_options( $loop, $variation_data, $variation ) {
        $variation_id = $variation->ID;
        $product = wc_get_product( $variation_id );
        
        $booking_min = get_post_meta( $variation_id, '_booking_min', true );
        $booking_max = get_post_meta( $variation_id, '_booking_max', true );
        $first_available_date = get_post_meta( $variation_id, '_first_available_date', true );

        include('views/wceb-html-variation-booking-options.php');
        
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
                'class'  => array( 'show_if_simple show_if_bookable hide_if_variable' ),
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

        $product = wc_get_product( $post->ID );
        include('views/wceb-html-product-booking-options.php');

    }

    /**
    *
    * Saves checkbox value and booking options for the product
    *
    * @param int $post_id
    *
    **/
    public function easy_booking_add_custom_price_fields_save( $post_id ) {
        $is_bookable = isset( $_POST['_booking_option'] ) ? sanitize_text_field( 'yes' ) : '';
        $booking_min = isset( $_POST['_booking_min'] ) ? absint( $_POST['_booking_min'] ) : 0;
        $booking_max = isset( $_POST['_booking_max'] ) ? absint( $_POST['_booking_max'] ) : 0;
        $first_available_date = isset( $_POST['_first_available_date'] ) ? absint( $_POST['_first_available_date'] ) : 0;

        if ( $booking_min != 0 && $booking_max != 0 && $booking_min > $booking_max ) {
            WC_Admin_Meta_Boxes::add_error( __( 'Minimum booking duration must be inferior to maximum booking duration', 'easy_booking' ) );
        } else {
            update_post_meta( $post_id, '_booking_min', $booking_min );
            update_post_meta( $post_id, '_booking_max', $booking_max );
        }

        update_post_meta( $post_id, '_first_available_date', $first_available_date );
        update_post_meta( $post_id, '_booking_option', $is_bookable );

    }

    public function easy_booking_save_booking_options( $variation_id , $i ) {
        $is_bookable = isset( $_POST['_var_booking_option'][$i] ) ? sanitize_text_field( 'yes' ) : '';
        $booking_min = isset( $_POST['_var_booking_min'][$i] ) ? absint( $_POST['_var_booking_min'][$i] ) : 0;
        $booking_max = isset( $_POST['_var_booking_max'][$i] ) ? absint( $_POST['_var_booking_max'][$i] ) : 0;
        $first_available_date = isset( $_POST['_var_first_available_date'][$i] ) ? absint( $_POST['_var_first_available_date'][$i] ) : 0;

        if ( $booking_min != 0 && $booking_max != 0 && $booking_min > $booking_max ) {
            WC_Admin_Meta_Boxes::add_error( __( 'Minimum booking duration must be inferior to maximum booking duration', 'easy_booking' ) );
        } else {
            update_post_meta( $variation_id, '_booking_min', $booking_min );
            update_post_meta( $variation_id, '_booking_max', $booking_max );
        }

        update_post_meta( $variation_id, '_first_available_date', $first_available_date );
        update_post_meta( $variation_id, '_booking_option', $is_bookable );
    }

}

return new WCEB_Product_Settings();

endif;