<?php
/*
Plugin Name: Woocommerce Easy Booking
Plugin URI: http://herownsweetcode.com/product/woocommerce-easy-booking/
Description: Allows users to rent or book products
Version: 1.6.1
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
        include_once( 'includes/class-wceb-ajax.php' );
        include_once( 'includes/class-wceb-checkout.php' );
    }

    public function easy_booking_admin_includes() {
        include_once( 'includes/settings/class-wceb-settings.php' );
        include_once( 'includes/settings/class-wceb-reports.php' );
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

        // Backward compatibility 1.6
        if ( get_option( 'easy_booking_update_variable_product_meta' ) != 1 )
            $this->easy_booking_update_variable_product_meta();
    }

    public function easy_booking_update_variable_product_meta() {
        $args = array(
            'post_type'      => array( 'product' ),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'order'          => 'ASC',
            'orderby'        => 'parent title'
        );

        $posts = get_posts( $args );

        if ( $posts ) foreach ( $posts as $post ) {

            $product_id = $post->ID;

            $product = wc_get_product( $product_id );
            if ( $product->is_type('variable') ) {
                $is_bookable = $this->easy_booking_is_bookable( $product_id );
                if ( $is_bookable === 'yes' ) update_post_meta( $product_id, '_booking_option', 'yes' );
            }

        }

        update_option( 'easy_booking_update_variable_product_meta', 1 );
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

        if ( ! $product || ! is_object( $product ) )
            return false;

        if ( $product->is_type( 'simple' ) || $product->is_type( 'variation' ) ) {

            $is_bookable = get_post_meta( $product_id, '_booking_option', true );

        } else if ( $product->is_type( 'variable' ) ) {

            if ( ! empty( $variation_id ) ) {
                $is_bookable = get_post_meta( $variation_id, '_booking_option', true );
            } else {
                $variation_ids = $product->get_children();

                $bookable_variation = array();
                if ( $variation_ids ) foreach ( $variation_ids as $variation_id ) {
                    $variation_is_bookable = get_post_meta( $variation_id, '_booking_option', true );
                    
                    if ( $variation_is_bookable !== 'yes' ) {
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

    public function easy_booking_get_booked_items_from_orders() {

        $args = array(
            'post_type' => 'shop_order',
            'post_status' => apply_filters( 
                                'easy_booking_get_order_statuses',
                                array(
                                    'wc-pending',
                                    'wc-processing',
                                    'wc-on-hold',
                                    'wc-completed',
                                    'wc-refunded'
                                ) ),
            'posts_per_page' => -1
        );

        $query_orders = new WP_Query( $args );
        $products = array();
        foreach ( $query_orders->posts as $post ) :

            $order_id = $post->ID;
            $order = new WC_Order( $order_id );
            $items = $order->get_items();

            if ( $items ) foreach ( $items as $item_id => $item ) {

                $product_id = $item['product_id'];
                $variation_id = $item['variation_id'];

                $product = $order->get_product_from_item( $item );

                $is_bookable = WCEB()->easy_booking_is_bookable( $product_id, $variation_id );

                if ( isset( $is_bookable ) && $is_bookable === 'yes' && ! empty( $product->id ) ) {

                    if ( isset( $item['ebs_start_format'] ) && isset( $item['ebs_end_format'] ) ) {

                        $id = empty( $variation_id ) || $variation_id === '0' ? $product_id : $variation_id;
                        $start = $item['ebs_start_format'];
                        $end = $item['ebs_end_format'];

                        $quantity = intval( $item['qty'] );

                        $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

                        if ( $refunded_qty > 0 )
                            $quantity = $quantity - $refunded_qty;

                        if ( $quantity <= 0 )
                            continue;

                        $products[] = apply_filters( 'easy_booking_booked_reports', array(
                            'product_id' => $id,
                            'order_id' => $order_id,
                            'start' => $start,
                            'end' => $end,
                            'qty' => $quantity
                        ));

                    }

                }
            
            }

        endforeach;

        $booked = array();
        if ( $products ) foreach ( $products as $booked_product ) {

            $product_id = $booked_product['product_id'];
            $start = $booked_product['start'];
            $end = $booked_product['end'];
            $quantity = intval( $booked_product['qty'] );

            unset( $booked_product['product_id'] );
            
            $booked[$product_id][] = $booked_product;

        }

        return array_filter( $booked );

    }

    // CSS minifying function
    public function easy_booking_minify_css( $css ) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove space after colons
        $css = str_replace(': ', ':', $css);

        // Remove space before brackets
        $css = str_replace(' {', '{', $css);

        // Remove whitespace
        $css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );

        return $css;
    }

}

function WCEB() {
    return Easy_booking::instance();
}

WCEB();

endif;