==== WooCommerce Easy Booking System ====
Contributors: @natashalavail, @amsul (For pickadate.js)
Tags: woocommerce, booking, renting, products
Requires at least: 3.0, WooCommerce 2.2.4
Tested up to: 4.0
Stable tag: 1.3.1
License: GPLv2 or later

WooCommerce Easy Booking System allows users to add an option to book or rent their products.

== Description ==

This plugins allows you to add an option to your product in order to book or rent it. It adds two fields to your product page : a start date and an end date and then calculates a new price based on a daily basis (E.g. 4$ per day).

It uses Pickadate.js (http://amsul.ca/pickadate.js/) to display the calendars and set the dates.

== Installation ==

1. Install the "WooCommerce Easy Booking System" Plugin
2. Activate the plugin
7. Go to the WooCommerce Easy Booking System option page to set up the texts and colors you want to display
3. Go to the WooCommerce Product Page your want to allow for booking or renting
4. Check the "Bookable" box next to the product type dropdown
8. And you're done !

== Changelog ==

= 1.3.1 =

* Fixed an issue where products were not added to cart if the user was not logged in.

= 1.3 =

#### This update has a lot of modifications, please do not hesitate to tell me if it's not working on the support forum here https://wordpress.org/support/plugin/woocommerce-easy-booking-system.

* Disabled dates before first date and dates after second dates, preventing users to select invalid dates
* Prevent users to select the same date in "nights" mode
* Fixed an error in the calculation price for one day in "days" mode
* Prevent product add to cart if one or both dates are missing
* Changed the way selected dates were set (old : post meta, new : session) so it doesn't affect the product itself
* Updated and cleaned Ajax requests
* Added a few things for the future stock management plugin
* Corrected an error in the French translation
* Added US translation for pickadate.js

= 1.2.2 =
* You can now choose whether to calculate the final price depending on number of days or number of nights.

= 1.2.1 =
* Changed the way CSS was added
* Security update

= 1.2 =
* The calendar is now fully customizable !
* Fixed an issue with variable products' sale price
* Added filters to easily change picker form
* Security updates
* Scripts updates
* Updated French translation

= 1.1 =
* Fixed a few issues
* WooCommerce EBS now works with variable products

= 1.0.5 =
* Fixed issues with WooCommerce 2.2

= 1.0.4 =
* Added price format
* Updated French translation

= 1.0.3 =
* Fixed an issue where fields were not showing up on product page

= 1.0.2 =
* Fix for WooCommerce 2.1

= 1.0.1 =
* Disabled dates before current day

= 1.0 =
* Initial Release