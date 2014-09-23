<?php

class WC_EBS extends WC_AJAX {

    public function __construct() {

        // get plugin options values
        $this->options = get_option('wc_ebs_options');

        add_action( 'wp_enqueue_scripts', array( $this, 'wc_ebs_enqueue_scripts' ));
        add_action( 'product_type_options', array( $this, 'wc_ebs_add_product_option_pricing' ));
        add_filter( 'woocommerce_process_product_meta', array( $this, 'wc_ebs_add_custom_price_fields_save' ));
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'wc_ebs_before_add_to_cart_button' ));
        add_filter( 'woocommerce_get_price_html', array( $this, 'wc_ebs_add_price_html' ), 10, 2 );
        add_action( 'wp_ajax_add_new_price', array( $this, 'wc_ebs_get_new_price' ));
        add_action( 'wp_ajax_nopriv_add_new_price', array( $this, 'wc_ebs_get_new_price' ));
        add_filter( 'add_to_cart_fragments', array( $this, 'wc_ebs_new_price_fragment' ));
        add_filter( 'woocommerce_loop_add_to_cart_link', array($this, 'wc_ebs_custom_loop_add_to_cart' ), 10, 2 );
    }

    public function wc_ebs_enqueue_scripts() {
        global $woocommerce, $post;

        // Get page language in order to load Pickadate translation
        $site_language = get_bloginfo( 'language' );
        $lang = str_replace("-","_", $site_language);
        
        // Load scripts only on product page if "booking" option is checked
        $wc_ebs_options = get_post_meta($post->ID, '_booking_option', true);

        if ( is_product() && $wc_ebs_options) {

            // Concatenated and minified script including datepick.js, legacy.js, picker.js and picker.date.js
            wp_enqueue_script( 'datepicker', plugins_url( '/js/pickadate.min.js', __FILE__ ), array('jquery'), '1.0', true);

            // wp_enqueue_script( 'picker', plugins_url( '/js/picker.js', __FILE__ ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'picker.date', plugins_url( '/js/picker.date.js', __FILE__ ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'legacy', plugins_url( '/js/legacy.js', __FILE__ ), array('jquery'), '1.0', true);
            // wp_enqueue_script( 'datepick', plugins_url( '/js/datepick.js', __FILE__ ), array('jquery'), '1.0', true);

            wp_enqueue_script( 'datepicker.language', plugins_url( '/js/translations/' . $lang . '.js', __FILE__ ), array('jquery'), '1.0', true);

            wp_register_style( 'picker', plugins_url('/css/default.min.css', __FILE__), true);

            wp_enqueue_style( 'picker' );
            // wp_enqueue_style( 'picker.date' );

            // in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
            wp_localize_script( 'datepicker', 'ajax_object',
                    array( 
                        'ajax_url' => admin_url( 'admin-ajax.php' )
                    )
                );
        }
    }

    // Add checkbox to the product admin page
    public function wc_ebs_add_product_option_pricing( $product_type_options ) {
        global $woocommerce, $post;

        $product_type_options['booking_option'] = array(
            'id'            => '_booking_option',
            'wrapper_class' => 'show_if_simple show_if_variable',
            'label'         => __( 'Bookable', 'wc_ebs' ),
            'description'   => __( 'Bookable products can be rent or booked on a daily schedule', 'wc_ebs' ),
            'default'       => 'no'
        );

        return $product_type_options;
    }

    // Save checkbox value to the product admin page
    public function wc_ebs_add_custom_price_fields_save( $post_id ) {

        $woocommerce_checkbox = isset( $_POST['_booking_option'] ) ? 'yes' : '';
        update_post_meta( $post_id, '_booking_option', $woocommerce_checkbox );

    }

    // Add custom form to the product page.
    public function wc_ebs_before_add_to_cart_button() {
        global $woocommerce, $post, $product;

        // Is product bookable ?
        $wc_ebs_options = get_post_meta($post->ID, '_booking_option', true);

        if ( isset($wc_ebs_options) && $wc_ebs_options == 'yes' ) {

            // Display info text
            if ( isset( $this->options['wc_ebs_info_text_display'] ) ) {
                echo '<p class="woocommerce-info">' . __( $this->options['wc_ebs_info_text'] ) . '</p>';
            }

            echo '<div class="wc_ebs_errors">' . wc_print_notices() . '</div>
                <p>
                    <label for="start_date">' . __( $this->options['wc_ebs_start_date_text'], 'wc_ebs' ) . ' : </label>
                    <input type="hidden" id="variation_id" name="variation_id" value="">
                    <input type="text" id="start_date" class="datepicker1" data-product_id="' . $product->id . '" data-variation_id="" data-value="">
                </p>
                <p>
                    <label for="end_date">' . __( $this->options['wc_ebs_end_date_text'], 'wc_ebs' ) . ' : </label>
                    <input type="text" id="end_date" class="datepicker2" data-product_id="' . $product->id . '" data-value="">
                </p>';

            if ( ! $product->is_type( 'variable' ) ) {

                echo '<p class="booking_price">
                        <span class="price"></span>
                </p>';

            }

        }
    }

    // Display base price or new price
    public function wc_ebs_add_price_html($content) {

        global $woocommerce, $post;

        $output = isset($_POST['days']) ? $_POST['days'] : 1; // Booking duration
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : $post->ID; // Product ID
        $variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : ''; // Variation ID

        $wc_ebs_options = get_post_meta($product_id, '_booking_option', true); // Is it bookable ?
        $new_price = get_post_meta($product_id, '_booking_price', true); // New booking price
        $currency = get_woocommerce_currency_symbol(); // Currency

        // Return either the new price or a price / day or normal price
        if ( isset($_POST['days']) && $_POST['days'] > 0 ) {
            return sprintf( get_woocommerce_price_format(), $currency, $new_price );
        } else if ( isset($wc_ebs_options) && $wc_ebs_options == 'yes' ) {
            return $content . __(' / day', 'wc_ebs');
        } else {
            return $content;
        }

    }

    // Calculate new price, update product meta and refresh fragments
    public function wc_ebs_get_new_price() {

        global $woocommerce, $post;

        $output = isset($_POST['days']) ? $_POST['days'] : 1; // Booking duration
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : ''; // Product ID
        $variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : ''; // Variation ID
        $start_date = isset($_POST['start']) ? $_POST['start'] : ''; // Booking start date
        $end_date = isset($_POST['end']) ? $_POST['end'] : ''; // Booking end date

        $product = get_product( $product_id ); // Product object
        
        // If product is variable, get variation price
        if ( $product->is_type( 'variable' ) ) {

            $variable_product = new WC_Product_Variation( $variation_id );
            $variation_price = $variable_product ->regular_price;
            $new_price = $variation_price * $output;

        } else {

            $price = get_post_meta($product_id,'_price', true); // Product price (Regular or sale)
            $new_price = $price * $output;

        }

        // If number of days is inferior to 0
        if ( $output <= 0 ) {
            $error_code = 1;
        }

        // If one date is empty
        if ( $start_date == '' || $end_date == '' ) {
            $error_code = 2;
        }

        // Show error message
        if ( isset( $error_code ) ) {

            $error_message = $this->wc_ebs_get_date_error( $error_code );
            wc_add_notice( $error_message, 'error' );

            $this->wc_ebs_error_fragment($error_message);

        } else {

            // Update product meta
            $this->wc_ebs_update_product_meta( $product_id, $new_price, $start_date, $end_date );

            // Update product price
            $product->get_price_html();

            // Return fragments
            $this->get_refreshed_fragments();

        }

        die();

    }

    // Get error messages
    public function wc_ebs_get_date_error( $error_code ) {

        switch ( $error_code ) {
            case 1:
                $err = __( 'Please choose valid dates', 'wc_ebs' );
            break;
            case 2:
                $err = __( 'Please choose two dates', 'wc_ebs' );
            break;
            default:
                $err = '';
            break;
        }

        return $err;
    }

    // Update product meta (New price, start date and end date)
    public function wc_ebs_update_product_meta( $product_id, $new_price, $start_date, $end_date ) {

        global $woocommerce;

        if ( get_post_meta($product_id, '_booking_price', true ) ) {
            update_post_meta($product_id, '_booking_price', $new_price);
        } else {
            add_post_meta($product_id, '_booking_price', $new_price, true);
        }

        if ( get_post_meta($product_id, '_start_date', true ) ) {
            update_post_meta($product_id, '_start_date', $start_date);
        } else {
            add_post_meta($product_id, '_start_date', $start_date, true);
        }

        if ( get_post_meta($product_id, '_end_date', true ) ) {
            update_post_meta($product_id, '_end_date', $end_date);
        } else {
            add_post_meta($product_id, '_end_date', $end_date, true); 
        }

    }

    // Update error messages with Ajax
    public function wc_ebs_error_fragment( $messages ) {

        global $woocommerce;

        header( 'Content-Type: application/json; charset=utf-8' );

        ob_start();
        wc_print_notices();
        $messages = ob_get_clean();

            $data = array(
                'errors' => array(
                    'div.wc_ebs_errors' => '<div class="wc_ebs_errors">' . $messages . '</div>'
                )
            );

        echo json_encode( $data );

        die();

    }

    // Update price fragment
    public function wc_ebs_new_price_fragment( $fragments ) {

        global $woocommerce, $post, $product;
        
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : $product->id;
        $product = get_product( $product_id );

        if ( isset($_POST['days']) ) {

            ob_start();
                echo '<span class="price">' . $product->get_price_html() . '</span>';
            $fragments['span.price'] = ob_get_clean();

        }
        

        return $fragments;

    }

    // Add custom text link on product archive
    public function wc_ebs_custom_loop_add_to_cart($content, $product) {

        global $woocommerce, $post, $product;
        $wc_ebs_options = get_post_meta($post->ID, '_booking_option', true);

        if (isset($wc_ebs_options) && $wc_ebs_options) {

            $link = get_permalink( $product->id );
            $label = __( 'Select dates', 'wc_ebs' );

            return '<a href="' . $link . '" rel="nofollow" class="product_type_variable button"><span>' . $label . '</span></a>';
        } else {
            return $content;
        }
    }

}

$wcebs = new WC_EBS;
