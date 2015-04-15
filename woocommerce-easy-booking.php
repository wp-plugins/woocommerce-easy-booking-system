<?php
/*
Plugin Name: Woocommerce Easy Booking
Plugin URI: http://herownsweetway.com/product/woocommerce-easy-booking/
Description: Allows users to rent or book products
Version: 1.5
Author: @_Ashanna
Author URI: http://ashanna.com
Licence : GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Easy_booking' ) ) :

class Easy_booking {

    protected static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {
        $plugin = plugin_basename( __FILE__ );

        $active_plugins = (array) get_option( 'active_plugins', array() );

        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
        }

        // Check if WooCommerce is active
        if ( array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) || in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
            add_action( 'plugins_loaded', array( $this, 'easy_booking_init' ), 10 );
            add_filter( 'plugin_action_links_' . $plugin, array( $this, 'easy_booking_add_settings_link' ) );

            add_action( 'admin_notices', array($this, 'easy_booking_add_notices') );
        }

    }

    public function easy_booking_init() {
        define( 'WCEB_PLUGIN_FILE', __FILE__ );
        load_plugin_textdomain( 'easy_booking', false, basename( dirname(__FILE__) ) . '/languages/' );

        $this->easy_booking_includes();

        if ( is_admin() ) {
           $this->easy_booking_admin_includes(); 
        }
        
        if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
            $this->easy_booking_frontend_includes();
        }
    }

    public function easy_booking_includes() {
        include_once( 'includes/settings/class-wceb-settings.php' );
        include_once( 'includes/class-wceb-ajax.php' );
        include_once( 'includes/class-wceb-checkout.php' );
    }

    public function easy_booking_admin_includes() {
        include_once( 'includes/admin/class-wceb-product-settings.php' );
        include_once( 'includes/admin/class-wceb-order.php' );
        include_once( 'includes/admin/class-wceb-admin-assets.php' );
    }

    public function easy_booking_frontend_includes() {
        include_once( 'includes/class-wceb-product-archive-view.php' );
        include_once( 'includes/class-wceb-product-view.php' );
        include_once( 'includes/class-wceb-assets.php' );
        include_once( 'includes/class-wceb-cart.php' );
    }

    public function easy_booking_add_notices() {
        if ( get_option( 'easy_booking_display_notice_ebac-info' ) != 1 )
            include( 'includes/admin/views/wceb-html-notice-ebac.php' );

        if ( get_option( 'easy_booking_display_notice_ebdd-info' ) != 1 )
            include( 'includes/admin/views/wceb-html-notice-ebdd.php' );
    }

    // Add settings link
    public function easy_booking_add_settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=easy-booking">' . __('Settings', 'easy_booking') . '</a>';
        array_push( $links, $settings_link );

        return $links;
    }

    public function easy_booking_is_bookable( $product_id, $variation_id = '' ) {
        $is_bookable = false;
        $product = wc_get_product( $product_id );
        if ( $product->is_type( 'simple' ) ) {

            $is_bookable = get_post_meta( $product_id, '_booking_option', true );

        } else if ( $product->is_type( 'variable' ) ) {

            if ( ! empty( $variation_id ) ) {
                $is_bookable = get_post_meta( $variation_id, '_booking_option', true );
            } else {
                $variation_ids = $product->get_children();

                $bookable_variation = array();
                if ( $variation_ids ) foreach ( $variation_ids as $variation_id ) {
                    
                    if ( ! get_post_meta( $variation_id, '_booking_option', true ) === 'yes' ) {
                        continue;
                    } else {
                        $bookable_variation[] = $variation_id;
                    }

                }

                if ( ! empty( $bookable_variation ) )
                    $is_bookable = 'yes';

            }
            
        }

        return $is_bookable;
            
    }

}

function WCEB() {
    return Easy_booking::instance();
}

WCEB();

endif;