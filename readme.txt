=== SEPA Payment Gateway for WooCommerce ===
Contributors: nhathuynhvandotcom
Tags: gutenberg, disable, disable gutenberg, editor, classic editor, blocks, block editor
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extends WooCommerce support SEPA Payment Gateway.

== Description ==

SEPA Payment Gateway is a way to directly withdraw money from your customers account. This is convenient for your customers, because they only need to provide their account details during checkout and everything else is automatic for them. It is also a very cheap payment method for you as a shop owner because there is no third party, like a payment service involved and bank fees for SEPA direct debit are typically very low!

In order to collect SEPA direct debit withdrawals, you need a so called Creditor ID.

Seamlessly adds SEPA Payment Gateway support to WooCommerce. Easily collect IBAN and BIC of your customers during checkout and export SEPA-XML-files ready for upload to your bank.

**Features**

* Validation of IBAN
* Creates XML-files that are 100% compliant to PAIN.008.003.02 and new PAIN.008.001.02 (SEPA 3.x) standard
* Overview before exporting XML in order detail
* Automatically mark order status to “On Hold”

**How does it work?**

WooCommerce --> Order --> SEPA Payment Gateway --> XML --> Your Bank

**Simple steps work?**

SEPA Payment Gateway for WooCommerce provides an easy way to offer SEPA Payment Gateway payment to your customers in 4 simple steps:

1. Every time, one of your customers chooses SEPA Payment Gateway as the payment method in an order, SEPA Payment Gateway creates a new outstanding payment.
2. You can now login to the Wordpress Admin backend and export outstanding payments in SEPA XML file on the order detail.
3. Download the SEPA-XML file created and upload it to the online banking of your bank. You will need a business account for this to work and some bank require you to unlock SEPA direct debit payments before this is possible. Contact your bank to find out more.
4. Check in your online-banking that the payment has arrived, then manually set the corresponding order to “Processing” in the WooCommerce backend.


== Changelog ==

= 1.0.0 =
Initial release.

== Frequently Asked Questions ==

= Default settings =

When activated this plugin, you can setting plugin in menu Setting WooCommerce --> Payments --> SEPA Payment Gateway


= Feature Ask for BIC? =

Some banks don't require BIC so you can allow this.

== Installation ==

This section describes how to install the plugin and get it working.
1. Upload `sepa-payment-gateway-for-woocommerce` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the “WooCommerce/Settings” menu and select the Payments Tab. Finally, click on the “SEPA Payment Gateway” link.
4. Enable the SEPA Payment Gateway payment gateway by checking the “Dieses Payment Gateway aktivieren” checkbox.
5. Fill in the information of the target bank account to which SEPA direct debit payments shall be transferred.
6. You can choose to not ask your customers for the BIC in case your bank accepts domestic SEPA debits without BIC. In this case, deselect the “Ask for BIC” checkbox.
7. Don’t forget to save your changes by clicking “Save changes”.

== Screenshots ==
1. Method "SEPA Payment Gateway" on page checkout
2. Info "SEPA Payment Gateway" after checkout on page ThankYou
3. Info "SEPA Payment Gateway" in Order detail Admin Dashboard
4. Info "SEPA Payment Gateway" in email
5. XML SEPA
6. Method "SEPA Payment Gateway" in setting payment gateway
7. Config "SEPA Payment Gateway"