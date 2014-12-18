<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_EBS_settings {

	public function __construct() {

		// get plugin options values
		$this->options = get_option('wc_ebs_options');
		
		// initialize options the first time
		if ( !$this->options ) {
		
		    $this->options = array( 'wc_ebs_calc_mode' => 'nights',
		    						'wc_ebs_info_text' => __('', 'wc_ebs'),
		    						'wc_ebs_start_date_text' => __('Start', 'wc_ebs'), 
		                            'wc_ebs_end_date_text' => __('End', 'wc_ebs'),
		                            'wc_ebs_background_color' => '#FFFFFF',
		                            'wc_ebs_color_select' => '#0089EC',
		                            'wc_ebs_text_color' => '#000000'
		                        );

		    add_option('wc_ebs_options', $this->options);

		}

		if ( is_admin() ) {

			add_action('admin_menu', array($this, 'wc_ebs_add_option_page'));
			add_action('admin_init', array($this, 'wc_ebs_admin_init'));

		}

	}

	public function wc_ebs_add_option_page() {
		$option_page = add_options_page('Easy Booking Options', 'Easy Booking Options', 'manage_options', 'wc_ebs_options', array( $this, 'wc_ebs_option_page' ));
		add_action( 'admin_print_scripts-'. $option_page, array($this, 'load_admin_scripts'));
	}

	public function load_admin_scripts() {
	  	wp_enqueue_style('wp-color-picker');
	  	wp_enqueue_script('color-picker', plugins_url('js/admin/script.js', dirname(__FILE__)), array('wp-color-picker'), false, true );
	}

	public function generate_options_css( $newdata ) {

        $data = $newdata;   
        $plugin_dir = plugin_dir_path( dirname(__FILE__) ); // Shorten code, save 1 call
        $php_file = realpath ( $plugin_dir . 'css/default.min.css.php' );
        $css_file = realpath ( $plugin_dir . 'css/default.min.css' );
        ob_start(); // Capture all output (output buffering)

        require( $php_file ); // Generate CSS

        $css = ob_get_clean(); // Get generated CSS (output buffering)

        if ( is_writable( $css_file ) )
        	file_put_contents($css_file, $css); // Save it
    }

	public function wc_ebs_admin_init() {

		register_setting(
			'wc_ebs_options',
			'wc_ebs_options', 
			array( $this, 'sanitize_values' )
		);

		add_settings_section(
			'wc_ebs_main_settings',
			__('General settings', 'wc_ebs'),
			array( $this, 'wc_ebs_section_general' ),
			'wc_ebs_options'
		);

		add_settings_field(
			'wc_ebs_calc_mode',
			__('Calculation mode', 'wc_ebs'),
			array( $this, 'wc_ebs_calc_mode' ),
			'wc_ebs_options',
			'wc_ebs_main_settings'
		);

		add_settings_section(
			'wc_ebs_main_text',
			__('Text settings', 'wc_ebs'),
			array( $this, 'wc_ebs_section_text' ),
			'wc_ebs_options'
		);

		add_settings_field(
			'wc_ebs_info_text',
			__('Information text', 'wc_ebs'),
			array( $this, 'wc_ebs_info' ),
			'wc_ebs_options',
			'wc_ebs_main_text'
		);

		add_settings_field(
			'wc_ebs_start_date_text',
			__('First date title', 'wc_ebs'),
			array( $this, 'wc_ebs_start_date' ),
			'wc_ebs_options',
			'wc_ebs_main_text'
		);

		add_settings_field(
			'wc_ebs_end_date_text',
			__('Second date title', 'wc_ebs'),
			array( $this, 'wc_ebs_end_date' ),
			'wc_ebs_options',
			'wc_ebs_main_text'
		);

		add_settings_section(
			'wc_ebs_main_color',
			__('Color settings', 'wc_ebs'),
			array( $this, 'wc_ebs_section_color' ),
			'wc_ebs_options'
		);

		add_settings_field(
			'wc_ebs_background_color',
			__('Background color', 'wc_ebs'),
			array( $this, 'wc_ebs_background' ),
			'wc_ebs_options',
			'wc_ebs_main_color'
		);

		add_settings_field(
			'wc_ebs_color_select',
			__('Main color', 'wc_ebs'),
			array( $this, 'wc_ebs_color' ),
			'wc_ebs_options',
			'wc_ebs_main_color'
		);

		add_settings_field(
			'wc_ebs_text_color',
			__('Text color', 'wc_ebs'),
			array( $this, 'wc_ebs_text' ),
			'wc_ebs_options',
			'wc_ebs_main_color'
		);

		$data = get_option('wc_ebs_options');
		$this->generate_options_css( $data ); // Generate static css file

	}

	public function wc_ebs_option_page() {

		?><div class="wrap">
	
			<?php screen_icon('generic'); ?>

			<h2><?php _e('WooCommerce Easy Booking System options', 'wc_ebs'); ?></h2>

			<form method="post" action="options.php">

				<?php settings_fields('wc_ebs_options'); ?>
				<?php do_settings_sections('wc_ebs_options'); ?>
				 
				<?php submit_button(); ?>

			</form>

		</div>
		<?php

	}

	public function wc_ebs_section_general() {
		echo '';
	}

	public function wc_ebs_calc_mode() {

		$calc_mode = isset ( $this->options['wc_ebs_calc_mode'] ) ? $this->options['wc_ebs_calc_mode'] : 'nights';

		echo '<select id="calc_mode" name="wc_ebs_options[wc_ebs_calc_mode]">
			<option value="days"' . selected( $calc_mode, 'days', false) . '>' . __('Days', 'wc_ebs') . '</option>
			<option value="nights"' . selected( $calc_mode, 'nights', false) . '>' . __('Nights', 'wc_ebs') . '</option>
		</select>
		<p class="description">' . __('Choose whether to calculate the final price depending on number of days or number of nights (i.e. 5 days = 4 nights).' , 'wc_ebs') . '</p>';
	}

	public function wc_ebs_section_text() {
		echo '<p>' . __('Make this plugin yours by choosing the different texts you want to display !', 'wc_ebs') . '</p>';
	}

	public function wc_ebs_info() {
		echo '<textarea id="wc_ebs_text_info" name="wc_ebs_options[wc_ebs_info_text]" rows="4" cols="50" />' . $this->options['wc_ebs_info_text'] . '</textarea>
		<p class="description">' . __('Displays an information text before date inputs. Leave empty if you don\'t want the information text.' , 'wc_ebs') . '</p>';
	}

	public function wc_ebs_start_date() {
		echo '<input id="wc_ebs_start_date_text" name="wc_ebs_options[wc_ebs_start_date_text]" size="40" type="text" value="' . $this->options['wc_ebs_start_date_text'] . '" />
		<p class="description">' . __('Text displayed before the first date', 'wc_ebs') . '</p>';
	}

	public function wc_ebs_end_date() {
		echo '<input id="wc_ebs_end_date_text" name="wc_ebs_options[wc_ebs_end_date_text]" size="40" type="text" value="' . $this->options['wc_ebs_end_date_text'] . '" />
		<p class="description">' . __('Text displayed before the second date', 'wc_ebs') . '</p>';
	}

	public function wc_ebs_section_color() {
		echo '<p>' . __('Customize the calendar so it looks great with your theme !', 'wc_ebs') . '</p>';
	}

	public function wc_ebs_background() {
		$background_color = ( isset( $this->options['wc_ebs_background_color'] ) ) ? $this->options['wc_ebs_background_color'] : '';
		echo '<input type="text" name="wc_ebs_options[wc_ebs_background_color]" class="color-field" value="' . $background_color . '">';
	}

	public function wc_ebs_color() {
		$main_color = ( isset( $this->options['wc_ebs_color_select'] ) ) ? $this->options['wc_ebs_color_select'] : '';
		echo '<input type="text" name="wc_ebs_options[wc_ebs_color_select]" class="color-field" value="' . $main_color . '">';
	}

	public function wc_ebs_text() {
		$text_color = ( isset( $this->options['wc_ebs_text_color'] ) ) ? $this->options['wc_ebs_text_color'] : '';
		echo '<input type="text" name="wc_ebs_options[wc_ebs_text_color]" class="color-field" value="' . $text_color . '">';
	}

	public function sanitize_values( $settings ) {
		
		foreach ( $settings as $key => $value ) {
			$settings[$key] = esc_html( $value );
		}

		return $settings;
	}
}

new WC_EBS_settings();