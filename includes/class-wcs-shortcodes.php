<?php
/**
 * Subscription Shortcodes
 *
 * @class WCSS_Shortcodes
 * @since 1.0.0
 */

class WCSS_Shortcodes {

	/**
	 * Initialize the shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'subscription_price'           => 'get_subscription_price',
			'subscription_discount'        => 'get_subscription_discount',
			'subscription_period'          => 'get_subscription_period',
			'subscription_period_interval' => 'get_subscription_period_interval',
			'subscription_length'          => 'get_subscription_length',
			'subscription_sign_up_fee'     => 'get_subscription_sign_up_fee',
			'subscription_trial_length'    => 'get_subscription_trial_length',
			'subscription_trial_period'    => 'get_subscription_trial_period',
			'subscription_first_payment'   => 'get_subscription_first_payment',
			'subscription_initial_payment' => 'get_subscription_initial',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), array( __CLASS__, $function ) );
		} // END foreach()

		// Adds support for product types that have subscription scheme options.
		add_filter( 'woocommerce_is_subscription', array( __CLASS__, 'force_is_subscription' ), 10, 3 );

		// Adds alternative subscription price from the WooCommerce extension "Subscribe to All the Things" and returns the lowest scheme price.
		add_action( 'woocommerce_subscriptions_shortcode_get_price', array( __CLASS__, 'get_satt_lowest_price' ), 10, 1 );
	} // END init()

	/**
	 * Get the supported product types.
	 * By default, this is only the subscription product types.
	 * However, it can be filtered to allow other product types should you need to.
	 *
	 * @return array
	 */
	public static function get_supported_product_types() {
		return apply_filters( 'wcss_product_types', array(
			'subscription', 
			'subscription-variation', 
		) );
	} // END get_supported_product_types()

	/**
	 * Adds support for product types that have subscription scheme options.
	 *
	 * @param  bool   $is_subscription
	 * @param  int    $product_id
	 * @param  object $product
	 * @return bool
	 */
	public static function force_is_subscription( $is_subscription, $product_id, $product ) {
		if ( is_object( $product_id ) ) {
			$product    = $product_id;
			$product_id = $product->id;
		} elseif ( is_numeric( $product_id ) ) {
			$product = wc_get_product( $product_id );
		}

		if ( in_array( $product->product_type, self::get_supported_product_types() ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && WCS_ATT_Schemes::get_product_subscription_schemes( $product ) ) {
				$is_subscription = true;
			}
		}

		return $is_subscription;
	} // END force_is_subscription()

