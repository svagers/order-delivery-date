<?php
/**
 * Display Custom Delivery Settings in admin.
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/Custom-Delivery
 * @since 2.8.3
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Orddd_Shipping_Based_Settings class
 *
 * @class Orddd_Shipping_Based_Settings
 */
class Orddd_Shipping_Based_Settings {

	/**
	 * Callback for adding Custom Delivery settings tab settings
	 */
	public static function orddd_shipping_based_delivery_settings_callback() {}

	/**
	 * Callback for adding Enable custom delivery settings checkbox setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_enable_shipping_based_delivery_callback( $args ) {
		$enable_shipping_based_delivery = '';
		if ( 'on' === get_option( 'orddd_enable_shipping_based_delivery' ) ) {
			$enable_shipping_based_delivery = 'checked';
		}
		?>
		<input type="checkbox" name="orddd_enable_shipping_based_delivery" id="orddd_enable_shipping_based_delivery" class="day-checkbox" <?php echo esc_attr( $enable_shipping_based_delivery ); ?>/>
		<label for="orddd_enable_shipping_based_delivery"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding Save changes button
	 *
	 * @since 2.8.3
	 */
	public static function orddd_enable_shipping_based_delivery_save_callback() {
		submit_button( __( 'Save Settings', 'order-delivery-date' ), 'primary', 'save', true );
	}

