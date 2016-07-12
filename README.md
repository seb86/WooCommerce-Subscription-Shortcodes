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
Shortcode: `[subscription_period]`

##### Subscription Period Interval
Shortcode: `[subscription_period_interval]`

##### Subscription Length
Shortcode: `[subscription_length]`

##### Subscription Sign Up Fee
Shortcode: `[subscription_sign_up_fee]`
###### Default Arguments
* before_price: NULL
* after_price: NULL

##### Subscription Trial Length
Shortcode: `[subscription_trial_length]`

##### Subscription Trial Length
Shortcode: `[subscription_trial_period]`

##### Subscription First Payment
Shortcode: `[subscription_first_payment]`
###### Default Arguments
* show_time: false

---

### Other Shortcodes

##### Subscription Discount
> This shortcode only works for products using the mini-extension "[WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things)" that have a discount applied.

Shortcode: `[subscription_discount]`

### Other Notes
In order to show details for sign up fee, trial length and trial period of a product using [WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) you will need to also have [WooCommerce Subscribe to All the Things - Sign-up and Trial Add-on](https://github.com/seb86/woocommerce-subscribe-to-all-the-things-signup-trial-add-on) installed.

---

#### License
This plugin is released under [GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html).

#### Credits
[Prospress](http://prospress.com/) are the developers of the [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) extension and [WooCommerce Subscribe to All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) mini-extension.

This extension is developed and maintained by [me](https://sebastiendumont.com).