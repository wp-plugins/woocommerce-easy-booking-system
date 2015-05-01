<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

header("Content-type: text/css; charset: UTF-8");

$background_color = isset( $data['easy_booking_background_color'] ) ? $data['easy_booking_background_color'] : '#FFFFFF';
$main_color = isset( $data['easy_booking_main_color'] ) ? $data['easy_booking_main_color'] : '#0089EC';
$text_color = isset( $data['easy_booking_text_color'] ) ? $data['easy_booking_text_color'] : '#000000';

function classicAdjustBrightness($hex, $steps) {
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
 * Note: the root picker element should *NOT* be styled more than what’s here.
 */
.wceb_picker_wrap {
  position: relative;
}

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
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
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
 * Classic picker styling for pickadate.js
 * Demo: http://amsul.github.io/pickadate.js
 */
/**
 * Note: the root picker element should *NOT* be styled more than what’s here.
 */
.picker {
  width: 100%;
}
/**
 * The holder is the base of the picker.
 */
.picker__holder {
  position: absolute;
  background: <?php echo $background_color; ?>;
  border: 1px solid <?php echo adjustBrightness($background_color, -75); ?>;
  border-top-width: 0;
  border-bottom-width: 0;
  border-radius: 0 0 5px 5px;
  box-sizing: border-box;
  min-width: 176px;
  max-width: 466px;
  max-height: 0;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
  filter: alpha(opacity=0);
  -moz-opacity: 0;
  opacity: 0;
  -webkit-transform: translateY(-1em) perspective(600px) rotateX(10deg);
          transform: translateY(-1em) perspective(600px) rotateX(10deg);
  transition: -webkit-transform 0.15s ease-out, opacity 0.15s ease-out, max-height 0s 0.15s, border-width 0s 0.15s;
  transition: transform 0.15s ease-out, opacity 0.15s ease-out, max-height 0s 0.15s, border-width 0s 0.15s;
}
/**
 * The frame and wrap work together to ensure that
 * clicks within the picker don’t reach the holder.
 */
.picker__frame {
  padding: 1px;
}
.picker__wrap {
  margin: -1px;
}
/**
 * When the picker opens...
 */
.picker--opened .picker__holder {
  max-height: 25em;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
  filter: alpha(opacity=100);
  -moz-opacity: 1;
  opacity: 1;
  border-top-width: 1px;
  border-bottom-width: 1px;
  -webkit-transform: translateY(0) perspective(600px) rotateX(0);
          transform: translateY(0) perspective(600px) rotateX(0);
  transition: -webkit-transform 0.15s ease-out, opacity 0.15s ease-out, max-height 0s, border-width 0s;
  transition: transform 0.15s ease-out, opacity 0.15s ease-out, max-height 0s, border-width 0s;
  box-shadow: 0 6px 18px 1px rgba(0, 0, 0, 0.12);
}

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
  border: 1px solid <?php echo adjustBrightness($background_color, -50); ?>;
  height: 2em;
  padding: .5em;
  margin-left: .25em;
  margin-right: .25em;
}
@media (min-width: 24.5em) {
  .picker__select--month,
  .picker__select--year {
    margin-top: -0.5em;
  }
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
  padding: .5em 1.25em;
  width: 1em;
  height: 1em;
  box-sizing: content-box;
  top: -0.25em;
}
@media (min-width: 24.5em) {
  .picker__nav--prev,
  .picker__nav--next {
    top: -0.33em;
  }
}
.picker__nav--prev {
  left: -1em;
  padding-right: 1.25em;
}
@media (min-width: 24.5em) {
  .picker__nav--prev {
    padding-right: 1.5em;
  }
}
.picker__nav--next {
  right: -1em;
  padding-left: 1.25em;
}
@media (min-width: 24.5em) {
  .picker__nav--next {
    padding-left: 1.5em;
  }
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
  text-align: center;
  border-collapse: collapse;
  border-spacing: 0;
  table-layout: fixed;
  font-size: inherit;
  width: 100%;
  margin-top: .75em;
  margin-bottom: .5em;
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
.picker__day--disabled:before {
  border-top-color: <?php echo adjustBrightness($background_color, -75); ?>;
}
.picker__day--outfocus {
  color: #dddddd;
}
.picker__day--infocus:hover,
.picker__day--outfocus:hover {
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  background: <?php echo adjustBrightness($main_color, 75); ?>;
}
.picker__day--highlighted {
  border-color: <?php echo $main_color; ?>;
}
.picker__day--highlighted:hover,
.picker--focused .picker__day--highlighted {
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  background: <?php echo adjustBrightness($main_color, 75); ?>;
}
.picker__day--selected,
.picker__day--selected:hover,
.picker--focused .picker__day--selected {
  background: <?php echo $main_color; ?>;
  color: <?php echo $background_color; ?>;
}
.picker__day--disabled,
.picker__day--disabled:hover,
.picker--focused .picker__day--disabled {
  background: <?php echo adjustBrightness($background_color, -10); ?>;
  border-color: <?php echo adjustBrightness($background_color, -10); ?>;
  color: #dddddd;
  cursor: default;
}
.picker__day--highlighted.picker__day--disabled,
.picker__day--highlighted.picker__day--disabled:hover {
  background: <?php echo adjustBrightness($background_color, -50); ?>;
}
/**
 * The footer containing the "today", "clear", and "close" buttons.
 */
.picker__footer {
  text-align: center;
}
.picker__button--today,
.picker__button--clear,
.picker__button--close {
  border: 1px solid <?php echo $background_color; ?>;
  background: <?php echo $background_color; ?>;
  font-size: .8em;
  padding: .66em 0;
  font-weight: bold;
  width: 33%;
  display: inline-block;
  vertical-align: bottom;
}
.picker__button--today:hover,
.picker__button--clear:hover,
.picker__button--close:hover {
  cursor: pointer;
  color: <?php echo $text_color; ?>;
  background: <?php echo adjustBrightness($main_color, 75); ?>;
  border-bottom-color: <?php echo adjustBrightness($main_color, 75); ?>;
}
.picker__button--today:focus,
.picker__button--clear:focus,
.picker__button--close:focus {
  background: <?php echo adjustBrightness($main_color, 75); ?>;
  border-color: <?php echo $main_color; ?>;
  outline: none;
}
.picker__button--today:before,
.picker__button--clear:before,
.picker__button--close:before {
  position: relative;
  display: inline-block;
  height: 0;
}
.picker__button--today:before,
.picker__button--clear:before {
  content: " ";
  margin-right: .45em;
}
.picker__button--today:before {
  top: -0.05em;
  width: 0;
  border-top: 0.66em solid <?php echo adjustBrightness($main_color, -50); ?>;
  border-left: .66em solid transparent;
}
.picker__button--clear:before {
  top: -0.25em;
  width: .66em;
  border-top: 3px solid #ee2200;
}
.picker__button--close:before {
  content: "\D7";
  top: -0.1em;
  vertical-align: top;
  font-size: 1.1em;
  margin-right: .35em;
  color: <?php echo adjustBrightness($background_color, -150); ?>;
}
.picker__button--today[disabled],
.picker__button--today[disabled]:hover {
  background: <?php echo adjustBrightness($background_color, -10); ?>;
  border-color: <?php echo adjustBrightness($background_color, -10); ?>;
  color: #dddddd;
  cursor: default;
}
.picker__button--today[disabled]:before {
  border-top-color: <?php echo adjustBrightness($background_color, -75); ?>;
}

/* ==========================================================================
   $CLASSIC-DATE-PICKER
   ========================================================================== */