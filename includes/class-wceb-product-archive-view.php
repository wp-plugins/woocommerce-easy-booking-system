<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Product_Archive_View' ) ) :

class WCEB_Product_Archive_View {

	public function __construct() {
        add_filter( 'woocommerce_loop_add_to_cart_link', array($this, 'easy_booking_custom_loop_add_to_cart' ), 10, 2 );
	}

    /**
    *
    * Adds a custom text link on product archive page
    *
    * @param str $content - Current text
    * @param WC_Product $product
    * @return str $content - Custom or current text
    *
    **/
    public function easy_booking_custom_loop_add_to_cart( $content, $product ) {
        global $post, $product;

        if ( ! $product )
            return;

        // Is product bookable ?
        $is_bookable = get_post_meta( $product->id, '_booking_option', true );

        // If product is bookable
        if ( isset( $is_bookable ) && $is_bookable === 'yes' ) {
            $link = get_permalink( $product->id );
            $label = __( 'Select dates', 'easy_booking' );
            return '<a href="' . esc_url( $link ) . '" rel="nofollow" class="product_type_variable button">' . esc_html( $label  ) . '</a>';
        } else {
            return $content;
        }
    }
}

return new WCEB_Product_Archive_View();

endif;