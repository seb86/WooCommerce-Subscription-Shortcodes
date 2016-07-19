<?php
/*
 * Plugin Name: WooCommerce Subscription Shortcodes
 * Plugin URI:  https://github.com/seb86/WooCommerce-Subscription-Shortcodes
 * Description: Experimental extension providing a few shortcodes that you can use to add details about the subscription product where you want them to be.
 * Version:     1.0.0 Beta
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 *
 * Text Domain: woocommerce-subscription-shortcodes
 * Domain Path: /languages/
 *
 * Requires at least: 4.1
 * Tested up to: 4.5.3
 *
 * Copyright: © 2016 Sébastien Dumont.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined('ABSPATH') ) exit; // Exit if accessed directly.

if ( ! class_exists( 'WCSS' ) ) {

	class WCSS {

		/* Plugin version. */
		const VERSION = '1.0.0';

		/* Required WC version. */
		const REQ_WC_VERSION = '2.3.0';

		/* Text domain. */
		const TEXT_DOMAIN = 'woocommerce-subscription-shortcodes';

		/**
		 * @var WCSS - the single instance of the class.
		 *
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main WCSS Instance.
		 *
		 * Ensures only one instance of WCSS is loaded or can be loaded.
		 *
		 * @static
		 * @see WCSS()
		 * @return WCSS - Main instance
		 * @since 1.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		} // END instance()

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Foul!' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Foul!' ), '1.0.0' );
		}

		/**
		 * Do some work.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			add_action( 'init', array( $this, 'init_textdomain' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 4 );
		}

		public function plugin_url() {
			return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		} // END plugin_url()

		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		} // END plugin_path()

		public function plugins_loaded() {
			global $woocommerce;

			// Subs 2 check
			if ( ! function_exists( 'wcs_is_subscription' ) ) {
				add_action( 'admin_notices', array( $this, 'wcs_admin_notice' ) );
				return false;
			}

			// WC 2 check
			if ( version_compare( $woocommerce->version, self::REQ_WC_VERSION ) < 0 ) {
				add_action( 'admin_notices', array( $this, 'wc_admin_notice' ) );
				return false;
			}

			require_once( 'includes/class-wcs-shortcodes.php' );
		} // END plugins_loaded()

		/**
		 * Display a warning message if Subs version check fails.
		 *
		 * @return void
		 */
		public function wc_admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Subscription Shortcodes requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', self::TEXT_DOMAIN ), self::REQ_WC_VERSION ) . '</p></div>';
		} // END wc_admin_notice()

		/**
		 * Display a warning message if WC version check fails.
		 *
		 * @return void
		 */
		public function wcs_admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Subscription Shortcodes requires WooCommerce Subscriptions version 2.0+.', self::TEXT_DOMAIN ), self::REQ_WC_VERSION ) . '</p></div>';
		} // END wcs_admin_notice()

		/**
		 * Load textdomain.
		 *
		 * @return void
		 */
		public function init_textdomain() {
			load_plugin_textdomain( 'woocommerce-subscription-shortcodes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END init_text_domain()

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @param  mixed $links Plugin Row Meta
		 * @param  mixed $file  Plugin Base file
		 * @return array
		 */
		public function plugin_meta_links( $links, $file, $data, $status ) {
			if ( $file == plugin_basename( __FILE__ ) ) {
				$author1 = '<a href="' . $data[ 'AuthorURI' ] . '">' . $data[ 'Author' ] . '</a>';
				$author2 = '<a href="http://www.subscriptiongroup.co.uk/">Subscription Group Limited</a>';
				$links[ 1 ] = sprintf( __( 'By %s', self::TEXT_DOMAIN ), sprintf( __( '%s and %s', self::TEXT_DOMAIN ), $author1, $author2 ) );

			}

			return $links;
		} // END plugin_meta_links()

	} // END class

} // END if class exists

return WCSS::instance();
