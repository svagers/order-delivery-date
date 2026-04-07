<?php
/**
 * Common functions for same day/next day & minimum deliver time.
 *
 * @package order-delivery-date/cutoff-functions
 */

/**
 * Get the current timestamp
 */
function orddd_get_current_time() {
	$gmt = false;
	if ( has_filter( 'orddd_gmt_calculations' ) ) {
		$gmt = apply_filters( 'orddd_gmt_calculations', '' );
	}
	$current_time = current_time( 'timestamp', $gmt );

	return $current_time;
}

/**
 * Get the same day/next day cutoff
 *
 * @param string $cutoff_day Same day/Next day.
 */
function orddd_get_cutoff_timestamp( $cutoff_day = 'same_day' ) {
	$current_time  = orddd_get_current_time();
	$current_day   = gmdate( 'd', $current_time );
	$current_month = gmdate( 'm', $current_time );
	$current_year  = gmdate( 'Y', $current_time );

	$cutoff_hours = get_option( 'orddd_disable_same_day_delivery_after_hours' );
	$cutoff_mins  = get_option( 'orddd_disable_same_day_delivery_after_minutes' );

	if ( 'next_day' === $cutoff_day ) {
		$cutoff_hours = get_option( 'orddd_disable_next_day_delivery_after_hours' );
		$cutoff_mins  = get_option( 'orddd_disable_next_day_delivery_after_minutes' );
	}

	if ( 'on' === get_option( 'orddd_enable_day_wise_settings' ) ) {
		$current_weekday  = 'orddd_weekday_' . gmdate( 'w', $current_time );
		$advance_settings = false !== get_option( 'orddd_advance_settings' ) ? get_option( 'orddd_advance_settings' ) : array();

		if ( '' !== $advance_settings && '{}' !== $advance_settings && '[]' !== $advance_settings ) {
			foreach ( $advance_settings as $ak => $av ) {
				if ( $current_weekday === $av['orddd_weekdays'] ) {
					if ( 'same_day' === $cutoff_day && '' !== $av['orddd_disable_same_day_delivery_after_hours'] ) {
						$cut_off_time = explode( ':', $av['orddd_disable_same_day_delivery_after_hours'] );
						$cutoff_hours = $cut_off_time[0];
						$cutoff_mins  = $cut_off_time[1];
					} elseif ( 'next_day' === $cutoff_day && '' !== $av['orddd_disable_next_day_delivery_after_hours'] ) {
						$cut_off_time = explode( ':', $av['orddd_disable_next_day_delivery_after_hours'] );
						$cutoff_hours = $cut_off_time[0];
						$cutoff_mins  = $cut_off_time[1];
					}
				}
			}
		}
	}

	$cut_off_timestamp = gmmktime( $cutoff_hours, $cutoff_mins, 0, $current_month, $current_day, $current_year );

	return apply_filters( 'orddd_modify_cutoff_timestamp', $cut_off_timestamp, $cutoff_day );
}

/**
 * Get the minimum delivery time in seconds for general settings.
 */
function orddd_get_minimum_delivery_time() {
	$current_time          = orddd_get_current_time();
	$minimum_delivery_time = '' !== get_option( 'orddd_minimumOrderDays' ) ? get_option( 'orddd_minimumOrderDays' ) * 60 * 60 : 0;

	if ( 'on' === get_option( 'orddd_enable_day_wise_settings' ) ) {
		$current_weekday  = 'orddd_weekday_' . gmdate( 'w', $current_time );
		$advance_settings = false !== get_option( 'orddd_advance_settings' ) ? get_option( 'orddd_advance_settings' ) : array();

		if ( '' !== $advance_settings && '{}' !== $advance_settings && '[]' !== $advance_settings ) {
			foreach ( $advance_settings as $ak => $av ) {
				if ( $current_weekday === $av['orddd_weekdays'] ) {
					if ( '' !== $av['orddd_minimumOrderDays'] ) {
						$minimum_delivery_time = $av['orddd_minimumOrderDays'] * 60 * 60;
					}
				}
			}
		}
	}

	return apply_filters( 'orddd_modify_minimum_delivery_time', $minimum_delivery_time );
}

/**
 * Get the minimum delivery time for custom settings.
 */
