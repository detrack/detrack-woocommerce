=== Detrack for WooCommerce ===
Contributors: chester0detrack
Tags: detrack, woocommerce, integration, api
Requires at least: 3.0.1
Tested up to: 5.2.2
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Real-time tracking and electronic proof of delivery solution that allows you and your customers to track their orders and have full visibility of the entire delivery process.

== Description ==

On Aug 20, 2019, we will deprecate support for this open source WooCommerce plugin. There will be no future updates beyond version 1.6.4. If you are currently using the plugin, you should still be able to use it as per before. We recommend that if you wish to establish or continue to establish an integration between WooCommerce and Detrack, you can refer to our API documentation to build your own integration.

Want vital delivery information of all your orders at your fingertips?

Why not allow your customers to track their WooCommerce orders themselves?

Detrack is a real-time vehicle tracking and proof of delivery solution that provides both you and your customers the ability to track their orders and have full visibility of the entire delivery process.

Knowing exactly where your orders are at any time will now allow you to answer customers’ queries or resolve delivery issues both efficiently and confidently.

The Detrack plugin allows you to seamlessly integrate your WooCommerce store with your Detrack dashboard.

To use the plugin, simply set up a [free Detrack account](https://app.detrack.com/sign_up).

Completely new to Detrack? We got you.

Check out Detrack’s key features on a [Quick Tour](https://www.detrack.com/tour/) or see how you can get started in no time with our [Quick Start Guide](https://www.detrack.com/quick-start-guide/).

Once the plugin is successfully installed and configured, new orders on your WooCommerce store will automatically be sent to your Detrack dashboard to start facilitating deliveries.

By default, only the Delivery Address and Customer Name will be reflected on your Detrack dashboard, but you can easily opt for more information to appear by selecting the necessary options in the settings.
View the full tutorial on how to [integrate your WooCommerce store with Detrack here](https://www.detrack.com/tutorials/woocommerce-integration/).


== Installation ==

You must already have a WooCommerce store set up on your WordPress installation to use this plugin.

1. Install the plugin through the WordPress plugins screen, or go to the Releases page for this plugin's [GitHub Repository](https://github.com/detrack/detrack-woocommerce).
2. Activate the plugin through the "Plugins" screen in WordPress
3. Go to WooCommerce -> Settings -> Integration Page to set your Detrack API Key and configure other settings. Your Detrack API Key can be found in your Detrack Dashboard.

== Changelog ==

= 1.6.4 =
- Added a failsafe to address an issue where orders that (somehow) do not have a date will cause the plugin to crash. 

= 1.6.3 =
- The `sync_on_update` setting ("Automatically push to detrack on updating orders") will now affect **any** status transition, to allow allow users to push orders to Detrack in complete manual mode.

= 1.6.2 =
- Fixed a bug that caused the processing of certain line items to crash
- Improve logging
- Tested with the lastest versions of WordPress 5 and WooCommerce 3.6

= 1.6.1 =
- Tested with the latest versions of WordPress 5
- Updated dependencies

= 1.6.0 =
- Added the "type" attribute in attribute mapping – you can now use this option to choose which deliveries get posted to Detrack as a Delivery or a Collection.

= 1.5.4 =
- Fixed a bug where the detrack_do post metadata is not being set when the sync_on_checkout option is not selected
- Added failsafe for marking orders as complete – if a matching detrack_do post metadata is not found, an order with matching internal post id will be marked as completed instead.
- Minor code cleanups

= 1.5.3 =
- Minor changes on backend logging

= 1.5.2 =
- Minor changes on backend logging
- Test on WordPress 5.x and WooCommerce 3.5.x

= 1.5.1 =
- Fixed a bug where orders placed after the new year but made in 2018 will show up as 2018 instead of 2019. Happy New Year!

= 1.5.0 =
- Add support for automatically marking orders as complete while using custom DO numbers

= 1.4.2 =
- Fix a bug where some orders will not be marked as completed in WooCommerce when they already have been marked as complete in the Detrack Dashboard caused by other plugins denying access to the REST API routes.

= 1.4.1 =
- Fixed a bug where setting a certain option for address data formats will result in errors

= 1.4.0 =
- Added the "ignore" attribute in attribute mapping – you can now use this option to filter out deliveries to not post to Detrack

= 1.3.1 =
- Fixed a bug where testing the date field in expert mode will cause a crash

= 1.3.0 =
- Added support for PHP string and array functions in the Attribute Mapping Expert Mode
- Fixed a bug where retrieving order metadata in the Attribute Mapping Expert Mode test console would cause an error

= 1.2.0 =
- Fixed a bug where cancelled order status is not synced when the customer abandons the payment with Paypal
- Add partial support for customising DO numbers
- Other minor bug fixers

= 1.1.2 =
- Fixed a bug where not having a country set in the order will cause the plugin to crash

= 1.1.1 =
- Fixed a bug where opening the Integration settings without any orders in the WC database will cause the settings page to stop rendering.
- Removed alpha test note

= 1.1 =
- Expert Mode: Expert Mode Data Formatting allows you to access the order objects directly and implement your own custom formulae to customise how the data should be sent to Detrack.
- Fixed a bug where posting null data in the checkout hook will cause the plugin to crash.
- Fixed a bug where customising the date format will rarely cause the plugin to crash.

= 1.0 =
* Initial Public Release

= 0.0 =
* For older releases, please look at the plugin's [GitHub Repository](https://github.com/detrack/detrack-woocommerce).

== Privacy Policy ==
This plugin sends the following data to our servers in order to function:
- Details of new orders, for us to dispatch delivery jobs
- The current version number of the plugin installed (to check for updates)
No other information (such as the other posts that you and your customers make on your site) are sent to us.
The data stored on our servers is then subject to our platform's privacy policy, which can be viewed in full [here](https://www.detrack.com/privacy-policy/)

There is a debug log that stores non-sensitive diagnostic information about your store, but it is *not* sent to us automatically.
