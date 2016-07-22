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
			'subscription_price_meta'      => 'get_subscription_price_meta',
			'subscription_discount'        => 'get_subscription_discount',
			'subscription_period'          => 'get_subscription_period',
			'subscription_period_interval' => 'get_subscription_period_interval',
			'subscription_length'          => 'get_subscription_length',
			'subscription_sign_up_fee'     => 'get_subscription_sign_up_fee',
			'subscription_trial'           => 'get_subscription_trial_string',
			'subscription_trial_length'    => 'get_subscription_trial_length',
			'subscription_trial_period'    => 'get_subscription_trial_period',
			'subscription_first_payment'   => 'get_subscription_first_payment',
			'subscription_initial_payment' => 'get_subscription_initial',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), array( __CLASS__, $function ) );
		} // END foreach()

		// Adds alternative subscription price from the WooCommerce extension "Subscribe to All the Things" and returns the lowest scheme price.
		add_action( 'woocommerce_subscriptions_shortcode_get_price', array( __CLASS__, 'get_satt_lowest_price' ), 10, 1 );

		// Adds the product types supported from the WooCommerce extension "Subscribe to All the Things".
		add_filter( 'wcss_product_types', array( __CLASS__, 'support_product_types_for_wc_satt' ), 10, 1 );
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
	 * Returns the lowest subscription scheme.
	 * Only works with WooCommerce Subscribe to All the Things v1.1.0+
	 *
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function get_satt_lowest_scheme_data( $product ) {
		if ( class_exists( 'WCS_ATT_Schemes' ) && class_exists( 'WCS_ATT_Scheme_Prices' ) ) {
			$product_level_schemes = WCS_ATT_Schemes::get_product_subscription_schemes( $product );

			return WCS_ATT_Scheme_Prices::get_lowest_price_subscription_scheme_data( $product, $product_level_schemes );
		}
	} // END get_satt_lowest_scheme()

	/**
	 * Returns the lowest subscription scheme price string.
	 * Only works with WooCommerce Subscribe to All the Things v1.1.0+
	 *
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function get_satt_lowest_price( $product ) {
		$scheme = self::get_satt_lowest_scheme_data( $product );

		if ( !empty( $scheme ) && is_array( $scheme ) ) {
			// Override price?
			$override = $scheme['scheme']['subscription_pricing_method'];

			// Discount?
			$discount = $scheme['scheme']['subscription_discount'];

			// Prices
			$prices = array(
				'price'                      => $scheme['price'],
				'regular_price'              => $scheme['regular_price'],
				'sale_price'                 => $scheme['sale_price'],
				'subscription_price'         => $scheme['scheme']['subscription_price'],
				'subscription_regular_price' => $scheme['scheme']['subscription_regular_price'],
				'subscription_sale_price'    => $scheme['scheme']['subscription_sale_price']
			);

			// Prepare the price
			$price = '';

			if ( 'inherit' == $override ) {
				$price = empty( $discount ) ? $price : ( empty( $prices[ 'regular_price' ] ) ? $prices[ 'regular_price' ] : round( ( double ) $prices[ 'regular_price' ] * ( 100 - $discount ) / 100, wc_get_price_decimals() ) );
			} else if ( 'override' == $override ) {
				$price = $prices['subscription_price'];

				if ( $prices[ 'subscription_price' ] < $prices[ 'subscription_regular_price' ] ) {
					$price = $prices[ 'subscription_sale_price' ];
				}
			}

			// If the price is returned as an array, return just the first.
			if ( is_array( $price ) ) {
				$price = $price[0];
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

		$defaults = shortcode_atts( array(
			'id'           => '',
			'sku'          => '',
			'period'       => false,
			'length'       => false,
			'sign_up_fee'  => false,
			'trial_length' => false,
			'before_price' => '<span class="price subscription-price">',
			'after_price'  => '</span>',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
	 * Displays the price meta of the subscription product.
	 *
	 * @global $wpdb
	 * @global WP_Post $post
	 * @param  array   $atts
	 * @return string
	 */
	public static function get_subscription_price_meta( $atts ) {
		global $wpdb, $post;

		$defaults = shortcode_atts( array(
			'id'           => '',
			'sku'          => '',
			'meta'         => 'both',
			'before_price' => '',
			'after_price'  => '',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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

		$price = WC_Subscriptions_Product::get_price( $product_data->id );

		// Remove the subscription price wrapper.
		$price_html = str_replace('<span class="subscription-details">', '', $price);
		$price = str_replace('</span">', '', $price_html);

		// If the subscription product has no price, then look for alternative.
		if ( empty( $price ) ) {
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {

				// These values will be overridden. They are defined here to acknowledge their existence.
				$price = '';
				$regular_price = '';
				$sale_price = '';

				$prices = array(
					'price'                      => $scheme['price'],
					'regular_price'              => $scheme['regular_price'],
					'sale_price'                 => $scheme['sale_price'],
					'method'                     => $scheme['scheme']['subscription_pricing_method'],
					'subscription_price'         => $scheme['scheme']['subscription_price'],
					'subscription_regular_price' => $scheme['scheme']['subscription_regular_price'],
					'subscription_sale_price'    => $scheme['scheme']['subscription_sale_price']
				);

				// Return the subscription price based on the pricing method.
				switch( $prices['method'] ) {
					case 'override':
						$price         = $prices['subscription_price'];
						$regular_price = $prices['subscription_regular_price'];
						$sale_price    = $prices['subscription_sale_price'];
						break;
					case 'inherit':
						$discount      = $scheme['scheme']['subscription_discount'];
						$price         = $prices['price'];
						$regular_price = $prices['regular_price'];
						$sale_price    = $prices['sale_price'];

						if ( !empty( $discount ) && $discount > 0 ) {
							$sale_price = round( ( double ) $regular_price * ( 100 - $discount ) / 100, wc_get_price_decimals() );
						}

						break;
				}

				// Display both the regular price striked out and the sale price 
				// should the active price be less than the regular price.
				if ( $atts['meta'] != 'active' && !empty( $sale_price ) && $price < $regular_price ) {
					$price = '<del>' . ( ( is_numeric( $regular_price ) ) ? wc_price( $regular_price ) : $regular_price ) . '</del> <ins>' . ( ( is_numeric( $sale_price ) ) ? wc_price( $sale_price ) : $sale_price ) . '</ins>';

					// Trim the whitespace.
					$price = trim( $price );
				}

				// Override the value should only one value be returned.
				if ( $atts['meta'] != 'both' ) {
					if ( $atts['meta'] == 'active' ) {
						$price = $price;
					}

					if ( $atts['meta'] == 'regular' ) {
						$price = $regular_price;
					}

					if ( $atts['meta'] == 'sale' ) {
						$price = $sale_price;
					}
				}

				if ( is_numeric( $price ) ) {
					$price = wc_price( $price );
				}

				// Clean the price tag.
				$price = self::clean_wc_price( $price );

			}

		}

		$price_html = sprintf( __( '%s%s%s', WCSS::TEXT_DOMAIN ), $atts['before_price'], $price, $atts['after_price'] );

		echo html_entity_decode( $price_html );

		return ob_get_clean();
	} // END get_subscription_price_meta()

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

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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

		$override = 'inherit'; // Returns inherit by default. Will be overridden later.
		$discount = ''; // Returns empty by default.

		// Get Subscription Discount - Only available with the WooCommerce extension "Subscribe to All the Things".
		$scheme = self::get_satt_lowest_scheme_data( $product_data );

		if ( !empty( $scheme ) && is_array( $scheme ) ) {
			// Override price?
			$override = $scheme['scheme']['subscription_pricing_method'];

			// Discount ?
			$discount = $scheme['scheme']['subscription_discount'];
		}

		if ( ! empty( $discount ) && is_numeric( $discount ) && $override == 'inherit' ) {
			$discount = sprintf( __( '%s%s %s', WCSS::TEXT_DOMAIN ), $discount, '%', apply_filters( 'wcs_shortcodes_sub_discount_string', __( 'discount', WCSS::TEXT_DOMAIN ) ) );
		}

		echo $discount;

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

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
			'raw' => false
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {
				$period = $scheme['scheme']['subscription_period'];
			}
		}

		if ( ! $atts['raw'] ) {
			$period = sprintf( __( 'Per %s', WCSS::TEXT_DOMAIN ), $period );
			$period = ucwords($period);
		}

		echo $period;

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

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {
				$period_interval = $scheme['scheme']['subscription_period_interval'];
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

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
			'raw' => false,
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {

				$period = self::get_subscription_period( array( 'id' => $product_data->id, 'raw' => true ) );
				$length = $scheme['scheme']['subscription_length'];

				// If we are not returning raw data then making it readable for humans.
				if ( ! $atts['raw'] ) {

					if ( $length > 0 ) {
						$length = sprintf( '%s %s', $length, $period );
					} else {
						$length = sprintf( __( 'Every %s', WCSS::TEXT_DOMAIN ), $period );
					}

					$length = ucfirst($length);

				}

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

		$defaults = shortcode_atts( array(
			'id'           => '',
			'sku'          => '',
			'raw'          => false,
			'before_price' => '',
			'after_price'  => '',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {
				$sign_up_fee = $scheme['scheme']['subscription_sign_up_fee'];
			}
		}

		if ( ! $atts['raw'] ) {
			// Convert number into a price tag.
			if ( is_numeric( $sign_up_fee ) ) {
				$sign_up_fee = wc_price( $sign_up_fee );
			}

			// Clean the price tag.
			$sign_up_fee = self::clean_wc_price( $sign_up_fee );

			$price_html = sprintf( __( '%s%s%s', WCSS::TEXT_DOMAIN ), $atts['before_price'], $sign_up_fee, $atts['after_price'] );

			$sign_up_fee = html_entity_decode( $price_html );
		}

		echo $sign_up_fee;

		return ob_get_clean();
	} // END get_subscription_sign_up_fee()

	/**
	 * Displays the subscription trial details of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_trial_string( $atts ) {
		global $wpdb, $post;

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
		$trial_length = self::get_subscription_trial_length( array( 'id' => $product_data->id ) );

		// Get Subscription Trial Period
		$trial_period = self::get_subscription_trial_period( array( 'id' => $product_data->id, 'raw' => true ) );

		if ( ! empty( $trial_length ) && $trial_length > 0 ) {

			switch ( $trial_period ) {
				case 'day':
					echo sprintf( _n( '%s day', '%s days', $trial_length, WCSS::TEXT_DOMAIN ), $trial_length );
					break;

				case 'week':
					echo sprintf( _n( '%s week', '%s weeks', $trial_length, WCSS::TEXT_DOMAIN ), $trial_length );
					break;

				case 'month':
					echo sprintf( _n( '%s month', '%s months', $trial_length, WCSS::TEXT_DOMAIN ), $trial_length );
					break;

				case 'year':
					echo sprintf( _n( '%s year', '%s years', $trial_length, WCSS::TEXT_DOMAIN ), $trial_length );
					break;
			}

		}

		return ob_get_clean();
	} // END get_subscription_trial_string()

	/**
	 * Displays the subscription trial length of the subscription product.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function get_subscription_trial_length( $atts ) {
		global $wpdb, $post;

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {
				$trial_length = $scheme['scheme']['subscription_trial_length'];
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

		$defaults = shortcode_atts( array(
			'id'  => '',
			'sku' => '',
			'raw' => false,
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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
		$trial_length = self::get_subscription_trial_length( array( 'id' => $product_data->id ) );

		// Get Subscription Trial Period
		$trial_period = WC_Subscriptions_Product::get_trial_period( $product_data );

		// If the trial length is empty or is not zero, look for alternative.
		if ( empty( $trial_length ) || $trial_length != 0 ) {
			$scheme = self::get_satt_lowest_scheme_data( $product_data );

			if ( !empty( $scheme ) && is_array( $scheme ) ) {
				$trial_length = $scheme['scheme']['subscription_trial_length'];
				$trial_period = $scheme['scheme']['subscription_trial_period'];
			}
		}

		if ( ! empty( $trial_length ) && $trial_length > 0 ) {

			if ( ! $atts['raw'] ) {
				$trial_period = ucfirst($trial_period);
			}

		}

		echo $trial_period;

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

		$defaults = shortcode_atts( array(
			'id'        => '',
			'sku'       => '',
			'show_time' => false,
			'from_date' => '',
			'timezone'  => 'gmt',
			'format'    => 'timestamp'
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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

		$billing_interval = self::get_subscription_period_interval( array( 'id' => $product_data->id ) );
		$billing_length   = self::get_subscription_length( array( 'id' => $product_data->id, 'raw' => true ) );
		$trial_length     = self::get_subscription_trial_length( array( 'id' => $product_data->id ) );

		$from_date = $atts['from_date'];

		if ( $billing_interval !== $billing_length || $trial_length > 0 ) {
			if ( empty( $from_date ) ) {
				$from_date = gmdate( 'Y-m-d H:i:s' );
			}

			// If the subscription has a free trial period, the first renewal is the same as the expiration of the free trial.
			if ( $trial_length > 0 ) {
				$first_renewal_timestamp = strtotime( self::get_trial_expiration_date( $product_data->id, $from_date ) );
			} else {
				$from_timestamp = strtotime( $from_date );
				$billing_period = self::get_subscription_period( array( 'id' => $product_data->id, 'raw' => true ) );

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

		$date_format = ''; // Will be overridden later on.

		if ( $first_renewal_timestamp > 0 ) {
			if ( $atts['show_time'] ) {
				if ( 'timestamp' == $atts['format'] ) {
					$date_format = 'Y-m-d H:i:s';
				} else if ( 'string' == $atts['format'] ) {
					$date_format = 'D jS F Y H:i A';
				}
			} else {
				if ( 'timestamp' == $atts['format'] ) {
					$date_format = 'Y-m-d';
				} else if ( 'string' == $atts['format'] ) {
					$date_format = 'D jS F Y';
				}
			}

			$date_format = apply_filters( 'wcss_first_payment_date_format', $date_format, $atts );

			$first_payment = date( $date_format, $first_renewal_timestamp );
		} else {
			$first_payment = '';
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

		$defaults = shortcode_atts( array(
			'id'        => '',
			'sku'       => '',
			'total'     => false
		), $atts );

		$atts = wp_parse_args( $atts, $defaults );

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

		// Subscription Active Price
		$initial_payment = self::get_subscription_price_meta( array( 'id' => $product_data->id, 'meta' => 'active' ) );

		// Free Trial ?
		$trial_length = self::get_subscription_trial_length( array( 'id' => $product_data->id ) );

		// If there is a free trial then the initial payment is Zero.
		if ( $trial_length > 0 ) {
			$initial_payment = 0;
		}

		// Sign up fee ?
		$sign_up_fee = self::get_subscription_sign_up_fee( array( 'id' => $product_data->id, 'raw' => true ) );

		// Apply the sign up fee if it exists.
		if ( !empty( $sign_up_fee ) && $sign_up_fee > 0 ) {

			if ( ! $atts['total'] ) {
				$initial_payment = sprintf( __( '%s with a %s sign up fee.', WCSS::TEXT_DOMAIN ), wc_price( $initial_payment ), wc_price( $sign_up_fee ) );
			} else {
				$initial_payment = round( ( double ) $initial_payment+$sign_up_fee, wc_get_price_decimals() );
			}

		}

		// Convert number into a price tag.
		if ( is_numeric( $initial_payment ) ) {
			$initial_payment = wc_price( $initial_payment );
		}

		// Clean the price tag.
		$initial_payment = self::clean_wc_price( $initial_payment );

		echo $initial_payment;

		return ob_get_clean();
	} // END get_subscription_initial()

	/**
	 * Adds the product types supported from the WooCommerce extension "Subscribe to All the Things".
	 *
	 * @param  $product_types
	 * @return array
	 */
	public static function support_product_types_for_wc_satt( $product_types ) {
		// Only add the product types from the WooCommerce extension "Subscribe to All the Things" if it is active.
		if ( class_exists( 'WCS_ATT' ) ) {
			$satt_product_types = WCS_ATT()->get_supported_product_types();
			$product_types = array_merge( $satt_product_types, $product_types );
		}

		return $product_types;
	} // support_product_types_for_wc_satt()

	/**
	 * This function returns the formatted price tag clean without
	 * WooCommerce price span wrapper which was added in version 2.6
	 *
	 * @param  string $price
	 * @global $woocommerce
	 * @return string
	 */
	public static function clean_wc_price( $price ) {
		global $woocommerce;

		if ( version_compare( $woocommerce->version, '2.6.0' ) >= 0 ) {

			$find = array(
				'<span class="woocommerce-Price-amount amount">', 
				'<span class="woocommerce-Price-currencySymbol">', 
				'</span>'
			);

			foreach( $find as $remove ) {
				$price = str_replace( $remove, '', $price );
			}

		}

		return $price;
	} // END clean_wc_price

	/**
	 * Takes a subscription product's ID and returns the date on which the subscription trial will expire,
	 * based on the subscription's trial length and calculated from either the $from_date if specified,
	 * or the current date/time.
	 *
	 * @param int $product_id The product/post ID of the subscription
	 * @param mixed $from_date A MySQL formatted date/time string from which to calculate the expiration date (in UTC timezone), or empty (default), which will use today's date/time (in UTC timezone).
	 */
	public static function get_trial_expiration_date( $product_id, $from_date = '' ) {
		$trial_expiration_date = WC_Subscriptions_Product::get_trial_expiration_date( $product_id, $from_date );

		// If returned empty then try alternative.
		if ( empty( $trial_expiration_date ) ) {

			$trial_period = self::get_subscription_trial_period( array( 'id' => $product_id, 'raw' => true ) );
			$trial_length = self::get_subscription_trial_length( array( 'id' => $product_id ) );

			if ( $trial_length > 0 ) {

				if ( empty( $from_date ) ) {
					$from_date = gmdate( 'Y-m-d H:i:s' );
				}

				if ( 'month' == $trial_period ) {
					$trial_expiration_date = date( 'Y-m-d H:i:s', wcs_add_months( strtotime( $from_date ), $trial_length ) );
				} else { // Safe to just add the billing periods
					$trial_expiration_date = date( 'Y-m-d H:i:s', strtotime( "+ {$trial_length} {$trial_period}s", strtotime( $from_date ) ) );
				}

			} else {
				$trial_expiration_date = 0;
			}

		}

		return $trial_expiration_date;
	} // END get_trial_expiration_date()

} // END WCSS_Shortcodes

WCSS_Shortcodes::init();