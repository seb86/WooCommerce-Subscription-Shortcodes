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

Simply insert the shortcode you wish to use on the product page or blog post or even a standard page. If you use the shortcode outside of the product page then you will need to add the product ID attribute.

> Example: `[subscription_price id="123"]`

---

##### Subscription Price
Shortcode: `[subscription_price]`

##### Subscription Period
Shortcode: `[subscription_period]`

##### Subscription Period Interval
Shortcode: `[subscription_period_interval]`

##### Subscription Length
Shortcode: `[subscription_length]`

##### Subscription Sign Up Fee
Shortcode: `[subscription_sign_up_fee]`

##### Subscription Trial Length
Shortcode: `[subscription_trial_length]`

##### Subscription Trial Length
Shortcode: `[subscription_trial_period]`

##### Subscription First Payment
Shortcode: `[subscription_first_payment]`

---

#### License
This plugin is released under [GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html).