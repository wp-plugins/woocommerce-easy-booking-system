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

        $admin_picker_script = '';

        // Concatenated and minified script including picker.js, picker.date.js and legacy.js
        wp_register_script( 'pickadate', plugins_url( 'assets/js/pickadate.min.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true );
        // wp_register_script( 'picker', plugins_url( 'assets/js/dev/picker.js', WCEB_PLUGIN_FILE  ), array('jquery'), '1.0', true );
        // wp_register_script( 'picker.date', plugins_url( 'assets/js/dev/picker.date.js', WCEB_PLUGIN_FILE  ), array('jquery'), '1.0', true );
        // wp_register_script( 'legacy', plugins_url( 'assets/js/dev/legacy.js', WCEB_PLUGIN_FILE  ), array('jquery'), '1.0', true );

        // Calendar theme
        $theme = $this->options['easy_booking_calendar_theme'];

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {
            $blog_id = get_current_blog_id();
            wp_register_style( 'picker', plugins_url('assets/css/' . $theme . '.' . $blog_id . '.min.css', WCEB_PLUGIN_FILE), true);
        } else {
            wp_register_style( 'picker', plugins_url('assets/css/' . $theme . '.min.css', WCEB_PLUGIN_FILE), true);
        }

        if ( in_array( $screen->id, array( 'product' ) ) ) {

            $product = wc_get_product( $post->ID );

            wp_enqueue_script( 'ebs-admin-product', plugins_url('assets/js/admin/ebs-admin-product.min.js', WCEB_PLUGIN_FILE), array('jquery'), '1.0', true );
            // wp_enqueue_script( 'ebs-admin-product', plugins_url('assets/js/admin/dev/ebs-admin-product.js', WCEB_PLUGIN_FILE), array('jquery'), '1.0', true );

            wp_register_style( 'static-picker', plugins_url( 'assets/css/admin/static-picker.min.css', WCEB_PLUGIN_FILE ), true);
            // wp_register_style( 'static-picker', plugins_url( 'assets/css/admin/dev/static-picker.css', WCEB_PLUGIN_FILE ), true);
            wp_enqueue_style( 'static-picker' );

            $admin_picker_script = 'ebs-admin-product';
            
        }

        if ( in_array( $screen->id, array( 'shop_order' ) ) ) {

            // Calculation mode (Days or Nights)
            $calc_mode = $this->options['easy_booking_calc_mode'];

            wp_enqueue_script( 'pickadate-custom-admin', plugins_url( 'assets/js/admin/pickadate-custom-admin.min.js', WCEB_PLUGIN_FILE ), array('jquery', 'pickadate'), '1.0', true);
            // wp_enqueue_script( 'pickadate-custom-admin', plugins_url('assets/js/admin/dev/pickadate-custom-admin.js', WCEB_PLUGIN_FILE), array('jquery', 'pickadate'), '1.0', true );
            
            wp_enqueue_style( 'picker' );

            wp_localize_script( 'pickadate-custom-admin', 'order_ajax_info',
                array( 
                    'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
                    'order_id' => $post->ID,
                    'calc_mode' => esc_html( $calc_mode )
                )
            );

            $admin_picker_script = 'pickadate-custom-admin';

        }

        if ( in_array( $screen->id, array( 'product' ) ) || in_array( $screen->id, array( 'shop_order' ) ) ) {

            wp_enqueue_script( 'pickadate' );

            if ( is_rtl() ) {
                // Load Right to left CSS file
                wp_register_style( 'rtl-style', plugins_url( 'assets/css/rtl.min.css', WCEB_PLUGIN_FILE ), true );
                // wp_register_style( 'rtl-style', plugins_url('assets/css/dev/rtl.css', WCEB_PLUGIN_FILE), true);
                wp_enqueue_style( 'rtl-style' );  
            }

            wp_enqueue_script( 'datepicker.language', plugins_url( 'assets/js/translations/' . $lang . '.js', WCEB_PLUGIN_FILE  ), array('jquery', 'pickadate', $admin_picker_script), '1.0', true);
        }

        // JS for admin notices
        // wp_enqueue_script( 'easy_booking_functions', plugins_url( 'assets/js/admin/dev/wceb-admin-functions.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
        wp_enqueue_script( 'easy_booking_functions', plugins_url( 'assets/js/admin/wceb-admin-functions.min.js', WCEB_PLUGIN_FILE ), array('jquery'), '1.0', true);
        
        // CSS for admin notices
        wp_enqueue_style( 'easy_booking_notices', plugins_url(  'assets/css/admin/notices.min.css', WCEB_PLUGIN_FILE ) );
        // wp_enqueue_style( 'easy_booking_notices', plugins_url(  'assets/css/admin/dev/notices.css', WCEB_PLUGIN_FILE ) );

        wp_localize_script( 'easy_booking_functions', 'ajax_object',
            array( 
                'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) )
            )
        );

    }
}

return new WCEB_Admin_Assets();

endif;