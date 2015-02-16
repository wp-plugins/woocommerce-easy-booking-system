<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCEB_Admin_Assets' ) ) :

class WCEB_Admin_Assets {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'easy_booking_enqueue_admin_scripts' ));
	}

	public function easy_booking_enqueue_admin_scripts() {
        global $post;

        $screen = get_current_screen();
        $this->options = get_option('easy_booking_settings');

        // Get page language in order to load Pickadate translation
        $site_language = get_bloginfo( 'language' );
        $lang = str_replace( '-', '_', $site_language );

        if ( in_array( $screen->id, array( 'product' ) ) ) {
            $is_bookable = get_post_meta($post->ID, '_booking_option', true);

            wp_enqueue_script( 'ebs-admin-product', plugins_url('assets/js/admin/ebs-admin-product.min.js', WCEB_PLUGIN_FILE), array('jquery'), '1.0', true );
            // wp_enqueue_script( 'ebs-admin-product', plugins_url('assets/js/admin/dev/ebs-admin-product.js', WCEB_PLUGIN_FILE), array('jquery'), '1.0', true );

            wp_register_style( 'static-picker', plugins_url( 'assets/css/admin/static-picker.min.css', WCEB_PLUGIN_FILE ), true);
            wp_enqueue_style( 'static-picker' );

            wp_localize_script( 'ebs-admin-product', 'options',
                array( 
                    'booking_option' => $is_bookable
                )
            );
        }

        if ( in_array( $screen->id, array( 'shop_order' ) ) ) {

            // Calculation mode (Days or Nights)
            $calc_mode = $this->options['easy_booking_calc_mode'];

            // Calendar theme
            $theme = $this->options['easy_booking_calendar_theme'];

            wp_enqueue_script( 'pickadate-custom-admin', plugins_url( 'assets/js/admin/pickadate-custom-admin.min.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);
            // wp_enqueue_script( 'pickadate-custom-admin', plugins_url('assets/js/admin/dev/pickadate-custom-admin.js', WCEB_PLUGIN_FILE), array('jquery', 'pickadate'), '1.0', true );

            wp_register_style( 'picker', plugins_url( 'assets/css/' . $theme . '.min.css', WCEB_PLUGIN_FILE ), true);
            wp_enqueue_style( 'picker' );

            wp_localize_script( 'pickadate-custom-admin', 'order_ajax_info',
                array( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'order_id' => $post->ID,
                    'calc_mode' => $calc_mode
                )
            );

        }

        if ( in_array( $screen->id, array( 'product' ) ) || in_array( $screen->id, array( 'shop_order' ) ) ) {

            // Concatenated and minified script including picker.js, picker.date.js and legacy.js
            wp_enqueue_script( 'pickadate', plugins_url( 'assets/js/pickadate.min.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker', plugins_url( 'assets/js/dev/picker.js', WCEB_PLUGIN_FILE  ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker.date', plugins_url( 'assets/js/dev/picker.date.js', WCEB_PLUGIN_FILE  ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'legacy', plugins_url( 'assets/js/dev/legacy.js', WCEB_PLUGIN_FILE  ), array('jquery'), '1.0', true);

            wp_enqueue_script( 'datepicker.language', plugins_url( 'assets/js/translations/' . $lang . '.js', WCEB_PLUGIN_FILE  ), array('jquery', 'pickadate', 'pickadate-custom-admin'), '1.0', true);
        }

    }
}

return new WCEB_Admin_Assets();

endif;