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
		$hook = add_menu_page(
			__('Easy Booking', 'easy_booking'),
			__('Easy Booking', 'easy_booking'),
			'manage_options',
			'easy-booking',
			'',
			'dashicons-calendar-alt',
			58
		);

		$option_page = add_submenu_page(
			'easy-booking',
			__('Settings', 'easy_booking'),
			__('Settings', 'easy_booking'),
			'manage_options',
			'easy-booking',
			array($this, 'easy_booking_option_page')
		);

		$addons_page = add_submenu_page(
			'easy-booking',
			__('Add-ons', 'easy_booking'),
			__('Add-ons', 'easy_booking'),
			'manage_options',
			'easy-booking-addons',
			array($this, 'easy_booking_addons_page')
		);
		
		add_action( 'load-'. $hook, array($this, 'easy_booking_settings_save') );
		add_action( 'admin_print_scripts-'. $option_page, array($this, 'easy_booking_load_admin_scripts') );
	}

	public function easy_booking_settings_save() {

	  	if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
	  		$data = get_option('easy_booking_settings');
	  		$this->easy_booking_generate_css( $data );

			do_action( 'easy_booking_save_settings', $data );
			
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

        $blog_id = '';

        if ( function_exists( 'is_multisite' ) && is_multisite() )
			$blog_id = '.' . get_current_blog_id();

		$css_files = array(
        	'default' => realpath( $plugin_dir . 'assets/css/default' . $blog_id . '.min.css' ),
        	'classic' => realpath( $plugin_dir . 'assets/css/classic' . $blog_id . '.min.css' )
        );

        if ( $php_files ) foreach ( $php_files as $theme => $php_file ) {
        	ob_start(); // Capture all output (output buffering)

	        require( $php_file ); // Generate CSS
	        
	        $css = ob_get_clean(); // Get generated CSS (output buffering)
	        $minified_css = WCEB()->easy_booking_minify_css( $css ); // Minify CSS

	        if ( file_exists( $css_files[$theme] ) ) {
	        	if ( is_writable( $css_files[$theme] ) )
	        		file_put_contents( $css_files[$theme], $minified_css ); // Save it

	        } else {
	        	$file = fopen( $plugin_dir . 'assets/css/' . $theme . $blog_id . '.min.css', 'a+' );
		        fwrite( $file, $minified_css );
		        fclose( $file );
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

	}

	public function easy_booking_option_page() {

		?><div class="wrap">

			<div id="wceb-settings">

				<h2><?php _e('WooCommerce Easy Booking settings', 'easy_booking'); ?></h2>

				<form method="post" action="options.php">

					<?php settings_fields('easy_booking_settings'); ?>
					<?php do_settings_sections('easy_booking_settings'); ?>
					 
					<?php submit_button(); ?>

				</form>

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
		echo '<p>' . __('Customize the calendar so it looks great with your theme !', 'easy_booking') . '</br>' . __('Prefer a light background and a dark text color, for better rendering.') . '</p>';
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

	public function easy_booking_addons_page() {
		include_once('views/html-wceb-addons.php');
	}

	public function sanitize_values( $settings ) {
		
		foreach ( $settings as $key => $value ) {
			$settings[$key] = esc_html( $value );
		}

		return $settings;
	}
}

return new WCEB_Settings();