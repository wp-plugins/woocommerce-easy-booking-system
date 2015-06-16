<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<div class="wrap">
	<h2><?php _e('WooCommerce Easy Booking Add-ons'); ?></h2>
	<div class="addons-container row">
		<?php $addons = array(
			array(
				'name' => 'easy-booking-availability-check',
				'desc' => '<p>'
					. __( 'This add-on will allow you to manage stocks and availabilties when renting or booking your products.', 'easy_booking' ) .
				'</p>
				<ul>
					<li>' . __('Keeps track of every booked date and quantity booked for each product (keeps only future dates).', 'easy_booking') . '</li>
					<li>' . __('Updates availabilities when an order is made, modified or cancelled.', 'easy_booking') . '</li>
					<li>' . __('Prevents users to order unavailable quantity or out-of-stock products.', 'easy_booking') . '</li>
					<li>' . __('Allows you to easily check which dates and quantities are available in the administration panel.', 'easy_booking') . '</li>
				</ul>'
			),
			array(
				'name' => 'easy-booking-duration-discounts',
				'desc' => '<p>
					' .  __( 'This add-on will allow you to add discounts to your products depending on the duration booked by your clients.', 'easy_booking' ) . '
				</p>
				<ul>
					<li>' .  __('Choose custom discount amounts.', 'easy_booking') . '</li>
					<li>' .  __('Choose between "Product % discount", "Product discount", "Total % discount" or "Total discount".', 'easy_booking') . '</li>
					<li>' .  __('Define booked duration to add your discount (E.g : from 10 to 20 days).', 'easy_booking') . '</li>
					<li>' .  __('Add as many discounts as you want per product or variation.', 'easy_booking') . '</li>
				</ul>'
			)
		);

		$plugin_dir = plugins_url( '/', WCEB_PLUGIN_FILE );

		$active_plugins = (array) get_option( 'active_plugins', array() );

        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
        }

		foreach ( $addons as $addon ) : ?>
			<div class="addon-single">
				<div class="addon-single__img">
					<img src="<?php echo $plugin_dir . 'assets/img/addons/' . $addon['name'] . '.jpg'; ?>" alt="<?php echo $addon['name']; ?>">
					<div class="addon-single__desc">
						<?php echo $addon['desc']; ?>
						<p>
							<?php if ( ! ( array_key_exists( $addon['name'] .'/' . $addon['name'] . '.php', $active_plugins ) || in_array( $addon['name'] .'/' . $addon['name'] . '.php', $active_plugins ) ) ) { ?>
							<a href="http://herownsweetway.com/product/<?php echo $addon['name']; ?>" target="_blank" class="button easy-booking-button">
								<?php _e('Learn more', 'easy_booking'); ?>
							</a>
							<?php } else { ?>
							<a href="#" class="button easy-booking-button easy-booking-button--installed">
								<?php _e('Installed', 'easy_booking'); ?>
							</a>
							<a href="http://herownsweetway.com/product/<?php echo $addon['name']; ?>/#faq" target="_blank" class="button easy-booking-button">
								<?php _e('Documentation', 'easy_booking'); ?>
							</a>
							<a href="http://herownsweetway.com/support/<?php echo $addon['name']; ?>" target="_blank" class="button easy-booking-button">
								<?php _e('Support', 'easy_booking'); ?>
							</a>
							<?php } ?>
						</p>
					</div>
				</div>
			</div>

		<?php endforeach; ?>
	</div>
</div>