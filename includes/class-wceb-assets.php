<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Assets' ) ) :

class WCEB_Assets {

	public function __construct() {
        // Get plugin options values
        $this->options = get_option('easy_booking_settings');
        
		if ( ! is_admin() )
            add_action( 'wp_enqueue_scripts', array( $this, 'easy_booking_enqueue_scripts' ));
	}

	public function easy_booking_enqueue_scripts() {
        global $post;

        if ( ! is_product() )
            return;

        $post_id = $post->ID;
        $product = wc_get_product( $post_id );
        $product_type = $product->product_type;
        
        // Load scripts only on product page if "booking" option is checked
        $is_bookable = WCEB()->easy_booking_is_bookable( $post_id );

        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

        if ( isset( $is_bookable ) && $is_bookable === 'yes' ) {

            if ( $product->is_type( 'simple' ) ) {

                $booking_min = get_post_meta($post->ID, '_booking_min', true) ? get_post_meta($post->ID, '_booking_min', true) : 0;
                $booking_max = get_post_meta($post->ID, '_booking_max', true) ? get_post_meta($post->ID, '_booking_max', true) : 0;
                $first_available_date = get_post_meta($post->ID, '_first_available_date', true) ? get_post_meta($post->ID, '_first_available_date', true) : 0;

                $price = $tax_display_mode === 'incl' ? $product->get_price_including_tax() : $product->get_price_excluding_tax(); // Product price (Regular or sale)

            } else if ( $product->is_type( 'variable' ) ) {

                $variation_ids = $product->get_children();

                $price = array();
                $booking_min = array();
                $booking_max = array();
                $first_available_date = array();
                if ( $variation_ids ) foreach ( $variation_ids as $variation_id ) {

                    $variation = wc_get_product( $variation_id );
                    $price[$variation_id] = $tax_display_mode === 'incl' ? $variation->get_price_including_tax() : $variation->get_price_excluding_tax(); // Product price (Regular or sale)
                    $booking_min[$variation_id] = get_post_meta($variation_id, '_booking_min', true) ? get_post_meta($variation_id, '_booking_min', true) : 0;
                    $booking_max[$variation_id] = get_post_meta($variation_id, '_booking_max', true) ? get_post_meta($variation_id, '_booking_max', true) : 0;
                    $first_available_date[$variation_id] = get_post_meta($variation_id, '_first_available_date', true) ? get_post_meta($variation_id, '_first_available_date', true) : 0;

                }

            }

            $this->easy_booking_load_scripts( $product_type, $price, $booking_min, $booking_max, $first_available_date );

        }
        
    }

    public function easy_booking_load_scripts( $product_type, $price, $booking_min, $booking_max, $first_available_date ) {

        // Get page language in order to load Pickadate translation
        $site_language = get_bloginfo( 'language' );
        $lang = str_replace( '-', '_', $site_language );

        // Calculation mode (Days or Nights)
        $calc_mode = $this->options['easy_booking_calc_mode'];
        $start_date_text = $this->options['easy_booking_start_date_text'];
        $end_date_text = $this->options['easy_booking_end_date_text'];

        // Calendar theme
        $theme = $this->options['easy_booking_calendar_theme'];

        // Concatenated and minified script including picker.js, picker.date.js and legacy.js
        wp_enqueue_script( 'pickadate', plugins_url( 'assets/js/pickadate.min.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
        // wp_enqueue_script( 'pickadate', plugins_url( 'assets/js/dev/picker.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
        // wp_enqueue_script( 'picker.date', plugins_url( 'assets/js/dev/picker.date.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);
        // wp_enqueue_script( 'legacy', plugins_url( 'assets/js/dev/legacy.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate', 'picker.date'), '1.0', true);

        wp_enqueue_script( 'pickadate-custom', plugins_url( 'assets/js/pickadate-custom.min.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);
        // wp_enqueue_script( 'pickadate-custom', plugins_url( 'assets/js/dev/pickadate-custom.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {
            $blog_id = get_current_blog_id();
            wp_register_style( 'picker', plugins_url('assets/css/' . $theme . '.' . $blog_id . '.min.css.php', WCEB_PLUGIN_FILE), true);
        } else {
            wp_register_style( 'picker', plugins_url('assets/css/' . $theme . '.min.css.php', WCEB_PLUGIN_FILE), true);
        }
        
        wp_enqueue_style( 'picker' );

        if ( is_rtl() ) {
            // Load Right to left CSS file
            wp_register_style( 'rtl-style', plugins_url('assets/css/rtl.min.css', WCEB_PLUGIN_FILE ), true );
            // wp_register_style( 'rtl-style', plugins_url('assets/css/dev/rtl.css', WCEB_PLUGIN_FILE), true);
            wp_enqueue_style( 'rtl-style', array('picker') );  
        }

        wp_enqueue_script( 'datepicker.language', plugins_url( 'assets/js/translations/' . $lang . '.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate', 'pickadate-custom'), '1.0', true);

        $pickadate_params = array(
            'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
            'product_type' => esc_html( $product_type ),
            'product_price' => is_array( $price ) ? array_map( 'absint', $price ) : absint( $price ),
            'calc_mode' => esc_html( $calc_mode ),
            'start_text' => esc_html( $start_date_text ),
            'end_text' => esc_html( $end_date_text ),
            'min' => is_array( $booking_min ) ? array_map( 'absint', $booking_min ) : absint( $booking_min ),
            'max' => is_array( $booking_max ) ? array_map( 'absint', $booking_max ) : absint( $booking_max ),
            'first_date' => is_array( $first_available_date ) ? array_map( 'absint', $first_available_date ) : absint( $first_available_date ),
            'currency_format_num_decimals' => absint( get_option( 'woocommerce_price_num_decimals' ) ),
            'currency_format_symbol'       => get_woocommerce_currency_symbol(),
            'currency_format_decimal_sep'  => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
            'currency_format_thousand_sep' => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) )
        );

        wp_localize_script( 'pickadate-custom', 'ajax_object', $pickadate_params );

    }
}

return new WCEB_Assets();

endif;