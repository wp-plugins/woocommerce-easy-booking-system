<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Product_View' ) ) :

class WCEB_Product_View {

	public function __construct() {
        // Get plugin options values
        $this->options = get_option('easy_booking_settings');
        
        add_filter( 'woocommerce_available_variation', array( $this, 'easy_booking_add_variation_bookable_attribute' ), 10, 3);
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'easy_booking_before_add_to_cart_button' ), 20);
        add_filter( 'woocommerce_get_price_html', array( $this, 'easy_booking_add_price_html' ), 10, 1 );
	}

    public function easy_booking_add_variation_bookable_attribute( $available_variations, $product, $variation ) {
        $is_bookable = get_post_meta( $variation->variation_id, '_booking_option', true );
        
        if ( empty( $is_bookable ) )
            $is_bookable = false;

        $available_variations['is_bookable'] = $is_bookable;

        return $available_variations;
    }

    /**
    *
    * Adds a custom form to the product page.
    *
    **/
    public function easy_booking_before_add_to_cart_button() {
        global $post, $product;

        $product = wc_get_product( $product->id );

        // Is product bookable ?
        $is_bookable = get_post_meta( $product->id, '_booking_option', true );
        
        $info_text = $this->options['easy_booking_info_text'];
        $start_date_text = $this->options['easy_booking_start_date_text'];
        $end_date_text = $this->options['easy_booking_end_date_text'];
        $product_price = $product->get_price();
        
        $args = apply_filters( 'easy_booking_new_price_args', array() );

        // Product is bookable
        if ( isset( $is_bookable ) && $is_bookable === 'yes' ) {

            // Display info text
            if ( isset( $info_text ) && ! empty ( $info_text ) ) {
                echo apply_filters( 'easy_booking_before_picker_form',
                    '<div class="woocommerce-info">' . wpautop( esc_textarea( $info_text ) ) . '</div>', $info_text );
            }

            echo '<div class="wc_ebs_errors">' . wc_print_notices() . '</div>';

            // Please do not remove inputs' attributes (classes, ids, etc.)
            echo '<div class="wceb_picker_wrap">';
            echo apply_filters( 'easy_booking_picker_form',
                '<p class="form-row form-row-wide">
                    <label for="start_date">' . esc_html( $start_date_text ) . '</label>
                    <input type="hidden" id="variation_id" name="variation_id" data-product_id="' . absint ( $product->id ) . '" value="">
                    <input type="text" id="start_date" class="datepicker datepicker_start" data-value="" placeholder="' . esc_html( $start_date_text ) . '">
                </p>
                <p class="form-row form-row-wide">
                    <label for="end_date">' . esc_html( $end_date_text ) . '</label>
                    <input type="text" id="end_date" class="datepicker datepicker_end" data-value="" placeholder="' . esc_html( $end_date_text ) . '">
                </p>', $start_date_text, $end_date_text, $product );
            echo '</div>';

            // If product is not variable, add a new price field before add to cart button
            if ( ! $product->is_type( 'variable' ) )
                echo '<p class="booking_price"><span class="price">' . wc_price( $product_price, $args ) . '</span></p>';

        }
    }

    /**
    *
    * Displays a custom price if the product is bookable on the product page
    *
    * @param str $content - Product price
    * @return str $content - Custom or base price
    *
    **/
    public function easy_booking_add_price_html( $content ) {
        global $post;
        
        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : $post->ID; // Product ID
        
        $is_bookable = get_post_meta( $product_id, '_booking_option', true ); // Is it bookable ?
        $calc_mode = $this->options['easy_booking_calc_mode'];

        // If bookable, return a price / day. If not, return normal price
        if ( isset( $is_bookable ) && $is_bookable === 'yes' ) {

            if ( $calc_mode === 'nights' ) {
                $price_text = __(' / night', 'easy_booking');
            } else {
                $price_text = __(' / day', 'easy_booking');
            }
            
            $display_price = $content . '<span class="wceb-price-format">' . $price_text . '</span>';

            return apply_filters( 'easy_booking_display_price', $display_price, $content );
        } else {
            return $content;
        }

    }
}

return new WCEB_Product_View();

endif;