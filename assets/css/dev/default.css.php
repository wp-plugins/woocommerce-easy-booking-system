<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

header("Content-type: text/css; charset: UTF-8");

$background_color = isset( $data['easy_booking_background_color'] ) ? $data['easy_booking_background_color'] : '#FFFFFF';
$main_color = isset( $data['easy_booking_main_color'] ) ? $data['easy_booking_main_color'] : '#0089EC';
$text_color = isset( $data['easy_booking_text_color'] ) ? $data['easy_booking_text_color'] : '#000000';

function adjustBrightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Format the hex color string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Get decimal values
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));

    // Adjust number of steps and keep it inside 0 to 255
    $r = max(0,min(255,$r + $steps));
    $g = max(0,min(255,$g + $steps));  
    $b = max(0,min(255,$b + $steps));

    $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
    $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
    $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

    return '#'.$r_hex.$g_hex.$b_hex;
}

?>
/* ==========================================================================
   $BASE-PICKER
   ========================================================================== */
/**
 * Note: the root picker element should __NOT__ be styled
 * more than what’s here. Style the `.picker__holder` instead.
 */

input[readonly] {
    cursor: pointer !important;
}

.picker {
  font-size: 16px;
  text-align: left;
  line-height: 1.2;
  color: <?php echo $text_color; ?>;
  position: absolute;
  z-index: 10000;
}

.picker, .picker > * {
  outline: none;
}

/**
 * The picker input element.
 */
.picker__input {
  cursor: default;
}
/**
 * When the picker is opened, the input element is “activated”.
 */
.picker__input.picker__input--active {
  border-color: <?php echo $main_color; ?>;
}
/**
 * The holder is the only “scrollable” top-level container element.
 */