function orddd_get_minimum_delivery_time_custom() {
	if ( 'on' === get_option( 'orddd_enable_shipping_based_delivery' ) ) {
		$shipping_method    = '';
		$shipping_class     = '';
		$location           = '';
		$shipping_settings  = array();
		$product_categories = array();

		if ( isset( $_POST['orddd_location'] ) ) { //phpcs:ignore
			$location = $_POST['orddd_location']; //phpcs:ignore
		}

		if ( isset( $_POST['shipping_method'] ) ) { //phpcs:ignore
			$shipping_method = $_POST['shipping_method']; //phpcs:ignore
		}

		if ( isset( $_POST['shipping_class'] ) ) { //phpcs:ignore
			$shipping_class   = $_POST['shipping_class']; //phpcs:ignore
			$shipping_classes = explode( ',', $shipping_class );
		}

		if ( isset( $_POST['product_category'] ) ) { //phpcs:ignore
			$product_category   = $_POST['product_category']; //phpcs:ignore
			$product_categories = explode( ',', $product_category );
		}

		$results                  = orddd_common::orddd_get_shipping_settings();
		$custom_settings          = array();
		$shipping_settings_exists = 'No';

		foreach ( $results as $key => $value ) {
			$shipping_settings = get_option( $value->option_name );
			if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) &&
			'orddd_locations' === $shipping_settings['delivery_settings_based_on'][0] ) {
				if ( in_array( $location, $shipping_settings['orddd_locations'], true ) ) {
					$shipping_settings_exists = 'Yes';
					$custom_settings[]        = $shipping_settings;
				}
			} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_settings['delivery_settings_based_on'][0] ) {
				if ( has_filter( 'orddd_get_shipping_method' ) ) {
					$shipping_methods_values               = apply_filters( 'orddd_get_shipping_method', $custom_settings, $_POST, $shipping_settings['shipping_methods'], $shipping_method ); //phpcs:ignore
					$shipping_settings['shipping_methods'] = $shipping_methods_values['shipping_methods'];
					$shipping_method                       = $shipping_methods_values['shipping_method'];
				}

				if ( isset( $shipping_settings[ 'shipping_methods' ] ) && in_array( $shipping_method, $shipping_settings['shipping_methods'], true ) ) {
					$shipping_settings_exists = 'Yes';
					$custom_settings[]        = $shipping_settings;
				}
			} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'product_categories' === $shipping_settings['delivery_settings_based_on'][0] ) {
				foreach ( $product_categories as $pkey => $pvalue ) {
					if ( isset( $shipping_settings[ 'product_categories' ] ) && in_array( $pvalue, $shipping_settings['product_categories'], true ) ) {
						if ( isset( $shipping_settings['shipping_methods_for_categories'] )
							&& ( in_array( $shipping_method, $shipping_settings['shipping_methods_for_categories'], true )
							|| in_array( $shipping_class, $shipping_settings['shipping_methods_for_categories'], true ) ) ) {
							$shipping_settings_exists = 'Yes';
							$custom_settings[]        = $shipping_settings;
						} elseif ( ! isset( $shipping_settings['shipping_methods_for_categories'] ) ) {
							$shipping_settings_exists = 'Yes';
							$custom_settings[]        = $shipping_settings;
						}
					}
				}
			} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_settings['delivery_settings_based_on'][0] ) {
				foreach ( $shipping_classes as $skey => $svalue ) {
					if ( isset( $shipping_settings[ 'shipping_methods' ] ) && in_array( $svalue, $shipping_settings['shipping_methods'], true ) ) {
						$shipping_settings_exists = 'Yes';
						$custom_settings[]        = $shipping_settings;
					}
				}
			}
		}

		$min_hour = 0;
		if ( 'Yes' === $shipping_settings_exists ) {
			$minimum_time     = orddd_get_higher_minimum_delivery_time();
			$same_day_enabled = 'No';
			$next_day_enabled = 'No';
			foreach ( $custom_settings as $key => $val ) {
				if ( isset( $val['same_day'] ) ) {
					$same_day = $val['same_day'];
					if ( isset( $same_day['after_hours'] ) && $same_day['after_hours'] == 0 && isset( $same_day['after_minutes'] ) && $same_day['after_minutes'] == 00 ) {
						$same_day_enabled = 'No';
					} else {
						$same_day_enabled = 'Yes';
					}
				}

				if ( isset( $val['next_day'] ) ) {
					$next_day = $val['next_day'];
					if ( isset( $next_day['after_hours'] ) && $next_day['after_hours'] == 0 && isset( $next_day['after_minutes'] ) && $next_day['after_minutes'] == 00 ) {
						$next_day_enabled = 'No';
					} else {
						$next_day_enabled = 'Yes';
					}
				}

				if ( '' !== $minimum_time && 0 != $minimum_time ) { //phpcs:ignore
					$min_hour = $minimum_time;
				} else {
					if ( isset( $val['minimum_delivery_time'] ) && '' !== $val['minimum_delivery_time'] ) {
						$min_hour = $val['minimum_delivery_time'];
						if ( '' === $min_hour ) {
							$min_hour = 0;
						}
					}
				}
			}
		}
	}

	$minimum_delivery_time = $min_hour * 60 * 60;
	return apply_filters( 'orddd_modify_minimum_delivery_time_custom', $minimum_delivery_time, $custom_settings );
}