	/**
	 * Callback for adding Add settings link on the Custom Delivery page
	 *
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_admin_settings_callback() {
		echo '<a href=\'admin.php?page=order_delivery_date&action=shipping_based\' class=\'view_shipping_setting\'>' . esc_html__( 'View Settings', 'order-delivery-date' ) . '</a>';
	}

	/**
	 * Callback for adding Shipping based on setting
	 *
	 * @since 2.8.3
	 */
	public static function orddd_custom_delivery_type_callback() {
		global $woocommerce, $wpdb;
		$shipping_methods_stored   = array();
		$product_categories_stored = array();
		$shipping_method_str       = 'checked';
		$row_id                    = '';
		$option_key                = '';
		$product_categories_str    = '';

		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['shipping_methods'] ) ) {
					$shipping_methods_stored = $shipping_methods_arr['shipping_methods'];
				}
				if ( isset( $shipping_methods_arr['product_categories'] ) ) {
					$product_categories_stored = $shipping_methods_arr['product_categories'];
				}

				if ( isset( $shipping_methods_arr['delivery_settings_based_on'][0] ) && 'shipping_methods' === $shipping_methods_arr['delivery_settings_based_on'][0] ) {
					$shipping_method_str    = 'checked';
					$product_categories_str = '';
				} elseif ( isset( $shipping_methods_arr['delivery_settings_based_on'][0] ) && 'product_categories' === $shipping_methods_arr['delivery_settings_based_on'][0] ) {
					$shipping_method_str    = '';
					$product_categories_str = 'checked';
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		$shipping_methods_to_display = self::orddd_get_shipping_methods();

		// Fetch Product Categories.
		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => 0,
		);

		$all_categories = get_categories( $args );

		?>
		<p>
			<label>
				<input type="radio" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[delivery_settings_based_on][]" value="shipping_methods" id="orddd_delivery_settings_type" class="input-radio" <?php echo esc_attr( $shipping_method_str ); ?>/><?php esc_html_e( 'Shipping methods', 'order-delivery-date' ); ?>
			</label>
		</p>
		<div class="delivery_type_options delivery_type_shipping_methods">
			<select class="orddd_shipping_methods" id="orddd_shipping_methods" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[shipping_methods][]" multiple="multiple" placeholder="<?php esc_html_e( 'Choose Shipping methods', 'order-delivery-date' ); ?>">
				<?php

				foreach ( $shipping_methods_to_display as $sk => $sv ) {
					if ( in_array( $sv['method_key'], $shipping_methods_stored ) ) {
						// phpcs:ignore
						echo '<option value="' . esc_attr( $sv['method_key'] ) . '" selected>' . esc_html__( $sv['title'], 'order-delivery-date' ) . '</option>';
					} else {
						// phpcs:ignore
						echo '<option value="' . esc_attr( $sv['method_key'] ) . '">' . esc_html__( $sv['title'], 'order-delivery-date' ) . '</option>';
					}
				}

				?>
			</select>
		</div>      
		<p>
			<label>
				<input type="radio" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[delivery_settings_based_on][]" value="product_categories" id="orddd_delivery_settings_type" class="input-radio" <?php echo esc_attr( $product_categories_str ); ?>/><?php esc_html_e( 'Product categories', 'order-delivery-date' ); ?>
			</label>
		</p>
		<div class="delivery_type_options delivery_type_product_categories">          
			<select class="orddd_shipping_methods" id="orddd_shipping_methods" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[product_categories][]" multiple="multiple" placeholder="<?php esc_html_e( 'Choose Product categories', 'order-delivery-date' ); ?>">
				<?php
				foreach ( $all_categories as $key => $value ) {
					$delivery_date = get_term_meta( $value->term_id, 'orddd_delivery_date_for_product_category', true );
					if ( 'on' === $delivery_date ) {
						if ( in_array( $value->slug, $product_categories_stored, true ) ) {
							// phpcs:ignore
							echo '<option value="' . esc_attr( $value->slug ) . '" selected>' . esc_html__( $value->name, 'order-delivery-date' ) . '</option>';
						} else {
							// phpcs:ignore
							echo '<option value="' . esc_attr( $value->slug ) . '">' . esc_html__( $value->name, 'order-delivery-date' ) . '</option>';
						}
					}
				}
				?>
			</select>
			<br>
			<small><?php esc_html_e( 'Delivery settings created for the above product categories will be applied to the translated categories in other languages as well.', 'order-delivery-date' ); ?></small>
		</div>
		<input type="hidden" name="is_shipping_based_page" id="is_shipping_based_page" value="yes"/>
		<?php
		do_action( 'orddd_after_custom_product_categories', $option_key );
	}

	/**
	 * Callback for adding custom date settings
	 */
	public static function orddd_shipping_based_date_settings_callback() {
		if ( isset( $_GET['row_id'] ) ) {
			$row_id               = $_GET['row_id'];
			$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );

			if ( ! isset( $shipping_methods_arr['enable_shipping_based_delivery'] ) && get_option( 'orddd_shipping_settings_status_' . $row_id ) ) {
				delete_option( 'orddd_shipping_settings_status_' . $row_id );
			}
		}
	}

	/**
	 * Callback for adding custom Enable delivery date checkbox setting
	 *
	 * @since 2.8.3
	 */
	public static function orddd_shipping_methods_for_product_categories_callback() {
		$row_id                                 = '';
		$edit                                   = '';
		$shipping_methods_for_categories_stored = array();
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['shipping_methods_for_categories'] ) ) {
					$shipping_methods_for_categories_stored = $shipping_methods_arr['shipping_methods_for_categories'];
				}
			}
		}

		$option_key                  = orddd_common::get_shipping_setting_option_key( $row_id );
		$shipping_methods_to_display = self::orddd_get_shipping_methods();
		?>
		<select class="orddd_shipping_methods_for_categories" id="orddd_shipping_methods_for_categories" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[shipping_methods_for_categories][]" multiple="multiple" placeholder="<?php esc_html_e( 'Choose Shipping methods', 'order-delivery-date' ); ?>">
			<?php
			foreach ( $shipping_methods_to_display as $sk => $sv ) {
				if ( in_array( $sv['method_key'], $shipping_methods_for_categories_stored, true ) ) {
					// phpcs:ignore
					echo '<option value="' . esc_attr( $sv['method_key'] ) . '" selected>' . esc_attr__( $sv['title'], 'order-delivery-date' ) . '</option>';
				} else {
					// phpcs:ignore
					echo '<option value="' . esc_attr( $sv['method_key'] ) . '">' . esc_attr__( $sv['title'], 'order-delivery-date' ) . '</option>';
				}
			}
			?>
		</select>
		<script type='text/javascript'>
			jQuery( document ).ready( function(){
				jQuery( '.orddd_shipping_methods_for_categories' ).select2();
			});
		</script>
		<?php
	}

	/**
	 * Callback for adding settings to selected weekdays
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_enable_shipping_based_delivery_date_callback( $args ) {
		$enable_shipping_based_delivery_settings = 'checked';
		$weekday_delivery                        = 'checked';
		$row_id                                  = '';
		$edit                                    = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$edit                 = 'yes';
				$weekday_delivery     = '';
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['enable_shipping_based_delivery'] ) && 'on' === $shipping_methods_arr['enable_shipping_based_delivery'] ) {
					$enable_shipping_based_delivery_settings = 'checked';
				} else {
					$enable_shipping_based_delivery_settings = '';
				}
				if ( isset( $shipping_methods_arr['delivery_type'] ) ) {
					$delivery_type = $shipping_methods_arr['delivery_type'];
					if ( isset( $delivery_type['weekdays'] ) && 'on' === $delivery_type['weekdays'] ) {
						$weekday_delivery = 'checked';
					}
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		print( '<script type="text/javascript">
            jQuery( document ).ready( function() {
                var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
                var checked = "' . esc_attr( $weekday_delivery ) . '";
                var isEdit = "' . esc_attr( $edit ) . '";
                if( isChecked == true ) {
                   i = 0;
                   jQuery(".form-table").each( function() {
                        if( i == 1 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            jQuery.each( row , function() {
                                jQuery( this ).fadeIn();
                                k++;
                            });
                        } else {
                            jQuery( this ).fadeIn();
                        } 
                        i++;
                    } ); 
                    jQuery( "h2" ).show();
                    if( isEdit == "yes" ) {
                        if( checked == "checked" ) {
                            jQuery( "#orddd_shipping_delivery_type_weekdays" ).attr( "checked", "checked" );
                        } else {
                            jQuery( "#orddd_shipping_delivery_type_weekdays" ).removeAttr( "checked" );
                        }
                    } else {
                        jQuery( "#orddd_shipping_delivery_type_weekdays" ).attr( "checked", "checked" );
                    }
                } else {
                    i = 0;
                    jQuery(".form-table").each( function() {
                        if( i == 0 ) {
                            // the field needs to be shown so we do nothing
                        } else if( i == 1 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            jQuery.each( row , function() {
                                if( k == 1 || k == 0 ) {
                                    // the field needs to be shown so we do nothing
                                } else {
                                    jQuery( this ).fadeOut();
                                }
                                k++;
                            });
                        } else {
                            jQuery( this ).fadeOut();
                        }
                        i++;
                    });
                        
                    j = 0;
                    jQuery( "h2" ).each( function() {
                        if( j == 0 || j == 1 || j == 2 ) {
                            // the field needs to be shown so we do nothing
                        } else {
                            jQuery( this ).fadeOut();
                        }
                        j++;
                    } );
                    if( isEdit == "yes" ) {
                        if( checked == "checked" ) {
                            jQuery( "#orddd_shipping_delivery_type_weekdays" ).attr( "checked", "checked" );
                        } else {
                            jQuery( "#orddd_shipping_delivery_type_weekdays" ).removeAttr( "checked" );
                        }
                    } else {
                        jQuery( "#orddd_shipping_delivery_type_weekdays" ).removeAttr( "checked" );
                    }
                }
            
                jQuery( "#orddd_enable_shipping_based_delivery_date" ).change( function() {
                    var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
                    if( isChecked == true ) {
                        i = 0;
                        jQuery(".form-table").each( function() {
                            if( i == 1 ) {
                                k = 0;
                                var row = jQuery( this ).find( "tr" );
                                jQuery.each( row , function() {
                                   jQuery( this ).fadeIn();
                                   k++ 
                                });
                            } else {
                                jQuery( this ).fadeIn();
                            } 
                            i++;
                        } ); 
                        jQuery( "h2" ).show();
                        if( isEdit == "yes" ) {
                            if( checked == "checked" ) {
                                jQuery( "#orddd_shipping_delivery_type_weekdays" ).attr( "checked", "checked" );
                            } else {
                                jQuery( "#orddd_shipping_delivery_type_weekdays" ).removeAttr( "checked" );
                            }
                        } else {
                            jQuery( "#orddd_shipping_delivery_type_weekdays" ).attr( "checked", "checked" );
                        }
                    } else {
                       i = 0;
                        jQuery(".form-table").each( function() {
                            if( i == 0 ) {
                                // the field needs to be shown so we do nothing
                            } else if( i == 1 ) {
                                k = 0;
                                var row = jQuery( this ).find( "tr" );
                                jQuery.each( row , function() {
                                   if( k == 1 || k == 0 ) {
                                       // the field needs to be shown so we do nothing
                                   } else {
                                        jQuery( this ).fadeOut();
                                   }
                                   k++ 
                                });
                            } else {
                                jQuery( this ).fadeOut();
                            }
                            i++;
                        });
                        
                        j = 0;
                        jQuery( "h2" ).each( function() {
                            if( j == 0 || j == 1 || j == 2 ) {
                            } else {
                                jQuery( this ).fadeOut();
                            }
                            j++;
                        });
            
                        if( isEdit == "yes" ) {
                            if( checked == "checked" ) {
                                jQuery( "#orddd_shipping_delivery_type_weekdays" ).attr( "checked", "checked" );
                            } else {
                                jQuery( "#orddd_shipping_delivery_type_weekdays" ).removeAttr( "checked" );
                            }
                        } else {
                            jQuery( "#orddd_shipping_delivery_type_weekdays" ).removeAttr( "checked" );
                        }
                    }
                })
            });
        </script>' );
		?>
		<input type="checkbox" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[enable_shipping_based_delivery]" id="orddd_enable_shipping_based_delivery_date" class="day-checkbox" value="on" <?php echo esc_attr( $enable_shipping_based_delivery_settings ); ?> />
		<label for="orddd_enable_delivery_date"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding custom delivery checkout options
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_delivery_checkout_options_callback( $args ) {
		$orddd_delivery_checkout_options_delivery_calendar = 'checked';
		$orddd_delivery_checkout_options_text_block        = '';
		$row_id = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['orddd_delivery_checkout_options'] ) && 'text_block' === $shipping_methods_arr['orddd_delivery_checkout_options'] ) {
					$orddd_delivery_checkout_options_text_block        = 'checked';
					$orddd_delivery_checkout_options_delivery_calendar = '';
				} elseif ( isset( $shipping_methods_arr['orddd_delivery_checkout_options'] ) && 'delivery_calendar' === $shipping_methods_arr['orddd_delivery_checkout_options'] ) {
					$orddd_delivery_checkout_options_delivery_calendar = 'checked';
					$orddd_delivery_checkout_options_text_block        = '';
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<p><label><input type="radio" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[orddd_delivery_checkout_options]" id="orddd_delivery_checkout_options" value="delivery_calendar"' . esc_attr( $orddd_delivery_checkout_options_delivery_calendar ) . '/>' . esc_html__( 'Calendar', 'order-delivery-date' ) . '</label>
        <label><input type="radio" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[orddd_delivery_checkout_options]" id="orddd_delivery_checkout_options" value="text_block"' . esc_attr( $orddd_delivery_checkout_options_text_block ) . '/>' . esc_html__( 'Text block', 'order-delivery-date' ) . '</label></p>';
		?>
		<label for="orddd_enable_shipping_based_delivery_date"><?php echo wp_kses_post( $args[0] ); ?></label>
		<script type='text/javascript'>
			jQuery( document ).ready( function(){
				if ( jQuery( "input[type=radio][id=\"orddd_delivery_checkout_options\"][value=\"delivery_calendar\"]" ).is(":checked") ) {
					var i = 0;
					var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
					jQuery( ".form-table"  ).each( function() {
						if( i == 0 ) {
						} else if( i == 1 ) {
							var k = 0;
							var row = jQuery( this ).find( "tr" );
							jQuery.each( row , function() {
								if( k == ( row.length - 1 ) ) {
									jQuery( this ).fadeOut();
								} else {
									if( isChecked == true ) {
										jQuery( this ).fadeIn();    
									}
								}
								k++;
							});
						} else {
							if( isChecked == true ) {
								jQuery( this ).fadeIn();
							}
						} 
						i++;
					}); 
					var j = 0;
					jQuery( "h2" ).each( function() {
						if( isChecked == true ) {
							jQuery( this ).fadeIn();
						}
						j++;
					}); 
				} else if ( jQuery( "input[type=radio][id=\"orddd_delivery_checkout_options\"][value=\"text_block\"]" ).is(":checked") ) {
					var i = 0;
					jQuery( ".form-table" ).each( function() {
						if( i == 0 ) {
						} else if( i == 1 ) {
							var k = 0;
							var row = jQuery( this ).find( "tr" );
							jQuery.each( row , function() {
								if( k == 0 || k == 1 || k == 2 || k == ( row.length - 1 ) || k == ( row.length - 5 ) ) {
									// the field needs to be shown so we do nothing
									if( isChecked == true ) {
										jQuery( this ).fadeIn();    
									}
								} else {
									jQuery( this ).fadeOut();
								}
								k++;
							});
						} else {
							jQuery( this ).fadeOut();
						}
						i++;
					});

					var j = 0;  
					jQuery( "h2" ).each( function() {
						if( j == 0 || j == 1 || j == 2 ) {
						} else {
							jQuery( this ).fadeOut();
						}
						j++;
					});
				}

				jQuery( "input[type=radio][id=\"orddd_delivery_checkout_options\"]" ).on( 'change', function() {
					if ( jQuery( this ).is(':checked') ) {
						var value = jQuery( this ).val();
						if( value == 'delivery_calendar' ) {
							var i = 0;
							jQuery( ".form-table"  ).each( function() {
								if( i == 0 ) {
								} else if( i == 1 ) {
									var k = 0;
									var row = jQuery( this ).find( "tr" );
									jQuery.each( row , function() {
										if( k == ( row.length - 1 ) ) {
											jQuery( this ).fadeOut();
										} else {
											if( isChecked == true ) {
												jQuery( this ).fadeIn();    
											}
										}
										k++;
									});
								} else {
									if( isChecked == true ) {
										jQuery( this ).fadeIn();
									}
								} 
								i++;
							}); 
							var j = 0;
							jQuery( "h2" ).each( function() {
								if( isChecked == true ) {
									jQuery( this ).fadeIn();
								}
								j++;
							});
						} else if( value == 'text_block' ) {
							var i = 0;
							jQuery( ".form-table" ).each( function() {
								if( i == 0 ) {
								} else if( i == 1 ) {
									var k = 0;
									var row = jQuery( this ).find( "tr" );
									jQuery.each( row , function() {
										if( k == 0 || k == 1 || k == 2 || k == ( row.length - 1 ) || k == ( row.length - 5 ) ) {
											if( isChecked == true ) {
												jQuery( this ).fadeIn();    
											}
											// the field needs to be shown so we do nothing
										} else {
											jQuery( this ).fadeOut();
										}
										k++;
									});
								} else {
									jQuery( this ).fadeOut();
								}
								i++;
							});

							var j = 0;  
							jQuery( "h2" ).each( function() {
								if( j == 0 || j == 1 || j == 2 ) {
								} else {
									jQuery( this ).fadeOut();
								}
								j++;
							});
						}
					}
				} );
			});
		</script>
		<?php
	}

	/** Callback for adding between days range for textblock
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_text_block_between_days_callback( $args ) {
		$orddd_min_between_days = 1;
		$orddd_max_between_days = 3;

		$row_id = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['orddd_min_between_days'] ) && '' !== $shipping_methods_arr['orddd_min_between_days'] ) {
					$orddd_min_between_days = $shipping_methods_arr['orddd_min_between_days'];
				}
				if ( isset( $shipping_methods_arr['orddd_max_between_days'] ) && '' !== $shipping_methods_arr['orddd_max_between_days'] ) {
					$orddd_max_between_days = $shipping_methods_arr['orddd_max_between_days'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		echo '<label for="orddd_text_block_between_days">Between 
            <input id="orddd_min_between_days" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[orddd_min_between_days]" type="number" value="' . esc_attr( $orddd_min_between_days ) . '" style="width:50px;" min="1" step="1"> 
            and 
            <input id="orddd_max_between_days" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[orddd_max_between_days]" type="number" value="' . esc_attr( $orddd_max_between_days ) . '" style="width:50px;" min="1" step="1"> 
            days.
        </label>';
		?>
		<label for="orddd_text_block_between_days"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}
	/**
	 * Callback for adding custom delivery type setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_delivery_type_callback( $args ) {
		$weekday_delivery       = 'checked';
		$specific_date_delivery = '';
		$row_id                 = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['delivery_type'] ) ) {
					$delivery_type    = $shipping_methods_arr['delivery_type'];
					$weekday_delivery = '';
					if ( isset( $delivery_type['weekdays'] ) && 'on' === $delivery_type['weekdays'] ) {
						$weekday_delivery = 'checked';
					}

					if ( isset( $delivery_type['specific_dates'] ) && 'on' === $delivery_type['specific_dates'] ) {
						$specific_date_delivery = 'checked';
					}
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		echo '<input type="checkbox" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[delivery_type][weekdays]" id="orddd_shipping_delivery_type_weekdays" ' . esc_attr( $weekday_delivery ) . '/><div class="orddd_shipping_weekdays">' . esc_html__( 'Weekdays', 'order-delivery-date' ) . '</div>';
		echo '<input type="checkbox" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[delivery_type][specific_dates]" id="orddd_shipping_delivery_type_specific_days" ' . esc_attr( $specific_date_delivery ) . '/><div class="orddd_shipping_specific_dates">' . esc_html__( 'Specific Delivery Dates', 'order-delivery-date' ) . '</div>';

		echo '<label for="orddd_shipping_delivery_type">' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom Weekdays setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_weekdays_callback( $args ) {
		global $orddd_weekdays;
		$enable_weekdays        = array();
		$enabled_weekday        = 'checked';
		$additional_charges     = '';
		$row_id                 = '';
		$delivery_charges_label = '';
		$currency_symbol        = get_woocommerce_currency_symbol();
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['weekdays'] ) ) {
					$weekdays_settings = $shipping_methods_arr['weekdays'];
					foreach ( $orddd_weekdays as $wk => $wv ) {
						$weekday = $weekdays_settings[ $wk ];
						if ( isset( $weekday['enable'] ) && 'checked' === $weekday['enable'] ) {
							$enable_weekdays[]                  = $wk;
							$weekdays_additional_charges[ $wk ] = $weekday['additional_charges'];
							if ( isset( $weekday['delivery_charges_label'] ) ) {
								$weekdays_delivery_charges_label[ $wk ] = $weekday['delivery_charges_label'];
							} else {
								$weekdays_delivery_charges_label[ $wk ] = '';
							}
						}
					}
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		print( '<script type="text/javascript">
            jQuery( document ).ready( function() {
                var isChecked = jQuery( "#orddd_shipping_delivery_type_weekdays"  ).is( ":checked" );
                if( isChecked == true ) {
                    jQuery( "#weekdays_fieldset" ).removeAttr( "disabled" );  
                    jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).removeAttr("disabled");               
                } else {
                    jQuery( "#weekdays_fieldset" ).attr( "disabled", "disabled" );  
                    jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).attr( "disabled", "disabled" );              
               }
                jQuery("#orddd_shipping_delivery_type_weekdays").change(function() {
                    var isChecked = jQuery("#orddd_shipping_delivery_type_weekdays").is(":checked");
                    if( isChecked == true ) {
                        jQuery("#weekdays_fieldset").removeAttr("disabled");  
                        jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).removeAttr("disabled"); 
                    } else {
                        jQuery("#weekdays_fieldset").attr("disabled","disabled");
                        jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).attr( "disabled", "disabled" );              
                    }
                })
            });
        </script>' );
		echo '<fieldset class="days-fieldset" id="weekdays_fieldset" disabled="disabled">
        	<legend><b>' . esc_html__( 'Weekdays & Additional Charges:', 'order-delivery-date' ) . '</b></legend>
                <table id="orddd_weekdays_table">';
				echo '<tr>
                        <th class="orddd_custom_fieldset_padding"></th>
                        <th class="orddd_custom_fieldset_padding">' . esc_html__( 'Enable', 'order-delivery-date' ) . '</th>
                        <th class="orddd_custom_fieldset_padding"></th>
                        <th class="orddd_custom_fieldset_padding">' . esc_html__( 'Charges', 'order-delivery-date' ) . '</th>
                        <th class="orddd_custom_fieldset_label">' . esc_html__( 'Checkout page Label', 'order-delivery-date' ) . '</th>
                </tr>';
		foreach ( $orddd_weekdays as $n => $day_name ) {
			if ( in_array( $n, $enable_weekdays, true ) ) {
				$enabled_weekday        = 'checked';
				$additional_charges     = $weekdays_additional_charges[ $n ];
				$delivery_charges_label = $weekdays_delivery_charges_label[ $n ];

			} elseif ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
				$enabled_weekday        = '';
				$additional_charges     = '';
				$delivery_charges_label = '';
			}
			echo '<tr>
						<td class="orddd_custom_fieldset_padding"><input type="checkbox" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[weekdays][' . esc_attr( $n ) . '][enable]" id="' . esc_attr( $n ) . '_custom_setting" value="checked" ' . esc_attr( $enabled_weekday ) . '/></td>';
			// phpcs:ignore						
			echo '<td class="orddd_custom_fieldset_padding"><label class="ord_label" for="' . esc_attr( $day_name ) . '">' . esc_html__( $day_name, 'order-delivery-date' ) . '</label></td>
                        <td class="orddd_custom_fieldset_padding">' . esc_attr( $currency_symbol ) . '</td>
                	    <td class="orddd_custom_fieldset_padding"><input type="text" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[weekdays][' . esc_attr( $n ) . '][additional_charges] id="additional_charges_' . esc_attr( $n ) . '"  class="orddd_custom_additional_charges" value="' . esc_attr( $additional_charges ) . '"/></td>
                	    <td class="orddd_custom_fieldset_padding"><input type="text" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[weekdays][' . esc_attr( $n ) . '][delivery_charges_label] id="delivery_charges_label_' . esc_attr( $n ) . '"  class="orddd_custom_charges_label" value="' . esc_attr( $delivery_charges_label ) . '"/></td>    
                    </tr>';
		}
				echo '</table>
            </fieldset>';
			echo '<script type="text/javascript">
            jQuery( document ).ready( function() {';
		for ( $i = 0; $i <= 6; $i++ ) {
			echo 'var isChecked = jQuery( "#orddd_weekday_' . esc_attr( $i ) . '_custom_setting" ).is(":checked");
                    if( isChecked == true ) {
                        jQuery( "#orddd_shipping_based_time_slot_for_weekdays" ).append( jQuery("<option></option>").val( "orddd_weekday_' . esc_attr( $i ) . '_custom_setting" ).html("' . esc_attr( $orddd_weekdays[ 'orddd_weekday_' . $i ] ) . '" ) );    
                    }';
		}
				echo 'jQuery("#orddd_weekdays_table :checkbox").on( "change", function () {
                    var isChecked = jQuery( "#" + this.id ).is(":checked");
                    if( isChecked == false ) {
                        jQuery( "#orddd_shipping_based_time_slot_for_weekdays option[value=" + this.id + "]").remove();    
                    } else {
                        var rowCount = jQuery( "#orddd_shipping_based_time_slot_for_weekdays option[value=" + this.id + "]" ).length;
                        if( rowCount == 0 ) {';
						for ( $i = 0; $i <= 6; $i++ ) {
							echo 'if( this.id == "orddd_weekday_' . esc_attr( $i ) . '_custom_setting" ) {';
							if ( 6 === $i ) {
												echo 'jQuery( "#orddd_shipping_based_time_slot_for_weekdays option" ).eq( ' . esc_attr( $i ) . ' ).after( jQuery("<option></option>").val( this.id ).html("' . esc_attr( $orddd_weekdays[ 'orddd_weekday_' . $i ] ) . '") );';
							} else {
													echo 'jQuery( "#orddd_shipping_based_time_slot_for_weekdays option" ).eq( ' . esc_attr( $i ) . ' + 1 ).before( jQuery("<option></option>").val( this.id ).html("' . esc_attr( $orddd_weekdays[ 'orddd_weekday_' . $i ] ) . '") );';
							}
																	echo '}';
						}
						echo '}
                    }
                });
            });
			</script>';
		?>
		<label for="orddd_delivery_days"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding custom delivery dates setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_specific_days_callback( $args ) {
		$currency_symbol = get_woocommerce_currency_symbol();
		$day_selected    = get_option( 'start_of_week' );

		print( '<script type="text/javascript">
	       jQuery( document ).ready( function() {
                jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "en-GB" ] );
                jQuery( "#orddd_delivery_date" ).width( "100px" );
                var formats = [ "mm-dd-yy", "d.m.y", "d M, yy","MM d, yy" ];
                jQuery( "#orddd_delivery_date" ).val( "" ).datepicker( {constrainInput: true, dateFormat: formats[0],minDate: new Date(), firstDay:' . esc_attr( $day_selected ) . ' })
                var isChecked = jQuery( "#orddd_shipping_delivery_type_specific_days" ).is( ":checked" );
                if( isChecked == true ) {
                    jQuery( "#orddd_delivery_date" ).removeAttr( "disabled" );  
                    jQuery( "#save_specific_date" ).removeAttr( "disabled" );
                    jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).removeAttr( "disabled" );
                    var isWeekdayChecked = jQuery("#orddd_shipping_delivery_type_weekdays").is(":checked");
                    if( isWeekdayChecked == false ) {
                        jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "checked", true);
                    }
                } else {
                    jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );              
                }
                jQuery( "#orddd_shipping_delivery_type_specific_days" ).change( function() {
                    var isChecked = jQuery( "#orddd_shipping_delivery_type_specific_days" ).is( ":checked" );
                    if( isChecked  == true ) {
                        jQuery( "#orddd_delivery_date" ).removeAttr( "disabled" );  
                        jQuery( "#save_specific_date" ).removeAttr( "disabled" );
                        jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );              
                    } else {
                        jQuery( "#orddd_delivery_date" ).attr( "disabled", "disabled" );  
                        jQuery( "#save_specific_date" ).attr( "disabled", "disabled" );  
                        jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );              
                    }
                });
                jQuery( "table#orddd_specific_date_list" ).on( "click", "a.confirmation_specific_date", function() {
                    var specific_dates_hidden = jQuery( "#orddd_specific_date_hidden" ).val();
                    var split_array = specific_dates_hidden.split( "," );
                    var specific_date = jQuery( "table#orddd_specific_date_list tr#" + this.id + " td#orddd_specific_date" ).html();
                    var split_date = specific_date.split( "-" );
	                var dt = new Date ( split_date[ 0 ] + "/" + split_date[ 1 ] + "/" + split_date[ 2 ] );
                    var date = ( dt.getMonth() + 1 ) + "-" + dt.getDate() + "-" + dt.getFullYear();
                    var updatedString = "";
                    for( i=0; i < ( split_array.length - 1 ); i++ ) {
                        if( split_array[i].indexOf( date ) == -1 ) {
                            updatedString = updatedString + split_array[i] + ",";
                        }
                    }
                    jQuery( "#orddd_specific_date_hidden" ).val( updatedString );
                    jQuery( "table#orddd_specific_date_list tr#"+ this.id ).remove();
                    var rowCount = jQuery( "#orddd_specific_date_list tr" ).length;
                    if( rowCount == 1 ) {
                        jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );              
                    } else {
                        jQuery( "#orddd_shipping_based_select_delivery_dates option[value=" + date + "]").remove(); 
                    }
                });
            
            jQuery( "#save_specific_date" ).click(function() {
                var row_arr = [];
                var specific_dates = [];
                if( jQuery( "#orddd_delivery_date" ).val() != "" ) {
                    var split = jQuery( "#orddd_delivery_date" ).val().split( "-" );
                    split[0] = split[0] - 1;
					var dt = new Date( split[2], split[0], split[1] );
                    var date = ( dt.getMonth() + 1 ) + "-" + dt.getDate() + "-" + dt.getFullYear();
                    var row = jQuery( "#orddd_specific_date_hidden" ).val();
                    if( row != "" ) {
                        row_arr = row.split(",");
                        for( i = 0; i < row_arr.length; i++ ) {
                            if( row_arr[ i ] != "" ) {
                                var string = row_arr[ i ].replace( "{", "" );
                                string = string.replace( "}", "" );
                                var string_arr = string.split( ":" );
                                specific_dates.push( string_arr[ 0 ] );
                            }
                        }
                    }
                    if( jQuery.inArray( date, specific_dates ) == -1 ) {
                        var rowCount = jQuery( "#orddd_specific_date_list tr" ).length;
                        if( rowCount == 0 ) {
                            jQuery( "#orddd_specific_date_list" ).append( "<tr class=\"orddd_common_list_tr\"><th class=\"orddd_specific_date_list\">' . esc_html__( 'Delivery Dates', 'order-delivery-date' ) . '</th><th class=\"orddd_specific_date_list\">' . esc_html__( 'Additional Charges', 'order-delivery-date' ) . '</th><th class=\"orddd_specific_date_list\">' . esc_html__( 'Checkout page Label', 'order-delivery-date' ) . '</th><th class=\"orddd_specific_date_list\">' . esc_html__( 'Actions', 'order-delivery-date' ) . '</th></tr>" );
                            jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );              
                            var rowCount = 1;
                        }
                        if( rowCount >= 1 ) {
                            jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).removeAttr( "disabled" );
                        }
                        rowCount = rowCount - 1;
                        if( jQuery("#additional_charges").val() != "" ) {
                            jQuery( "#orddd_specific_date_list tr:last" ).after( "<tr class=\"orddd_common_list_tr\" id=\"orddd_delete_specific_dates_" + rowCount + "\"><td class=\"orddd_specific_date_list\" id=\"orddd_specific_date\">" + jQuery( "#orddd_delivery_date" ).val() + "</td><td class=\"orddd_specific_date_list\" id=\"orddd_additional_charges\">' . esc_attr( $currency_symbol ) . '" + jQuery("#additional_charges").val() + "</td><td class=\"orddd_specific_date_list\" id=\"orddd_specific_charges_label\">" + jQuery("#specific_charges_label").val() + "</td><td class=\"orddd_specific_date_list\" id=\"orddd_max_orders_specific\">" + jQuery( "#orddd_max_orders_specific" ).val() + "</td><td class=\"orddd_specific_date_list\"><a href=\"javascript:void(0)\" class=\"confirmation_specific_date\" id=\"orddd_delete_specific_dates_" + rowCount + "\">' . esc_html__( 'Delete', 'order-delivery-date' ) . '</a></td></tr>" );
                        } else {
                            jQuery( "#orddd_specific_date_list tr:last" ).after( "<tr class=\"orddd_common_list_tr\" id=\"orddd_delete_specific_dates_" + rowCount + "\"><td class=\"orddd_specific_date_list\" id=\"orddd_specific_date\">" + jQuery( "#orddd_delivery_date" ).val() + "</td><td class=\"orddd_specific_date_list\" id=\"orddd_additional_charges\"></td><td class=\"orddd_specific_date_list\" id=\"orddd_specific_charges_label\">" + jQuery("#specific_charges_label").val() + "</td><td class=\"orddd_specific_date_list\" id=\"orddd_max_orders_specific\">"+jQuery( "#orddd_max_orders_specific" ).val()+"</td><td class=\"orddd_specific_date_list\"><a href=\"javascript:void(0)\" class=\"confirmation_specific_date\" id=\"orddd_delete_specific_dates_" + rowCount + "\">' . esc_html__( 'Delete', 'order-delivery-date' ) . '</a></td></tr>" );
                        }
                    
                        row += "{" + date + ":" + jQuery( "#additional_charges" ).val() + ":" + jQuery( "#specific_charges_label" ).val() + ":" + jQuery( "#orddd_max_orders_specific" ).val() + "},";
                        jQuery( "#orddd_specific_date_hidden" ).val( row );
                        jQuery( "#orddd_shipping_based_select_delivery_dates" ).append( jQuery("<option></option>").val(date).html( jQuery( "#orddd_delivery_date" ).val() ) );
                    }
                    jQuery( "#orddd_delivery_date" ).datepicker( "setDate", "" );
                    jQuery( "#additional_charges" ).val( "" );
					jQuery( "#specific_charges_label" ).val( "" );
					jQuery( "#orddd_max_orders_specific" ).val("");
                }
            });
        });
	    </script>' );

		echo '<input type="text" name="orddd_delivery_date" id="orddd_delivery_date" class="day-checkbox" disabled="disabled" placeholder="Select Date"/>
		    ' . esc_attr( $currency_symbol ) . '<input class="orddd_custom_specific_charges" type="text" name="additional_charges" id="additional_charges" disabled="disabled" placeholder="Charges"/>
            <input class="orddd_specific_charges_label" type="text" name="specific_charges_label" id="specific_charges_label" disabled="disabled" placeholder="Delivery Charges Label"/>&nbsp;
            <input class="orddd_max_orders_specific" type="number" min="0" step="1" name="orddd_max_orders_specific" id="orddd_max_orders_specific" placeholder="Maximum Orders" />&nbsp;
            <input type="button" value="' . esc_html__( 'Save', 'order-delivery-date' ) . '" id="save_specific_date" class="save_button" disabled="disabled">';

		echo '<label for="orddd_shipping_based_specific_day">' . wp_kses_post( $args[0] ) . '</label>';

		$specific_date_hidden_str = '';
		$row_id                   = '';
		echo '<table id="orddd_specific_date_list">';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['specific_dates'] ) ) {
					$specific_days_settings = explode( ',', $shipping_methods_arr['specific_dates'] );
					echo '<tr class="orddd_common_list_tr">
                            <th class="orddd_specific_date_list">' . esc_html__( 'Delivery Dates', 'order-delivery-date' ) . '</th>
                            <th class="orddd_specific_date_list">' . esc_html__( 'Additional Charges', 'order-delivery-date' ) . '</th>
                            <th class="orddd_specific_date_list">' . esc_html__( 'Checkout page Label', 'order-delivery-date' ) . '</th>
                            <th class="orddd_specific_date_list">' . esc_html__( 'Maximum Orders', 'order-delivery-date' ) . '</th>
                            <th class="orddd_specific_date_list">' . esc_html__( 'Actions', 'order-delivery-date' ) . '</th>
                        </tr>';
					$i = 0;
					foreach ( $specific_days_settings as $sk => $sv ) {
						$specific_date_value = str_replace( '}', '', $sv );
						$specific_date_value = str_replace( '{', '', $specific_date_value );
						$specific_date_arr   = explode( ':', $specific_date_value );
						if ( isset( $specific_date_arr[0] ) && '' !== $specific_date_arr[0] ) {
							$date_arr = explode( '-', trim( $specific_date_arr[0] ) );
							$date_str = gmdate( 'm-d-Y', gmmktime( 0, 0, 0, $date_arr[0], $date_arr[1], $date_arr[2] ) );
							echo '<tr class="orddd_common_list_tr" id="orddd_delete_specific_dates_' . esc_attr( $i ) . '">
                                <td class="orddd_specific_date_list" id="orddd_specific_date">' . esc_attr( $date_str ) . '</td>';
							if ( '' !== $specific_date_arr[1] ) {
								echo '<td class="orddd_specific_date_list" id="orddd_additional_charges">' . esc_attr( $currency_symbol ) . '' . esc_attr( $specific_date_arr[1] ) . '</td>';
							} else {
								echo '<td class="orddd_specific_date_list" id="orddd_additional_charges"></td>';
							}
								echo '<td class="orddd_specific_date_list" id="orddd_specific_charges_label">' . esc_attr( $specific_date_arr[2] ) . '</td>
                                <td class="orddd_specific_date_list" id="orddd_max_orders_specific">' . esc_attr( $specific_date_arr[3] ) . '</td>
                                <td class="orddd_specific_date_list"><a href="javascript:void(0)" class="confirmation_specific_date" id="orddd_delete_specific_dates_' . esc_attr( $i ) . '">' . esc_html__( 'Delete', 'order-delivery-date' ) . '</a></td>
                            </tr>';
							$specific_date_hidden_str .= $sv . ',';
						}
						$i++;
					}
				}
			}
		}
		echo '</table>';
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="hidden" id="orddd_specific_date_hidden"  name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[specific_dates]" value="' . esc_attr( $specific_date_hidden_str ) . '"/>';
	}

	/**
	 * Callback for adding custom Minimum delivery time(in hours) setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_minimum_delivery_time_callback( $args ) {
		$minimum_delivery_time = '';
		$row_id                = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['minimum_delivery_time'] ) ) {
					$minimum_delivery_time = $shipping_methods_arr['minimum_delivery_time'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		?>
		<input type="text" name="orddd_shipping_based_settings_<?php echo esc_attr( $option_key ); ?>[minimum_delivery_time]" id="orddd_shipping_based_minimumOrderDays" value="<?php echo esc_attr( $minimum_delivery_time ); ?>"/>
		<label for="orddd_shipping_based_minimumOrderDays"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding custom number of dates to choose setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_number_of_dates_callback( $args ) {
		$number_of_dates = 30;
		$row_id          = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['number_of_dates'] ) ) {
					$number_of_dates = $shipping_methods_arr['number_of_dates'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		echo '<input type="number" min="0" step="1" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[number_of_dates]" id="orddd_shipping_based_number_of_dates" value="' . esc_attr( $number_of_dates ) . '"/>';
		echo '<label for="orddd_shipping_based_number_of_dates"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom delivery date field mandatory setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_date_field_mandatory_callback( $args ) {
		$mandatory_field = '';
		$row_id          = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['date_mandatory_field'] ) ) {
					$mandatory_field = $shipping_methods_arr['date_mandatory_field'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="checkbox" name="orddd_shipping_based_settings_' . $option_key . '[date_mandatory_field]" id="orddd_shipping_based_date_field_mandatory" value="checked"' . esc_attr( $mandatory_field ) . '/>';

		echo '<label for="orddd_shipping_based_date_field_mandatory">' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom Maximum Order Deliveries per day (based on per order) setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_date_lockout_callback( $args ) {
		$lockout = '';
		$row_id  = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['date_lockout'] ) ) {
					$lockout = $shipping_methods_arr['date_lockout'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="number" min="0" step="1" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[date_lockout]" id="orddd_shipping_based_date_lockout" value="' . esc_attr( $lockout ) . '"/>';

		echo '<label for="orddd_shipping_based_date_lockout"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom Time settings
	 */
	public static function orddd_shipping_based_time_settings_callback() { }

	/**
	 * Callback for adding custom Time range setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_time_sliders_callback( $args ) {
		$from_hours = '';
		$to_hours   = '';
		$from_mins  = '';
		$to_mins    = 0;
		$row_id     = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['time_settings'] ) ) {
					$time_settings = $shipping_methods_arr['time_settings'];
					$from_hours    = $time_settings['from_hours'];
					$from_mins     = isset( $time_settings['from_mins'] ) ? $time_settings['from_mins'] : '0';
					$to_hours      = $time_settings['to_hours'];
					$to_mins       = isset( $time_settings['to_mins'] ) ? $time_settings['to_mins'] : '0';
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		$step_min = apply_filters( 'orddd_time_slider_minute_step', 5 );

		echo '<select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[time_settings][from_hours]" id="orddd_delivery_from_hours" size="1">';
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				"<option %s value='%s' >%s</option>\n",
				selected( $i, $from_hours, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
		echo '</select>&nbsp;:&nbsp';
		echo '<select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[time_settings][from_mins]" id="orddd_delivery_from_mins" size="1">';
		// time options.
		for ( $i = 0; $i <= 59; ) {
			printf(
				"<option %s value='%s'>%s</option>\n",
				selected( $i, $from_mins, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
			$i += $step_min;
		}
		echo '</select>&nbsp;-&nbsp';
		echo '<select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[time_settings][to_hours]" id="orddd_delivery_to_hours" size="1">';
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				"<option %s value='%s'>%s</option>\n",
				selected( $i, $to_hours, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
		}

		echo '</select>&nbsp;:&nbsp;';
		echo '<select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[time_settings][to_mins]" id="orddd_delivery_to_mins" size="1">';
		for ( $i = 0; $i <= 59; ) {
			printf(
				"<option %s value='%s'>%s</option>\n",
				selected( $i, $to_mins, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
			$i += $step_min;
		}
		echo '</select>';
		echo '<label for="orddd_shipping_based_time_settings"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom Same day cut-off setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_sameday_cutoff_callback( $args ) {
		$after_hours   = 0;
		$after_minutes = 0;
		$row_id        = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['same_day'] ) ) {
					$same_day      = $shipping_methods_arr['same_day'];
					$after_hours   = $same_day['after_hours'];
					$after_minutes = $same_day['after_minutes'];
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<fieldset>';
		echo '<label for="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[same_day][after_hours]">' . esc_html__( 'Hours:', 'order-delivery-date' ) . '</label><select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[same_day][after_hours]" id="orddd_shipping_based_sameday_after_hours" size="1">';
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				'<option %svalue="%s">%s</option>',
				selected( $i, $after_hours, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
		}

		echo '</select>&nbsp;&nbsp;<label for="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[same_day][after_minutes]">' . esc_html__( 'Mins:', 'order-delivery-date' ) . '</label><select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[same_day][after_minutes]" id="orddd_shipping_based_sameday_after_minutes" size="1">';
		for ( $i = 0; $i <= 59; $i++ ) {
			if ( $i < 10 ) {
				$i = '0' . $i;
			}
			printf(
				'<option %svalue="%s">%s</option>',
				selected( $i, $after_minutes, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
		echo '</select>';
		echo '</fieldset>';
		echo '<label for="orddd_shipping_based_sameday_cutoff"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom next day cut-off setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_nextday_cutoff_callback( $args ) {
		$after_hours   = 0;
		$after_minutes = 0;
		$row_id        = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['next_day'] ) ) {
					$next_day      = $shipping_methods_arr['next_day'];
					$after_hours   = $next_day['after_hours'];
					$after_minutes = $next_day['after_minutes'];
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<fieldset>';

		echo '<label for="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[next_day][after_hours]">' . esc_html__( 'Hours:', 'order-delivery-date' ) . '</label><select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[next_day][after_hours]" id="orddd_shipping_based_nextday_after_hours" size="1">';
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				'<option %svalue="%s">%s</option>',
				selected( $i, $after_hours, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
		}

		echo '</select>&nbsp;&nbsp;<label for="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[next_day][after_minutes]">' . esc_html__( 'Mins:', 'order-delivery-date' ) . '</label><select name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[next_day][after_minutes]" id="orddd_shipping_based_nextday_after_minutes" size="1">';
		for ( $i = 0; $i <= 59; $i++ ) {
			if ( $i < 10 ) {
				$i = '0' . $i;
			}
			printf(
				'<option %svalue="%s">%s</option>',
				selected( $i, $after_minutes, false ),
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
		echo '</select>';
		echo '</fieldset>';

		echo '<label for="orddd_shipping_based_nextday_cutoff"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom same day additional charges setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_same_day_additional_charges_callback( $args ) {
		$additional_charges = $row_id = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['same_day'] ) ) {
					$same_day           = $shipping_methods_arr['same_day'];
					$additional_charges = $same_day['additional_charges'];
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="text" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[same_day][additional_charges]" id="orddd_shipping_based_same_day_additional_charges" value="' . esc_attr( $additional_charges ) . '"/>';
		echo '<label for="orddd_shipping_based_same_day_additional_charges"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom next day additional charges setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_next_day_additional_charges_callback( $args ) {
		$additional_charges = '';
		$row_id             = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['next_day'] ) ) {
					$next_day           = $shipping_methods_arr['next_day'];
					$additional_charges = $next_day['additional_charges'];
				}
			}
		}
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="text" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[next_day][additional_charges]" id="orddd_shipping_based_next_day_additional_charges" value="' . esc_attr( $additional_charges ) . '"/>';

		echo '<label for="orddd_shipping_based_next_day_additional_charges"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom Holidays setting
	 */
	public static function orddd_shipping_based_holidays_callback() { }

	/**
	 * Callback for enabling General Settings Holidays
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_enable_global_holidays_callback( $args ) {
		$enable_global_holidays = '';
		$row_id                 = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['enable_global_holidays'] ) ) {
					$enable_global_holidays = $shipping_methods_arr['enable_global_holidays'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="checkbox" name="orddd_shipping_based_settings_' . esc_attr( $option_key ) . '[enable_global_holidays]" id="orddd_enable_global_holidays" value="checked" ' . esc_attr( $enable_global_holidays ) . '/>';
		echo '<label for="orddd_enable_global_holidays"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom holiday name setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_holiday_name_callback( $args ) {
		echo '<input type="text" name="orddd_shipping_based_holiday_name" id="orddd_shipping_based_holiday_name" class="day-checkbox"/>';
		echo '<label for="orddd_shipping_based_holiday_name"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom holiday from date and to date setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_holiday_from_date_callback( $args ) {
		$day_selected = get_option( 'start_of_week' );

		echo '<input type="text" name="orddd_shipping_based_holiday_from_date" id="orddd_shipping_based_holiday_from_date" class="day-checkbox" placeholder= "' . esc_html__( 'From', 'order-delivery-date' ) . '"/>&nbsp;';
		echo '<input type="text" name="orddd_shipping_based_holiday_to_date" id="orddd_shipping_based_holiday_to_date" class="day-checkbox" placeholder= "' . esc_html__( 'To', 'order-delivery-date' ) . '" />&nbsp;';
		echo '<input type="hidden" value="' . esc_attr( $day_selected ) . '" name="orddd_holiday_start_day">'; // add a hidden value to set the first day of calendar in js.

		echo '<label for="orddd_shipping_based_holiday_from_date"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for adding custom recurring holiday date
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 *
	 * @since 8.0
	 */
	public static function orddd_shipping_based_allow_recurring_holiday_callback( $args ) {
		echo '<input type="checkbox" name="orddd_shipping_based_allow_recurring_holiday" id="orddd_shipping_based_allow_recurring_holiday" class="day-checkbox" />';
		echo '<label for="orddd_shipping_based_allow_recurring_holiday"> ' . wp_kses_post( $args[0] ) . '</label>';
	}

	/**
	 * Callback for displaying the saved holidays in a table
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_holiday_save_callback( $args ) {
		echo '<input type="button" value="' . esc_html__( 'Save', 'order-delivery-date' ) . '" id="save_holidays" class="save_button">';

		$holiday_hidden_str = '';
		$row_id             = '';
		echo '<table id="orddd_holidays_list" class="order-delivery-holiday-list-settings">';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				$holiday_str          = '';
				if ( isset( $shipping_methods_arr['holidays'] ) && '' !== $shipping_methods_arr['holidays'] ) {
					$holiday_settings = explode( ',', $shipping_methods_arr['holidays'] );
					$holiday_str      = '';

					$holidays_view_table = '<tr class="orddd_common_list_tr">
                            <th class="orddd_holidays_list">' . __( 'Name', 'order-delivery-date' ) . '</th>
                            <th class="orddd_holidays_list">' . __( 'Date', 'order-delivery-date' ) . '</th>
                            <th class="orddd_holidays_list">' . __( 'Type', 'order-delivery-date' ) . '</th>
                            <th class="orddd_holidays_list">' . __( 'Actions', 'order-delivery-date' ) . '</th>
                        </tr>';

					$holidays_view_table = apply_filters( 'orddd_shipping_holidays_table', $holidays_view_table );

					echo $holidays_view_table;

					$i = 0;
					foreach ( $holiday_settings as $hk => $hv ) {
						$holiday_value = str_replace( '}', '', $hv );
						$holiday_value = str_replace( '{', '', $holiday_value );
						$holiday_arr   = explode( ':', $holiday_value );
						if ( isset( $holiday_arr[1] ) && '' !== $holiday_arr[1] ) {
							$date_arr = explode( '-', trim( $holiday_arr[1] ) );
							$date_str = gmdate( 'm-d-Y', gmmktime( 0, 0, 0, $date_arr[0], $date_arr[1], $date_arr[2] ) );

							if ( isset( $holiday_arr[2] ) && 'on' === $holiday_arr[2] ) {
								$recurring_type = __( 'Recurring', 'order-delivery-date' );
							} else {
								$recurring_type = __( 'Current Year', 'order-delivery-date' );
							}

							$holidays_table_data = '<tr class="orddd_common_list_tr" id="orddd_delete_holidays_' . $i . '">
                                <td class="orddd_holidays_list" id="orddd_holiday_name">' . trim( $holiday_arr[0] ) . '</td>
                                <td class="orddd_holidays_list" id="orddd_holiday_date">' . $date_str . '</td>
                                <td class="orddd_holidays_list" id="orddd_allow_recurring_type">' . $recurring_type . '</td>
                                <td class="orddd_holidays_list"><a href="javascript:void(0)" class="confirmation_holidays" id="orddd_delete_holidays_' . $i . '">' . __( 'Delete', 'order-delivery-date' ) . '</a></td>
                            </tr>';

							$holidays_table_data = apply_filters(
								'orddd_shipping_holidays_table_data',
								$holidays_table_data,
								$holiday_arr,
								$date_str,
								$recurring_type,
								$i
							);

							echo $holidays_table_data;

							$holiday_hidden_str .= $hv . ',';
						}
						$i++;
					}
				}
			}
		}
		echo '</table>';
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="hidden" id="orddd_holiday_hidden"  name="orddd_shipping_based_settings_' . $option_key . '[holidays]" value="' . $holiday_hidden_str . '"/>';
	}

	/**
	 * Callback for adding custom Time slot settings
	 *
	 * @since 2.8.3
	 */

	public static function orddd_shipping_based_timeslot_callback() { }

	/**
	 * Callback for adding custom time slot from hour and to hour setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */

	public static function orddd_shipping_based_time_from_hours_callback( $args ) {
		echo 'From <select name="orddd_shipping_based_time_from_hours" id="orddd_shipping_based_time_from_hours" size="1">';
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				$i
			);
		}
		echo '</select> : <select name="orddd_shipping_based_time_from_minutes" id="orddd_shipping_based_time_from_minutes" size="1">';
		for ( $i = 0; $i <= 59; $i++ ) {
			if ( $i < 10 ) {
				$i = '0' . $i;
			}
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				$i
			);
		}
		echo '</select>';

		echo '&nbsp;&nbsp;To <select name="orddd_shipping_based_time_to_hours" id="orddd_shipping_based_time_to_hours" size="1">';
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				$i
			);
		}
		echo '</select> : <select name="orddd_shipping_based_time_to_minutes" id="orddd_shipping_based_time_to_minutes" size="1">';
		for ( $i = 0; $i <= 59; $i++ ) {
			if ( $i < 10 ) {
				$i = '0' . $i;
			}
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				$i
			);
		}
		echo '</select>';

		$html = '<label for="orddd_shipping_based_time_from_hours"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for adding custom Maximum Order Deliveries per time slot (based on per order) setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_time_slot_lockout_callback( $args ) {
		echo '<input type="number" min="0" step="1" name="orddd_shipping_based_time_slot_lockout" id="orddd_shipping_based_time_slot_lockout"/>';

		$html = '<label for="orddd_shipping_based_time_slot_lockout"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for adding custom Additional Charges for time slot and Checkout label setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_time_slot_additional_charges_callback( $args ) {
		echo '<input type="text" name="orddd_shipping_based_time_slot_additional_charges" id="orddd_shipping_based_time_slot_additional_charges" placeholder="Charges"/>';
		echo '<input type="text" name="orddd_shipping_based_time_slot_additional_charges_label" id="orddd_shipping_based_time_slot_additional_charges_label" placeholder="Time slot Charges Label"/>';

		$html = '<label for="orddd_shipping_based_time_slot_additional_charges"> ' . $args[0] . '</label>';
		echo $html;
	}
	/**
	 * Callback for adding custom time slot field mandatory setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_timeslot_field_mandatory_callback( $args ) {
		$mandatory_field = '';
		$row_id          = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['timeslot_mandatory_field'] ) ) {
					$mandatory_field = $shipping_methods_arr['timeslot_mandatory_field'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="checkbox" name="orddd_shipping_based_settings_' . $option_key . '[timeslot_mandatory_field]" id="orddd_shipping_based_timeslot_field_mandatory" value="checked" ' . $mandatory_field . '/>';

		$html = '<label for="orddd_shipping_based_timeslot_field_mandatory"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for adding custom as soon as possible option for time slot dropdown on checkout page.
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 7.9
	 */
	public static function orddd_shipping_based_timeslot_field_asap_callback( $args ) {
		$mandatory_field = '';
		$row_id          = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['timeslot_asap_option'] ) ) {
					$mandatory_field = $shipping_methods_arr['timeslot_asap_option'];
				}
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="checkbox" name="orddd_shipping_based_settings_' . $option_key . '[timeslot_asap_option]" id="orddd_shipping_based_timeslot_field_mandatory" value="checked" ' . $mandatory_field . '/>';

		$html = '<label for="orddd_shipping_based_timeslot_field_asap"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for adding the options to select weekdays or specific dates
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_time_slot_for_delivery_days_callback( $args ) {
		global $orddd_weekdays;

		$orddd_shipping_based_time_slot_for_weekdays       = 'checked';
		$orddd_shipping_based_time_slot_for_specific_dates = '';
		if ( 'weekdays' === get_option( 'orddd_shipping_based_time_slot_for_delivery_days' ) ) {
			$orddd_shipping_based_time_slot_for_weekdays       = 'checked';
			$orddd_shipping_based_time_slot_for_specific_dates = '';
		} elseif ( 'specific_dates' === get_option( 'orddd_shipping_based_time_slot_for_delivery_days' ) ) {
			$orddd_shipping_based_time_slot_for_specific_dates = 'checked';
			$orddd_shipping_based_time_slot_for_weekdays       = '';
		}

		?>
		<p><label><input type="radio" name="orddd_shipping_based_time_slot_for_delivery_days" id="orddd_shipping_based_time_slot_for_delivery_days" value="weekdays"<?php echo esc_attr( $orddd_shipping_based_time_slot_for_weekdays ); ?>/><?php esc_html_e( 'Weekdays', 'order-delivery-date' ); ?></label>
		<label><input type="radio" name="orddd_shipping_based_time_slot_for_delivery_days" id="orddd_shipping_based_time_slot_for_delivery_days" value="specific_dates"<?php echo esc_attr( $orddd_shipping_based_time_slot_for_specific_dates ); ?>/><?php esc_html_e( 'Specific Delivery Dates', 'order-delivery-date' ); ?></label></p>
		<label for="orddd_shipping_based_time_slot_for_delivery_days"><?php echo wp_kses_post( $args[0] ); ?></label>
		?>
		<script type='text/javascript'>
			jQuery( document ).ready( function(){
				if ( jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).is(":checked") ) {
					jQuery( '.shipping_based_time_slot_options' ).slideUp();
					jQuery( '.shipping_based_time_slot_for_weekdays' ).slideDown();
				} else {
					jQuery( '.shipping_based_time_slot_options' ).slideDown();
					jQuery( '.shipping_based_time_slot_for_weekdays' ).slideUp();
				}
				jQuery( '.orddd_shipping_based_time_slot_for_weekdays' ).select2();
				jQuery( '.orddd_shipping_based_time_slot_for_weekdays' ).css({'width': '300px' });
				jQuery( "input[type=radio][id=\"orddd_shipping_based_time_slot_for_delivery_days\"]" ).on( 'change', function() {
					if ( jQuery( this ).is(':checked') ) {
						var value = jQuery( this ).val();
						jQuery( '.shipping_based_time_slot_options' ).slideUp();
						jQuery( '.shipping_based_time_slot_for_' + value ).slideDown();
					}
				})
			});
		</script>
		<?php
	}

	/**
	 * Callback for adding time slots based on weekdays
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_time_slot_for_weekdays_callback( $args ) {
		global $orddd_weekdays;
		$specific_date_option = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$edit                 = 'yes';
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['specific_dates'] ) ) {
					$specific_days_settings = explode( ',', $shipping_methods_arr['specific_dates'] );
					foreach ( $specific_days_settings as $sk => $sv ) {
						$specific_date_value = str_replace( '}', '', $sv );
						$specific_date_value = str_replace( '{', '', $specific_date_value );
						$specific_date_arr   = explode( ':', $specific_date_value );
						if ( isset( $specific_date_arr[0] ) && '' !== $specific_date_arr[0] ) {
							$date_arr              = explode( '-', trim( $specific_date_arr[0] ) );
							$date_str              = gmdate( 'm-d-Y', gmmktime( 0, 0, 0, $date_arr[0], $date_arr[1], $date_arr[2] ) );
							$specific_date_option .= "<option value='" . $specific_date_arr[0] . "'>" . $date_str . '</option>';
						}
					}
				}
			}
		}

		echo '<div class="shipping_based_time_slot_options shipping_based_time_slot_for_weekdays">
            <select class="orddd_shipping_based_time_slot_for_weekdays" id="orddd_shipping_based_time_slot_for_weekdays" name="orddd_shipping_based_time_slot_for_weekdays[]" multiple="multiple" placeholder="Select Weekdays">
                <option name="all" value="all">All</option>
            </select>
        </div>';

		echo '<div class="shipping_based_time_slot_options shipping_based_time_slot_for_specific_dates">
            <select class="orddd_shipping_based_time_slot_for_weekdays" id="orddd_shipping_based_select_delivery_dates" name="orddd_shipping_based_select_delivery_dates[]" multiple="multiple" placeholder="Select Specific Delivery Dates"> ' . $specific_date_option . ' </select>
        </div>';

		$html = '<label for="orddd_shipping_based_time_slot_for_weekdays"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for displaying the saved time slots in a table
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.8.3
	 */

	public static function orddd_shipping_based_time_slot_save_callback( $args ) {
		global $orddd_weekdays;
		$currency_symbol = get_woocommerce_currency_symbol();
		echo "<script type='text/javascript'>
        jQuery( document ).ready( function(){
            jQuery( 'table#orddd_time_slot_list' ).on( 'click', 'a.confirmation_time_slot', function() {
                var time_slot_hidden = jQuery( '#orddd_time_slot_hidden' ).val();
                var time_slot = jQuery( 'table#orddd_time_slot_list tr#'+ this.id + ' td#orddd_time_slot' ).html();
                var date_str = jQuery( 'table#orddd_time_slot_list tr#'+ this.id + ' td#orddd_delivery_day' ).html();
                if( date_str.indexOf( '-' ) !== -1 ) {
                    var delivery_day_type = 'specific_dates';
                } else {
                    var delivery_day_type = 'weekdays'; 
                }
                if( delivery_day_type == 'weekdays' ) { 
                    var orddd_weekdays_js = {};";
		foreach ( $orddd_weekdays as $k => $v ) {
			echo "orddd_weekdays_js[ '" . $k . "_custom_setting' ] = '" . $v . "';";
		}
					echo "jQuery.each( orddd_weekdays_js, function( key, name ) {
                        if ( name == date_str ) {
                            date_str = key;
                        } else if( 'All' == date_str ) {
                            date_str = 'all';
                        }
                    });
                } else if( delivery_day_type == 'specific_dates' ) {
                    var specific_date = date_str.split( '-' );
                    var new_specific_date = new Date( specific_date[ 0 ] + '/' + specific_date[ 1 ] + '/' + specific_date[ 2 ] );
                    date_str = ( new_specific_date.getMonth()+1 ) + '-' + new_specific_date.getDate() + '-' + new_specific_date.getFullYear(); 
                } 
            
                var hidden_arr = time_slot_hidden.split( '},' );
                var substring = '';
                for( i = 0; i < hidden_arr.length; i++ ) {
                    if( hidden_arr[ i ] != '' ) {
                        var date_hidden_arr = hidden_arr[ i ].split( ':' );
                        if( date_hidden_arr.length == '7' ) {
                            var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
                        } else {
                            var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
                        }    
                        var array = date_hidden_arr[ 1 ].split( ',' );
                        for( j = 0; j < array.length; j++ ) {
                            if( date_str == array[ j ].trim() && time_slot == time_slot_str ) {
                                array.splice( j, 1 );
                            }
                        }
                        var array_str = array.join( ',' );
                        if( array_str != '' ) {
                            if( date_hidden_arr.length == '7' ) {
                                hidden_arr[ i ] = date_hidden_arr[ 0 ] + ':' + array_str + ':' + time_slot_str + ':' + date_hidden_arr[ 4 ] + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ];
                            } else {
                                hidden_arr[ i ] = date_hidden_arr[ 0 ] + ':' + array_str + ':' + time_slot_str + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ] + ':' + date_hidden_arr[ 7 ];
                            }
                        } else {
                            hidden_arr.splice( i, 1 );                           
                        }
                    }
                }
                jQuery( '#orddd_time_slot_hidden' ).val(  hidden_arr.join( '},' ) );
                jQuery( 'table#orddd_time_slot_list tr#'+ this.id ).remove();
            });
        
            jQuery( '#save_timeslots' ).click(function() {
                var time_slot_hidden = jQuery( '#orddd_time_slot_hidden' ).val();
                if( time_slot_hidden != '' ) {
                	var hidden_arr = time_slot_hidden.split( '},' );	
                } else {
                	var hidden_arr = [];
                }
                

                var rowCount = jQuery( '#orddd_time_slot_list tr').length;
                var from_time = jQuery( '#orddd_shipping_based_time_from_hours' ).val() + ':' + jQuery( '#orddd_shipping_based_time_from_minutes' ).val();
                var to_time = jQuery( '#orddd_shipping_based_time_to_hours' ).val() + ':' + jQuery( '#orddd_shipping_based_time_to_minutes' ).val();
                if( from_time != '0:00' ) {
                    if( to_time != '0:00' ) {
                        var time_slot = from_time + ' - ' + to_time;
                    } else {
                        var time_slot = from_time;
                    }

                    var added_timeslots = [];
                    var weekdays = [];
                    for( i = 0; i < hidden_arr.length; i++ ) {
                        if( hidden_arr[ i ] != '' ) {
                            var date_hidden_arr = hidden_arr[ i ].split( ':' );
                            if( date_hidden_arr.length == '7' ) {
                                var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
                            } else {
                                var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
                            }    
                            added_timeslots.push( time_slot_str );
                            weekdays[time_slot_str] = date_hidden_arr[ 1 ];
                        }
                    }

                    var timeslot_present = 'no';
                    if( jQuery('input[name=orddd_shipping_based_time_slot_for_delivery_days]:checked' ).val() == 'weekdays' ) {
                        var dd = jQuery( '#orddd_shipping_based_time_slot_for_weekdays' ).val();
                        if( dd != '' && dd != null ) {
                            var orddd_weekdays_js = {};";
		foreach ( $orddd_weekdays as $k => $v ) {
			echo "orddd_weekdays_js[ '" . $k . "_custom_setting' ] = '" . $v . "';";
		}

							echo "if( rowCount == 0 ) {
                                jQuery( '#orddd_time_slot_list' ).append( '<tr class=\'orddd_common_list_tr\'><th class=\'orddd_holiday_list\'>" . __( 'Delivery days/dates', 'order-delivery-date' ) . "</th><th class=\'orddd_holiday_list\'>Time Slot</th><th class=\'orddd_holiday_list\'>Maximum Order Deliveries per time slot</th><th class=\'orddd_holiday_list\'>Additional Charges for time slot</th><th class=\'orddd_holiday_list\'>Checkout Label</th><th class=\'orddd_holiday_list\'>" . __( 'Actions', 'order-delivery-date' ) . "</th></tr>' );
                                var rowCount = 1;
                            }

                            rowCount = rowCount - 1;
                            for( i = 0; i < dd.length; i++ ) {
                                if( dd[ i ] == 'all' ) {
                                    for( k = 0; k < hidden_arr.length; k++ ) {
                                        if( hidden_arr[ k ] != '' ) {
                                            var date_hidden_arr = hidden_arr[ k ].split( ':' );
                                            if( date_hidden_arr.length == '7' ) {
                                                var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
                                            } else {
                                                var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
                                            }    
                                            if( time_slot == time_slot_str && date_hidden_arr[ 1 ] == 'all' ) {
                                                timeslot_present = 'yes';
                                            }
                                        }
                                    }

                                    if( timeslot_present == 'no' ) {
	                                    if( jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() != '' ) {

                                            //Add all the individual enabled weekdays if 'all' is selected 
                                            jQuery.each( orddd_weekdays_js, function(key, value ) {
                                                var isChecked = jQuery( '#'+key).is(':checked');
                                                
                                                if( isChecked == true ) {

                                                    jQuery( '#orddd_time_slot_list tr:last').after('<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_' + rowCount + '\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>'+value+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>' + time_slot + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>' + jQuery('#orddd_shipping_based_time_slot_lockout').val()+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' >" . $currency_symbol . "' +  jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >' + jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val() + '</td><td class=\'orddd_holiday_list\'><a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_' + rowCount + '\'>" . __( 'Delete', 'order-delivery-date' ) . "</a></td></tr>' );
                                                    rowCount = rowCount + 1;
                                                }
                                            }); 
	                                        
	                                    } else {
                                            jQuery.each( orddd_weekdays_js, function(key, value ) {
                                                var isChecked = jQuery( '#'+key).is(':checked');

                                                if( isChecked == true ) {
                                                    jQuery( '#orddd_time_slot_list tr:last').after('<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_' + rowCount + '\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>' + value + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>' + time_slot + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>' + jQuery('#orddd_shipping_based_time_slot_lockout').val()+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' ></td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >' + jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val() + '</td><td class=\'orddd_holiday_list\'><a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_' + rowCount + '\'>" . __( 'Delete', 'order-delivery-date' ) . "</a></td></tr>' );
                                                    rowCount = rowCount + 1;
                                                }
                                            });
	                                        
	                                    }
	                                }
                                } else {    
                                    var weekday_value = dd[ i ];
                                    for( j = 0; j < hidden_arr.length; j++ ) {
                                        if( hidden_arr[ j ] != '' ) {
                                            var date_hidden_arr = hidden_arr[ j ].split( ':' );
                                            if( date_hidden_arr.length == '7' ) {
                                                var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
                                            } else {
                                                var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
                                            }    
                                            
                                            if( time_slot == time_slot_str && date_hidden_arr[ 1 ].indexOf( weekday_value ) != -1 ) { 
                                                timeslot_present = 'yes';
                                            }
                                        }
                                    }	
                                    
                                    if( timeslot_present == 'no' ) {
	                                    if( jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() != '' ) {
	                                        jQuery( '#orddd_time_slot_list tr:last').after('<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_' + rowCount + '\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>' + orddd_weekdays_js[ weekday_value ] + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>' + time_slot + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>' + jQuery('#orddd_shipping_based_time_slot_lockout').val()+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' >" . $currency_symbol . "' + jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >' + jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val() + '</td><td class=\'orddd_holiday_list\'><a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_' + rowCount + '\'>" . __( 'Delete', 'order-delivery-date' ) . "</a></td></tr>' );
	                                    } else {
	                                        jQuery( '#orddd_time_slot_list tr:last').after('<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_' + rowCount + '\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>' + orddd_weekdays_js[ weekday_value ] + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>' + time_slot + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>' + jQuery('#orddd_shipping_based_time_slot_lockout').val()+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' ></td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >' + jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val() + '</td><td class=\'orddd_holiday_list\'><a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_' + rowCount + '\'>" . __( 'Delete', 'order-delivery-date' ) . "</a></td></tr>' );
	                                    }
                                    }
                                    rowCount = rowCount + 1;
                                    
                                }
                            }
                            jQuery( '#cdts-days-error-msg' ).hide();
                        } else { 
                            jQuery( '#orddd_time_slot_list' ).before( '<div id=\'cdts-days-error-msg\' class=\'error settings-error notice is-dismissible\' style=\' width:50%;position:absolute;margin-left:50px;\'>Please Select Delivery Days/Dates for the Time slot </div>' );
                            setTimeout( function() {
                                jQuery( '#cdts-days-error-msg' ).fadeOut();
                            }, 3000 );
                        }      
                    } else if( jQuery('input[name=orddd_shipping_based_time_slot_for_delivery_days]:checked' ).val() == 'specific_dates' ) {
                        var dd = jQuery( '#orddd_shipping_based_select_delivery_dates' ).val();
                        if( dd != '' && dd != null ) {
                            
                            if( rowCount == 0 ) {
                                jQuery( '#orddd_time_slot_list' ).append( '<tr class=\'orddd_common_list_tr\'><th class=\'orddd_holiday_list\'>" . __( 'Delivery days/dates', 'order-delivery-date' ) . "</th><th class=\'orddd_holiday_list\'>" . __( 'Time Slot', 'order-delivery-date' ) . "</th><th class=\'orddd_holiday_list\'>" . __( 'Maximum Order Deliveries per time slot', 'order-delivery-date' ) . "</th><th class=\'orddd_holiday_list\'>" . __( 'Additional Charges for time slot', 'order-delivery-date' ) . "</th><th class=\'orddd_holiday_list\'>" . __( 'Checkout Label', 'order-delivery-date' ) . "</th><th class=\'orddd_holiday_list\'>" . __( 'Actions', 'order-delivery-date' ) . "</th></tr>' );
                                var rowCount = 1;
                            }

                            rowCount = rowCount - 1;
                            for( i = 0; i < dd.length; i++ ) {
            	                var split_to_date = dd[ i ].split( '-' );
                                split_to_date[0] = split_to_date[0] - 1;
                                var date = new Date( split_to_date[2], split_to_date[0], split_to_date[1] );
            	                if( date.getDate() < 10 ) {
            	                   var dd_str = '0' + date.getDate();
            	                } else {
            	                   var dd_str = date.getDate();
            	                }
            	    
            	                if( ( date.getMonth() + 1 ) < 10 ) {
            	                   var mm = '0' + ( date.getMonth() + 1 );
            	                } else {
            	                   var mm = ( date.getMonth() + 1 );
            	                }

                                var date_str = mm + '-' + dd_str + '-' + date.getFullYear();
                                for( k = 0; k < hidden_arr.length; k++ ) {
                                    if( hidden_arr[ k ] != '' ) {
                                        var date_hidden_arr = hidden_arr[ k ].split( ':' );
                                        if( date_hidden_arr.length == '7' ) {
                                            var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
                                        } else {
                                            var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
                                        }    
                                        
                                        if( time_slot == time_slot_str && date_str == date_hidden_arr[ 1 ] ) {
                                            timeslot_present = 'yes';
                                        }
                                    }
                                }

                                if( timeslot_present == 'no' ) {
	                                if( jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() != '' ) {
	                                    jQuery( '#orddd_time_slot_list tr:last').after('<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_' + rowCount + '\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>' +  date_str + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>' + time_slot + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>' + jQuery('#orddd_shipping_based_time_slot_lockout').val()+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' >" . $currency_symbol . "' + jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >' + jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val() + '</td><td class=\'orddd_holiday_list\'><a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_' + rowCount + '\'>" . __( 'Delete', 'order-delivery-date' ) . "</a></td></tr>' );
	                                } else {
	                                    jQuery( '#orddd_time_slot_list tr:last').after('<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_' + rowCount + '\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>' +  date_str + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>' + time_slot + '</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>' + jQuery('#orddd_shipping_based_time_slot_lockout').val()+'</td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' ></td><td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >' + jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val() + '</td><td class=\'orddd_holiday_list\'><a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_' + rowCount + '\'>" . __( 'Delete', 'order-delivery-date' ) . "</a></td></tr>' );
	                                }
	                            }
                                rowCount = rowCount + 1;
                            }
                            jQuery( '#cdts-dates-error-msg' ).hide();
                        } else { 
                            jQuery( '#orddd_time_slot_list' ).before( '<div id=\'cdts-dates-error-msg\' class=\'error settings-error notice is-dismissible\' style=\' width:50%;position:absolute;margin-left:50px; \'>Please Select Delivery Days/Dates for the Time slot </div>' );
                            setTimeout( function() {
                                jQuery( '#cdts-dates-error-msg' ).fadeOut();
                            },3000 );
                        } 

                    } else {
                        var dd = [];
                    }

                    if( dd != '' && dd != null && timeslot_present == 'no' ) {
                        var row = jQuery( '#orddd_time_slot_hidden' ).val();

                        if( jQuery.inArray( 'all', dd ) != -1 ) {
                            jQuery.each( orddd_weekdays_js, function(key, value ) {
                                var isChecked = jQuery( '#'+key).is(':checked');
                                if( isChecked == true ) {
                                    row += '{' + jQuery('input[name=orddd_shipping_based_time_slot_for_delivery_days]:checked' ).val() + ':' + key + ':' + time_slot + ':' + jQuery('#orddd_shipping_based_time_slot_lockout').val() + ':' + jQuery('#orddd_shipping_based_time_slot_additional_charges').val() + ':' + jQuery('#orddd_shipping_based_time_slot_additional_charges_label').val() + '},';
                                    jQuery( '#orddd_time_slot_hidden' ).val( row );
                                }
                            });
                        }else{
                            row += '{' + jQuery('input[name=orddd_shipping_based_time_slot_for_delivery_days]:checked' ).val() + ':' + dd + ':' + time_slot + ':' + jQuery('#orddd_shipping_based_time_slot_lockout').val() + ':' + jQuery('#orddd_shipping_based_time_slot_additional_charges').val() + ':' + jQuery('#orddd_shipping_based_time_slot_additional_charges_label').val() + '},';

                        }
                        jQuery( '#orddd_time_slot_hidden' ).val( row );
                        
                    }
                    jQuery( '#orddd_shipping_based_time_from_hours option[value=\'0\']').attr( 'selected', 'selected' );
                    jQuery( '#orddd_shipping_based_time_from_minutes option[value=\'00\']').attr( 'selected', 'selected' );
                    jQuery( '#orddd_shipping_based_time_to_hours option[value=\'0\']').attr( 'selected', 'selected' );
                    jQuery( '#orddd_shipping_based_time_to_minutes option[value=\'00\']').attr( 'selected', 'selected' );
                    jQuery( '#orddd_shipping_based_time_slot_lockout' ).val( '' );
                    jQuery( '#orddd_shipping_based_time_slot_for_weekdays' ).val('').trigger('change');
                    jQuery( '#orddd_shipping_based_select_delivery_dates' ).val('').trigger('change');
                    jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val( '' );
                    jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val( '' ); 
                }
            });
        });
        </script>";

		echo '<input type="button" style="position:absolute;" value="' . __( 'Save', 'order-delivery-date' ) . '" id="save_timeslots" class="save_button">';

		$time_slot_hidden_str = '';
		$row_id               = '';
		echo '<table id="orddd_time_slot_list" class="order-delivery-time-slot-list-settings" style="margin-top:50px">';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_methods_arr['time_slots'] ) && '' !== $shipping_methods_arr['time_slots'] ) {
					$time_slot_settings = explode( '},', $shipping_methods_arr['time_slots'] );
					$time_slot_str      = '';
					echo '<tr class="orddd_common_list_tr">
                        <th class="orddd_holidays_list">' . __( 'Delivery Days/Dates', 'order-delivery-date' ) . '</th>
                        <th class="orddd_holidays_list">' . __( 'Time Slot', 'order-delivery-date' ) . '</th>
                        <th class="orddd_holidays_list">' . __( 'Maximum Order Deliveries per time slot', 'order-delivery-date' ) . '</th>
                        <th class="orddd_holidays_list">' . __( 'Additional Charges for time slot', 'order-delivery-date' ) . '</th>
                        <th class="orddd_holidays_list">' . __( 'Checkout Label', 'order-delivery-date' ) . '</th>
                        <th class="orddd_holidays_list">' . __( 'Actions', 'order-delivery-date' ) . '</th>
                    </tr>';
					$i = 0;
					foreach ( $time_slot_settings as $hk => $hv ) {
						if ( '' !== $hv ) {
							$timeslot_values = orddd_common::get_timeslot_values( $hv );

							if ( 'weekdays' === $timeslot_values['delivery_days_selected'] ) {
								foreach ( $timeslot_values['selected_days'] as $key => $val ) {
									foreach ( $orddd_weekdays as $k => $v ) {
										if ( $k . '_custom_setting' === $val ) {
											echo '<tr class="orddd_common_list_tr" id="orddd_delete_time_slot_' . $i . '">
                                                <td class="orddd_holidays_list" id="orddd_delivery_day">' . $v . '</td>
                                                <td class="orddd_holidays_list" id="orddd_time_slot">' . $timeslot_values['time_slot'] . '</td>
                                                <td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $timeslot_values['lockout'] . '</td>';
											if ( '' !== $timeslot_values['additional_charges'] ) {
												echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $currency_symbol . '' . $timeslot_values['additional_charges'] . '</td>';
											} else {
												echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout"></td>';
											}
												echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $timeslot_values['additional_charges_label'] . '</td>
                                                <td class="orddd_holidays_list"><a href="javascript:void(0)" class="confirmation_time_slot" id="orddd_delete_time_slot_' . $i . '">' . __( 'Delete', 'order-delivery-date' ) . '</a></td>
                                            </tr>';
										}
									}
									if ( 'all' === $val ) {
										echo '<tr class="orddd_common_list_tr" id="orddd_delete_time_slot_' . $i . '">
                                                <td class="orddd_holidays_list" id="orddd_delivery_day">All</td>
                                                <td class="orddd_holidays_list" id="orddd_time_slot">' . $timeslot_values['time_slot'] . '</td>
                                                <td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $timeslot_values['lockout'] . '</td>';
										if ( '' !== $timeslot_values['additional_charges'] ) {
											echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $currency_symbol . '' . $timeslot_values['additional_charges'] . '</td>';
										} else {
											echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout"></td>';
										}
												echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $timeslot_values['additional_charges_label'] . '</td>
                                                <td class="orddd_holidays_list"><a href="javascript:void(0)" class="confirmation_time_slot" id="orddd_delete_time_slot_' . $i . '">' . __( 'Delete', 'order-delivery-date' ) . '</a></td>
                                            </tr>';
									}
									$i++;
								}
							} elseif ( 'specific_dates' === $timeslot_values['delivery_days_selected'] ) {
								foreach ( $timeslot_values['selected_days'] as $key => $val ) {
									$date_arr           = explode( '-', trim( $val ) );
									$date_str           = gmdate( 'm-d-Y', gmmktime( 0, 0, 0, $date_arr[0], $date_arr[1], $date_arr[2] ) );
									$additional_charges = $timeslot_values['additional_charges'];
									echo '<tr class="orddd_common_list_tr" id="orddd_delete_time_slot_' . $i . '">
                                        <td class="orddd_holidays_list" id="orddd_delivery_day">' . $date_str . '</td>
                                        <td class="orddd_holidays_list" id="orddd_time_slot">' . $timeslot_values['time_slot'] . '</td>
                                        <td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $timeslot_values['lockout'] . '</td>';
									if ( $additional_charges != '' ) {
										echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $currency_symbol . '' . $timeslot_values['additional_charges'] . '</td>';
									} else {
										echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout"></td>';
									}
										echo '<td class="orddd_holidays_list" id="orddd_time_slot_lockout">' . $timeslot_values['additional_charges_label'] . '</td>
                                        <td class="orddd_holidays_list"><a href="javascript:void(0)" class="confirmation_time_slot" id="orddd_delete_time_slot_' . $i . '">' . __( 'Delete', 'order-delivery-date' ) . '</a></td>
                                    </tr>';
									$i++;
								}
							}
							$time_slot_hidden_str .= $hv . '},';
						}
					}
				}
				echo '<input type="hidden" id="edit_row_id"  name="edit_row_id" value="' . $row_id . '"/>';
			}
		}
		echo '</table>';
		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
		echo '<input type="hidden" id="orddd_time_slot_hidden"  name="orddd_shipping_based_settings_' . $option_key . '[time_slots]" value="' . $time_slot_hidden_str . '"/>';
	}

	/**
	 * Callback for adding a unique key to each row while saving the Custom delivery settings
	 *
	 * @param array $input
	 * @since 2.8.3
	 */

	public static function orddd_shipping_based_settings_option_key_callback( $input ) {
		$row_id                      = '';
		$is_pickup_location_selected = 'no';

		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id = get_option( 'orddd_shipping_based_settings_option_key' );
			}
		} else {
			if ( isset( $_POST['edit_row_id'] ) ) {
				$row_id = get_option( 'orddd_shipping_based_settings_option_key' );
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		if ( isset( $_POST[ 'orddd_shipping_based_settings_' . $option_key ] ) ) {
			$shipping_settings = $_POST[ 'orddd_shipping_based_settings_' . $option_key ];
		} else {
			$shipping_settings = array();
		}

		if ( has_filter( 'is_pickup_location_selected' ) ) {
			$is_pickup_location_selected = apply_filters( 'is_pickup_location_selected', $_POST, $option_key );
		}

		if ( ( isset( $shipping_settings['shipping_methods'] ) && is_array( $shipping_settings['shipping_methods'] ) && count( $shipping_settings['shipping_methods'] ) > 0 ) || ( isset( $shipping_settings['product_categories'] ) && is_array( $shipping_settings['product_categories'] ) && count( $shipping_settings['product_categories'] ) > 0 ) || ( isset( $shipping_settings['orddd_locations'] ) && is_array( $shipping_settings['orddd_locations'] ) && count( $shipping_settings['orddd_locations'] ) > 0 ) ) {
		} elseif ( 'yes' === $is_pickup_location_selected ) {
		} else {
			$option_key = get_option( 'orddd_shipping_based_settings_option_key' );
		}

		return $option_key;
	}

	/**
	 * Callback for saving custom shipping methods
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_settings_save_callback( $input ) {
		$row_id = '';
		if ( ( isset( $_GET['action'] ) && 'shipping_based' === $_GET['action'] ) && ( isset( $_GET['mode'] ) && 'edit' === $_GET['mode'] ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id = $_GET['row_id'];
			}
		} else {
			if ( isset( $_POST['edit_row_id'] ) ) {
				$row_id = $_POST['edit_row_id'];
			}
		}

		if ( ( isset( $input['shipping_methods'] ) && is_array( $input['shipping_methods'] ) && count( $input['shipping_methods'] ) > 0 ) || ( isset( $input['product_categories'] ) && is_array( $input['product_categories'] ) && count( $input['product_categories'] ) > 0 ) || ( isset( $input['orddd_locations'] ) && is_array( $input['orddd_locations'] ) && count( $input['orddd_locations'] ) > 0 ) ) {
			$new_input = $input;
			if ( '' !== $row_id ) {
				$option_key        = orddd_common::get_shipping_setting_option_key( $row_id );
				$shipping_settings = get_option( 'orddd_shipping_based_settings_' . $row_id );
				if ( isset( $shipping_settings['orddd_lockout_date'] ) ) {
					$new_input['orddd_lockout_date'] = $shipping_settings['orddd_lockout_date'];
				}
				if ( isset( $shipping_settings['orddd_lockout_time_slot'] ) ) {
					$new_input['orddd_lockout_time_slot'] = $shipping_settings['orddd_lockout_time_slot'];
				}
			}
			$_REQUEST['_wp_http_referer'] = get_admin_url() . 'admin.php?page=order_delivery_date&action=shipping_based&settings-updated=true';
		} else {
			$option_key = orddd_common::get_shipping_setting_option_key( $row_id );
			unregister_setting( 'orddd_shipping_based_settings', 'orddd_shipping_based_settings_' . $option_key );

			if ( 'on' === get_option( 'orddd_enable_shipping_based_delivery' ) ) {
				if ( isset( $input['delivery_settings_based_on'] ) && 'shipping_methods' === $input['delivery_settings_based_on'][0] ) {
					add_settings_error( 'orddd_shipping_based_settings_' . $option_key, 'shipping_methods_error', 'Please select shipping methods', 'error' );
				} elseif ( isset( $input['delivery_settings_based_on'] ) && 'product_categories' === $input['delivery_settings_based_on'][0] ) {
					add_settings_error( 'orddd_shipping_based_settings_' . $option_key, 'shipping_methods_error', 'Please select product categories', 'error' );
				} elseif ( isset( $input['delivery_settings_based_on'] ) && 'orddd_locations' === $input['delivery_settings_based_on'][0] ) {
					add_settings_error( 'orddd_shipping_based_settings_' . $option_key, 'shipping_methods_error', 'Please select locations', 'error' );
				}
			}
			$new_input = false;
		}

		if ( has_filter( 'orddd_save_custom_settings' ) ) {
			$new_input = apply_filters( 'orddd_save_custom_settings', $new_input, $input, $row_id );
		}

		return $new_input;
	}

	/**
	 * Get all the WooCommerce shipping methods
	 *
	 * Compatible Plugins:
	 * WooCommerce Table Rate Shipping plugin by WooCommerce
	 * WooCommerce USPS Shipping plugin by Automattic
	 * WooCommerce Fedex Shipping plugin by WooCommerce
	 * WooCommerce FedEx Shipping Plugin with Print Label by Xadapter
	 * Flexible Shipping plugin by WP Desk
	 * WooCommerce UPS Shipping – Live Rates and Access Points plugin by WP Desk
	 * WooCommerce UPS Shipping by WooCommerce
	 * Table rate shipping plugin by Bolder elements
	 * Weight based Shippng plugin by weightbasedshipping.com
	 *
	 * @since 2.8.3
	 */
	public static function orddd_get_shipping_methods() {
		global $wpdb, $woocommerce;
		$shipping_classes = array();

		// WooCommerce default Shipping methods.
		$active_methods   = array();
		$shipping_methods = $woocommerce->shipping->load_shipping_methods();
		foreach ( $shipping_methods as $id => $shipping_method ) {
			if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '2.6', '>=' ) > 0 ) {
				if ( isset( $shipping_method->id ) && false !== strstr( $shipping_method->id, 'legacy' ) ) {
					$title                 = $shipping_method->title . ' (Legacy)';
					$active_methods[ $id ] = array(
						'title'      => $title,
						'tax_status' => $shipping_method->tax_status,
					);
				}
			} else {
				if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {
					$active_methods[ $id ] = array(
						'title'      => $shipping_method->title,
						'tax_status' => $shipping_method->tax_status,
					);
				}
			}
		}

		// Default WooCommerce Shipping zones from verison 2.6
		$shipping_default_zones = array();
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '2.6', '>=' ) > 0 ) {
			if ( class_exists( 'WC_Shipping_Zones' ) ) {
				$shipping_zone_class = new WC_Shipping_Zones();
				$shipping_zones      = array();
				if ( method_exists( $shipping_zone_class, 'get_zones' ) ) {
					$shipping_zones = $shipping_zone_class->get_zones();
				}
				foreach ( $shipping_zones as $shipping_default_key => $shipping_default_value ) {
					if ( isset( $shipping_default_value['shipping_methods'] ) ) {
						foreach ( $shipping_default_value['shipping_methods'] as $key => $value ) {
							if ( 'table_rate' == $value->id ) {
								// Custom delivery settings for WooCommerce Table Rate Shipping plugin by WooCommerce.
								$table_rate_shipping_classes = $wpdb->get_results( "SELECT * FROM `" . $wpdb->prefix . "woocommerce_shipping_table_rates` WHERE shipping_method_id = {$value->instance_id} ORDER BY rate_order ASC;" );
								foreach ( $table_rate_shipping_classes as $tkey => $tvalue ) {
									$option_settings = get_option( 'woocommerce_table_rate_' . $value->instance_id . '_settings' );

									if ( '' == $option_settings['calculation_type'] ) {
										$title = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ' . $tvalue->rate_label;
										$id    = $value->id . ':' . $value->instance_id . ':' . $tvalue->rate_id;
									} else {
										$title = $shipping_default_value['zone_name'] . ' -> ' . $value->title;
										$id    = $value->id . ':' . $value->instance_id;
									}
									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $title,
										'shipping_default_zone_id' => $id,
									);
								}
							} elseif ( 'usps' == $value->id ) {
								// Custom Delivery Settings for WooCommerce USPS Shipping plugin by Automattic.
								$usps_settings  = $value->instance_settings;
								$usps_title     = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';
								$express_title  = $usps_title . 'Priority Mail Express Flat Rate®';
								$priority_title = $usps_title . 'Priority Mail Flat Rate®';

								if ( 'yes' == $usps_settings['enable_flat_rate_boxes'] ) {
									$express_id = $shipping_default_value['zone_id'] . ':' . 'usps' . ':' . 'flat_rate_box_express';
									if ( isset( $usps_settings['flat_rate_express_title'] ) && '' != $usps_settings['flat_rate_express_title'] ) {
										$express_title = $usps_title . $usps_settings['flat_rate_express_title'];
									}

									$priority_id = $shipping_default_value['zone_id'] . ':' . 'usps' . ':' . 'flat_rate_box_priority';
									if ( isset( $usps_settings['flat_rate_priority_title'] ) && '' != $usps_settings['flat_rate_priority_title'] ) {
										$priority_title = $usps_title . $usps_settings['flat_rate_priority_title'];
									}

									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $express_title,
										'shipping_default_zone_id' => $express_id,
									);

									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $priority_title,
										'shipping_default_zone_id' => $priority_id,
									);
								} elseif ( 'priority' == $usps_settings['enable_flat_rate_boxes'] ) {
									$priority_id = $shipping_default_value['zone_id'] . ':' . 'usps' . ':' . 'flat_rate_box_priority';
									if ( isset( $usps_settings['flat_rate_priority_title'] ) && '' != $usps_settings['flat_rate_priority_title'] ) {
										$priority_title = $usps_title . $usps_settings['flat_rate_priority_title'];
									}

									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $priority_title,
										'shipping_default_zone_id' => $priority_id,
									);
								} elseif ( 'express' == $usps_settings['enable_flat_rate_boxes'] ) {
									$express_id = $shipping_default_value['zone_id'] . ':' . 'usps' . ':' . 'flat_rate_box_express';
									if ( isset( $usps_settings['flat_rate_express_title'] ) && '' != $usps_settings['flat_rate_express_title'] ) {
										$express_title = $usps_title . $usps_settings['flat_rate_express_title'];
									}

									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $express_title,
										'shipping_default_zone_id' => $express_id,
									);
								}

								if ( 'yes' == $usps_settings['enable_standard_services'] ) {
									$usps_services = $usps_settings['services'];
									foreach ( $usps_services as $usps_skey => $usps_svalue ) {
										$usps_service_name = $usps_svalue['name'];
										if ( '' == $usps_service_name ) {
											$usps_service_name = orddd_common::orddd_get_shipping_service_name( $usps_skey );
										}
										$id                       = $shipping_default_value['zone_id'] . ':' . 'usps' . ':' . $usps_skey;
										$shipping_default_zones[] = array(
											'shipping_default_zone_title' => $usps_title . $usps_service_name,
											'shipping_default_zone_id' => $id,
										);
									}
								}
							} elseif ( 'fedex' == $value->id && is_plugin_active( 'woocommerce-shipping-fedex/woocommerce-shipping-fedex.php' ) ) {
								// Custom delivery settings for WooCommerce Fedex Shipping plugin by WooCommerce

								$fedex_instance = $value->instance_id;
								$fedex_settings = $value->instance_settings;
								$fedex_title    = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';

								$fedex_services = $fedex_settings['services'];
								if ( is_array( $fedex_services ) && count( $fedex_services ) > 0 ) {
									foreach ( $fedex_services as $fedex_services_key => $fedex_services_value ) {
										$fedex_services_enabled = $fedex_services_value['enabled'];
										if ( '1' == $fedex_services_enabled ) {
											$fedex_services_name = $fedex_services_value['name'];
											if ( '' == $fedex_services_name ) {
												$fedex_services_name = orddd_common::orddd_get_fedex_service_name( $fedex_services_key );
											}
											$fedex_id = 'fedex' . ':' . $fedex_instance . ':' . $fedex_services_key;

											$shipping_default_zones[] = array(
												'shipping_default_zone_title' => $fedex_title . $fedex_services_name,
												'shipping_default_zone_id' => $fedex_id,
											);
										}
									}
								}
							} elseif ( 'fedex' == $value->id ) {
								// Custom delivery settings for WooCommerce FedEx Shipping Plugin with Print Label by Xadapter

								$fedex_instance = $value->instance_id;
								$fedex_settings = $value->instance_settings;
								$fedex_title    = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';

								$fedex_services = $fedex_settings['services'];
								if ( is_array( $fedex_services ) && count( $fedex_services ) > 0 ) {
									foreach ( $fedex_services as $fedex_services_key => $fedex_services_value ) {
										$fedex_services_enabled = $fedex_services_value['enabled'];
										if ( '1' == $fedex_services_enabled ) {
											$fedex_services_name = $fedex_services_value['name'];
											if ( '' == $fedex_services_name ) {
												$fedex_services_name = orddd_common::orddd_get_fedex_service_name( $fedex_services_key );
											}
											$fedex_id                 = $shipping_default_value['zone_id'] . ':' . 'fedex' . ':' . $fedex_services_key;
											$shipping_default_zones[] = array(
												'shipping_default_zone_title' => $fedex_title . $fedex_services_name,
												'shipping_default_zone_id' => $fedex_id,
											);
										}
									}
								}
							} elseif ( 'flexible_shipping' == $value->id ) {
								// Custom Delivery Settings for Flexible Shipping plugin by WP Desk.
								$flexible_methods = get_option( $value->shipping_methods_option );

								foreach ( $flexible_methods as $flexible_methods_key => $flexible_methods_value ) {
									$flexible_title = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';
									if ( 'yes' == $flexible_methods_value['method_enabled'] ) {

										$flexible_title .= $flexible_methods_value['method_title'];

										$shipping_default_zones[] = array(
											'shipping_default_zone_title' => $flexible_title,
											'shipping_default_zone_id' => $flexible_methods_value['id_for_shipping'],
										);
									}
								}
							} elseif ( 'flexible_shipping_ups' == $value->id ) {
								// Custom Delivery Setting for WooCommerce UPS Shipping – Live Rates and Access Points plugin by WP Desk.
								$flexible_methods = get_option( 'woocommerce_flexible_shipping_ups_' . $value->instance_id . '_settings' );
								if ( isset( $flexible_methods['custom_services'] ) && $flexible_methods['custom_services'] == 'yes' ) {
									$flexible_shipping_services = $flexible_methods['services'];
									$flexible_title             = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';
									foreach ( $flexible_shipping_services as $service_key => $service_value ) {
										if ( isset( $service_value['enabled'] ) && $service_value['enabled'] == true ) {
											$flexible_title          .= $service_value['name'];
											$shipping_default_zones[] = array(
												'shipping_default_zone_title' => $flexible_title,
												'shipping_default_zone_id' => $value->id . ':' . $value->instance_id . ':' . $service_key,
											);
										}
									}
								}

								if ( isset( $flexible_methods['fallback'] ) && $flexible_methods['fallback'] == 'yes' ) {
									$flexible_title           = $shipping_default_value['zone_name'] . ' -> ' . $value->title;
									$id                       = $value->id . ':' . $value->instance_id . ':fallback';
									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $flexible_title,
										'shipping_default_zone_id' => $id,
									);
								}
							} elseif ( 'ups' == $value->id ) {
								// Custom Delivery Settings for WooCommerce UPS Shipping by WooCommerce
								$ups_instance = $value->instance_id;
								$ups_settings = $value->instance_settings;
								$ups_title    = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';

								$ups_services = $ups_settings['services'];
								foreach ( $ups_services as $ups_services_key => $ups_services_value ) {
									$ups_services_enabled = $ups_services_value['enabled'];
									if ( '1' == $ups_services_enabled ) {
										$ups_services_name = $ups_services_value['name'];
										if ( '' == $ups_services_name ) {
											$ups_services_name = orddd_common::orddd_get_ups_service_name( $ups_services_key );
										}
										$ups_id                   = 'ups' . ':' . $ups_instance . ':' . $ups_services_key;
										$shipping_default_zones[] = array(
											'shipping_default_zone_title' => $ups_title . $ups_services_name,
											'shipping_default_zone_id' => $ups_id,
										);
									}
								}
							} elseif ( 'betrs_shipping' == $value->id ) {
								// Custom Delivery Settings for Table rate shipping plugin by Bolder elements.
								$betrs_instance          = $value->instance_id;
								$betrs_options_save_name = $value->id . '_options-' . $betrs_instance;
								$betrs_shipping_options  = get_option( $betrs_options_save_name );

								$betrs_settings = $betrs_shipping_options['settings'];

								foreach ( $betrs_settings as $betrs_settings_key => $betrs_settings_value ) {
									$betrs_title  = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ';
									$betrs_title .= $betrs_settings_value['title'];
									if ( '' == $betrs_title ) {
										$betrs_title .= 'Table Rate';
									}

									$betrs_id                 = $value->id . ':' . $betrs_instance . '-' . $betrs_settings_value['option_id'];
									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $betrs_title,
										'shipping_default_zone_id' => $betrs_id,
									);
								}
							} elseif ( 'wbs' == $value->id && is_plugin_active( 'weight-based-shipping-for-woocommerce/plugin.php' ) ) {
								// Custom Delivery Settings for Weight based Shippng plugin by weightbasedshipping.com
								$wbs_settings = get_option( 'wbs_' . $value->instance_id . '_config' );
								if ( isset( $wbs_settings['enabled'] ) && $wbs_settings['enabled'] == 1 ) {
									foreach ( $wbs_settings['rules'] as $rk => $rv ) {
										if ( isset( $rv['meta']['enabled'] ) && $rv['meta']['enabled'] == 1 ) {
											$package_title            = $rv['meta']['title'];
											$package_id               = self::orddd_get_shipping_package_id( $package_title );
											$title                    = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ' . $package_title;
											$id                       = $value->id . ':' . $value->instance_id . ':' . $package_id;
											$shipping_default_zones[] = array(
												'shipping_default_zone_title' => $title,
												'shipping_default_zone_id' => $id,
											);
										}
									}
								}
							} elseif ( 'canada_post' == $value->id ) {
								$settings = $value->custom_services;
								foreach ( $settings as $service => $options ) {
									if ( $options['enabled'] ) {
										$title                        = $shipping_default_value['zone_name'] . ' -> ' . $value->title . ' -> ' . $service;
											$id                       = $value->id . ':' . $service;
											$shipping_default_zones[] = array(
												'shipping_default_zone_title' => $title,
												'shipping_default_zone_id' => $id,
											);
									}
								}
							} else {
								// WooCommerce Default Shipping methods for shipping zones.
								$title                    = $shipping_default_value['zone_name'] . ' -> ' . $value->title;
								$id                       = $value->id . ':' . $value->instance_id;
								$shipping_default_zones[] = array(
									'shipping_default_zone_title' => $title,
									'shipping_default_zone_id' => $id,
								);
							}
						}
					}
				}

				$raw_shipping_method = 'SELECT instance_id, method_id FROM `' . $wpdb->prefix . 'woocommerce_shipping_zone_methods` WHERE zone_id = 0';
				$results             = $wpdb->get_results( $raw_shipping_method );
				foreach ( $results as $result_key => $result_value ) {
					$wc_shipping     = WC_Shipping::instance();
					$allowed_classes = $wc_shipping->get_shipping_method_class_names();
					if ( ! empty( $results ) && in_array( $result_value->method_id, array_keys( $allowed_classes ) ) ) {
						if ( isset( $allowed_classes[ $result_value->method_id ] ) ) {
							$class_name = $allowed_classes[ $result_value->method_id ];
							if ( is_object( $class_name ) ) {
								$class_name = get_class( $class_name );
							}
							$default_shipping_method = new $class_name( $result_value->instance_id );
							if ( $default_shipping_method != '' ) {
								if ( 'table_rate' != $default_shipping_method->id && 'flexible_shipping_ups' != $default_shipping_method->id ) {
									// Default Shipping methods for Rest of the World Shipping zone.
									$title                    = 'Rest of the World' . ' -> ' . $default_shipping_method->title;
									$id                       = $default_shipping_method->id . ':' . $result_value->instance_id;
									$shipping_default_zones[] = array(
										'shipping_default_zone_title' => $title,
										'shipping_default_zone_id' => $id,
									);
								}

								if ( 'fedex' == $default_shipping_method->id ) {
									// Rest of the World shipping zone with WooCommerce Fedex Shipping plugin by WooCommerce.
									$fedex_settings = get_option( 'woocommerce_fedex_' . $result_value->instance_id . '_settings' );
									$fedex_services = $fedex_settings['services'];
									if ( is_array( $fedex_services ) && count( $fedex_services ) > 0 ) {
										foreach ( $fedex_services as $fedex_services_key => $fedex_services_value ) {
											$fedex_services_enabled = $fedex_services_value['enabled'];
											if ( '1' == $fedex_services_enabled ) {
												$fedex_services_name = $fedex_services_value['name'];
												if ( '' == $fedex_services_name ) {
													$fedex_services_name = orddd_common::orddd_get_fedex_service_name( $fedex_services_key );
												}
												$title                    = 'Rest of the World' . ' -> ' . $default_shipping_method->title . ' -> ' . $fedex_services_name;
												$id                       = 0 . ':' . 'fedex' . ':' . $fedex_services_key;
												$shipping_default_zones[] = array(
													'shipping_default_zone_title' => $title,
													'shipping_default_zone_id' => $id,
												);
											}
										}
									}
								}

								if ( 'table_rate' == $default_shipping_method->id ) {
									// Rest of the World shipping zone with WooCommerce Table Rate Shipping plugin by WooCommerce
									$table_rate_shipping_classes = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->prefix . "woocommerce_shipping_table_rates` WHERE shipping_method_id = {$default_shipping_method->instance_id} ORDER BY rate_order ASC;" );
									foreach ( $table_rate_shipping_classes as $tkey => $tvalue ) {
										$option_settings = get_option( 'woocommerce_table_rate_' . $default_shipping_method->instance_id . '_settings' );

										if ( '' == $option_settings['calculation_type'] ) {
											$title = 'Rest of the World' . ' -> ' . $default_shipping_method->title . ' -> ' . $tvalue->rate_label;
											$id    = $default_shipping_method->id . ':' . $default_shipping_method->instance_id . ':' . $tvalue->rate_id;
										} else {
											$title = 'Rest of the World' . ' -> ' . $default_shipping_method->title;
											$id    = $default_shipping_method->id . ':' . $default_shipping_method->instance_id;
										}
										$shipping_default_zones[] = array(
											'shipping_default_zone_title' => $title,
											'shipping_default_zone_id' => $id,
										);
									}
								}

								if ( 'flexible_shipping' == $default_shipping_method->id ) {
									// Rest of the World shipping zone with Flexible Shipping plugin by WP Desk
									$flexible_methods = get_option( $default_shipping_method->shipping_methods_option );

									foreach ( $flexible_methods as $flexible_methods_key => $flexible_methods_value ) {
										$flexible_title = 'Rest of the World' . ' -> ' . $default_shipping_method->title . ' -> ';
										if ( 'yes' == $flexible_methods_value['method_enabled'] ) {

											$flexible_title .= $flexible_methods_value['method_title'];

											$shipping_default_zones[] = array(
												'shipping_default_zone_title' => $flexible_title,
												'shipping_default_zone_id' => $flexible_methods_value['id_for_shipping'],
											);
										}
									}
								}

								if ( 'flexible_shipping_ups' == $default_shipping_method->id ) {
									// Rest of the World shipping zone with WooCommerce UPS Shipping – Live Rates and Access Points plugin by WP Desk
									$flexible_methods = get_option( 'woocommerce_flexible_shipping_ups_' . $result_value->instance_id . '_settings' );
									if ( isset( $flexible_methods['custom_services'] ) && $flexible_methods['custom_services'] == 'yes' ) {
										$flexible_shipping_services = $flexible_methods['services'];
										$flexible_title             = 'Rest of the World' . ' -> ' . $value->title . ' -> ';
										foreach ( $flexible_shipping_services as $service_key => $service_value ) {
											if ( isset( $service_value['enabled'] ) && $service_value['enabled'] == true ) {
												$flexible_title          .= $service_value['name'];
												$shipping_default_zones[] = array(
													'shipping_default_zone_title' => $flexible_title,
													'shipping_default_zone_id' => $value->id . ':' . $result_value->instance_id . ':' . $service_key,
												);
											}
										}
									}

									if ( isset( $default_shipping_method->instance_settings['fallback'] ) && $default_shipping_method->instance_settings['fallback'] == 'yes' ) {
										$title                    = 'Rest of the World' . ' -> ' . $default_shipping_method->title;
										$id                       = $default_shipping_method->id . ':' . $result_value->instance_id . ':fallback';
										$shipping_default_zones[] = array(
											'shipping_default_zone_title' => $title,
											'shipping_default_zone_id' => $id,
										);
									}
								}

								if ( 'wbs' == $default_shipping_method->id && is_plugin_active( 'weight-based-shipping-for-woocommerce/plugin.php' ) ) {
									// Rest of the World shipping zone with  Weight based Shippng plugin by weightbasedshipping.com
									$wbs_settings = get_option( 'wbs_' . $result_value->instance_id . '_config' );
									if ( isset( $wbs_settings['enabled'] ) && $wbs_settings['enabled'] == 1 ) {
										foreach ( $wbs_settings['rules'] as $rk => $rv ) {
											if ( isset( $rv['meta']['enabled'] ) && $rv['meta']['enabled'] == 1 ) {
												$package_title = $rv['meta']['title'];
												$package_id    = self::orddd_get_shipping_package_id( $package_title );

												$title = 'Rest of the World' . ' -> ' . $value->title . ' -> ' . $package_title;
												$id    = $default_shipping_method->id . ':' . $result_value->instance_id . ':' . $package_id;

												$shipping_default_zones[] = array(
													'shipping_default_zone_title' => $title,
													'shipping_default_zone_id' => $id,
												);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		// Fetch the shipping packages from Weight Based Shipping for WooCommerce plugin
		if ( is_plugin_active( 'weight-based-shipping-for-woocommerce/plugin.php' ) ) {
			$wbs_settings = get_option( 'wbs_config' );
			if ( isset( $wbs_settings['enabled'] ) && $wbs_settings['enabled'] == 1 ) {
				foreach ( $wbs_settings['rules'] as $rk => $rv ) {
					if ( isset( $rv['meta']['enabled'] ) && $rv['meta']['enabled'] == 1 ) {
						$package_title = $rv['meta']['title'];
						$package_id    = self::orddd_get_shipping_package_id( $package_title );

						$title = 'Weight Based Shipping -> ' . $package_title;
						$id    = 'wbs:' . $package_id;

						$shipping_default_zones[] = array(
							'shipping_default_zone_title' => $title,
							'shipping_default_zone_id'    => $id,
						);
					}
				}
			}
		}

		// Fetch the shipping methods from FedEx plugin from X-Adapter
		$wf_fedex_settings = get_option( 'woocommerce_wf_fedex_woocommerce_shipping_settings' );
		$fedex_services    = array();
		if ( $wf_fedex_settings != '' && $wf_fedex_settings != '[]' && $wf_fedex_settings != '{}' && $wf_fedex_settings != null ) {
			$fedex_services = $wf_fedex_settings['services'];
		}
		foreach ( $fedex_services as $fedex_services_key => $fedex_services_value ) {
			$fedex_services_enabled = $fedex_services_value['enabled'];
			if ( '1' == $fedex_services_enabled ) {
				$fedex_services_name = $fedex_services_value['name'];
				if ( '' == $fedex_services_name ) {
					$fedex_services_name = orddd_common::orddd_get_fedex_service_name( $fedex_services_key );
				}
				$title                    = $fedex_services_name;
				$id                       = 'wf_fedex_woocommerce_shipping:' . $fedex_services_key;
				$shipping_default_zones[] = array(
					'shipping_default_zone_title' => $title,
					'shipping_default_zone_id'    => $id,
				);
			}
		}

		// Fetch Shipping methods from WooCommerce Table Rate shipping plugin.
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . "woocommerce_shipping_zone_shipping_methods'" );
		if ( is_array( $table_exists ) && count( $table_exists ) > 0 && is_plugin_active( 'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php' ) ) {
			$shipping_zone_methods = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->prefix . 'woocommerce_shipping_zone_shipping_methods`' );
			foreach ( $shipping_zone_methods as $shipping_key => $shipping_value ) {
				$option_settings = get_option( 'woocommerce_table_rate-' . $shipping_value->shipping_method_id . '_settings' );
				if ( isset( $option_settings['enabled'] ) && $option_settings['enabled'] != 'no' ) {
					if ( isset( $option_settings['calculation_type'] ) && $option_settings['calculation_type'] == '' ) {
						$shipping_zone_classes = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->prefix . "woocommerce_shipping_table_rates` WHERE shipping_method_id = {$shipping_value->shipping_method_id} ORDER BY rate_order ASC" );
						foreach ( $shipping_zone_classes as $key => $value ) {
							$shipping_classes[] = array(
								'shipping_method_title' => $option_settings['title'],
								'shipping_method_id'    => $shipping_value->shipping_method_id,
								'rate_label'            => $value->rate_label,
								'rate_id'               => $value->rate_id,
							);
						}
					} else {
						$shipping_classes[] = array(
							'shipping_method_title' => $option_settings['title'],
							'shipping_method_id'    => $shipping_value->shipping_method_id,
							'rate_label'            => '',
							'rate_id'               => '',
						);
					}
				}
			}
		}
		$shipping_methods = array();

		// Fetch Shipping Methods from Woocommerce Advanced Shipping plugin.
		$methods = get_posts(
			array(
				'posts_per_page' => '-1',
				'post_type'      => 'was',
				'post_status'    => array( 'publish' ),
				'order'          => 'ASC',
			)
		);
		if ( is_array( $methods ) && count( $methods ) > 0 && is_plugin_active( 'woocommerce-advanced-shipping/woocommerce-advanced-shipping.php' ) ) {
			foreach ( $methods as $method ) {
				$method_details = get_post_meta( $method->ID, '_was_shipping_method', true );
				if ( isset( $method_details['shipping_title'] ) && $method_details['shipping_title'] != '' ) {
					$method_title = $method->post_title . ' -> ' . $method_details['shipping_title']; // Display the shipping method name along with title.
				} else {
					$method_title = $method->post_title;
				}
				$shipping_methods[] = array(
					'title'      => $method_title,
					'method_key' => $method->ID,
				);
			}
		}

		// Advanced Flat Rate Shipping Method WooCommerce by Multidots.
		$methods = get_posts(
			array(
				'posts_per_page' => '-1',
				'post_type'      => 'wc_afrsm',
				'post_status'    => array( 'publish' ),
				'order'          => 'ASC',
			)
		);

		if ( is_array( $methods ) && count( $methods ) > 0 && ( is_plugin_active( 'woo-extra-flat-rate/advanced-flat-rate-shipping-for-woocommerce.php' ) || is_plugin_active( 'advanced-flat-rate-shipping-for-woocommerce/advanced-flat-rate-shipping-for-woocommerce.php' ) ) ) {
			foreach ( $methods as $method ) {
				$method_title       = $method->post_title;
				$shipping_methods[] = array(
					'title'      => $method_title,
					'method_key' => 'advanced_flat_rate_shipping:' . $method->ID,
				);
			}
		}

		 // Fetch Shipping Classes
		$args_shipping_class = array(
			'hide_empty' => 0,
		);

		$default_shipping_classes = get_terms( 'product_shipping_class', $args_shipping_class );

		foreach ( $active_methods as $method_key => $method_name ) {
			$shipping_methods[] = array(
				'title'      => $method_name['title'],
				'method_key' => $method_key,
			);
		}

		foreach ( $shipping_default_zones as $zone_key => $zone_value ) {
			$shipping_methods[] = array(
				'title'      => $zone_value['shipping_default_zone_title'],
				'method_key' => $zone_value['shipping_default_zone_id'],
			);
		}

		foreach ( $default_shipping_classes as $class_key => $class_value ) {
			$shipping_methods[] = array(
				'title'      => $class_value->name,
				'method_key' => $class_value->slug,
			);
		}

		foreach ( $shipping_classes as $key => $value ) {
			if ( $value['rate_id'] != '' ) {
				$selected_value = 'table_rate-' . $value['shipping_method_id'] . ' : ' . $value['rate_id'];
				if ( $value['rate_label'] != '' ) {
					$label = $value['shipping_method_title'] . ' > ' . $value['rate_label'];
				} else {
					$label = $value['shipping_method_title'];
				}
			} else {
				$selected_value = 'table_rate-' . $value['shipping_method_id'];
				$label          = $value['shipping_method_title'];
			}

			if ( in_array( $selected_value, $shipping_methods_stored ) ) {
				echo '<option value="' . $selected_value . '" selected>' . __( $label, 'order-delivery-date' ) . '</option>';
			} else {
				echo '<option value="' . $selected_value . '">' . __( $label, 'order-delivery-date' ) . '</option>';
			}
		}

		 // Returns the other shipping methods for the default zones in WooCommerce. 
        // For example, shipping packages from the Advance Shipping packages for WooCommerce plugin.
        return apply_filters( 'orddd_custom_setting_shipping_methods', $shipping_methods );
	}

	/**
	 * Returs the shipping package if for the shipping packages added from the
	 * Weight based Shippng plugin by weightbasedshipping.com
	 *
	 * @param string $package_tile - Title of the shipping package
	 *
	 * @since 9.7
	 */
	public static function orddd_get_shipping_package_id( $package_title ) {
		$idParts   = array();
		$hash      = substr( md5( $package_title ), 0, 8 );
		$idParts[] = $hash;
		$slug      = strtolower( $package_title );
		$slug      = preg_replace( '/[^a-z0-9]+/', '_', $slug );
		$slug      = preg_replace( '/_+/', '_', $slug );
		$slug      = trim( $slug, '_' );
		if ( $slug !== '' ) {
			$idParts[] = $slug;
		}
		$package_id = join( '_', $idParts );
		return $package_id;
	}

	/**
	 * Callback for adding custom Appearance setting
	 */
	public static function orddd_shipping_based_appearance_section_callback() {}

	/**
	 * Callback for setting the delivery date field label on the checkout
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_delivery_date_field_label_callback( $args ) {
		$row_id               = $date_field_label = '';
		$shipping_methods_arr = array();
		if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'shipping_based' ) && ( isset( $_GET['mode'] ) && $_GET['mode'] == 'edit' ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		if ( isset( $shipping_methods_arr['orddd_shipping_based_delivery_date_field_label'] ) ) {
			$date_field_label = $shipping_methods_arr['orddd_shipping_based_delivery_date_field_label'];
		}

		echo '<input type="text" name="orddd_shipping_based_settings_' . $option_key . '[orddd_shipping_based_delivery_date_field_label]" id="orddd_shipping_based_delivery_date_field_label" value="' . $date_field_label . '" maxlength="40"/>';

		$html = '<label for="orddd_shipping_based_delivery_date_field_label"> ' . $args[0] . '</label>';
		echo $html;
	}


	/**
	 * Callback for setting the timeslots field label on the checkout
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.8.3
	 */
	public static function orddd_shipping_based_delivery_timeslot_field_label_callback( $args ) {
		$row_id               = $time_field_label = '';
		$shipping_methods_arr = array();
		if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'shipping_based' ) && ( isset( $_GET['mode'] ) && $_GET['mode'] == 'edit' ) ) {
			if ( isset( $_GET['row_id'] ) ) {
				$row_id               = $_GET['row_id'];
				$shipping_methods_arr = get_option( 'orddd_shipping_based_settings_' . $row_id );
			}
		}

		$option_key = orddd_common::get_shipping_setting_option_key( $row_id );

		if ( isset( $shipping_methods_arr['orddd_shipping_based_delivery_timeslot_field_label'] ) ) {
			$time_field_label = $shipping_methods_arr['orddd_shipping_based_delivery_timeslot_field_label'];
		}

		echo '<input type="text" name="orddd_shipping_based_settings_' . $option_key . '[orddd_shipping_based_delivery_timeslot_field_label]" id="orddd_shipping_based_delivery_timeslot_field_label" value="' . $time_field_label . '" maxlength="40"/>';

		$html = '<label for="orddd_shipping_based_delivery_timeslot_field_label"> ' . $args[0] . '</label>';
		echo $html;
	}


	/**
	 * Callback for activating or deactivating a custom delivery schedule
	 *
	 * @param int custom_setting_id - id of the custom delivery schedule to activate or deactivate
	 * @param string current_state  - activate or deactivate, values: on / off
	 * @since 9.6
	 */
	public static function orddd_toggle_custom_setting_status() {

		if ( isset( $_POST['custom_setting_id'] ) &&
			'' != $_POST['custom_setting_id'] ) {

			$custom_setting_id = sanitize_text_field( $_POST['custom_setting_id'] );
			$current_state     = sanitize_text_field( $_POST['current_state'] );

			if ( 'on' === $current_state ) {
				delete_option( 'orddd_shipping_settings_status_' . $custom_setting_id );
			} elseif ( 'off' === $current_state ) {
				update_option( 'orddd_shipping_settings_status_' . $custom_setting_id, 'inactive' );
			}
		}
		wp_die();
	}
}