	/**
	 * Returns the subscription price string.
	 *
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function get_price( $product ) {
		if ( WC_Subscriptions_Product::get_price( $product->id ) > 0 ) {

			return ecs_html( WC_Subscriptions_Product::get_price( $product->id, array(
				'subscription_period' => false,
				'subscription_length' => false,
				'sign_up_fee'         => false,
				'trial_length'        => false,
			) ) );

		} else {

			/**
			 * This hook enables for other price possibilities.
			 *
			 * hooked: get_satt_lowest_price - 10
			 */
			do_action( 'woocommerce_subscriptions_shortcode_get_price', $product );

		}
	} // END get_price()

	/**
	 * Returns the lowest subscription scheme price string.
	 *
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function get_satt_lowest_price( $product ) {
		if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
			$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product );

			$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product, $product_level_schemes );

			// Override price?
			$override = $lowest_scheme['scheme']['subscription_pricing_method'];

			// Discount?
			$discount = $lowest_scheme['scheme']['subscription_discount'];

			// Prices
			$prices = array(
				'price'                      => $lowest_scheme['price'],
				'regular_price'              => $lowest_scheme['regular_price'],
				'sale_price'                 => $lowest_scheme['sale_price'],
				'subscription_price'         => $lowest_scheme['scheme']['subscription_price'],
				'subscription_regular_price' => $lowest_scheme['scheme']['subscription_regular_price'],
				'subscription_sale_price'    => $lowest_scheme['scheme']['subscription_sale_price']
			);

			if ( $override === 'inherit' && ! empty( $discount ) && $prices[ 'price' ] > 0 ) {
				$price = empty( $discount ) ? $price : ( empty( $prices[ 'regular_price' ] ) ? $prices[ 'regular_price' ] : round( ( double ) $prices[ 'regular_price' ] * ( 100 - $discount ) / 100, wc_get_price_decimals() ) );
			} else {
				$price = $prices['subscription_price'];

				if ( $prices[ 'subscription_price' ] < $prices[ 'subscription_regular_price' ] ) {
					$price = $prices[ 'subscription_sale_price' ];
				}
			}

			return $price;
		}
	} // END get_price_satt()

	/**
	 * Displays the price of the subscription product and 
	 * returns only the price information you wish to return.
	 *
	 * @global $wpdb
	 * @global WP_Post $post
	 * @param  array   $atts
	 * @return string
	 */
	public static function get_subscription_price( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'           => '',
			'sku'          => '',
			'period'       => false,
			'length'       => false,
			'sign_up_fee'  => false,
			'trial_length' => false,
			'before_price' => '',
			'after_price'  => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		$price_html = WC_Subscriptions_Product::get_price_string( $product_data->id, array(
			'price'               => self::get_price( $product_data ),
			'subscription_period' => isset( $atts['period'] ) ? $atts['period'] : true,
			'subscription_length' => isset( $atts['length'] ) ? $atts['length'] : true,
			'sign_up_fee'         => isset( $atts['sign_up_fee'] ) ? $atts['sign_up_fee'] : true,
			'trial_length'        => isset( $atts['trial_length'] ) ? $atts['trial_length'] : true,
		) );

		// Clean the subscription price wrapper.
		$price_html = str_replace('<span class="subscription-details">', '', $price_html);
		$price_html = str_replace('</span">', '', $price_html);

		// Trim the whitespace.
		$price_html = trim( $price_html );

		// Convert to Price Tag.
		$price_html = wc_price( $price_html );

		$price_html = sprintf( __( '%s%s%s', WCSS::TEXT_DOMAIN ), $atts['before_price'], $price_html, $atts['after_price'] );

		echo html_entity_decode( $price_html );

		return ob_get_clean();
	} // END get_subscription_price()

	/**
	 * Displays the subscription discount of the subscription product.
	 * This shortcode only work with products using the mini-extension
	 * "WooCommerce Subscribe to All the Things".
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_discount( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		$discount = ''; // Returns empty by default.

		// Get Subscription Discount
		if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
			$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

			$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

			$discount = $lowest_scheme['scheme']['subscription_discount'];
		}

		if ( ! empty( $discount ) && is_numeric( $discount ) ) {
			echo sprintf( __( '%s%s %s', WCSS::TEXT_DOMAIN ), $discount, '%', apply_filters( 'wcs_shortcodes_sub_discount_string', __( 'discount', WCSS::TEXT_DOMAIN ) ) );
		}

		return ob_get_clean();
	} // END get_subscription_discount()

	/**
	 * Displays the subscription period of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_period( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		// Get Subscription Period
		$period = WC_Subscriptions_Product::get_period( $product_data );

		// If the period is empty, look for alternative.
		if ( empty( $period ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
				$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

				$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

				$period = $lowest_scheme['scheme']['subscription_period'];
			}
		}

		echo sprintf( __( 'Per %s', WCSS::TEXT_DOMAIN ), ucfirst($period) );

		return ob_get_clean();
	} // END get_subscription_period()

	/**
	 * Displays the subscription period interval of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_period_interval( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		// Get Subscription Period Interval
		$period_interval = WC_Subscriptions_Product::get_interval( $product_data );

		// If the period is empty, look for alternative.
		if ( empty( $period_interval ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
				$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

				$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

				$period_interval = $lowest_scheme['scheme']['subscription_period_interval'];
			}
		}

		echo $period_interval;

		return ob_get_clean();
	} // END get_subscription_period_interval()

	/**
	 * Displays the subscription length of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_length( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		// Get Subscription Length
		$length = WC_Subscriptions_Product::get_length( $product_data );

		// If the length is empty, look for alternative.
		if ( empty( $length ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
				$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

				$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

				$length = $lowest_scheme['scheme']['subscription_length'];
			}
		}

		echo $length;

		return ob_get_clean();
	} // END get_subscription_length()

	/**
	 * Displays the subscription sign-up fee of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_sign_up_fee( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'           => '',
			'sku'          => '',
			'before_price' => '',
			'after_price'  => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		// Get Subscription Sign Up Fee
		$sign_up_fee = WC_Subscriptions_Product::get_sign_up_fee( $product_data );

		// If the sign up fee is empty, look for alternative.
		if ( empty( $sign_up_fee ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
				$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

				$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

				$sign_up_fee = $lowest_scheme['scheme']['subscription_sign_up_fee'];
			}
		}

		// Convert number into a price tag.
		if ( is_numeric( $sign_up_fee ) ) {
			$price_html = wc_price( $sign_up_fee );
		}

		$price_html = sprintf( __( '%s%s%s', WCSS::TEXT_DOMAIN ), $atts['before_price'], $price_html, $atts['after_price'] );

		echo html_entity_decode( $price_html );

		return ob_get_clean();
	} // END get_subscription_sign_up_fee()

	/**
	 * Displays the subscription trial length of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_trial_length( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		// Get Subscription Trial Length
		$trial_length = WC_Subscriptions_Product::get_trial_length( $product_data );

		// If the trial length is empty, look for alternative.
		if ( empty( $trial_length ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
				$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

				$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

				$trial_length = $lowest_scheme['scheme']['subscription_trial_length'];
			}
		}

		echo $trial_length;

		return ob_get_clean();
	} // END get_subscription_trial_length()

	/**
	 * Displays the subscription trial period of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_trial_period( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		// Get Subscription Trial Period
		$trial_period = WC_Subscriptions_Product::get_trial_period( $product_data );

		// If the trial period is empty, look for alternative.
		if ( empty( $trial_period ) ) {
			if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
				$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product_data );

				$lowest_scheme = WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product_data, $product_level_schemes );

				$trial_length = $lowest_scheme['scheme']['subscription_trial_length'];
				$trial_period = $lowest_scheme['scheme']['subscription_trial_period'];

				if ( ! empty( $trial_length ) && $trial_length > 0 ) {
					$trial_period = sprintf( __( '%s%s', WCSS::TEXT_DOMAIN ), $trial_period, __( 's', WCSS::TEXT_DOMAIN ) );
				}
			}
		}

		echo ucfirst($trial_period);

		return ob_get_clean();
	} // END get_subscription_trial_period()

	/**
	 * Displays the date and/or time of the first payment of the subscription.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_first_payment( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'        => '',
			'sku'       => '',
			'show_time' => false,
			'from_date' => '',
			'timezone'  => 'gmt',
			'format'    => 'timestamp'
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		$billing_interval = self::get_subscription_period_interval( $atts = array('id' => $product_data->id ) );
		$billing_length   = self::get_subscription_length( $atts = array('id' => $product_data->id ) );
		$trial_length     = self::get_subscription_trial_length( $atts = array('id' => $product_data->id ) );

		$from_date = $atts['from_date'];

		if ( $billing_interval !== $billing_length || $trial_length > 0 ) {
			if ( empty( $from_date ) ) {
				$from_date = gmdate( 'Y-m-d H:i:s' );
			}

			// If the subscription has a free trial period, the first renewal is the same as the expiration of the free trial
			if ( $trial_length > 0 ) {
				$first_renewal_timestamp = strtotime( WC_Subscriptions_Product::get_trial_expiration_date( $product_data->id, $from_date ) );
			} else {
				$from_timestamp = strtotime( $from_date );
				$billing_period = self::get_subscription_period( $atts = array('id' => $product_data->id ) );

				if ( 'month' == $billing_period ) {
					$first_renewal_timestamp = wcs_add_months( $from_timestamp, $billing_interval );
				} else {
					$first_renewal_timestamp = strtotime( "+ $billing_interval {$billing_period}s", $from_timestamp );
				}

				if ( 'site' == $atts['timezone'] ) {
					$first_renewal_timestamp += ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
				}
			}
		} else {
			$first_renewal_timestamp = 0;
		}

		if ( $first_renewal_timestamp > 0 ) {
			if ( $atts['show_time'] ) {
				if ( 'timestamp' == $atts['format'] ) {
					$date_format = 'Y-m-d H:i:s';
				} else {
					$date_format = 'D jS M Y H:i A';
				}
			} else {
				if ( 'timestamp' == $atts['format'] ) {
					$date_format = 'Y-m-d';
				} else {
					$date_format = 'D jS M Y';
				}
			}

			$first_payment = date( $date_format, $first_renewal_timestamp );
		} else {
			$first_payment = 0;
		}

		echo $first_payment;

		return ob_get_clean();
	} // END get_subscription_first_payment()

	/**
	 * Displays the price of the initial payment of the subscription.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_initial( $atts ) {
		global $wpdb, $post;

		$atts = shortcode_atts( array(
			'id'        => '',
			'sku'       => '',
		), $atts );

		if ( ! empty( $atts['id'] ) && $atts['id'] > 0 ) {
			$product_data = wc_get_product( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			$product_data = wc_get_product( $post->ID );
		}

		// Check that the product type is supported. Return blank if not supported.
		if ( ! is_object( $product_data ) || ! in_array( $product_data->product_type, self::get_supported_product_types() ) ) {
			return '';
		}

		ob_start();

		$initial_payment = '';

		echo $initial_payment;

		return ob_get_clean();
	} // END get_subscription_initial()

} // END WCSS_Shortcodes

WCSS_Shortcodes::init();