# WooCommerce Subscription Shortcodes

Experimental extension providing a few shortcodes that you can use to add details about the subscription product where you want them to be. Either to be used in a custom theme for your single product pages or use them to blog about a product you are selling.

These shortcodes are compaitable with [WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) currently in development.

---

# Guide

### Requirements

In order to use the extension, you will need:

* WooCommerce 2.3 or newer.
* WooCommerce Subscriptions v2.0 or newer.
* A staging or test site, I do not recommend using this on live sites yet.
* A sense of adventure as the codebase is still beta.

### Installation

1. Upload the plugin's files to the `/wp-content/plugins/` directory of your WordPress site
2. Activate the plugin through the **Plugins** menu in WordPress

### Shortcode Usage

Simply insert the shortcode you wish to use on the product page or blog post or even a standard page. If you use the shortcode outside of the product page then you will need to add the product ID attribute or the sku ID attribute.

> Example: `[subscription_price id="123"]`

#### More Information
* [How to use shortcodes](https://codex.wordpress.org/Shortcode)
* [How to insert shorcodes into your templates](https://developer.wordpress.org/reference/functions/do_shortcode/#user-contributed-notes)

---

##### Subscription Price
Shortcode: `[subscription_price]`
###### Default Arguments
* period: false
* length: false
* sign_up_fee: false
* trial_length: false
* before_price: NULL
* after_price: NULL

##### Subscription Period
Displays the subscription period of the subscription product.

Shortcode: `[subscription_period]`
###### Default Arguments
* just_period: true

> Example: Returns as default: `Per Month`. Changing the argument to false returns just `Month`.

##### Subscription Period Interval
Displays the subscription period interval of the subscription product.

Shortcode: `[subscription_period_interval]`

##### Subscription Length
Displays the subscription length of the subscription product.

Shortcode: `[subscription_length]`

> Example: `Every Month`

##### Subscription Sign Up Fee
Displays the subscription sign-up fee of the subscription product.

Shortcode: `[subscription_sign_up_fee]`
###### Default Arguments
* before_price: NULL
* after_price: NULL

##### Subscription Trial Length
Displays the subscription trial length of the subscription product.

Shortcode: `[subscription_trial_length]`

##### Subscription Trial Period
Displays the subscription trial period of the subscription product.

Shortcode: `[subscription_trial_period]`

##### Subscription First Payment
This displays the date and/or time of the first payment of the subscription.

Shortcode: `[subscription_first_payment]`
###### Default Arguments
* show_time: false
* from_date: NULL
* timezone: gmt
* format: timestamp

##### Subscription Initial Payment
Displays the price of the initial payment of the subscription.

Shortcode: `[subscription_first_payment]`

---

### Other Shortcodes

##### Subscription Discount
Displays the subscription discount of the subscription product based on the regular price.

> Please Note: This shortcode only works for products using the mini-extension "[WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things)" that have a discount applied.

Shortcode: `[subscription_discount]`

> Example: `65% discount`

### Other Notes
In order to show details for sign up fee, trial length and trial period of a product using [WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) you will need to also have [WooCommerce Subscribe to All the Things - Sign-up and Trial Add-on](https://github.com/seb86/woocommerce-subscribe-to-all-the-things-signup-trial-add-on) installed.

---

### Filters
For the moment I thought it would be best to allow the developer to add support for a product type by filtering it in. By default, only `subscription` and `subscription-variation` are supported.

> Will push a PR for WooCommerce Subscribe to All the Things mini-extension to add support for all the product types it supports once more stable.

Below is an example on how you can filter the supported product types yourself should you need to.

```
/**
 * Add product support for a new product type.
 *
 * @param array $product_types
 * @return array
 */
function add_sub_shortcode_product_support( $product_types ) {
	$product_types[] = 'mix-and-match';

	return $product_types;
}
add_filter( 'wcss_product_types', 'add_sub_shortcode_product_support', 10, 1 );
```

---

#### License
This plugin is released under [GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html).

#### Credits
[Prospress](http://prospress.com/) are the developers of the [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) extension and [WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) mini-extension.

This extension is developed and maintained by [me](https://sebastiendumont.com).

This project was backed by [Subscription Group Limited](http://www.subscriptiongroup.co.uk).