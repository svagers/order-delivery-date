<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Availability Widget added to show the available delivery dates in the calendar on the frontend. 
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Frontend/Widgets
 * @since       8.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class for adding availability widget on the frontend
 *
 * @class orddd_widget
 */

class orddd_widget {
    
    /**
     * Default Constructor
     * 
     * @since 8.6
     */
    public function __construct() {
        // Register and load the widget
        add_action( 'widgets_init', array( &$this, 'orddd_load_widget' ) );
        add_action( 'wp_ajax_nopriv_orddd_show_availability_calendar', array( &$this, 'orddd_show_availability_calendar' ), 10, 1 );
        add_action( 'wp_ajax_orddd_show_availability_calendar', array( &$this, 'orddd_show_availability_calendar' ), 10, 1 );
    }
    
    /**
     * Registers the Availability Widget
     * 
     * @hook orddd_load_widget
     * @since 8.6
     */
    public function orddd_load_widget() {
        register_widget( 'orddd_availability_widget' );
    }

    /**
     * Updates the availability of the dates based on the postcode when the Show availability button is clicked.
     *
     * @hook wp_ajax_nopriv_orddd_show_availability_calendar
     * @hook wp_ajax_orddd_show_availability_calendar
     * @since 8.6 
     */
    public function orddd_show_availability_calendar() {
        $zone_details = explode( "-", orddd_common::orddd_get_zone_id( '', false ) );
        $shipping_method = $zone_details[ 1 ];
        $partially_booked_dates_str = self::get_partially_booked_dates( $shipping_method );
        echo $shipping_method . "&" . $partially_booked_dates_str;
        die();
    }
    
    /**
     * Returns the availability of the dates. 
     * Dates will be returned as partially booked dates if one or more orders are placed. 
     * Else, it will be returned as Fully Available date. 
     *
     * If the lockout is set to zero or blank, on hover of date, 'Unlimited' will be shown.
     * Else, the remaining orders for that date. i.e.  
     *
     * @since 8.6
     */
    public static function get_partially_booked_dates( $shipping_method, $shipping_settings = array() ) {
        global $wpdb;     
        
        $gmt = false;
        if( has_filter( 'orddd_gmt_calculations' ) ) {
            $gmt = apply_filters( 'orddd_gmt_calculations', '' );
        }
        $current_time = current_time( 'timestamp', $gmt );
        $current_date = date( "j-n-Y", $current_time );

        $time_format_to_show = orddd_common::orddd_get_time_format(); 
        $available_deliveries = '';   
		$partially_lockout_dates = '';
        $shipping_settings_to_check = array();
        $is_custom_enabled = 'no';
        if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' ) {

            //Fetch Custom Delivery Settings for which dates should be checked for partially booked dates. 
            if( '' != $shipping_method ) {
                $results = orddd_common::orddd_get_shipping_settings();
                if( is_array( $results ) && count( $results ) > 0 && $shipping_method != '' ) {
                    foreach ( $results as $key => $value ) {
                        $shipping_methods = array();
                        $shipping_settings = get_option( $value->option_name );
                        if( isset( $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] ) &&
                            $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'shipping_methods' ) {
                            if( isset( $shipping_settings[ 'shipping_methods' ] ) && in_array( $shipping_method, $shipping_settings[ 'shipping_methods' ] ) ) {
                                $shipping_settings_to_check = $shipping_settings;
                            }
                        }
                    }
                }
            } else if( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 ) {
                $shipping_settings_to_check = $shipping_settings;
            }

