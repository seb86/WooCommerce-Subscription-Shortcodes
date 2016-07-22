=== WooCommerce Subscription Shortcodes ===
Contributors: sebd86
Tags: woocommerce, subscriptions, subscribe, order, cart, product, product type, extension, short, shortcode, shortcodes
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=mailme@sebastiendumont.com&currency_code=&amount=&return=&item_name=Donation+for+WooCommerce+Subscription+Shortcodes
Requires at least: 4.1
Tested up to: 4.5.3
Stable tag: 1.0.0 Beta
WC requires at least: 2.3
WC tested up to: 2.6.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provides shortcodes that you can use to add details about the subscription product where you want them to be.

== Description ==

Provides shortcodes that you can use to add details about the subscription product where you want them to be. Either to be used in a custom theme for your single product pages or use them in your blog to talk about a product you are selling.

> #### Please note
> Due to the size of **[WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/)** and it's experimental extension **[WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things)**, this plugin is in beta and requires your feedback to make sure everything is stable. So far there is only one shortcode that is still in development.
> Tests so far shows all other shortcodes are working fine.

= Requirements =
In order to use this extension, you will need the follow:

* WooCommerce 2.3 or newer.
* WooCommerce Subscriptions v2.0 or newer.
* A staging or test site, I do not recommend using this on live sites yet.
* A sense of adventure as the codebase is still beta.

= Support =
As this is a free plugin I can not provide support. I recommend you ask the community for support should you need it.

= Contributing =

If you have a patch, or stumbled upon an issue with the shortcodes, you can contribute this back to the code. Please read the [contributor guidelines](https://github.com/seb86/WooCommerce-Subscription-Shortcodes/blob/master/CONTRIBUTING.md) for more information on how you can do this.

= Credits =

[Prospress](http://prospress.com/) are the developers of the [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) extension and [WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) mini-extension.

This project was backed by [Subscription Group Limited](http://www.subscriptiongroup.co.uk/).

= Disclaimers =
The WooCommerce logo and the WooCommerce name are trademarks of Automattic Inc. No affiliation or endorsement of this plugin by Automattic is intended or implied.

== Installation ==

To install the Subscription Shortcodes:

1. Download the latest version of the plugin here
2. Go to Plugins > Add New > Upload administration screen on your WordPress site
3. Select the ZIP file you just downloaded
4. Click Install Now
5. Click Activate
6. After installation, you can use the shortcodes listed below.

== Other Notes ==

= Shortcode Usage =

Simply insert the shortcode you wish to use on the product page or blog post or even a standard page. If you use the shortcode outside of the product page then you will need to add the product ID attribute or the sku ID attribute.

> Example: `[subscription_price id="123"]` or `[subscription_price sku="123"]`

A full list of the shortcodes can be found [here](https://github.com/seb86/WooCommerce-Subscription-Shortcodes).

### More Information
* [How to use shortcodes](https://codex.wordpress.org/Shortcode)
* [How to insert shorcodes into your templates](https://developer.wordpress.org/reference/functions/do_shortcode/#user-contributed-notes)

In order to show details for sign up fee, trial length and trial period of a product using [WooCommerce Subscribe All the Things](https://github.com/Prospress/woocommerce-subscribe-all-the-things) you will need to also have [WooCommerce Subscribe All the Things - Sign-up and Trial Add-on](https://github.com/seb86/woocommerce-subscribe-to-all-the-things-signup-trial-add-on) installed.

- Other [WordPress plugins](http://profiles.wordpress.org/sebd86/) by [Sébastien Dumont](https://sebastiendumont.com)
- Contact Sébastien on Twitter: [@sebd86](http://twitter.com/sebd86)
- If you're a developer yourself, follow or contribute to the [WooCommerce Subscription Shortcodes plugin on GitHub](https://github.com/seb86/WooCommerce-Subscription-Shortcodes)

== Changelog ==

= 1.0.0 Beta - 22/07/2016 =
* Initial release