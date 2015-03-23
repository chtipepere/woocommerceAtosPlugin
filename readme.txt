=== WoocommerceAtos ===
Contributors: chtipepere
Tags: Woocommerce, Payment Gateway, Atos, Cartes Bancaires
Requires at least: 4.0.1
Tested up to: 4.0.1
Stable tag: 1.1
License: Apache License 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0

Handles Atos payment for Woocommerce plugin.

== Description ==

Handles Atos payment in Woocommerce, credit card payment for french banks.

https://github.com/chtipepere/woocommerceAtosPlugin

== Installation ==

Depending on your web server, copy the correct binary files on your server.
If you are on Linux, and want to know if you run 32 or 64 bits, just type:

    getconf LONG_BIT

For these binaries, don't forget to add execution rights.

    chmod +x

Put your params files too on your web server.

To use the credit cards logos given with this plugin, change images path in your param/pathfile.

    D_LOGO!/wp-content/plugins/woocommerceAtos/images/!

Take a look at the examples atos files provided with this plugin to put the correct values in **YOUR** param files.

**Automatic response**

Create a page that contains shortcode above and fill the automatic_response_url field in admin.

    [woocommerce_atos_automatic_response]

See https://github.com/chtipepere/woocommerceAtosPlugin

== Screenshots ==

1. Backoffice options
2. Gateway choices
3. Pick your card and pay

== Changelog ==

= 1.1 =
* Fix variable name
* Compatible with php 5.3
* Increase compatibility

= 1.0 =
* Initial version

== Woocommerce Compatibility ==

Requires at least: 2.3.5
Tested up to: 2.3.7

== Credits ==

Based on http://thomasdt.com