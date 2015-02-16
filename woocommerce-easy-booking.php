<?php
/*
Plugin Name: Woocommerce Easy Booking
Plugin URI: https://wordpress.org/plugins/woocommerce-easy-booking-system/
Description: Allows users to rent or book products
Version: 1.4.2
Author: @_Ashanna
Author URI: http://ashanna.com
Licence : GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Easy_booking' ) ) :

class Easy_booking {

    public function __construct() {
        $plugin = plugin_basename( __FILE__ );

        // Check if WooCommerce is active
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_action( 'plugins_loaded', array( $this, 'easy_booking_init' ), 10 );
            add_filter( 'plugin_action_links_' . $plugin, array( $this, 'easy_booking_add_settings_link' ) );
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

    // Add settings link
    public function easy_booking_add_settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=easy-booking">' . __('Settings', 'easy_booking') . '</a>';
        array_push( $links, $settings_link );

        return $links;
    }

}

return new Easy_booking();

endif;