.picker__holder {
  width: 100%;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

/*!
 * Default mobile-first, responsive styling for pickadate.js
 * Demo: http://amsul.github.io/pickadate.js
 */
/**
 * Make the holder and frame fullscreen.
 */
.picker__holder,
.picker__frame {
  bottom: 0;
  left: 0;
  right: 0;
  top: 100%;
}
/**
 * The holder should overlay the entire screen.
 */
.picker__holder {
  position: fixed;
  -webkit-transition: background 0.15s ease-out, top 0s 0.15s;
  -moz-transition: background 0.15s ease-out, top 0s 0.15s;
  transition: background 0.15s ease-out, top 0s 0.15s;
}
/**
 * The frame that bounds the box contents of the picker.
 */
.picker__frame {
  position: absolute;
  margin: 0 auto;
  min-width: 256px;
  max-width: 666px;
  width: 100%;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
  filter: alpha(opacity=0);
  -moz-opacity: 0;
  opacity: 0;
  -webkit-transition: all 0.15s ease-out;
  -moz-transition: all 0.15s ease-out;
  transition: all 0.15s ease-out;
}
@media (min-height: 33.875em) {
  .picker__frame {
    overflow: visible;
    top: auto;
    bottom: -100%;
    max-height: 80%;
  }
}
@media (min-height: 40.125em) {
  .picker__frame {
    margin-bottom: 7.5%;
  }
}
/**
 * The wrapper sets the stage to vertically align the box contents.
 */
.picker__wrap {
  display: table;
  width: 100%;
  height: 100%;
}
@media (min-height: 33.875em) {
  .picker__wrap {
    display: block;
  }
}
/**
 * The box contains all the picker contents.
 */
.picker__box {
  background:<?php echo $background_color; ?>;
  display: table-cell;
  vertical-align: middle;
}
@media (min-height: 26.5em) {
  .picker__box {
    font-size: 1.25em;
  }
}
@media (min-height: 33.875em) {
  .picker__box {
    display: block;
    font-size: 1.33em;
    border: 1px solid <?php echo adjustBrightness($background_color, -150); ?>;
    border-top-color: <?php echo adjustBrightness($background_color, -125); ?>;
    border-bottom-width: 0;
    -webkit-border-radius: 5px 5px 0 0;
    -moz-border-radius: 5px 5px 0 0;
    border-radius: 5px 5px 0 0;
    -webkit-box-shadow: 0 12px 36px 16px rgba(0, 0, 0, 0.24);
    -moz-box-shadow: 0 12px 36px 16px rgba(0, 0, 0, 0.24);
    box-shadow: 0 12px 36px 16px rgba(0, 0, 0, 0.24);
  }
}
@media (min-height: 40.125em) {
  .picker__box {
    font-size: 1.5em;
    border-bottom-width: 1px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
  }
}
/**
 * When the picker opens...
 */
.picker--opened .picker__holder {
  top: 0;
  background: transparent;
  -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#1E000000,endColorstr=#1E000000)";
  zoom: 1;
  background: rgba(0, 0, 0, 0.32);
  -webkit-transition: background 0.15s ease-out;
  -moz-transition: background 0.15s ease-out;
  transition: background 0.15s ease-out;
}
.picker--opened .picker__frame {
  top: 0;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
  filter: alpha(opacity=100);
  -moz-opacity: 1;
  opacity: 1;
}
@media (min-height: 33.875em) {
  .picker--opened .picker__frame {
    top: auto;
    bottom: 0;
  }
}
/**
 * For `large` screens, transform into an inline picker.
 */

 /* ==========================================================================
   $BASE-DATE-PICKER
   ========================================================================== */
/**
 * The picker box.
 */
.picker__box {
  padding: 0 1em;
}
/**
 * The header containing the month and year stuff.
 */
.picker__header {
  text-align: center;
  position: relative;
  margin-top: .75em;
}
/**
 * The month and year labels.
 */
.picker__month,
.picker__year {
  font-weight: 500;
  display: inline-block;
  margin-left: .25em;
  margin-right: .25em;
}
.picker__year {
  color: <?php echo adjustBrightness($background_color, -100); ?>;
  font-size: .8em;
  font-style: italic;
}
/**
 * The month and year selectors.
 */
.picker__select--month,
.picker__select--year {
  font-size: .8em;
  border: 1px solid <?php echo adjustBrightness($background_color, -50); ?>;
  height: 2.5em;
  padding: .5em .25em;
  margin-left: .25em;
  margin-right: .25em;
  margin-top: -0.5em;
}
.picker__select--month {
  width: 35%;
}
.picker__select--year {
  width: 22.5%;
}
.picker__select--month:focus,
.picker__select--year:focus {
  border-color: <?php echo $main_color; ?>;
}
/**
 * The month navigation buttons.
 */
.picker__nav--prev,
.picker__nav--next {
  position: absolute;
  top: -0.33em;
  padding: .5em 1.33em;
  width: 1em;
}
.picker__nav--prev {
  left: -1em;
  padding-right: 1.5em;
}
.picker__nav--next {
  right: -1em;
  padding-left: 1.5em;
}
.picker__nav--prev:before,
.picker__nav--next:before {
  content: " ";
  border-top: .5em solid transparent;
  border-bottom: .5em solid transparent;
  border-right: 0.75em solid <?php echo $text_color; ?>;
  width: 0;
  height: 0;
  display: block;
  margin: 0 auto;
}
.picker__nav--next:before {
  border-right: 0;
  border-left: 0.75em solid <?php echo $text_color; ?>;
}
.picker__nav--prev:hover,
.picker__nav--next:hover {
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  background: <?php echo adjustBrightness($main_color, 75); ?>;
}
.picker__nav--disabled,
.picker__nav--disabled:hover,
.picker__nav--disabled:before,
.picker__nav--disabled:before:hover {
  cursor: default;
  background: none;
  border-right-color: <?php echo adjustBrightness($background_color, -10); ?>;
  border-left-color: <?php echo adjustBrightness($background_color, -10); ?>;
}
/**
 * The calendar table of dates
 */
.picker__table {
  background: none;
  text-align: center;
  border-collapse: collapse;
  border-spacing: 0;
  table-layout: fixed;
  font-size: inherit;
  width: 100%;
  margin-top: .75em;
  margin-bottom: .5em;
}

.picker__table td, .picker__table th {
  border: none;
}

@media (min-height: 33.875em) {
  .picker__table {
    margin-bottom: .75em;
  }
}
.picker__table td {
  margin: 0;
  padding: 0;
}
/**
 * The weekday labels
 */
.picker__weekday {
  width: 14.285714286%;
  font-size: .75em;
  padding-bottom: .25em;
  color: <?php echo adjustBrightness($background_color, -100); ?>;
  font-weight: 500;
  text-align: center;
  /* Increase the spacing a tad */

}
@media (min-height: 33.875em) {
  .picker__weekday {
    padding-bottom: .5em;
  }
}
/**
 * The days on the calendar
 */
.picker__day {
  padding: .3125em 0;
  font-weight: 200;
  border: 1px solid transparent;
  text-align: center;
}
.picker__day--today {
  color: <?php echo $main_color; ?>;
  position: relative;
}
.picker__day--today:before {
  content: " ";
  position: absolute;
  top: 2px;
  right: 2px;
  width: 0;
  height: 0;
  border-top: 0.5em solid <?php echo adjustBrightness($main_color, -50); ?>;
  border-left: .5em solid transparent;
}
.picker__day--selected,
.picker__day--selected:hover {
  border-color: <?php echo $main_color; ?>;
}
.picker__day--highlighted {
  background: <?php echo $main_color; ?>;
}
.picker__day--disabled:before {
  border-top-color: <?php echo adjustBrightness($background_color, -75); ?>;
}
.picker__day--outfocus {
  color: <?php echo adjustBrightness($background_color, -50); ?>;
}
.picker__day--infocus:hover,
.picker__day--outfocus:hover {
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  background: <?php echo adjustBrightness($main_color, 75); ?>;
}
.picker__day--highlighted:hover,
.picker--focused .picker__day--highlighted {
  background: <?php echo $main_color; ?>;
  color: <?php echo $background_color; ?>;
}
.picker__day--disabled,
.picker__day--disabled:hover {
  background: <?php echo adjustBrightness($background_color, -10); ?>;
  border-color: <?php echo adjustBrightness($background_color, -10); ?>;
  color: <?php echo adjustBrightness($background_color, -50); ?>;
  cursor: default;
}
.picker__day--highlighted.picker__day--disabled,
.picker__day--highlighted.picker__day--disabled:hover {
  background: <?php echo adjustBrightness($background_color, -40); ?>;
}
/**
 * The footer containing the "today" and "clear" buttons.
 */
.picker__footer {
  text-align: center;
}
.picker__button--today,
.picker__button--clear {
  border: 1px solid <?php echo $background_color; ?>;
  background: <?php echo $background_color; ?>;
  color: <?php echo $text_color; ?>;
  font-size: .8em;
  padding: .66em 0;
  font-weight: bold;
  width: 50%;
  display: inline-block;
  vertical-align: bottom;
}
.picker__button--today:hover,
.picker__button--clear:hover {
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  background: <?php echo adjustBrightness($main_color, 75); ?>;
  border-bottom-color: <?php echo adjustBrightness($main_color, 75); ?>;
}
.picker__button--today:focus,
.picker__button--clear:focus {
  background: <?php echo adjustBrightness($main_color, 75); ?>;
  border-color: <?php echo $main_color; ?>;
  outline: none;
}
.picker__button--today:before,
.picker__button--clear:before {
  position: relative;
  display: inline-block;
  height: 0;
}
.picker__button--today:before {
  content: " ";
  margin-right: .45em;
  top: -0.05em;
  width: 0;
  border-top: 0.66em solid <?php echo adjustBrightness($main_color, -50); ?>;
  border-left: .66em solid transparent;
}
.picker__button--clear:before {
  content: "\D7";
  margin-right: .35em;
  top: -0.1em;
  color: #ee2200;
  vertical-align: top;
  font-size: 1.1em;
}

/* ==========================================================================
   $DEFAULT-DATE-PICKER
   ========================================================================== */