/**
 * Get the highest minimum delivery time for 2 product categories or shipping classes.
 */
function orddd_get_higher_minimum_delivery_time() {
	$minimum_delivery_time = wp_cache_get( 'orddd_get_higher_minimum_delivery_time' );

	if ( false === $minimum_delivery_time ) {
		global $wpdb;
		$minimum_delivery_time = 0;
		$terms_id              = array();
		$shipping_class        = array();

		$orddd_shipping_based_delivery = get_option( 'orddd_enable_shipping_based_delivery' );
		if ( 'on' === $orddd_shipping_based_delivery ) {
			$results = orddd_common::orddd_get_shipping_settings();

			if ( isset( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					$product_id = $values['data']->get_id();
					if ( 'product_variation' === $values['data']->post_type ) {
						$product_id = $values['product_id'];
					}

					$terms          = get_the_terms( $product_id, 'product_cat' );
					$shipping_class = get_the_terms( $product_id, 'product_shipping_class' );

					if ( ! $shipping_class ) {
						$shipping_class = array();
					}
					// get the category IDs.
					if ( '' !== $terms ) {
						foreach ( $terms as $term => $val ) {
							$id = orddd_common::get_base_product_category( $val->term_id );

							array_push( $terms_id, $id );
						}
					}
				}
			}

			if ( is_array( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $key => $value ) {
					$shipping_settings = get_option( $value->option_name );

					if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) &&
					'product_categories' === $shipping_settings['delivery_settings_based_on'][0] ) {
						if ( isset( $shipping_settings['product_categories'] ) && ! isset( $shipping_settings['shipping_methods_for_categories'] ) ) {
							$product_category = $shipping_settings['product_categories'];
							foreach ( $terms_id as $term => $val ) {
								$cat_slug = orddd_common::ordd_get_cat_slug( $val );
								if ( in_array( $cat_slug, $product_category, true ) && $minimum_delivery_time < $shipping_settings['minimum_delivery_time'] && '' !== $shipping_settings['minimum_delivery_time'] ) {
									$minimum_delivery_time = $shipping_settings['minimum_delivery_time'];
									break;
								}
							}
						}
					} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_settings['delivery_settings_based_on'][0] ) {
						if ( isset( $shipping_settings['shipping_methods'] ) ) {
							$shipping_methods = $shipping_settings['shipping_methods'];

							if ( '' !== $shipping_class ) {
								foreach ( $shipping_class as $term => $val ) {
									if ( in_array( $val->slug, $shipping_methods, true ) && $minimum_delivery_time < $shipping_settings['minimum_delivery_time'] && '' !== $shipping_settings['minimum_delivery_time'] ) {
										$minimum_delivery_time = $shipping_settings['minimum_delivery_time'];
										break;
									}
								}
							}
						}
					}
				}
			}
			wp_cache_set( 'orddd_get_higher_minimum_delivery_time', $minimum_delivery_time );
		}
	}
	return $minimum_delivery_time;
}

/**
 * Get the highest same day cutoff from 2 product categories or shipping classes.
 */
