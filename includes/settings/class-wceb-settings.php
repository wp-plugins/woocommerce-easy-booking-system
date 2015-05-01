<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCEB_Settings {

	public function __construct() {

		// get plugin options values
		$this->options = get_option('easy_booking_settings');
		
		// initialize options the first time
		if ( ! $this->options ) {
		
		    $this->options = array( 'easy_booking_calc_mode' => 'nights',
		    						'easy_booking_info_text' => '',
		    						'easy_booking_start_date_text' => __('Start', 'easy_booking'), 
		                            'easy_booking_end_date_text' => __('End', 'easy_booking'),
		                            'easy_booking_calendar_theme' => 'default',
		                            'easy_booking_background_color' => '#FFFFFF',
		                            'easy_booking_main_color' => '#0089EC',
		                            'easy_booking_text_color' => '#000000'
		                        );

		    add_option( 'easy_booking_settings', $this->options );

		}

		if ( is_admin() ) {

			add_action( 'admin_menu', array($this, 'easy_booking_add_option_pages'), 10 );
			add_action( 'admin_init', array($this, 'easy_booking_admin_init') );

		}

	}

	public function easy_booking_add_option_pages() {
		$hook = add_menu_page( __('Easy Booking', 'easy_booking'), __('Easy Booking', 'easy_booking'), 'manage_options', 'easy-booking', '', 'dashicons-calendar-alt', 58 );
		$option_page = add_submenu_page( 'easy-booking', __('Settings', 'easy_booking'), __('Settings', 'easy_booking'), 'manage_options', 'easy-booking', array($this, 'easy_booking_option_page') );
		
		add_action( 'load-'. $hook, array($this, 'easy_booking_settings_save') );
		add_action( 'admin_print_scripts-'. $option_page, array($this, 'easy_booking_load_admin_scripts') );
	}

	public function easy_booking_settings_save() {

	  	if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
	  		$data = get_option('easy_booking_settings');
	  		$this->easy_booking_generate_css( $data );
	   	}

	}

	public function easy_booking_load_admin_scripts() {
	  	wp_enqueue_style('wp-color-picker');
	  	wp_enqueue_script('color-picker', plugins_url('assets/js/admin/script.js', WCEB_PLUGIN_FILE), array('wp-color-picker'), false, true );
	}

	// Generate static css file
	public function easy_booking_generate_css( $data ) {
		$plugin_dir = plugin_dir_path( WCEB_PLUGIN_FILE ); // Shorten code, save 1 call

        $php_files = array(
        	'default' => realpath( $plugin_dir . 'assets/css/dev/default.css.php' ),
        	'classic' => realpath( $plugin_dir . 'assets/css/dev/classic.css.php' )
        );

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$blog_id = get_current_blog_id();

			$default = str_replace('/', '\\', $plugin_dir . 'assets/css/dev/default.' . $blog_id . '.css' ); // Replace backslashes with forwardslashes
			$classic = str_replace('/', '\\', $plugin_dir . 'assets/css/dev/classic.' . $blog_id . '.css' ); // Replace backslashes with forwardslashes

			$css_files = array(
	        	'default' => $default,
	        	'classic' => $classic
	        );

		} else {
			$css_files = array(
	        	'default' => realpath( $plugin_dir . 'assets/css/dev/default.css' ),
	        	'classic' => realpath( $plugin_dir . 'assets/css/dev/classic.css' )
	        );
		}

        if ( $php_files ) foreach ( $php_files as $theme => $php_file ) {
        	ob_start(); // Capture all output (output buffering)

	        require( $php_file ); // Generate CSS
	        
	        $css = ob_get_clean(); // Get generated CSS (output buffering)

	        if ( file_exists( $css_files[$theme] ) ) {

	        	if ( is_writable( $css_files[$theme] ) )
	        		file_put_contents( $css_files[$theme], $css ); // Save it

	        } else {

		        fopen( $css_files[$theme], 'w' );

		        if ( is_writable( $css_files[$theme] ) ) {
		        	fwrite( $css_files[$theme], $css );
		        	fclose( $css_files[$theme] );
		        }

	        }

        }
        
    }

	public function easy_booking_admin_init() {

		register_setting(
			'easy_booking_settings',
			'easy_booking_settings', 
			array( $this, 'sanitize_values' )
		);

		add_settings_section(
			'easy_booking_main_settings',
			__('General settings', 'easy_booking'),
			array( $this, 'easy_booking_section_general' ),
			'easy_booking_settings'
		);

		add_settings_field(
			'easy_booking_calc_mode',
			__('Calculation mode', 'easy_booking'),
			array( $this, 'easy_booking_calc_mode' ),
			'easy_booking_settings',
			'easy_booking_main_settings'
		);

		add_settings_section(
			'easy_booking_main_text',
			__('Text settings', 'easy_booking'),
			array( $this, 'easy_booking_section_text' ),
			'easy_booking_settings'
		);

		add_settings_field(
			'easy_booking_info_text',
			__('Information text', 'easy_booking'),
			array( $this, 'easy_booking_info' ),
			'easy_booking_settings',
			'easy_booking_main_text'
		);

		add_settings_field(
			'easy_booking_start_date_text',
			__('First date title', 'easy_booking'),
			array( $this, 'easy_booking_start_date' ),
			'easy_booking_settings',
			'easy_booking_main_text'
		);

		add_settings_field(
			'easy_booking_end_date_text',
			__('Second date title', 'easy_booking'),
			array( $this, 'easy_booking_end_date' ),
			'easy_booking_settings',
			'easy_booking_main_text'
		);

		add_settings_section(
			'easy_booking_main_color',
			__('Appearance', 'easy_booking'),
			array( $this, 'easy_booking_section_color' ),
			'easy_booking_settings'
		);

		add_settings_field(
			'easy_booking_calendar_theme',
			__('Calendar theme', 'easy_booking'),
			array( $this, 'easy_booking_theme' ),
			'easy_booking_settings',
			'easy_booking_main_color'
		);

		add_settings_field(
			'easy_booking_background_color',
			__('Background color', 'easy_booking'),
			array( $this, 'easy_booking_background' ),
			'easy_booking_settings',
			'easy_booking_main_color'
		);

		add_settings_field(
			'easy_booking_main_color',
			__('Main color', 'easy_booking'),
			array( $this, 'easy_booking_color' ),
			'easy_booking_settings',
			'easy_booking_main_color'
		);

		add_settings_field(
			'easy_booking_text_color',
			__('Text color', 'easy_booking'),
			array( $this, 'easy_booking_text' ),
			'easy_booking_settings',
			'easy_booking_main_color'
		);

		$data = get_option('easy_booking_settings');
		do_action( 'easy_booking_save_settings', $data );

	}

	public function easy_booking_option_page() {

		?><div class="wrap">

			<div id="wceb-settings-container">

				<div id="wceb-settings">

					<h2><?php _e('WooCommerce Easy Booking settings', 'easy_booking'); ?></h2>

					<form method="post" action="options.php">

						<?php settings_fields('easy_booking_settings'); ?>
						<?php do_settings_sections('easy_booking_settings'); ?>
						 
						<?php submit_button(); ?>

					</form>

				</div>

				<div id="wceb-sidebar">
					<div class="easy-booking-notice">

						<div class="easy-booking-notice__inner">
							<h4><?php _e('For any issue or suggestion, check the FAQ or the support forum.', 'easy_booking') ?></h4>
							<a href="http://herownsweetway.com/product/woocommerce-easy-booking/" class="button easy-booking-button" target="_blank"><?php _e('FAQ', 'easy_booking'); ?></a>
							<a href="http://herownsweetway.com/support/woocommerce-easy-booking/" class="button easy-booking-button" target="_blank"><?php _e('Support', 'easy_booking'); ?></a>
						</div>

						<?php $active_plugins = (array) get_option( 'active_plugins', array() );

				        if ( is_multisite() ) {
				            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
				        }
				        
				        if ( ! ( array_key_exists( 'easy-booking-availability-check/easy-booking-availability-check.php', $active_plugins ) || in_array( 'easy-booking-availability-check/easy-booking-availability-check.php', $active_plugins ) ) ) { ?>
							<div class="easy-booking-notice__inner">
								<h4><?php _e('Easy Booking : Availability Check', 'easy_booking'); ?></h4>
								<p>
									<?php _e( 'This add-on will allow you to manage stocks and availabilties when renting or booking your products.', 'easy_booking' ); ?>
								</p>
								<ul>
									<li><?php _e('Keeps track of every booked date and quantity booked for each product (keeps only future dates).', 'easy_booking'); ?></li>
									<li><?php _e('Updates availabilities when an order is made, modified or cancelled.', 'easy_booking'); ?></li>
									<li><?php _e('Prevents users to order unavailable quantity or out-of-stock products.', 'easy_booking'); ?></li>
									<li><?php _e('Allows you to easily check which dates and quantities are available in the administration panel.', 'easy_booking'); ?></li>
								</ul>
								<a href="http://herownsweetway.com/product/easy-booking-availability-check/" class="button button-hero easy-booking-button easy-booking-button--large" target="_blank"><?php _e( 'Learn more', 'easy_booking' ); ?></a>
							</div>
						<?php } ?>
						<?php if ( ! ( array_key_exists( 'easy-booking-duration-discounts/easy-booking-duration-discounts.php', $active_plugins ) || in_array( 'easy-booking-duration-discounts/easy-booking-duration-discounts.php', $active_plugins ) ) ) { ?>
							<div class="easy-booking-notice__inner">
								<h4><?php _e('Easy Booking : Duration Discounts', 'easy_booking'); ?></h4>
								<p>
									<?php _e( 'This add-on will allow you to add discounts to your products depending on the duration booked by your clients.', 'easy_booking' ); ?>
								</p>
								<ul>
									<li><?php _e('Choose custom discount amounts.', 'easy_booking'); ?></li>
									<li><?php _e('Choose between "Product % discount", "Product discount", "Total % discount" or "Total discount".', 'easy_booking'); ?></li>
									<li><?php _e('Define booked duration to add your discount (E.g : from 10 to 20 days).', 'easy_booking'); ?></li>
									<li><?php _e('Add as many discounts as you want per product or variation.', 'easy_booking'); ?></li>
								</ul>
								<p><?php _e('The plugin then calculates the new product price when the client orders, depending on the discounts set.', 'easy_booking'); ?></p>
								<a href="http://herownsweetway.com/product/easy-booking-duration-discounts/" class="button button-hero easy-booking-button easy-booking-button--large" target="_blank"><?php _e( 'Learn more', 'easy_booking' ); ?></a>
							</div>
						<?php } ?>
					</div>
				</div>

			</div>

		</div>
		<?php

	}

	public function easy_booking_section_general() {
		echo '';
	}

	public function easy_booking_calc_mode() {

		$calc_mode = isset ( $this->options['easy_booking_calc_mode'] ) ? $this->options['easy_booking_calc_mode'] : 'nights';

		echo '<select id="calc_mode" name="easy_booking_settings[easy_booking_calc_mode]">
			<option value="days"' . selected( $calc_mode, 'days', false) . '>' . __('Days', 'easy_booking') . '</option>
			<option value="nights"' . selected( $calc_mode, 'nights', false) . '>' . __('Nights', 'easy_booking') . '</option>
		</select>
		<p class="description">' . __('Choose whether to calculate the final price depending on number of days or number of nights (i.e. 5 days = 4 nights).' , 'easy_booking') . '</p>';
	}

	public function easy_booking_section_text() {
		echo '<p>' . __('Make this plugin yours by choosing the different texts you want to display !', 'easy_booking') . '</p>';
	}

	public function easy_booking_info() {
		echo '<textarea id="easy_booking_text_info" name="easy_booking_settings[easy_booking_info_text]" rows="4" cols="50" />' . $this->options['easy_booking_info_text'] . '</textarea>
		<p class="description">' . __('Displays an information text before date inputs. Leave empty if you don\'t want the information text.' , 'easy_booking') . '</p>';
	}

	public function easy_booking_start_date() {
		echo '<input id="easy_booking_start_date_text" name="easy_booking_settings[easy_booking_start_date_text]" size="40" type="text" value="' . $this->options['easy_booking_start_date_text'] . '" />
		<p class="description">' . __('Text displayed before the first date', 'easy_booking') . '</p>';
	}

	public function easy_booking_end_date() {
		echo '<input id="easy_booking_end_date_text" name="easy_booking_settings[easy_booking_end_date_text]" size="40" type="text" value="' . $this->options['easy_booking_end_date_text'] . '" />
		<p class="description">' . __('Text displayed before the second date', 'easy_booking') . '</p>';
	}

	public function easy_booking_section_color() {
		echo '<p>' . __('Customize the calendar so it looks great with your theme !', 'easy_booking') . '</p>';
	}

	public function easy_booking_theme() {
		$theme = isset ( $this->options['easy_booking_calendar_theme'] ) ? $this->options['easy_booking_calendar_theme'] : 'nights';

		echo '<select id="calendar_theme" name="easy_booking_settings[easy_booking_calendar_theme]">
			<option value="default"' . selected( $theme, 'default', false) . '>' . __('Default', 'easy_booking') . '</option>
			<option value="classic"' . selected( $theme, 'classic', false) . '>' . __('Classic', 'easy_booking') . '</option>
		</select>';
	}	

	public function easy_booking_background() {
		$background_color = ( isset( $this->options['easy_booking_background_color'] ) ) ? $this->options['easy_booking_background_color'] : '';
		echo '<input type="text" name="easy_booking_settings[easy_booking_background_color]" class="color-field" value="' . $background_color . '">';
	}

	public function easy_booking_color() {
		$main_color = ( isset( $this->options['easy_booking_main_color'] ) ) ? $this->options['easy_booking_main_color'] : '';
		echo '<input type="text" name="easy_booking_settings[easy_booking_main_color]" class="color-field" value="' . $main_color . '">';
	}

	public function easy_booking_text() {
		$text_color = ( isset( $this->options['easy_booking_text_color'] ) ) ? $this->options['easy_booking_text_color'] : '';
		echo '<input type="text" name="easy_booking_settings[easy_booking_text_color]" class="color-field" value="' . $text_color . '">';
	}

	public function sanitize_values( $settings ) {
		
		foreach ( $settings as $key => $value ) {
			$settings[$key] = esc_html( $value );
		}

		return $settings;
	}
}

return new WCEB_Settings();