            if( is_array( $shipping_settings_to_check ) && count( $shipping_settings_to_check ) > 0 ) {
                // Partially booked dates and available dates for the time sots of Custom Delivery Settings
                if( isset( $shipping_settings_to_check[ 'time_slots' ] ) && $shipping_settings_to_check[ 'time_slots' ] != '' ) { 

                    // Get Minimum Delivery Time Data 
                    $minimum_delivery_time = 0;
                    if( isset( $shipping_settings_to_check[ 'minimum_delivery_time' ] ) ) {
                        $minimum_delivery_time = $shipping_settings_to_check[ 'minimum_delivery_time' ];
                        if( '' == $minimum_delivery_time ) {
                            $minimum_delivery_time = 0;
                        }
                    } 

                    $delivery_time_seconds = $minimum_delivery_time * 60 * 60;
                    $holidays_str = orddd_common::orddd_get_custom_holidays( $shipping_settings_to_check );
                    $lockout_days_str = orddd_common::orddd_get_custom_lockout_days( $shipping_settings_to_check );

                    //Time Settings
                    $time_slider_enabled = '';
                    $from_hours = '';
                    $from_mins = '';
                    $to_hours = '';
                    $to_mins = '';
                    if( isset( $shipping_settings_to_check[ 'time_settings' ] ) ) {
                        $time_settings = $shipping_settings_to_check[ 'time_settings' ];
                        if( isset( $time_settings[ 'from_hours' ] ) && $time_settings[ 'from_hours' ] != 0
                            && isset( $time_settings[ 'to_hours' ] ) && $time_settings[ 'to_hours' ] != 0 ) {
                            $from_hour_values = orddd_common::orddd_get_shipping_from_time( $time_settings, $shipping_settings_to_check, $holidays_str, $lockout_days_str );
                            if( is_array( $from_hour_values ) && count( $from_hour_values ) ) {
                                $from_hours = $from_hour_values[ 'from_hours' ];
                                $from_mins = $from_hour_values[ 'from_minutes' ];
                            }
                            
                            $to_hours = $time_settings[ 'to_hours' ];
                            $to_mins = $time_settings[ 'to_mins' ];

                            $time_slider_enabled = 'on';
                        } 
                    }

                    // Fetch the first available date after calculating the minimum delivery time.
                    // Time is used to check for the available timeslots for that date.
                    $min_date_array = orddd_common::get_min_date( $delivery_time_seconds, 
                                                                array( 'enabled' => $time_slider_enabled, 
                                                                     'from_hours' => $from_hours, 
                                                                     'from_mins' => $from_mins, 
                                                                     'to_hours' => $to_hours, 
                                                                     'to_mins' => $to_mins ), 
                                                                $holidays_str, 
                                                                $lockout_days_str, 
                                                                $shipping_settings );

                    $lockout_arr             = array();
                    $lockout_time_arr        = array();
                    $date                    = array();
                    $previous_orders         = 0;
                    $specific_dates          = array();
                    $delivery_days           = array();
    
                    $time_slots = explode( '},', $shipping_settings_to_check[ 'time_slots' ] );
                    // Sort the multidimensional array
                    usort( $time_slots, array( 'orddd_common', 'orddd_custom_sort' ) );
                    foreach( $time_slots as $tk => $tv ) {
                        if( $tv != '' ) {
                            $timeslot_values = orddd_common::get_timeslot_values( $tv );
                            if( is_array( $timeslot_values[ 'selected_days' ] ) ) {
                                $time_slot_arr = explode( ' - ',  $timeslot_values[ 'time_slot' ] );
                                $from_time_hour_arr = explode( ":", $time_slot_arr[ 0 ] );
                                
                                //Convert the time slot in the selected time format.
                                $from_time = date( $time_format_to_show, strtotime( trim( $time_slot_arr[ 0 ] ) ) );
                                $from_time_arr = explode( ":", $from_time );
                                if( isset( $time_slot_arr[ 1 ] ) ) {
                                    $to_time = date( $time_format_to_show, strtotime( trim( $time_slot_arr[ 1 ] ) ) );
                                    $time_slot = $from_time . " - " . $to_time;    
                                } else {
                                    $time_slot = $from_time;
                                }
                                
                                if ( $timeslot_values[ 'delivery_days_selected' ] == 'weekdays' ) {
                                    foreach( $timeslot_values[ 'selected_days' ] as $dkey => $dval ) {
                                        if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                            $delivery_days[ $dval ][ $time_slot ] = $timeslot_values[ 'lockout' ];
                                        } else if ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                            $delivery_days[ $dval ][ $time_slot ] = get_option( 'orddd_global_lockout_time_slots' );
                                        } else {
                                            $delivery_days[ $dval ][ $time_slot ] = 0;
                                        }
                                    }
                                } else if ( $timeslot_values[ 'delivery_days_selected' ] == 'specific_dates' ) {
                                    foreach( $timeslot_values[ 'selected_days' ] as $dkey => $dval ) {
                                        if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                            $specific_dates[ $dval ][ $time_slot ] = $timeslot_values[ 'lockout' ];
                                        } else if ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                            $specific_dates[ $dval ][ $time_slot ] = get_option( 'orddd_global_lockout_time_slots' );
                                        } else {
                                            $specific_dates[ $dval ][ $time_slot ] = 0;
                                        }
                                    }
                                }
                                    
                                $min_time_on_last_slot = apply_filters( 'orddd_min_delivery_on_last_slot', false );
                                if( $min_time_on_last_slot ) {
                                    if( $delivery_time_seconds == 0 ) {
                                        $last_slot     =   date( 'G:i', current_time('timestamp') );
                                    } else {
                                        $last_slot     =   $min_date_array[ 'min_hour' ] . ':' . $min_date_array[ 'min_minute' ];
                                    }

                                    $ordd_date_two =   $min_date_array[ 'min_date' ] . " " . $last_slot;
                                    $current_date = date( 'j-n-Y', current_time('timestamp') );
                                    $date1  =   date( 'j-n-Y', current_time('timestamp') ) ." ". $to_time;
                                    $date3 =   new DateTime( $date1 );

                                    $date3str = strtotime( $date1 );
                                    $date2str = strtotime( $ordd_date_two );
                    
                                    if( ( $current_date == $min_date_array['min_date'] && $date2str < $date3str ) || $current_date != $min_date_array['min_date'] ) {
                                        $min_date = $min_date_array[ 'min_date' ];
                                        if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                            $specific_dates[ $min_date ][ $time_slot ] = $timeslot_values[ 'lockout' ];
                                        } else if ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                            $specific_dates[ $min_date ][ $time_slot ] = get_option( 'orddd_global_lockout_time_slots' );
                                        } else {
                                            $specific_dates[ $min_date ][ $time_slot ] = 0;
                                        }   
                                    }
                                } else {
                                    if( isset( $from_time_hour_arr[ 0 ] ) && 
                                    ( $from_time_hour_arr[ 0 ] > $min_date_array[ 'min_hour' ] || 
                                        ( $from_time_hour_arr[ 0 ] == $min_date_array[ 'min_hour' ] && 
                                            isset( $from_time_hour_arr[ 1 ] ) && 
                                            $from_time_hour_arr[ 1 ] > $min_date_array[ 'min_minute' ] 
                                        ) 
                                    ) ) {

                                        $min_date = $min_date_array[ 'min_date' ];
                                        if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                            $specific_dates[ $min_date ][ $time_slot ] = $timeslot_values[ 'lockout' ];
                                        } else if ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                            $specific_dates[ $min_date ][ $time_slot ] = get_option( 'orddd_global_lockout_time_slots' );
                                        } else {
                                            $specific_dates[ $min_date ][ $time_slot ] = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $lockout_time_arr = array();
                    $lockout_time = '';

                    if( isset( $shipping_settings_to_check[ 'orddd_lockout_time_slot' ] ) ) {
                        $lockout_time = $shipping_settings_to_check[ 'orddd_lockout_time_slot' ];
                    }

                    if ( $lockout_time != '' && $lockout_time != '{}' && $lockout_time != '[]' && $lockout_time!= 'null' ) {
                        $lockout_time_arr = json_decode( $lockout_time );
                    }
        
                    foreach ( $lockout_time_arr as $k => $v ) {
                        $lockout_time_slot = explode( " - ", $v->t );
                        $lockout_from_time = date( "G:i", strtotime( $lockout_time_slot[ 0 ] ) );
                        if( isset( $lockout_time_slot[1] ) )  {
                            $lockout_to_time = date( "G:i", strtotime( $lockout_time_slot[ 1 ] ) );
                            $lockout_time_str = $lockout_from_time . " - " . $lockout_to_time;
                        } else {
                            $lockout_time_str = $lockout_from_time;
                        }
    
                        $weekday = date( 'w', strtotime( $k ) );
                        $date_str = date( 'j-n-Y', strtotime( $v->d ) );
                        if ( array_key_exists( $date_str, $date ) ) {
                            if( isset( $date[ $date_str ][ $v->t ] ) ) {
                                $previous_orders =  $date[ $date_str ][ $v->t ] + $v->o;
                                $date[ $date_str ][ $v->t ] = $previous_orders;    
                            }else{
                                $date[ $date_str ][ $v->t ] = $v->o; //add the number of orders if timeslot is not set
                            }
                        } else {
                            $date[ $date_str ][ $v->t ] = $v->o;
                        }
                    }
                    
                    $partially_lockout_dates .= "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "--'," ;
                    $available_deliveries = "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "'," ;

                    foreach( $date as $dk => $dv ) {
                        $available_timeslot_deliveries = '';
                        $lockout_date_arr = explode( "-", $dk );
                        $date_lockout_time = strtotime( $dk );

                        $time_arr = array();
                        //create an array with time and lockout with the set time format.
                        foreach( $dv as $time => $lockout ) {   
                            $time_format = orddd_common::orddd_change_time_slot_format( $time, $time_format_to_show );
                            $time_arr[$time_format] = $lockout;
                        }
                       

                        if( $date_lockout_time >= strtotime( $current_date ) ) {
                            $lockout_date = $lockout_date_arr[1] . "-" . $lockout_date_arr[ 0 ] . "-" . $lockout_date_arr[2];

                            if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
                                if ( isset( $specific_dates[ $lockout_date ] ) ) {
                                    $time_slots = $specific_dates[ $lockout_date ];
                                    foreach( $time_slots as $tk => $tv ) {
                                        if( $tv == 0 ) {
                                            $available_timeslot_deliveries .= $tk . ": " . __( 'Unlimited', '   order-delivery-date' ) . '--';
                                        } else {
                                            if( isset( $time_arr[ $tk ] ) && $tv >= $time_arr[ $tk ] ) {
                                                if( ( $tv - $time_arr[ $tk ] ) > 0 ) {
                                                    $available_timeslot_deliveries .= $tk . ": " . ( $tv - $time_arr[ $tk ] ). "--";    
                                                }
                                            } else {
                                                $available_timeslot_deliveries .= $tk . ": " . $tv . "--";
                                            }
                                        }
                                    }
                                    $partially_lockout_dates .= "'" . $lockout_date . ">" . $available_timeslot_deliveries . "',";
                                }
                            }
                            
                            if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {

                                $weekday = date( 'w', $date_lockout_time );

                                if ( isset( $delivery_days[ 'orddd_weekday_' . $weekday . '_custom_setting' ] ) ) {
                                    $time_slots = $delivery_days[ 'orddd_weekday_' . $weekday . '_custom_setting' ];
                                } else if( isset( $delivery_days[ 'all' ] ) ) {
                                    $time_slots = $delivery_days[ 'all' ];
                                }
                                
                                foreach( $time_slots as $tk => $tv ) {
                                    if( $tv == 0 ) {
                                        $available_timeslot_deliveries .= $tk . ": " . __( 'Unlimited', 'order-delivery-date' ) . '--';
                                        $available_deliveries .="'" . $lockout_date . ">". $tk . ": " . __( 'Unlimited', 'order-delivery-date' ) . "',";
                                    } else {                                        
                                        if( isset( $time_arr[ $tk ] ) && $tv >= $time_arr[ $tk ] ) {

                                            if( ( $tv - $time_arr[ $tk ] ) > 0 ) {
                                                $available_timeslot_deliveries .= $tk . ": " . ( $tv - $time_arr[ $tk ] ). "--";    
                                                $available_deliveries .="'" . $lockout_date . ">". $tk . ": " . ( $tv - $time_arr[ $tk ] ) . "',";

                                            }
                                        } else {
                                            $available_timeslot_deliveries .= $tk . ": " . $tv . "--";
                                            $available_deliveries .="'" . $lockout_date . ">". $tk . ": " . $tv . "',";

                                        }
                                    }
                                }
                                $partially_lockout_dates .= "'" . $lockout_date . ">" . $available_timeslot_deliveries . "',";
                            }
                        }
                    }
    

                    if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
                        foreach ( $specific_dates as $s_key => $s_value ) {
                            $time_slots = $s_value;
                        
                            foreach( $time_slots as $tk => $tv ) {
                                if( $tv == 0 ) {
                                    $available_timeslot_deliveries = $tk . ": " . __( 'Unlimited', 'order-delivery-date' );
                                } else {
                                    $available_timeslot_deliveries = $tk . ": " . $tv;
                                }
                                $available_deliveries .= "'" . $s_key . ">" . $available_timeslot_deliveries . "',";
                            }
                        }
                    } 

                    if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {
                        foreach( $delivery_days as $del_days_key => $del_days_val ) {
                            if( $del_days_key == 'all' ) {
                                $time_slots = $delivery_days[ 'all' ];
                                for( $i = 0; $i < 7; $i++ ) {
                                    foreach( $time_slots as $tk => $tv ) {
                                        if( $tv == 0 ) {
                                            $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . __( 'Unlimited', 'order-delivery-date' ) . "',";            
                                        } else {
                                            $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . $tv . "',";    
                                        }
                                    }
                                }
                            } else {
                                $time_slots = $delivery_days[ $del_days_key ];
                                foreach( $time_slots as $tk => $tv ) {
                                    if( $tv == 0 ) {
                                        $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . __( 'Unlimited', 'order-delivery-date' ) . "',";            
                                    } else {
                                        $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . $tv . "',";
                                    }    
                                }
                            }
                        }
                    }
                } else {
                    // Partially Booked dates for only dates.
                    if( isset( $shipping_settings_to_check[ 'orddd_lockout_date' ] ) ) {
                        $lockout_date_array = $shipping_settings_to_check[ 'orddd_lockout_date' ];
                        if ( $lockout_date_array == '' || 
                             $lockout_date_array == '{}' || 
                             $lockout_date_array == '[]' || 
                             $lockout_date_array == 'null' ) {
                            $lockout_date_arr = array();
                        } else {
                            $lockout_date_arr = (array) json_decode( $lockout_date_array );
                        }
                    } else {
                        $lockout_date_arr = array();
                    }

                    $specific_dates = array();

                    if( isset( $shipping_settings_to_check['delivery_type']['specific_dates'] ) && 'on' == $shipping_settings_to_check['delivery_type']['specific_dates'] ) {
                        $delivery_dates = explode( ',', $shipping_settings_to_check['specific_dates'] );


                        foreach ( $lockout_date_arr as $k => $v ) {
                            $date = $v->d;
                            $lockout_date_split = explode( '-', $v->d );
                            $date_lockout_time = strtotime( $lockout_date_split[ 1 ] . "-" . $lockout_date_split[0] . "-" . $lockout_date_split[2] );
                            foreach( $delivery_dates as $key => $value ) {

                                if( $value != '' ) {
                                    $sv = str_replace( '}', '', $value );
                                    $sv = str_replace( '{', '', $sv );
                                    $specific_date_arr = explode( ':', $sv );
                                    $specific_dates[ $specific_date_arr[0] ] = $specific_date_arr[3];

                                    if( $date_lockout_time >= strtotime( $current_date ) && $date == $specific_date_arr[0] && $specific_date_arr[3] !== '' ) {
                                        $partially_lockout_dates .= "'" . $specific_date_arr[0] . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $specific_date_arr[3] - $v->o ) . "',";
                                        array_push( $specific_dates, $v->d );
                                    }else if( $date_lockout_time >= strtotime( $current_date ) && $date == $specific_date_arr[0] && $specific_date_arr[3] == ''){
                                        $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                                        array_push( $specific_dates, $v->d );
                                    }
                                }

                            }
                        }

                        foreach( $delivery_dates as $key => $value ) {
                            if( $value != '' ) {
                                $sv = str_replace( '}', '', $value );
                                $sv = str_replace( '{', '', $sv );
                                $specific_date_arr = explode( ':', $sv );
                                $specific_dates[ $specific_date_arr[0] ] = $specific_date_arr[3];
                            }

                        }
                    }
                    $date_custom_arr = array();

                    foreach ( $lockout_date_arr as $k => $v ) {
                        if( in_array( $v->d, $specific_dates ) ) {
                            continue;
                        }
                        $lockout_date_split = explode( '-', $v->d );
                        $date_lockout_time = strtotime( $lockout_date_split[ 1 ] . "-" . $lockout_date_split[0] . "-" . $lockout_date_split[2] );
                        if( $date_lockout_time >= strtotime( $current_date ) && !in_array( $v->d, $date_custom_arr ) ) {
                            if( isset( $shipping_settings_to_check[ 'date_lockout' ] ) && 
                                $shipping_settings_to_check[ 'date_lockout' ] != '' && 
                                $shipping_settings_to_check[ 'date_lockout' ] != '0' ) {
                                $date_lockout = $shipping_settings_to_check[ 'date_lockout' ];
                                $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $date_lockout - $v->o ) . "',";
                                $available_deliveries .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $date_lockout - $v->o ) . "',";
                            } else {
                                $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                                $available_deliveries .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                            }
                            $date_custom_arr[] = $v->d;
                        }
                    }

                    foreach( $specific_dates as $key => $value ) {
                        if( $value !== '' ) {
                            $available_deliveries .=  "'" . $key . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . $value . "',";
                        }else if( $value == '' ){
                            $available_deliveries .= "'" . $key . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                        }
                    }

                    if( isset( $shipping_settings_to_check[ 'date_lockout' ] ) && 
                        $shipping_settings_to_check[ 'date_lockout' ] != '' && 
                        $shipping_settings_to_check[ 'date_lockout' ] != '0' ) {
                        $available_deliveries .= "'>" . __( 'Available Deliveries: ', 'order-delivery-date' ) . $shipping_settings_to_check['date_lockout'] . "',";  
                    } else {
                        $available_deliveries .= "'>" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                    }
                }
                $is_custom_enabled = 'yes';
            }
        } 
        
        // change the condition for the global settings
        if( 'no' == $is_custom_enabled ) {
            $date_lockout = get_option( 'orddd_lockout_date_after_orders' );

            //Partially booked dates for Time slots.
            if ( get_option( 'orddd_enable_time_slot' ) == 'on' ) {
                // Check for Minimum Delivery Time to display the time slots. 
                $minimum_delivery_time = get_option( 'orddd_minimumOrderDays' );
                if( '' == $minimum_delivery_time ) {
                    $minimum_delivery_time = 0;
                }

                $delivery_time_seconds = $minimum_delivery_time * 60 * 60;
                $holidays_str = wp_cache_get( 'orddd_general_delivery_date_holidays' );
                $lockout_days_str = wp_cache_get( 'orddd_general_lockout_days_str' );

                $min_date_array = orddd_common::get_min_date( $delivery_time_seconds, array( 'enabled' => get_option( 'orddd_enable_delivery_time' ), 'from_hours' => get_option( 'orddd_delivery_from_hours' ), 'to_hours' => get_option( 'orddd_delivery_to_hours' ), 'from_mins' => get_option('orddd_delivery_from_mins' ), 'to_mins' => get_option('orddd_delivery_to_mins') ), $holidays_str, $lockout_days_str );

                $date = array();
                $lockout_timeslots_arr = array();
                $previous_orders = 0;

                $lockout_timeslots_days = get_option( 'orddd_lockout_time_slot' );
                if ( $lockout_timeslots_days != ''   && 
                    $lockout_timeslots_days  != '{}' && 
                    $lockout_timeslots_days  != '[]' && 
                    $lockout_timeslots_days  != 'null' ) {
                    $lockout_timeslots_arr = json_decode( get_option( 'orddd_lockout_time_slot' ) );
                }

                foreach ( $lockout_timeslots_arr as $k => $v ) {
                    $date_str = date( 'j-n-Y', strtotime( $v->d ) );
                    if( isset( $date[ $date_str ][ $v->t ] ) ) {
                        $previous_orders =  $date[ $date_str ][ $v->t ] + $v->o;
                        $date[ $date_str ][ $v->t ] = $previous_orders;
                    } else {
                        $date[ $date_str ][ $v->t ] = $v->o;
                    }
                }

                $specific_dates = array();
                $delivery_days = array();
                $previous_lockout = 0;

                $existing_timeslots_arr = json_decode( get_option( 'orddd_delivery_time_slot_log' ) );
                // Sort the multidimensional array
                usort( $existing_timeslots_arr, array( 'orddd_common', 'orddd_custom_sort' ) );
                foreach( $existing_timeslots_arr as $k => $v ) {
                    $from_time = date( $time_format_to_show, strtotime( $v->fh . ":" . trim( $v->fm, ' ' ) ) );
                    $to_time = date( $time_format_to_show, strtotime( $v->th . ":" . trim( $v->tm, ' ' ) ) );
                    if( $v->th != '' && $v->th != '00' && $v->tm != '' && $v->tm != '00' ) {
                        $time_slot = $from_time . " - " . $to_time;    
                    } else {
                        $time_slot = $from_time;
                    }

                    $dd = json_decode( $v->dd );
                    if ( is_array( $dd ) &&  count( $dd ) > 0 ) {
                        foreach ( $dd as $dkey => $dval ) {
                            if( $v->tv == 'weekdays' ) {
                                $delivery_days[ $dval ][ $time_slot ] = $v->lockout;
                            } else {
                                $specific_dates[ $dval ][ $time_slot ] = $v->lockout;
                            }
                        }
                    }

                    $min_time_on_last_slot = apply_filters( 'orddd_min_delivery_on_last_slot', false );
                    if( $min_time_on_last_slot ) {
                        if( $delivery_time_seconds == 0 ) {
                            $last_slot     =   date( 'G:i', current_time('timestamp') );
                        } else {
                            $last_slot     =   $min_date_array[ 'min_hour' ] . ':' . $min_date_array[ 'min_minute' ];
                        }

                        $ordd_date_two =   $min_date_array[ 'min_date' ] . " " . $last_slot;
                        $current_date = date( 'j-n-Y', current_time('timestamp') );
                        $ordd_date_one  =   date( 'j-n-Y', current_time('timestamp') ) ." ". $to_time;

                        $date3str = strtotime( $ordd_date_one );
                        $date2str = strtotime( $ordd_date_two );
        
                        if( ( $current_date == $min_date_array['min_date'] && $date2str < $date3str ) || $current_date != $min_date_array['min_date'] ) {
                            $min_date = $min_date_array[ 'min_date' ];
                            $specific_dates[ $min_date ][ $time_slot ] = $v->lockout;    
                        }
                    }else {
                        if( $v->fh > $min_date_array[ 'min_hour' ] || ( $v->fh == $min_date_array[ 'min_hour' ]  && $v->fm > $min_date_array[ 'min_minute' ] ) ) {
                            $min_date = $min_date_array[ 'min_date' ];
                            $specific_dates[ $min_date ][ $time_slot ] = $v->lockout;    
                        }
                    }                    
                }

                $partially_lockout_dates .= "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "--'," ;
                $available_deliveries = "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "'," ;

                foreach( $date as $dk => $dv ) {
                    $available_timeslot_deliveries = '';
                    $lockout_date_arr = explode( "-", $dk );
                    $date_lockout_time = strtotime( $dk );
                    $time_arr = array();

                    //create an array with time and lockout with the set time format.
                    foreach( $dv as $time => $lockout ) {   
                        $time_format = orddd_common::orddd_change_time_slot_format( $time, $time_format_to_show );
                        $time_arr[$time_format] = $lockout;
                    }

                    if( $date_lockout_time >= strtotime( $current_date ) ) {
                        $lockout_date = $lockout_date_arr[1] . "-" . $lockout_date_arr[ 0 ] . "-" . $lockout_date_arr[2];

                        if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
                            if ( isset( $specific_dates[ $lockout_date ] ) ) {
                                $time_slots = $specific_dates[ $lockout_date ];
                                foreach( $time_slots as $tk => $tv ) {
                                    if( $tv == 0 ) {
                                        $available_timeslot_deliveries .= $tk . ": " . __( 'Unlimited', '   order-delivery-date' ) . '--';
                                    } else {
                                        if( isset( $time_arr[ $tk ] ) && $tv >= $time_arr[ $tk ] ) {
                                            if( ( $tv - $time_arr[ $tk ] ) > 0 ) {
                                                $available_timeslot_deliveries .= $tk . ": " . ( $tv - $time_arr[ $tk ] ). "--";    
                                            }
                                        } else {
                                            $available_timeslot_deliveries .= $tk . ": " . $tv . "--";
                                        }
                                    }
                                }
                                $partially_lockout_dates .= "'" . $lockout_date . ">" . $available_timeslot_deliveries . "',";
                            }
                        }

                        if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {
                            $weekday = date( 'w', strtotime( $dk ) );
                            $time_slots = array();
                            if ( isset( $delivery_days[ 'orddd_weekday_' . $weekday ] ) ) {
                                $time_slots = $delivery_days[ 'orddd_weekday_' . $weekday ];
                            } else if( isset( $delivery_days[ 'all' ] ) ) {
                                $time_slots = $delivery_days[ 'all' ];
                            }

                            foreach( $time_slots as $tk => $tv ) {
                                if( 0 == $tv || '' == $tv ) {
                                    $available_timeslot_deliveries .= $tk . ": " . __( 'Unlimited', '   order-delivery-date' ) . '--';
                                    $available_deliveries .= "'" . $lockout_date . ">" . $tk . ": " . __( 'Unlimited', '   order-delivery-date' ) .  "',";
                                } else {
                                    if( isset( $time_arr[ $tk ] ) && $tv >= $time_arr[ $tk ] ) {
                                        if( ( $tv - $time_arr[ $tk ] ) > 0 ) {
                                            $available_timeslot_deliveries .= $tk . ": " . ( $tv - $time_arr[ $tk ] ). "--";    
                                            $available_deliveries .= "'" . $lockout_date . ">" . $tk . ": " . ( $tv - $time_arr[ $tk ] ) .  "',";
                                        }
                                    } else {
                                        $available_timeslot_deliveries .= $tk . ": " . $tv . "--";
                                        $available_deliveries .= "'" . $lockout_date . ">" . $tk . ": " . $tv .  "',";
                                    }
                                }
                            }
                            $partially_lockout_dates .= "'" . $lockout_date . ">" . $available_timeslot_deliveries . "',";
                        }
                    }
                }

                if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
                    foreach ( $specific_dates as $s_key => $s_value ) {
                        $time_slots = $s_value;
                        foreach( $time_slots as $tk => $tv ) {
                            if( $tv == 0 ) {
                                $available_timeslot_deliveries = $tk . ": " . __( 'Unlimited', 'order-delivery-date' );
                            } else {
                                $available_timeslot_deliveries = $tk . ": " . $tv;
                            }
                            $available_deliveries .= "'" . $s_key . ">" . $available_timeslot_deliveries . "',";
                        }
                    }
                } 

                if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {
                    foreach( $delivery_days as $del_days_key => $del_days_val ) {
                        if( $del_days_key != 'all' ) {
                            $time_slots = $delivery_days[ $del_days_key ];
                            foreach( $time_slots as $tk => $tv ) {
                                if( 0 == $tv || '' == $tv ) {
                                    $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . __( 'Unlimited', '   order-delivery-date' ) . "',";
                                } else {
                                    $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . $tv . "',";
                                }
                            }
                        } else {
                            $time_slots = $delivery_days[ 'all' ];
                            for( $i = 0; $i < 7; $i++ ) {
                                foreach( $time_slots as $tk => $tv ) {
                                    if( 0 == $tv || '' == $tv ) {
                                        $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . __( 'Unlimited', '   order-delivery-date' ) . "',";
                                    } else {
                                        $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . $tv . "',";
                                    }
                                }
                            }
                        }
                    }
                }
            } else {      
                //Partially booked dates for only date for general settings.           
                $lockout_days_arr = array();
                $lockout_days = get_option( 'orddd_lockout_days' );
                if ( $lockout_days != '' && $lockout_days != '{}' && $lockout_days != '[]' && $lockout_days != "null" ) {
                    $lockout_days_arr = json_decode( get_option( 'orddd_lockout_days' ) );
                }
                $specific_dates = array();
                $delivery_days = array();
                $all_specific_dates = array();

                if( 'on' == get_option('orddd_enable_specific_delivery_dates') ) {
                    $delivery_dates = get_option( 'orddd_delivery_dates' );
                    
                    if ( $delivery_dates != '' && $delivery_dates != '{}' && $delivery_dates != '[]' && $delivery_dates != 'null' ) {
                        $delivery_days = json_decode( $delivery_dates );
                    }
                    $is_specific_lockout = "no";
                    foreach ( $lockout_days_arr as $k => $v ) {
                        $date = $v->d;
                        $lockout_date_split = explode( '-', $v->d );
                        $date_lockout_time = strtotime( $lockout_date_split[ 1 ] . "-" . $lockout_date_split[0] . "-" . $lockout_date_split[2] );
                        foreach( $delivery_days as $key => $value ) {
                            if( $date_lockout_time >= strtotime( $current_date ) && $date == $value->date && $value->max_orders !== '' ) {
                                $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $value->max_orders - $v->o ) . "',";
                                array_push( $specific_dates, $v->d );
                            }else if($date_lockout_time >= strtotime( $current_date ) && $date == $value->date && $value->max_orders == ''){
                                $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                                array_push( $specific_dates, $v->d );
                            }
                        }
                    }

                    foreach( $delivery_days as $key => $value ) {
                        if( '' != $value && isset( $value->max_orders ) ) {
                            $all_specific_dates[ $value->date ] = $value->max_orders;
                        }
                    }
                }

                $date_arr = array();
                foreach ( $lockout_days_arr as $k => $v ) {
                    if( isset( $specific_dates ) && in_array( $v->d, $specific_dates ) ) {
                        continue;
                    }
                    $lockout_date_split = explode( '-', $v->d );
                    $date_lockout_time = strtotime( $lockout_date_split[ 1 ] . "-" . $lockout_date_split[0] . "-" . $lockout_date_split[2] );
                    if( $date_lockout_time >= strtotime( $current_date ) && !in_array( $v->d, $date_arr ) ) {
                        if ( $date_lockout > 0 && $date_lockout != '' ) {
                            $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $date_lockout - $v->o ) . "',";
                            
                            $available_deliveries .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $date_lockout - $v->o ) . "',";

                        } else {
                            $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";

                            $available_deliveries .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";

                        }
                        $date_arr[] = $v->d;
                    }
                }    
                $partially_lockout_dates = trim( $partially_lockout_dates, "," );

                foreach( $all_specific_dates as $key => $value ) {
                    if( $value !== '' ) {
                        $available_deliveries .=  "'" . $key . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . $value . "',";
                    }else if( $value == '' ){
                        $available_deliveries .= "'" . $key . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ) . "',";
                    }
                }

                if ( $date_lockout > 0 && $date_lockout != '' ) {
                    $available_deliveries .=  "'>" . __( 'Available Deliveries: ', 'order-delivery-date' ) . $date_lockout. "',";
                } else {
                    $available_deliveries .= "'>" .__( 'Available Deliveries: ', 'order-delivery-date' ) . __( 'Unlimited', 'order-delivery-date' ). "',";
                }

            }
        }
        $partially_lockout_dates .= "&" . $available_deliveries;        

        return $partially_lockout_dates;
    }
}
$orddd_widget = new orddd_widget();