function orddd_get_highest_same_day() {
	$results        = orddd_common::orddd_get_shipping_settings();
	$same_day       = array();
	$same_day_hours = 0;
	$same_day_min   = 00;
	$terms_id       = array();
	$shipping_class = array();

	if ( isset( WC()->cart ) ) {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product_id = $values['data']->get_id();
			if ( 'product_variation' === $values['data']->post_type ) {
				$product_id = $values['product_id'];
			}

			$terms          = get_the_terms( $product_id, 'product_cat' );
			$shipping_class = get_the_terms( $product_id, 'product_shipping_class' );

			if ( ! $shipping_class ) {
				$shipping_class = array();
			}
			// get the category IDs.
			if ( '' !== $terms ) {
				foreach ( $terms as $term => $val ) {
					$id = orddd_common::get_base_product_category( $val->term_id );
					array_push( $terms_id, $id );
				}
			}
		}
	}

	$orddd_shipping_based_delivery = get_option( 'orddd_enable_shipping_based_delivery' );
	if ( 'on' === $orddd_shipping_based_delivery && is_array( $results ) && count( $results ) > 0 ) {
		foreach ( $results as $key => $value ) {
			$shipping_settings = get_option( $value->option_name );

			if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'product_categories' === $shipping_settings['delivery_settings_based_on'][0] ) {
				if ( isset( $shipping_settings['product_categories'] ) && ! isset( $shipping_settings['shipping_methods_for_categories'] ) ) {
					$product_category = $shipping_settings['product_categories'];
					foreach ( $terms_id as $term => $val ) {
						$cat_slug = orddd_common::ordd_get_cat_slug( $val );

						if ( in_array( $cat_slug, $product_category, true ) && isset( $shipping_settings['same_day'] ) && $shipping_settings['same_day']['after_hours'] > 0 && ( $same_day_hours < $shipping_settings['same_day']['after_hours'] || ( $same_day_hours === $shipping_settings['same_day']['after_hours'] && $same_day_min === $shipping_settings['same_day']['after_minutes'] ) || $same_day_hours == 0 ) ) { //phpcs:ignore

							// same day is enabled.
							$same_day                      = $shipping_settings['same_day'];
							$same_day['same_day_disabled'] = 'no';
							$same_day_hours                = $shipping_settings['same_day']['after_hours'];
							$same_day_min                  = $shipping_settings['same_day']['after_minutes'];

						} elseif ( in_array( $cat_slug, $product_category, true ) && ( ! isset( $shipping_settings['same_day'] ) || ( isset( $shipping_settings['same_day'] ) && '0' === $shipping_settings['same_day']['after_hours'] && '00' === $shipping_settings['same_day']['after_minutes'] ) ) && ( isset( $shipping_settings['next_day'] ) && '0' !== $shipping_settings['next_day']['after_hours'] ) ) {

							// same day is not set, but next day is set.
							$same_day = array( 'same_day_disabled' => 'yes' );
							break 2;
						}
					}
				}
			} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_settings['delivery_settings_based_on'][0] ) {
				if ( isset( $shipping_settings['shipping_methods'] ) ) {
					$shipping_methods = $shipping_settings['shipping_methods'];
					foreach ( $shipping_class as $term => $val ) {

						if ( in_array( $val->slug, $shipping_methods, true ) && isset( $shipping_settings['same_day'] ) &&
						$shipping_settings['same_day']['after_hours'] > 0 && ( $same_day_hours < $shipping_settings['same_day']['after_hours'] || ( $same_day_hours === $shipping_settings['same_day']['after_hours'] && $same_day_min === $shipping_settings['same_day']['after_minutes']
						) || $same_day_hours == 0 ) ) { //phpcs:ignore

							// same day is set.
							$same_day                      = $shipping_settings['same_day'];
							$same_day['same_day_disabled'] = 'no';
							$same_day_hours                = $shipping_settings['same_day']['after_hours'];
							$same_day_min                  = $shipping_settings['same_day']['after_minutes'];

						} elseif ( in_array( $val->slug, $shipping_methods, true ) && ( ! isset( $shipping_settings['same_day'] ) || ( isset( $shipping_settings['same_day'] ) && '0' === $shipping_settings['same_day']['after_hours'] && '00' === $shipping_settings['same_day']['after_minutes'] ) ) && ( isset( $shipping_settings['next_day'] ) && '0' !== $shipping_settings['next_day']['after_hours'] ) ) {

							// same day is not set, but next day is set.
							if ( isset( $shipping_settings['next_day'] ) && $shipping_settings['next_day']['after_hours'] > 0 ) {

								$same_day = array( 'same_day_disabled' => 'yes' );
								break 2;
							}
						}
					}
				}
			} else {
				$same_day = array();
			}
		}
	}
	return $same_day;
}


/**
 * Get the highest next day cutoff from 2 product categories or shipping classes.
 */
