<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Assets' ) ) :

/**
 * WC_Admin_Assets Class
 */
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

        // Get page language in order to load Pickadate translation
        $site_language = get_bloginfo( 'language' );
        $lang = str_replace( '-', '_', $site_language );

        $post_id = $post->ID;
        
        // Load scripts only on product page if "booking" option is checked
        $is_bookable = get_post_meta($post->ID, '_booking_option', true);

        if ( is_product() && isset( $is_bookable ) && $is_bookable == 'yes' ) {

            // Calculation mode (Days or Nights)
            $calc_mode = $this->options['easy_booking_calc_mode'];

            // Calendar theme
            $theme = $this->options['easy_booking_calendar_theme'];

            $product = wc_get_product( $post_id );
            $product_type = $product->product_type;
            $booking_min = get_post_meta($post->ID, '_booking_min', true) ? get_post_meta($post->ID, '_booking_min', true) : 0;
            $booking_max = get_post_meta($post->ID, '_booking_max', true) ? get_post_meta($post->ID, '_booking_max', true) : 0;
            $first_available_date = get_post_meta($post->ID, '_first_available_date', true) ? get_post_meta($post->ID, '_first_available_date', true) : 0;

            // Concatenated and minified script including picker.js, picker.date.js and legacy.js
            wp_enqueue_script( 'pickadate', plugins_url( 'assets/js/pickadate.min.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker', plugins_url( 'assts/js/dev/picker.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker.date', plugins_url( 'assts/js/dev/picker.date.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'legacy', plugins_url( 'assts/js/dev/legacy.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);

            wp_enqueue_script( 'pickadate-custom', plugins_url( 'assets/js/pickadate-custom.min.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);
            // wp_enqueue_script( 'pickadate-custom', plugins_url( 'assets/js/dev/pickadate-custom.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);

            wp_enqueue_script( 'datepicker.language', plugins_url( 'assets/js/translations/' . $lang . '.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate', 'pickadate-custom'), '1.0', true);

            wp_register_style( 'picker', plugins_url('assets/css/' . $theme . '.min.css', WCEB_PLUGIN_FILE), true);
            wp_enqueue_style( 'picker' );

            // in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
            wp_localize_script( 'pickadate-custom', 'ajax_object',
                array( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'product_type' => $product_type,
                    'calc_mode' => $calc_mode,
                    'min' => $booking_min,
                    'max' => $booking_max,
                    'first_date' => $first_available_date
                )
            );
        }
    }
}

return new WCEB_Assets();

endif;