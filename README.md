# WooCommerce Subscription Shortcodes

[![Build Status](https://scrutinizer-ci.com/g/seb86/WooCommerce-Subscription-Shortcodes/badges/build.png?b=master)](https://scrutinizer-ci.com/g/seb86/WooCommerce-Subscription-Shortcodes/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/seb86/WooCommerce-Subscription-Shortcodes/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/seb86/WooCommerce-Subscription-Shortcodes/?branch=master)

Experimental extension providing a few shortcodes that you can use to add details about the subscription product where you want them to be. Either to be used in a custom theme for your single product pages or use them to blog about a product you are selling.

These shortcodes are compaitable with [WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) currently in development.

---

# Guide

### Requirements

In order to use the extension, you will need:

* WooCommerce 2.3 or newer.
* WooCommerce Subscriptions v2.0 or newer.
* A staging or test site, I do not recommend using this on live sites yet.
* A sense of adventure as the codebase is still beta.

### Installation
To install the Subscription Shortcodes:

1. Download the latest version of the plugin [here](https://github.com/seb86/WooCommerce-Subscription-Shortcodes/archive/master.zip)
2. Go to **Plugins > Add New > Upload** administration screen on your WordPress site
3. Select the ZIP file you just downloaded
4. Click **Install Now**
5. Click **Activate**

After installation, you can use the shortcodes listed below.

### Shortcode Usage

Simply insert the shortcode you wish to use on the product page or blog post or even a standard page. If you use the shortcode outside of the product page then you will need to add the product ID attribute or the sku ID attribute.

> Example: `[subscription_price id="123"]` or `[subscription_price sku="123"]`

#### More Information
* [How to use shortcodes](https://codex.wordpress.org/Shortcode)
* [How to insert shorcodes into your templates](https://developer.wordpress.org/reference/functions/do_shortcode/#user-contributed-notes)

---

##### Subscription Price
Returns the current price of the subscription along with the rest of the subscription data in a single string.

Shortcode: `[subscription_price]`
###### Default Arguments
* period: false
* length: false
* sign_up_fee: false
* trial_length: false
* before_price: NULL
* after_price: NULL

> This shortcode still requires some work.

##### Subscription Price Meta
This particular shortcode returns the price meta data. If the subscription is on sale, then the price meta returns both the sale price and regular price striked through. Otherwise it will return just the regular price as normal. However you can force it to return what you want only.

> Example: Enter `regular` as the meta value to display just the regular price. Enter `sale` as the meta value to display just the sale price. Enter `active` as the meta value to display just the active price.

Shortcode: `[subscription_price_meta]`
###### Default Arguments
* meta: both

##### Subscription Period
Displays the subscription period of the subscription product.

Shortcode: `[subscription_period]`
###### Default Arguments
* raw: false

> Example: Returns as default: `Per Month`. Changing the argument to true returns just `month`.

##### Subscription Period Interval
Displays the subscription period interval of the subscription product.

Shortcode: `[subscription_period_interval]`

##### Subscription Length
Displays the subscription length of the subscription product.

Shortcode: `[subscription_length]`
###### Default Arguments
* raw: false

> Example: Returns as default: `Every Month`. Changing the argument to true returns just `month`.

##### Subscription Sign Up Fee
Displays the price tag of the subscription sign-up fee of the subscription product.

Shortcode: `[subscription_sign_up_fee]`
###### Default Arguments
* raw: false
* before_price: NULL
* after_price: NULL

##### Subscription Trial
Displays the subscription trial details of the subscription product.

> Returns blank if no trial is set. Otherwise returns `28 Days` for example.

Shortcode: `[subscription_trial]`

##### Subscription Trial Length
Displays the subscription trial length of the subscription product.

> Returns blank if no trial is set.

Shortcode: `[subscription_trial_length]`

##### Subscription Trial Period
Displays the subscription trial period of the subscription product.

> Returns blank if no trial is set. Otherwise returns one of the following: `Day`, `Week`, `Month` or `Year`

Shortcode: `[subscription_trial_period]`
###### Default Arguments
* raw: false

##### Subscription First Payment
This displays the date and/or time of the first payment of the subscription.

Shortcode: `[subscription_first_payment]`
###### Default Arguments
* show_time: false
* from_date: NULL
* timezone: gmt
* format: timestamp

> Example: By default it returns `2016-08-15`. If __show_time__ is set to true it returns `2016-08-15 10:45:30`.
> You can also return the first payment in a string format which is more readable. Set the __format__ argument to `string` and it returns `Mon 15th Aug 2016` or `Mon 15th Aug 2016 10:45 AM` if __show_time__ is set to true.

##### Subscription Initial Payment
Displays the price of the initial payment of the subscription including any sign up fee.

Shortcode: `[subscription_initial_payment]`

> Example: If the subscription has a sign up fee then it will return `20,00€ with a 99,00€ sign up fee.` If you have set the total argument to true then you will get a total price `119€`.

###### Default Arguments
* total: false

---

### Other Shortcodes

##### Subscription Discount
Displays the subscription discount of the subscription product based on the regular price.

> Example: `65% discount`

> Please Note: This shortcode only works for products using the mini-extension "[WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things)" that have a discount applied.

Shortcode: `[subscription_discount]`

This shortcode string ending can also be filtered using this filter. `wcs_shortcodes_sub_discount_string`.

### Other Notes
In order to show details for sign up fee, trial length and trial period of a product using [WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) you will need to also have [WooCommerce Subscribe All the Things - Sign-up and Trial Add-on](https://github.com/seb86/woocommerce-subscribe-to-all-the-things-signup-trial-add-on) installed.

---

### Filters

##### Product Support
Developers can add support for a product type by filtering it in. By default, only `subscription` and `subscription-variation` are supported.

If you have **WooCommerce Subscribe All the Things** mini-extension installed then support for all the product types the extension supports is automatically applied.

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

##### Date Format
You can filter the date format for `[subscription_first_payment]` to how you wish to display it such as changing the order of the __day__, __month__ and __year__.

Below is an example on how you can filter the date format for UK.

```
/**
 * Filters the date format for UK display for the first payment subscription shortcode.
 *
 * @param  string $date_format
 * @param  array  $atts
 * @return string
 */
function wcss_first_payment_date_format_uk( $date_format, $atts ) {
	if ( $atts['show_time'] ) {
		if ( 'timestamp' == $atts['format'] ) {
			$date_format = 'd-m-Y H:i:s';
		}
	} else {
		if ( 'timestamp' == $atts['format'] ) {
			$date_format = 'd-m-Y';
		}
	}

	return $date_format;
} // END first_payment_date_format()
add_filter( 'wcss_first_payment_date_format', wcss_first_payment_date_format_uk, 10, 2 );
```

---

# Contributing

If you have a patch, or stumbled upon an issue with the shortcodes, you can contribute this back to the code. Please read the [contributor guidelines](https://github.com/seb86/WooCommerce-Subscription-Shortcodes/blob/master/CONTRIBUTING.md) for more information on how you can do this.

---

#### License
This plugin is released under [GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html).

#### Credits
[Prospress](http://prospress.com/) are the developers of the [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) extension and [WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) mini-extension.

This extension is developed and maintained by [me](https://sebastiendumont.com).

This project was backed by [Subscription Group Limited](http://www.subscriptiongroup.co.uk).