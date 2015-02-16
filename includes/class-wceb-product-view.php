<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Product_View' ) ) :

class WCEB_Product_View {

	public function __construct() {
        // Get plugin options values
        $this->options = get_option('easy_booking_settings');
        
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'easy_booking_before_add_to_cart_button' ));
        add_filter( 'woocommerce_get_price_html', array( $this, 'easy_booking_add_price_html' ), 10, 2 );
	}

    /**
    *
    * Adds a custom form to the product page.
    *
    **/
    public function easy_booking_before_add_to_cart_button() {
        global $post, $product;

        // Is product bookable ?
        $is_bookable = get_post_meta($post->ID, '_booking_option', true);
        $info_text = wpautop( wptexturize( $this->options['easy_booking_info_text'] ) );
        $start_date_text = $this->options['easy_booking_start_date_text'];
        $end_date_text = $this->options['easy_booking_end_date_text'];
        $product_price = $product->get_price();
        $currency = get_woocommerce_currency_symbol(); // Currency

        // Product is bookable
        if ( isset($is_bookable) && $is_bookable == 'yes' ) {

            // Display info text
            if ( isset( $info_text ) && ! empty ( $info_text ) ) {
                echo apply_filters( 'easy_booking_before_picker_form',
                    '<div class="woocommerce-info">' . $info_text . '</div>', $info_text );
            }

            echo '<div class="wc_ebs_errors">' . wc_print_notices() . '</div>';

            // Please do not remove inputs' attributes (classes, ids, etc.)
            echo '<div class="wceb_picker_wrap">';
            echo apply_filters( 'easy_booking_picker_form',
                '<p>
                    <label for="start_date">' . $start_date_text . ' : </label>
                    <input type="hidden" id="variation_id" name="variation_id" data-product_id="' . $product->id . '" value="">
                    <input type="text" id="start_date" class="datepicker datepicker_start" data-value="">
                </p>
                <p>
                    <label for="end_date">' . $end_date_text . ' : </label>
                    <input type="text" id="end_date" class="datepicker datepicker_end" data-value="">
                </p>', $start_date_text, $end_date_text );
            echo '</div>';

            // If product is not variable, add a new price field before add to cart button
            if ( ! $product->is_type( 'variable' ) )
                echo '<p class="booking_price"><span class="price">' . sprintf( get_woocommerce_price_format(), $currency, $product_price ) . '</span></p>';

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
    public function easy_booking_add_price_html($content) {
        global $post;
        
        $product_id = isset($_POST['product_id']) && intval($_POST['product_id']) ? $_POST['product_id'] : $post->ID; // Product ID

        $is_bookable = get_post_meta($product_id, '_booking_option', true); // Is it bookable ?

        // If bookable, return a price / day. If not, return normal price
        if ( isset( $is_bookable ) && $is_bookable == 'yes' ) {
            $display_price = $content . __(' / day', 'easy_booking');
            return apply_filters( 'easy_booking_display_price', $display_price, $content );
        } else {
            return $content;
        }

    }
}

return new WCEB_Product_View();

endif;