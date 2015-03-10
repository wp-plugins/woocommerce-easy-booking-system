<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Ajax' ) ) :

class WCEB_Ajax {

	public function __construct() {
        // Get plugin options values
        $this->options = get_option('easy_booking_settings');
        
		add_action( 'wp_ajax_add_new_price', array( $this, 'easy_booking_get_new_price' ));
        add_action( 'wp_ajax_nopriv_add_new_price', array( $this, 'easy_booking_get_new_price' ));
        add_action( 'wp_ajax_clear_booking_session', array( $this, 'easy_booking_clear_booking_session' ));
        add_action( 'wp_ajax_nopriv_clear_booking_session', array( $this, 'easy_booking_clear_booking_session' ));
        add_action( 'wp_ajax_woocommerce_get_refreshed_fragments', array( $this, 'easy_booking_new_price_fragment' ));
        add_action( 'wp_ajax_nopriv_woocommerce_get_refreshed_fragments',  array( $this, 'easy_booking_new_price_fragment' ));
        add_action( 'wp_ajax_easy_booking_hide_notice', array($this, 'easy_booking_hide_notice') );
	}

    /**
    *
    * Calculates new price, update product meta and refresh fragments
    *
    **/
    public function easy_booking_get_new_price() {
        global $post;

        $product_id = isset( $_POST['product_id'] ) && intval( $_POST['product_id'] ) ? $_POST['product_id'] : ''; // Product ID
        $variation_id = isset( $_POST['variation_id'] ) && intval( $_POST['variation_id'] ) ? $_POST['variation_id'] : ''; // Variation ID

        $days = isset( $_POST['days'] ) && intval( $_POST['days'] ) ? $_POST['days'] : 1; // Booking duration
        $calc_mode = $this->options['easy_booking_calc_mode']; // Calculation mode (Days or Nights)

        $start_date = isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : ''; // Booking start date
        $end_date = isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : ''; // Booking end date

        $start = isset( $_POST['start_format'] ) ? sanitize_text_field( $_POST['start_format'] ) : ''; // Booking start date 'yyyy-mm-dd'
        $end = isset( $_POST['end_format'] ) ? sanitize_text_field( $_POST['end_format'] ) : ''; // Booking end date 'yyyy-mm-dd'

        // If calculation mode is set to "Days", add one day
        if ( $calc_mode == "days" && ( $start != $end ) ) {
            $duration = $days + 1;
        } elseif ( ( $calc_mode == "days" ) && ( $start == $end ) ) {
            $duration = $days;
        } else {
            $duration = $days;
        }

        $id = ! empty( $variation_id ) ? $variation_id : $product_id; // Product or variation id

        $product = wc_get_product( $product_id ); // Product object
        $_product = wc_get_product( $id ); // Product or variation object

        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
        
        // If product is variable, get variation price
        if ( $product->is_type( 'variable' ) && empty( $variation_id ) ) // If no variation was selected
            $error_code = 3;

        $price = $tax_display_mode == 'incl' ? $_product->get_price_including_tax() : $_product->get_price_excluding_tax(); // Product price (Regular or sale)

        $new_price = apply_filters( 'easy_booking_get_new_item_price', $price * $duration, $product, $_product, $duration ); // Price for x days

        $array = array(
            'new_price' => $new_price,
            'duration' => $duration,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'start' => $start,
            'end' => $end
        );

        $booking_data[$product_id] = $array;

        // If number of days is inferior to 0
        if ( $duration <= 0 )
            $error_code = 1;

        // If one date is empty
        if ( empty( $start_date ) || empty( $end_date ) )
            $error_code = 2;

        // Show error message
        if ( isset( $error_code ) ) {

            $error_message = $this->easy_booking_get_date_error( $error_code );
            wc_add_notice( $error_message, 'error' );

            $this->easy_booking_error_fragment( $error_message );

        } else {

            // Update session data
            WC()->session->set_customer_session_cookie(true);
            WC()->session->set( 'booking', $booking_data );

            // Return fragments
            $this->easy_booking_new_price_fragment();

        }

        die();

    }

    /**
    *
    * Clears session if "Clear" button is clicked on the calendar
    *
    **/
    public function easy_booking_clear_booking_session() {
        WC()->session->set( 'booking', '' );
    }

    /**
    *
    * Gets error messages
    *
    * @param int $error_code
    * @return str $err - Error message
    *
    **/
    public function easy_booking_get_date_error( $error_code ) {

        switch ( $error_code ) {
            case 1:
                $err = __( 'Please choose valid dates', 'easy_booking' );
            break;
            case 2:
                $err = __( 'Please choose two dates', 'easy_booking' );
            break;
            case 3:
                $err = __( 'Please select product option', 'easy_booking' );
            break;
            default:
                $err = '';
            break;
        }

        return $err;
    }

    /**
    *
    * Updates error messages with Ajax
    *
    * @param str $error_message
    *
    **/
    public function easy_booking_error_fragment( $error_message ) {

        header( 'Content-Type: application/json; charset=utf-8' );

        ob_start();
        wc_print_notices();
        $error_message = ob_get_clean();

            $data = array(
                'errors' => array(
                    'div.wc_ebs_errors' => '<div class="wc_ebs_errors">' . $error_message . '</div>'
                )
            );

        wp_send_json( $data );

        die();

    }

    /**
    *
    * Updates price fragment
    *
    **/
    public function easy_booking_new_price_fragment() {
        global $post, $product;

        header( 'Content-Type: application/json; charset=utf-8' );
        
        $product_id = isset($_POST['product_id']) && intval($_POST['product_id']) ? $_POST['product_id'] : '';
        $booking_session = WC()->session->get( 'booking' );
        $new_price = $booking_session[$product_id]['new_price']; // New booking price
        $currency = apply_filters( 'easy_booking_currency', get_woocommerce_currency_symbol() ); // Currency

        // WooCommerce Currency Switcher compatibility
        if ( class_exists('WOOCS') ) {
            global $WOOCS;

            $currencies = $WOOCS->get_currencies();
            $new_price = $new_price * $currencies[$WOOCS->current_currency]['rate'];
            $new_price = number_format($new_price, 2, $WOOCS->decimal_sep, $WOOCS->thousands_sep);
            $currency = $currencies[$WOOCS->current_currency]['symbol'];
        }

        if ( ! empty( $booking_session[$product_id]['duration'] ) ) {

            ob_start();
            $fragments = ob_get_clean();

                $data = array(
                    'fragments' => apply_filters( 'easy_booking_fragments', array(
                        'span.price' => '<span class="price">' . sprintf( get_woocommerce_price_format(), $currency, $new_price ) . '</span>'
                        )
                    )
                );

            wp_send_json( $data );
            die();
        }

    }

    public function easy_booking_hide_notice() {
        $notice = isset( $_POST['notice'] ) ? $_POST['notice'] : '';

        if ( get_option( 'easy_booking_display_notice_' . $notice ) != 1 )
            update_option( 'easy_booking_display_notice_' . $notice, 1 );

        die();
    }
}

return new WCEB_Ajax();

endif;