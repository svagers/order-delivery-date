<?php
/**
 * Display General Settings -> Time slot settings in admin.
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.4
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Orddd_Time_Slot_Settings class
 *
 * @class Orddd_Time_Slot_Settings
 */
class Orddd_Time_Slot_Settings {

	/**
	 * Callback for adding Time slot tab settings.
	 */
	public static function orddd_time_slot_admin_settings_callback() { }

	/**
	 * Callback for adding Enable time slot setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_slot_enable_callback( $args ) {
		$enable_time_slot = '';
		if ( 'on' === get_option( 'orddd_enable_time_slot' ) ) {
			$enable_time_slot = 'checked';
		}
		?>
		<input type="checkbox" name="orddd_enable_time_slot" id="orddd_enable_time_slot" class="day-checkbox" <?php echo esc_attr( $enable_time_slot ); ?>/>
		<label for="orddd_enable_time_slot"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}


	/**
	 * Callback for adding Time slot field mandatory setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_slot_mandatory_callback( $args ) {
		?>
		<input type="checkbox" name="orddd_time_slot_mandatory" id="orddd_time_slot_mandatory" class="timeslot-checkbox" value="checked" <?php echo esc_attr( get_option( 'orddd_time_slot_mandatory' ) ); ?>/>
		<label for="orddd_time_slot_mandatory"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding As soon as possible option in time slot dropdown on checkout page
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 7.9
	 */
	public static function orddd_time_slot_asap_callback( $args ) {
		?>
		<input type="checkbox" name="orddd_time_slot_asap" id="orddd_time_slot_asap" class="timeslot-checkbox" value="checked" <?php echo esc_attr( get_option( 'orddd_time_slot_asap' ) ); ?> />
		<label for="orddd_time_slot_asap"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding Global lockout for Time slot setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_global_lockout_time_slots_callback( $args ) {
		?>
		<input type="number" min="0" step="1" name="orddd_global_lockout_time_slots" id="orddd_global_lockout_time_slots" value="<?php echo esc_attr( get_option( 'orddd_global_lockout_time_slots' ) ); ?>"/>
		<label for="orddd_global_lockout_time_slots"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding Show first available Time slot setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_show_first_available_time_slot_callback( $args ) {
		$orddd_show_select = '';
		if ( 'on' === get_option( 'orddd_auto_populate_first_available_time_slot' ) ) {
			$orddd_show_select = 'checked';
		}
		?>
		<input type='checkbox' name='orddd_auto_populate_first_available_time_slot' id='orddd_auto_populate_first_available_time_slot' value='on' <?php echo esc_attr( $orddd_show_select ); ?>>
		<label for="orddd_auto_populate_first_available_time_slot"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding Time slot settings Extra arguments containing label & class for the field
	 */
	public static function orddd_add_time_slot_admin_settings_callback() { }

	/**
	 * Callback to add time slots for weekday or specific dates
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_slot_for_delivery_days_callback( $args ) {
		global $orddd_weekdays;
		$orddd_time_slot_for_weekdays       = 'checked';
		$orddd_time_slot_for_specific_dates = '';
		if ( 'weekdays' === get_option( 'orddd_time_slot_for_delivery_days' ) ) {
			$orddd_time_slot_for_weekdays       = 'checked';
			$orddd_time_slot_for_specific_dates = '';
		} elseif ( 'specific_dates' === get_option( 'orddd_time_slot_for_delivery_days' ) ) {
			$orddd_time_slot_for_specific_dates = 'checked';
			$orddd_time_slot_for_weekdays       = '';
		}

		?>
		<p><label><input type="radio" name="orddd_time_slot_for_delivery_days" id="orddd_time_slot_for_delivery_days" value="weekdays"<?php echo esc_attr( $orddd_time_slot_for_weekdays ); ?>/><?php esc_html_e( 'Weekdays', 'order-delivery-date' ); ?></label>
		<label><input type="radio" name="orddd_time_slot_for_delivery_days" id="orddd_time_slot_for_delivery_days" value="specific_dates"<?php echo esc_attr( $orddd_time_slot_for_specific_dates ); ?>/><?php esc_html_e( 'Specific Dates', 'order-delivery-date' ); ?></label></p>
		<script type="text/javascript" language="javascript">
		<?php
		if ( 'on' !== get_option( 'orddd_enable_specific_delivery_dates' ) ) {
			?>
			jQuery( document ).ready( function() {
				jQuery( "input[type=radio][id=\"orddd_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );
			});
			<?php
		}
		$alldays = array();
		foreach ( $orddd_weekdays as $n => $day_name ) {
			$alldays[ $n ] = get_option( $n );
		}

		$alldayskeys = array_keys( $alldays );
		$checked     = 'No';
		foreach ( $alldayskeys as $key ) {
			if ( 'checked' === $alldays[ $key ] ) {
				$checked = 'Yes';
			}
		}
		?>
		</script> 
		<label for="orddd_time_slot_for_delivery_days"><?php echo wp_kses_post( $args[0] ); ?></label>
		?>
		<script type='text/javascript'>
			jQuery( document ).ready( function(){
				if ( jQuery( "input[type=radio][id=\"orddd_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).is(":checked") ) {
					jQuery( '.time_slot_options' ).slideUp();
					jQuery( '.time_slot_for_weekdays' ).slideDown();
				} else {
					jQuery( '.time_slot_options' ).slideDown();
					jQuery( '.time_slot_for_weekdays' ).slideUp();
				}
				jQuery( '.orddd_time_slot_for_weekdays' ).select2();
				jQuery( '.orddd_time_slot_for_weekdays' ).css({'width': '300px' });
				jQuery( "input[type=radio][id=\"orddd_time_slot_for_delivery_days\"]" ).on( 'change', function() {
					if ( jQuery( this ).is(':checked') ) {
						var value = jQuery( this ).val();
						jQuery( '.time_slot_options' ).slideUp();
						jQuery( '.time_slot_for_' + value ).slideDown();
					}
				})
			});
		</script>
		<?php
	}

	/**
	 * Callback for adding Weekdays for Time slot setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_slot_for_weekdays_callback( $args ) {
		global $orddd_weekdays;
		foreach ( $orddd_weekdays as $n => $day_name ) {
			$alldays[ $n ] = get_option( $n );
		}
		$alldayskeys = array_keys( $alldays );
		$checked     = 'No';
		foreach ( $alldayskeys as $key ) {
			if ( 'checked' === $alldays[ $key ] ) {
				$checked = 'Yes';
			}
		}

		printf(
			'<div class="time_slot_options time_slot_for_weekdays">
             <select class="orddd_time_slot_for_weekdays" id="orddd_time_slot_for_weekdays" name="orddd_time_slot_for_weekdays[]" multiple="multiple" placeholder="Select Weekdays">
                <option name="all" value="all">All</option>'
		);
		$weekdays_arr = array();
		foreach ( $orddd_weekdays as $n => $day_name ) {
			if ( 'checked' === get_option( $n ) ) {
				$weekdays[ $n ] = $day_name;
				printf( '<option name="' . esc_attr( $n ) . '" value="' . esc_attr( $n ) . '">' . esc_attr( $weekdays[ $n ] ) . '</option>' );
			}
		}

		if ( 'No' === $checked ) {
			foreach ( $orddd_weekdays as $n => $day_name ) {
				$weekdays[ $n ] = $day_name;
				printf( '<option name="' . esc_attr( $n ) . '" value="' . esc_attr( $n ) . '">' . esc_attr( $weekdays[ $n ] ) . '</option>' );
			}
		}
		print( '</select></div>' );

		if ( 'on' !== get_option( 'orddd_enable_specific_delivery_dates' ) ) {
			?>
			<script type="text/javascript" language="javascript">
				jQuery( document ).ready( function() {
					jQuery( '#orddd_select_delivery_dates' ).attr( "disabled", "disabled" );
				} );
			</script>
			<?php
		}

		printf(
			'<div class="time_slot_options time_slot_for_specific_dates">
            <select class="orddd_time_slot_for_weekdays" id="orddd_select_delivery_dates" name="orddd_select_delivery_dates[]" multiple="multiple" placeholder="Select Specific Delivery Dates" >'
		);

		$delivery_arr          = array();
		$delivery_dates_select = get_option( 'orddd_delivery_dates' );
		if ( '' !== $delivery_dates_select &&
			'{}' !== $delivery_dates_select &&
			'[]' !== $delivery_dates_select &&
			'null' !== $delivery_dates_select ) {
			$delivery_arr = json_decode( $delivery_dates_select );
		}
		foreach ( $delivery_arr as $key => $value ) {
			foreach ( $value as $k => $v ) {
				if ( 'date' === $k ) {
					$date            = explode( '-', $v );
					$date_to_display = gmdate( 'm-d-Y', gmmktime( 0, 0, 0, $date[0], $date[1], $date[2] ) );
					$temp_arr[ $k ]  = $date_to_display;
				} else {
					$temp_arr[ $k ] = $v;
				}
			}
			printf(
				'<option value=' . esc_attr( $temp_arr['date'] ) . '>' . esc_attr( $temp_arr['date'] ) . "</option>\n"
			);
		}
		printf( '</select></div>' );
		?>
		<label for="orddd_time_slot_for_weekdays"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for adding From hours for Time slot setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_from_hours_callback( $args ) {
		echo '<fieldset>
            <label for="orddd_time_from_hours">
                <select name="orddd_time_from_hours" id="orddd_time_from_hours" size="1">';
				// time options.
				$delivery_from_hours = get_option( 'orddd_delivery_from_hours' );
				$delivery_to_hours   = get_option( 'orddd_delivery_to_hours' );
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
				echo '</select>
                <label>&nbsp;' . esc_html__( 'Hours', 'order-delivery-date' ) . '</label>&nbsp&nbsp&nbsp;
                <select name="orddd_time_from_minutes" id="orddd_time_from_minutes" size="1">';
		for ( $i = 0; $i <= 59; $i++ ) {
			if ( $i < 10 ) {
				$i = '0' . $i;
			}
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
				echo '</select>
                <label>&nbsp;' . esc_html__( 'Minutes', 'order-delivery-date' ) . '</label>
            </label>';
		echo '<p>' . wp_kses_post( $args[0] ) . '</p></fieldset>';
	}

	/**
	 * Callback for adding To hours for Time slot setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_to_hours_callback( $args ) {
		echo '<fieldset>
            <label for="orddd_time_to_hours">
                <select name="orddd_time_to_hours" id="orddd_time_to_hours" size="1">';
				// time options.
				$delivery_from_hours = get_option( 'orddd_delivery_from_hours' );
				$delivery_to_hours   = get_option( 'orddd_delivery_to_hours' );
		for ( $i = 0; $i <= 23; $i++ ) {
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
				echo '</select>
                <label>&nbsp;' . esc_html__( 'Hours', 'order-delivery-date' ) . '</lable>&nbsp&nbsp&nbsp;
                <select name="orddd_time_to_minutes" id="orddd_time_to_minutes" size="1">';
		for ( $i = 0; $i <= 59; $i++ ) {
			if ( $i < 10 ) {
				$i = '0' . $i;
			}
			printf(
				"<option value='%s'>%s</option>\n",
				esc_attr( $i ),
				esc_attr( $i )
			);
		}
				echo '</select>
                <label>&nbsp;' . esc_html__( 'Minutes', 'order-delivery-date' ) . '</label>
            </label>';
		echo '<p>' . wp_kses_post( $args[0] ) . '</p></fieldset>';
	}

	/**
	 * Callback for adding Lockout Time slot after X orders setting
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_slot_lockout_callback( $args ) {
		?>
		<input type="number" min="0" step="1" name="orddd_time_slot_lockout" id="orddd_time_slot_lockout"/>
		<label for="orddd_time_slot_lockout"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback to add additional charges for a time slot
	 *
	 * @param array $args Extra arguments containing label & class for the field.
	 * @since 2.4
	 */
	public static function orddd_time_slot_additional_charges_callback( $args ) {
		?>
		<input type="text" name="orddd_time_slot_additional_charges" id="orddd_time_slot_additional_charges" placeholder="Charges"/>
		<input type="text" name="orddd_time_slot_additional_charges_label" id="orddd_time_slot_additional_charges_label" placeholder="Time slot Charges Label" />
		<label for="orddd_time_slot_additional_charges"><?php echo wp_kses_post( $args[0] ); ?></label>
		<?php
	}

	/**
	 * Callback for saving time slots
	 *
	 * @return string
	 * @since 2.4
	 */
	public static function orddd_delivery_time_slot_callback() {
		global $orddd_weekdays;
		foreach ( $orddd_weekdays as $n => $day_name ) {
			$alldays[ $n ] = get_option( $n );
		}
		$alldayskeys = array_keys( $alldays );

		$timeslot         = get_option( 'orddd_delivery_time_slot_log' );
		$timeslot_new_arr = array();
		if ( 'null' === $timeslot ||
			'' === $timeslot ||
			'{}' === $timeslot ||
			'[]' === $timeslot ) {
			$timeslot_arr = array();
		} else {
			$timeslot_arr = json_decode( $timeslot );
		}

		if ( isset( $timeslot_arr ) && is_array( $timeslot_arr ) && count( $timeslot_arr ) > 0 ) {
			foreach ( $timeslot_arr as $k => $v ) {
				$timeslot_new_arr[] = array(
					'tv'                       => $v->tv,
					'dd'                       => $v->dd,
					'lockout'                  => $v->lockout,
					'additional_charges'       => $v->additional_charges,
					'additional_charges_label' => $v->additional_charges_label,
					'fh'                       => $v->fh,
					'fm'                       => $v->fm,
					'th'                       => $v->th,
					'tm'                       => $v->tm,
				);
			}
		}

		if ( ( ! isset( $_POST['orddd_time_slot_for_weekdays'] ) && ! isset( $_POST['orddd_select_delivery_dates'] ) ) && isset( $_POST['orddd_time_from_hours'] ) && '0' !== $_POST['orddd_time_from_hours'] && isset( $_POST['orddd_time_to_hours'] ) && '0' !== $_POST['orddd_time_to_hours'] ) {
			add_settings_error( 'orddd_delivery_time_slot_log_error', 'time_slot_save_error', 'Please Select Delivery Days/Dates for the Time slot', 'error' );
		} else {
			$devel_dates = '';
			if ( isset( $_POST['orddd_time_slot_for_delivery_days'] ) ) {
				$time_slot_value = $_POST['orddd_time_slot_for_delivery_days'];
				if ( 'weekdays' === $time_slot_value ) {
					if ( isset( $_POST['orddd_time_slot_for_weekdays'] ) ) {
						$orddd_time_slot_for_weekdays = $_POST['orddd_time_slot_for_weekdays'];

						// Add all the individual enabled weekdays if 'all' is selected.
						if ( in_array( 'all', $orddd_time_slot_for_weekdays, true ) ) {
							$weekdays = array();
							foreach ( $alldayskeys as $key ) {
								if ( 'checked' === $alldays[ $key ] ) {
									array_push( $weekdays, $key );
								}
							}
						} else {
							$weekdays = $_POST['orddd_time_slot_for_weekdays'];
						}

						$devel_dates = wp_json_encode( $weekdays );
					}
				} elseif ( 'specific_dates' === $time_slot_value ) {
					if ( isset( $_POST['orddd_select_delivery_dates'] ) ) {
						$devel_dates_arr = $_POST['orddd_select_delivery_dates'];
						$dates_arr       = array();
						foreach ( $devel_dates_arr as $key => $value ) {
							$date              = explode( '-', $value );
							$date_to_store     = gmdate( 'n-j-Y', gmmktime( 0, 0, 0, $date[0], $date[1], $date[2] ) );
							$dates_arr[ $key ] = $date_to_store;
						}
						$devel_dates = wp_json_encode( $dates_arr );
					}
				}
			} else {
				$time_slot_value = '';
			}

			$from_hour                = 0;
			$from_minute              = 0;
			$to_hour                  = 0;
			$to_minute                = 0;
			$lockouttime              = '';
			$additional_charges       = '';
			$additional_charges_label = '';

			if ( isset( $_POST['orddd_time_from_hours'] ) ) {
				$from_hour = $_POST['orddd_time_from_hours'];
			}

			if ( isset( $_POST['orddd_time_from_minutes'] ) ) {
				$from_minute = $_POST['orddd_time_from_minutes'];
			}

			if ( isset( $_POST['orddd_time_to_hours'] ) ) {
				$to_hour = $_POST['orddd_time_to_hours'];
			}

			if ( isset( $_POST['orddd_time_to_minutes'] ) ) {
				$to_minute = $_POST['orddd_time_to_minutes'];
			}

			if ( isset( $_POST['orddd_time_slot_lockout'] ) ) {
				$lockouttime = $_POST['orddd_time_slot_lockout'];
			}

			if ( isset( $_POST['orddd_time_slot_additional_charges'] ) ) {
				$additional_charges = $_POST['orddd_time_slot_additional_charges'];
			}

			if ( isset( $_POST['orddd_time_slot_additional_charges_label'] ) ) {
				$additional_charges_label = $_POST['orddd_time_slot_additional_charges_label'];
			}

			$from_hour_new   = gmdate( 'G', gmmktime( $from_hour, $from_minute, 0, gmdate( 'm' ), gmdate( 'd' ), gmdate( 'Y' ) ) );
			$from_minute_new = gmdate( 'i ', gmmktime( $from_hour, $from_minute, 0, gmdate( 'm' ), gmdate( 'd' ), gmdate( 'Y' ) ) );
			$to_hour_new     = gmdate( 'G', gmmktime( $to_hour, $to_minute, 0, gmdate( 'm' ), gmdate( 'd' ), gmdate( 'Y' ) ) );
			$to_minute_new   = gmdate( 'i ', gmmktime( $to_hour, $to_minute, 0, gmdate( 'm' ), gmdate( 'd' ), gmdate( 'Y' ) ) );

			$timeslot_present = 'no';
			foreach ( $timeslot_new_arr as $key => $value ) {

				$fh = $value['fh'];
				$fm = $value['fm'];
				$th = $value['th'];
				$tm = $value['tm'];

				if ( 'weekdays' === $value['tv'] &&
					gettype( json_decode( $value['dd'] ) ) === 'array' &
					count( json_decode( $value['dd'] ) ) > 0 ) {
					$dd = json_decode( $value['dd'] );

					if ( 'all' === $dd[0] &&
						$fh === $from_hour_new &&
						$fm === $from_minute_new &&
						$th === $to_hour_new &&
						$tm === $to_minute_new ) {
						$timeslot_present = 'yes';
						break;
					} else {
						foreach ( $dd as $id => $day ) {
							if ( isset( $_POST['orddd_time_slot_for_weekdays'] ) &&
							in_array( $day, $_POST['orddd_time_slot_for_weekdays'], true ) &&
							$fh === $from_hour_new &&
							$fm === $from_minute_new &&
							$th === $to_hour_new &&
							$tm === $to_minute_new ) {
								$timeslot_present = 'yes';
								break;

							}
						}
					}
				} elseif ( 'specific_dates' === $value['tv'] ) {
					$dd = json_decode( $value['dd'] );
					foreach ( $dd as $id => $day ) {
						if ( isset( $_POST['orddd_select_delivery_dates'] ) &&
						in_array( $day, $_POST['orddd_select_delivery_dates'], true ) &&
						$fh === $from_hour_new &&
						$fm === $from_minute_new &&
						$th === $to_hour_new &&
						$tm === $to_minute_new ) {
							$timeslot_present = 'yes';
							break;

						}
					}
				}
			}

			if ( 'no' === $timeslot_present ) {
				if ( $from_hour_new !== $to_hour_new || $from_minute_new !== $to_minute_new ) {
					$timeslot_new_arr[] = array(
						'tv'                       => $time_slot_value,
						'dd'                       => $devel_dates,
						'lockout'                  => $lockouttime,
						'additional_charges'       => $additional_charges,
						'additional_charges_label' => $additional_charges_label,
						'fh'                       => $from_hour_new,
						'fm'                       => $from_minute_new,
						'th'                       => $to_hour_new,
						'tm'                       => $to_minute_new,
					);
				}
			}
		}
		$timeslot_jarr = wp_json_encode( $timeslot_new_arr );
		return $timeslot_jarr;
	}
}
