<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCEB_Reports {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_menu', array($this, 'easy_booking_add_reports_pages'), 10 );
		}

	}

	public function easy_booking_add_reports_pages() {
		$reports_page = add_submenu_page( 'easy-booking', __('Reports', 'easy_booking'), __('Reports', 'easy_booking'), 'manage_options', 'easy-booking-reports', array($this, 'easy_booking_reports_page') );

		add_action( 'admin_print_scripts-'. $reports_page, array($this, 'easy_booking_load_admin_reports_scripts') );
		add_action( 'easy_booking_reports_bookings', array($this, 'easy_booking_reports_content') );
	}

	public function easy_booking_load_admin_reports_scripts() {
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_script('easy_booking_reports', plugins_url('assets/js/admin/wceb-reports-functions.min.js', WCEB_PLUGIN_FILE), array('jquery'), '1.0', true );
		// wp_enqueue_script('easy_booking_reports', plugins_url('/assets/js/admin/dev/wceb-reports-functions.js', WCEB_PLUGIN_FILE), array('jquery'), '1.0', true );
	}

	public function easy_booking_reports_page() {

		?><div class="wrap">

			<div id="wceb-settings-container">

				<?php $wceb_tabs = apply_filters( 'easy_booking_reports_tabs', array(
					'bookings' => __('Bookings', 'easy_booking')
				));

				$current_tab = empty( $_GET['tab'] ) ? 'bookings' : sanitize_title( $_GET['tab'] ); ?>

				<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
					<?php foreach ( $wceb_tabs as $tab => $label ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=easy-booking-reports&tab=' . $tab ); ?>" class="nav-tab <?php echo ( $current_tab == $tab ? 'nav-tab-active' : '' ) ?>"><?php echo $label; ?></a>
					<?php } ?>
				</h2>

				<?php do_action( 'easy_booking_reports_' . $current_tab ); ?>

			</div>
		</div>

		<?php

	}

	public function easy_booking_reports_content() {

		$booked_dates = WCEB()->easy_booking_get_booked_items_from_orders();
		ksort($booked_dates);

		if ( $booked_dates ) : ?>

			<div id="poststuff" class="wc-metaboxes-wrapper">

				<div class="wc-metaboxes woocommerce-reports-wide">

				<?php foreach ( $booked_dates as $product_id => $booking_data ) { ?>

					<?php $product = wc_get_product( $product_id );
					$product_name = $product->get_title(); ?>
					<div class="wc-metabox closed">
						<h3 style="cursor: pointer;"><?php echo '#' . $product_id . ' - ' . $product_name ?>
							<?php // Get variation data
							if ( $product->is_type( 'variation' ) ) {
								$list_attributes = array();
								$attributes = $product->get_variation_attributes();

								foreach ( $attributes as $name => $attribute ) {
									$list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': <strong>' . $attribute . '</strong>';
								}

								echo '<div class="description">' . implode( ', ', $list_attributes ) . '</div>';
							} ?>
							
						</h3>
						<div class="wc-metabox-content">
							<table class="wp-list-table widefat fixed striped">

								<?php $cols = apply_filters( 'easy_booking_reports_cols', array(
									'order_id' => array(
										'id' => 'order_id',
										'label' => __('Order ID', 'easy_booking')
									),
									'start_date' => array(
										'id' => 'start_date',
										'label' => __('Start Date', 'easy_booking' )
									),
									'end_date' => array(
										'id' => 'end_date',
										'label' => __('End Date', 'easy_booking' )
									),
									'qty' => array(
										'id' => 'qty',
										'label' => __('Quantity booked', 'easy_booking' )
									)
								)); ?>
								<thead>
									<tr>
										<?php foreach ( $cols as $col ) : ?>
											<th scope="col" id="<?php echo $col['id'] ?>"><?php echo $col['label'] ?></th>
										<?php endforeach; ?>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<?php foreach ( $cols as $col ) : ?>
											<th scope="col" id="<?php echo $col['id'] ?>"><?php echo $col['label'] ?></th>
										<?php endforeach; ?>
									</tr>
								</tfoot>
									<tbody>

										<?php foreach ( $booking_data as $data ) : ?>

											<?php $start_time = strtotime( $data['start'] );
											$data['start'] = date( 'j M, Y', $start_time );

											$end_time = strtotime( $data['end'] );
											$data['end'] = date( 'j M, Y', $end_time ); ?>

											<tr>
												<?php foreach ( $data as $name => $value ) { ?>
													<td class="<?php echo $name; ?>" style="padding: 7px;"><?php esc_html_e( $value ); ?></td>
												<?php } ?>
											</tr>

										<?php endforeach; ?>

									</tbody>
							</table>
						</div>
					</div>

				<?php } ?>

				</div>

			</div>

		<?php endif;
	}
}

return new WCEB_Reports();