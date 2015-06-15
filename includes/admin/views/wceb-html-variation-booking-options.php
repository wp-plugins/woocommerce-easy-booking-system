<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div id="booking_variation_data" class="show_if_variation_bookable">

    <p class="form-row form-row-first">
        <label for="_var_booking_min[<?php echo $loop; ?>]">
            <?php _e( 'Minimum booking duration', 'easy_booking' ); ?>
            <span class="tips" data-tip="<?php _e( 'Enter zero to set no duration limit or leave blank to use the parent product\'s booking options.', 'easy_booking' ); ?>">[?]</span></label>
        <input type="number" class="input_text" min="0" name="_var_booking_min[<?php echo $loop; ?>]" value="<?php if ( isset( $booking_min ) ) echo esc_attr( $booking_min ); ?>" />
    </p>

    <p class="form-row form-row-last">
        <label for="_var_booking_max[<?php echo $loop; ?>]">
            <?php _e( 'Maximum booking duration', 'easy_booking' ); ?>
            <span class="tips" data-tip="<?php _e( 'Enter zero to set no duration limit or leave blank to use the parent product\'s booking options.', 'easy_booking' ); ?>">[?]</span></label>
        <input type="number" class="input_text" min="0" name="_var_booking_max[<?php echo $loop; ?>]" value="<?php if ( isset( $booking_max ) ) echo esc_attr( $booking_max ); ?>" />
    </p>

    <p class="form-row form-row-first">
        <label for="_var_first_available_date[<?php echo $loop; ?>]">
            <?php _e( 'First available date', 'easy_booking' ); ?>
            <span class="tips" data-tip="<?php _e( 'First available date, relative to today. I.e. : today + 5 days. Enter 0 for today or leave blank to use the parent product\'s booking options.', 'easy_booking' ); ?>">[?]</span></label>
        <input type="number" class="input_text" min="0" name="_var_first_available_date[<?php echo $loop; ?>]" value="<?php if ( isset( $first_available_date ) ) echo esc_attr( $first_available_date ); ?>" />
    </p>

    <p class="form-row form-row-full">

        <?php do_action('easy_booking_after_variation_booking_options', $product, $variation ); ?>

    </p>

</div>