function orddd_get_highest_next_day() {
	$next_day       = array();
	$next_day_hours = 0;
	$next_day_min   = 00;

	$results        = orddd_common::orddd_get_shipping_settings();
	$terms_id       = array();
	$shipping_class = array();

	if ( isset( WC()->cart ) ) {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product_id = $values['data']->get_id();
			if ( 'product_variation' === $values['data']->post_type ) {
				$product_id = $values['product_id'];
			}

			$terms          = get_the_terms( $product_id, 'product_cat' );
			$shipping_class = get_the_terms( $product_id, 'product_shipping_class' );

			if ( ! $shipping_class ) {
				$shipping_class = array();
			}
			// get the category IDs.
			if ( '' !== $terms ) {
				foreach ( $terms as $term => $val ) {
					$id = orddd_common::get_base_product_category( $val->term_id );
					array_push( $terms_id, $id );
				}
			}
		}
	}

	$orddd_shipping_based_delivery = get_option( 'orddd_enable_shipping_based_delivery' );
	if ( 'on' === $orddd_shipping_based_delivery && is_array( $results ) && count( $results ) > 0 ) {
		foreach ( $results as $key => $value ) {
			$shipping_settings = get_option( $value->option_name );

			if ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'product_categories' === $shipping_settings['delivery_settings_based_on'][0] ) {
				if ( isset( $shipping_settings['product_categories'] ) && ! isset( $shipping_settings['shipping_methods_for_categories'] ) ) {
					$product_category = $shipping_settings['product_categories'];
					foreach ( $terms_id as $term => $val ) {
						$cat_slug = orddd_common::ordd_get_cat_slug( $val );

						if ( in_array( $cat_slug, $product_category, true ) && isset( $shipping_settings['next_day'] ) && $shipping_settings['next_day']['after_hours'] > 0 && ( $next_day_hours < $shipping_settings['next_day']['after_hours'] || ( $next_day_hours == $shipping_settings['next_day']['after_hours'] && $next_day_min == $shipping_settings['next_day']['after_minutes'] ) || $next_day_hours == 0 ) ) { //phpcs:ignore

							// next day enabled.
							$next_day                      = $shipping_settings['next_day'];
							$next_day['next_day_disabled'] = 'no';
							$next_day_hours                = $shipping_settings['next_day']['after_hours'];
							$next_day_min                  = $shipping_settings['next_day']['after_minutes'];

						} elseif ( in_array( $cat_slug, $product_category, true ) && ( ! isset( $shipping_settings['next_day'] ) || ( isset( $shipping_settings['next_day'] ) && '0' === $shipping_settings['next_day']['after_hours'] && '00' === $shipping_settings['next_day']['after_minutes'] ) ) && ( ! isset( $shipping_settings['same_day'] ) || ( isset( $shipping_settings['same_day'] ) && '0' === $shipping_settings['same_day']['after_hours'] && '00' === $shipping_settings['same_day']['after_minutes'] ) ) ) {

							// next day is disabled & same day is disabled.
							// if next_day_hours are not set.
							if ( 0 == $next_day_hours ) { //phpcs:ignore
								$next_day = array( 'next_day_disabled' => 'yes' );
								break;
							}
						}
					}
				}
			} elseif ( isset( $shipping_settings['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_settings['delivery_settings_based_on'][0] ) {
				if ( isset( $shipping_settings['shipping_methods'] ) ) {
					$shipping_methods = $shipping_settings['shipping_methods'];
					foreach ( $shipping_class as $term => $val ) {
						if ( in_array( $val->slug, $shipping_methods, true ) && isset( $shipping_settings['next_day'] ) && $shipping_settings['next_day']['after_hours'] > 0 && ( $next_day_hours < $shipping_settings['next_day']['after_hours'] || ( $next_day_hours === $shipping_settings['next_day']['after_hours'] && $next_day_min === $shipping_settings['next_day']['after_minutes'] ) || 0 == $next_day_hours ) ) { //phpcs:ignore

							// next day is set.
							$next_day                      = $shipping_settings['next_day'];
							$next_day['next_day_disabled'] = 'no';
							$next_day_hours                = $shipping_settings['next_day']['after_hours'];
							$next_day_min                  = $shipping_settings['next_day']['after_minutes'];

						} elseif ( in_array( $val->slug, $shipping_methods, true ) && ( ! isset( $shipping_settings['next_day'] )
						|| ( isset( $shipping_settings['next_day'] ) && '0' === $shipping_settings['next_day']['after_hours'] && '00' === $shipping_settings['next_day']['after_minutes'] ) ) && ( ! isset( $shipping_settings['same_day'] ) || ( isset( $shipping_settings['same_day'] ) && '0' === $shipping_settings['same_day']['after_hours'] && '00' === $shipping_settings['same_day']['after_minutes'] ) ) ) {

							// next day is not set, same day is not set.
							$next_day = array( 'next_day_disabled' => 'yes' );
							break 2;

						}
					}
				}
			}
		}
	}

	return $next_day;
}
