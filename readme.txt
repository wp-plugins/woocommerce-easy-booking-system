==== WooCommerce Easy Booking ====
Contributors: @_Ashanna, @amsul (For pickadate.js)
Tags: woocommerce, booking, renting, products, book, rent, e-commerce
Requires at least: 3.8, WooCommerce 2.3
Tested up to: 4.2.2
Stable tag: 1.6.1
License: GPLv2 or later

WooCommerce Easy Booking allows users to add an option to book or rent their products.

== Description ==

This plugins allows you to add an option to your product in order to book or rent it. It adds two fields to your product page : a start date and an end date and then calculates a new price based on a daily basis (E.g. 4$ per day).

It uses Pickadate.js (http://amsul.ca/pickadate.js/) to display the calendars and set the dates.

See the plugin demo here : http://herownsweetway.com/product/woocommerce-easy-booking/

For more features, check these add-ons :

* Easy Booking : Availability Check : http://herownsweetway.com/product/easy-booking-availability-check/
* Easy Booking : Duration Discounts : http://herownsweetway.com/product/easy-booking-duration-discounts/

== Installation ==

First, you need to install WooCommerce.

1. Install the "WooCommerce Easy Booking" Plugin.
2. Activate the plugin.
3. Go to the WooCommerce Easy Booking tab to set up the plugin.
4. Go to the WooCommerce Product Page your want to allow for booking or renting.
5. Check the "Bookable" checkbox next to the product type dropdown. For variable products, you must check the "Bookable" option both on parent product and on the variation you want to set as bookable.
6. If you want to set bookings options for each product, go to the "Bookings" tab on the product page (or the "Variations" tab for variable products to manage booking options at variation level).
7. And you're done !

== Changelog ==

= 1.6.1 =

* Fix - CSS generation.
* Fix - disabled link on the add-ons page.

= 1.6 =

/!\ You might have to check variable products after this update. Backward compatibility should be ok, but you might have to check the "Bookable" checkbox again.

* Fix - Hook when saving plugin settings is now triggered when actually saving plugin settings.
* Fix - New way to generate and minify CSS. The old one was causing issues, especially with multisites.
* Fix - Calendars CSS, causing issues and conflicting with themes.
* Add - Possibility to manage booking at parent product level for variable products.
* Add - Add-ons page on the admin.
* Add - 'easy_booking_enqueue_additional_scripts' hook to enqueue scripts before the main pickadate script.
* Add - 'easy_booking_pickadate_dependecies' filter to add dependecies for the main pickadate script.
* Add - Custom Jquery events when initiliazing and setting calendars.
* Tweak - Improved Javascript for better flexibility and performance.

= 1.5.2 =

* Fix - Issue with WordPress 4.2.2 causing an error.

= 1.5.1 =

* Fix - Right to left function deprecated in WordPress 4.2.
* Fix - Backward compatibility with product booking metadata.
* Fix - First available date on start picker when minimum booking duration is set.
* Fix - is_bookable() function for variable products.
* Fix - Removed unnecessary Ajax call when clearing booking session.
* Fix - Input focus which made the calendar pop up when closing and opening window.
* Fix - Generated CSS after saving plugin settings.
* Fix - Registered CSS file for multisites.
* Fix - Price displayed on archive page for bookable products.
* Fix - Displayed price on non-bookable variable products.
* Add - Reports page on the admin.
* Add - "/ night" price when in "nights" mode.
* Add - Remove "/ day" or "/ night" text when variation is not bookable.
* Add - "WooCommerce Product Add-ons" compatibility. Please, refer to the documentation for more information about this : http://herownsweetway.com/product/woocommerce-easy-booking/#documentation.
* Add - Automatically open second date picker after selecting first date.
* Add - Calendar titles.
* Add - Minifying CSS on-the-fly after saving plugin settings.
* Add - Close button on the calendar.
* Update - Pickadate.js version 3.5.6.
* Remove - WooCommerce Currency Switcher compatibility. Please, refer to the documentation to makes these plugins compatible : http://herownsweetway.com/product/woocommerce-easy-booking/#documentation.

= 1.5 =

This update contains major changes for variable products. Backwards compatibility should be ok, but still check your variations after updating.

* Add - Variations are now handled individually, instead of inheriting from the parent product.
* Add - Multisite compatibility.
* Add - Right to left CSS, for right to left languages.
* Fix - Wrong price calculation when modifying an order.
* Fix - Security changes.
* Fix - Picker inputs pointer cursor.
* Fix - Added en.js file.
* Fix - Wrong $wpdb calls.
* Fix - Display product price on the right format.
* Tweak - Regenerate CSS only after saving plugin settings.
* Tweak - Improved Inputs CSS.
* Localization - Added Dutch translation.
* Localization - Update French translation.

= 1.4.4 =

* Fix - Javascript error on the notices

= 1.4.3 =

Easy Booking : Availability Check, the add-on to manage stocks and availabilities for WooCommerce Easy Booking is available !
Get it now on http://herownsweetway.com/product/easy-booking-availability-check/ !

* Add - Admin notices styles.
* Fix - Removed WooCommerce loading gif (which was not loaded, causing Javascript errors).

= 1.4.2 =

* Fix - Issue with WooCommerce 2.3 and variable products.
* Fix - Issue with WooCommerce 2.3 and products.
* Fix - Issue with WooCommerce 2.3 on the order page.
* Fix - Issue when calculating new price and taxes on the order page.
* Fix - Removed minimum start date on the calendar on the product page.
* Add - Another theme for the calendar.
* Add - Hook when saving settings.
* Add - Filter when calculating new price.
* Add - Filter when calculating new price on the order page.
* Add - Filter for the displayed price on the product page.
* Add - Elements for the future Stock Management plugin.
* Removed - Spanish translation.
* Update - French translation.
* Update - Calendar CSS.
* Dev - Refactored code and plugin's structure.

= 1.4.1 =

* Fix - Fixed an error when updating orders.
* Fix - Fixed an error when adding a normal product to cart.
* Add - Spanish translation.
* Add - Display base price for one day on the product page.
* Add - Added an option to set the first available date.
* Update - French translation.

= 1.4 =

* Add - Option to set a minimum and a maximum booking duration for each product.
* Add - Possibility to change booking dates on the order page.
* Add - Possibility to add booking products on the order page.
* Add - en_GB translation file for the calendar.
* Add - WooCommerce Currency Switcher Compatibility
* Fix - Timezone issue with the datepicker.
* Fix - Prevent adding a product to the cart after clicking the "clear" button on the calendar.
* Fix - Incorrect selected dates with keyboard.
* Fix - Wrong price displayed when "Price excluding tax" is set on the product page.

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