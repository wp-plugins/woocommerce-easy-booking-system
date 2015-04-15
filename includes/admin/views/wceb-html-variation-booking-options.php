<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div id="booking_product_data" class="show_if_variation_bookable" style="display: none;">

    <p class="form-row form-row-first">
        <label for="_var_booking_min[<?php echo $loop; ?>]"> <?php _e( 'Minimum booking duration', 'easy_booking' ); ?> <span class="tips" data-tip="<?php _e( 'Leave zero or empty to set no duration limit.', 'easy_booking' ); ?>">[?]</span></label>
        <input type="number" class="input_text" min="0" placeholder="0" name="_var_booking_min[<?php echo $loop; ?>]" value="<?php echo esc_attr( $booking_min ); ?>" />
    </p>

    <p class="form-row form-row-last">
        <label for="_var_booking_max[<?php echo $loop; ?>]"> <?php _e( 'Maximum booking duration', 'easy_booking' ); ?> <span class="tips" data-tip="<?php _e( 'Leave zero or empty to set no duration limit.', 'easy_booking' ); ?>">[?]</span></label>
        <input type="number" class="input_text" min="0" placeholder="0" name="_var_booking_max[<?php echo $loop; ?>]" value="<?php echo esc_attr( $booking_max ); ?>" />
    </p>

    <p class="form-row form-row-first">
        <label for="_var_first_available_date[<?php echo $loop; ?>]"> <?php _e( 'First available date', 'easy_booking' ); ?> <span class="tips" data-tip="<?php _e( 'First available date, relative to today. I.e. : today + 5 days. Leave zero or empty for today.', 'easy_booking' ); ?>">[?]</span></label>
        <input type="number" class="input_text" min="0" placeholder="0" name="_var_first_available_date[<?php echo $loop; ?>]" value="<?php echo esc_attr( $first_available_date ); ?>" />
    </p>

    <p class="form-row form-row-full">

        <?php do_action('easy_booking_after_variation_booking_options', $product, $variation ); ?>

    </p>

</div>