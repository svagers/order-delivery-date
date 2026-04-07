<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Handles the Add/Edit of the Delivery Date and Time in the admin order.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Admin/Edit-Order
 * @since       3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main class which will handle the Add/Edit of Delivery information in the WooCommerce Add/Edit Orders page.
 *
 * @class orddd_admin_delivery_class
 */

class orddd_admin_delivery_class {
    
    /**
     * Default Constructor.
     *
     * @since 3.2
     */
    public function __construct() {
        add_action( 'wp_ajax_woocommerce_save_order_items', array( &$this, 'orddd_load_delivery_dates' ) );
        add_action( 'wp_ajax_orddd_remove_order_item', array( &$this, 'orddd_remove_order_item' ) );
        add_action( 'woocommerce_saved_order_items', array( &$this, 'orddd_woocommerce_saved_order_items' ), 10, 2 );
        add_action( 'woocommerce_order_status_cancelled' , array( 'orddd_common', 'orddd_cancel_delivery' ), 10, 1 );
        add_action( 'woocommerce_order_status_refunded' , array( 'orddd_common', 'orddd_cancel_delivery' ), 10, 1 );
        add_action( 'woocommerce_order_status_failed' , array( 'orddd_common', 'orddd_cancel_delivery' ), 10, 1 );
        add_action( 'woocommerce_order_status_changed', array( 'orddd_common', 'orddd_restore_deliveries' ), 10, 3 );
        add_action( 'wp_trash_post', array( 'orddd_common', 'orddd_cancel_delivery_for_trashed' ), 10, 1 );
        add_action( 'untrash_post',  array( 'orddd_common', 'orddd_untrash_order' ), 10, 1 );
        add_action( 'wp_ajax_save_delivery_dates', array( &$this, 'save_delivery_dates' ) );
        //Display Order Delivery Date meta box on Add/Edit Orders Page
		if ( get_option( 'orddd_enable_delivery_date' ) == 'on' ) {
		    add_action( 'add_meta_boxes', array( &$this, 'orddd_admin_delivery_box' ) );
		}
    }
    
    /**
     * Meta box for Delivery date and/or Time slot in WooCommerce Add/Edit Orders page.
     *
     * @hook add_meta_boxes
     * @since 3.2
     */
    
    public static function orddd_admin_delivery_box() {
        add_meta_box( 'order-delivery-date', __( 'Edit Order Delivery Date and/or Time', 'order-delivery-date' ), array( 'orddd_admin_delivery_class', 'orddd_meta_box' ), 'shop_order','normal','core' );
        if ( 'on' == get_option( 'orddd_enable_woo_subscriptions_compatibility' ) ) {
            add_meta_box( 'order-delivery-date', __( 'Edit Order Delivery Date and/or Time', 'order-delivery-date' ), array( 'orddd_admin_delivery_class', 'orddd_meta_box' ), 'shop_subscription','normal','core' );
        }
    }
    
    /**
     * Delivery Date and/or Time slot fields in the Meta box 
     * 
     * @param resource $order - Order Details
     * @param array $post - Post Details
     *
     * @globals resource $wpdb
     * @globals array $orddd_date_formats
     * @globals resource $post
     * @globals resource $woocommerce
     * @globals array $orddd_languages
     * @globals array $orddd_weekdays
     * @since 3.2
     */
    
    public static function orddd_meta_box( $order, $post ) {
        global $wpdb, $orddd_date_formats, $post, $woocommerce, $orddd_languages, $orddd_weekdays;
        if ( get_option( 'orddd_enable_delivery_date' ) == 'on' ) {
            $field_name = 'e_deliverydate';
            $orddd_post_type = $post->post_type;
            $order_id = $order->ID;
            $data = get_post_meta( $order_id );
            
            $var = '';
            $var .= "<input type='hidden' id='orddd_order_id' name='orddd_order_id' value='" . $order_id . "'>";
            $var .= "<input type='hidden' id='orddd_post_type' name='orddd_post_type' value='" . $orddd_post_type . "'>";

            if( is_plugin_active( 'woocommerce-shipping-canada-post/woocommerce-shipping-canada-post.php' ) ) {
                $orddd_canada_post_id = get_post_meta( $order_id, '_orddd_canada_post_id', true );
                $var .= "<input type='hidden' name='orddd_canada_post_id' id='orddd_canada_post_id' value='". $orddd_canada_post_id ."'>"; 
            }
			
            $fixed_time = 'off';
            $default_date_time = '';
            if( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' ) {
                $default_date = date( "d-m-Y", $data[ '_orddd_timestamp' ][ 0 ] ); 
                $default_h_deliverydate = date( "j-n-Y", $data[ '_orddd_timestamp' ][ 0 ] );
                $time_selected = date( "H:i", $data[ '_orddd_timestamp' ][ 0 ] );
                if ( $time_selected != '00:01' && $time_selected != '' && $time_selected != '00:00' ) {
                    $fixed_time = 'on';
                    $default_date_time = $time_selected;
                }
            } elseif ( isset( $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] ) && $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] != '' ) {
                $default_date = date( "d-m-Y", strtotime( str_replace( ",", " ", $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] ) ) );
                $default_h_deliverydate = date( "j-n-Y", strtotime( str_replace( ",", " ", $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] ) ) );
                if( get_option( 'orddd_enable_delivery_time' ) == 'on' ) {
                    $time_settings_arr = explode( " ", $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] );
                    $time_settings_arr_1 = array_pop( $time_settings_arr );
                    $time_settings = date( "H:i", strtotime( $time_settings_arr_1 ) );
                    $default_date_time = $time_settings;
                }
            } else {
                $default_date = '';
                $default_h_deliverydate = '';
                
            }
            
            $var .= "<input type='hidden' id='orddd_default_date' name='orddd_default_date' value='" . $default_date . "'>";
            $var .= "<input type='hidden' id='orddd_default_h_date' name='orddd_default_h_date' value='" . $default_h_deliverydate . "'>";
            $var .= "<input type='hidden' id='default_date_time' name='default_date_time' value='" . $default_date_time . "'>";
            $var .= "<input type='hidden' id='orddd_fixed_time' name='orddd_fixed_time' value='$fixed_time'";
            
            $get_order_item_ids_query = "SELECT * FROM `" . $wpdb->prefix . "woocommerce_order_items` WHERE order_id = %d";
            $results_order_item_ids = $wpdb->get_results( $wpdb->prepare( $get_order_item_ids_query, $order_id ) );
            $product_id = $shipping_method = '';
            foreach( $results_order_item_ids as $key => $value ) {
                $order_item_id = $value->order_item_id;
                $get_itemmeta_query = "SELECT * FROM `" . $wpdb->prefix . "woocommerce_order_itemmeta` WHERE order_item_id = %d";
                $results = $wpdb->get_results( $wpdb->prepare( $get_itemmeta_query, $order_item_id ) );
                foreach( $results as $key => $value ) {
                    if( $value->meta_key == '_product_id' ) {
                        $product_id = $value->meta_value;
                    }
                    if( $value->meta_key == 'method_id' ) {
                        $shipping_method = $value->meta_value;
                    }
                }
            }
            
            $hidden_variables = orddd_common::load_hidden_fields();
            echo $hidden_variables;

            $order = new WC_Order( $order_id );
            $items = $order->get_items();
            $delivery_enabled = 'yes';
            if ( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' && get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
                foreach( $items as $key => $value ) {
                    $product_id = $value[ 'product_id' ];
                    if( $product_id != 0 ) {
                        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                            $product = wc_get_product( $product_id );
                        } else {
                            $product = get_product( $product_id );
                        }
                        if( $product->is_virtual() == false && $product->is_featured() == false ) {
                            $delivery_enabled = 'yes';
                            break;
                        } else {
                            $delivery_enabled = 'no';
                        }
                    }
                }
            } else if( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' && get_option( 'orddd_no_fields_for_featured_product' ) != 'on' ) {
                 foreach( $items as $key => $value ) {
                    $product_id = $value[ 'product_id' ];
                    if( $product_id != 0 ) {
                        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                            $product = wc_get_product( $product_id );
                        } else {
                            $product = get_product( $product_id );
                        }
                        if( $product->is_virtual() == false ) {
                            $delivery_enabled = 'yes';
                            break;
                        } else {
                            $delivery_enabled = 'no';
                        }
                    } 
                }
            } else if( get_option( 'orddd_no_fields_for_virtual_product' ) != 'on' && get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
                foreach( $items as $key => $value ) {
                    $product_id = $value[ 'product_id' ];
                    if( $product_id != 0 ) {
                        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                            $product = wc_get_product( $product_id );
                        } else {
                            $product = get_product( $product_id );
                        }
                        if( $product->is_featured() == false ) {
                            $delivery_enabled = 'yes';
                            break;
                        } else {
                            $delivery_enabled = 'no';
                        }
                    }
                }
            } else {
                $delivery_enabled = 'yes';
            }
                    
            $enable_delivery_date_for_category = 'on';
            foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                $enable_delivery_date_for_category = orddd_common::orddd_admin_product_has_delivery( $product_id );
                if( $enable_delivery_date_for_category === 'on' ) {
                    break;
                }
            }

            $var .= "<input type='hidden' id='orddd_delivery_enabled' name='orddd_delivery_enabled' value='" . $delivery_enabled . "'>";

            $var .= "<input type='hidden' id='orddd_enable_delivery_date_for_category' name='orddd_enable_delivery_date_for_category' value='" . $enable_delivery_date_for_category . "'>";
            
            $location = orddd_common::orddd_get_order_location( $order_id );
            $shipping_method = orddd_common::orddd_get_order_shipping_method( $order_id );
            $product_category = orddd_common::orddd_get_cart_product_categories( $order_id );
            $shipping_class = orddd_common::orddd_get_cart_shipping_classes( $order_id );

            $date_field_label = orddd_common::orddd_get_delivery_date_field_label( $shipping_method, $product_category, $shipping_class, $location, $order_id ); 
            $time_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location, $order_id ); 

            $var .= "<input type='hidden' id='orddd_field_name_admin' name='orddd_field_name_admin' value='" . $date_field_label . "'>";

            $var .= "<input type='hidden' id='orddd_time_field_name_admin' name='orddd_time_field_name_admin' value='" . $time_field_label . "'>";

            $var .= "<input type='hidden' id='orddd_location' name='orddd_location' value='" . $location . "'>";

            echo $var; 

            // Default the fees
            $fee = get_post_meta( $order_id, '_total_delivery_charges', true );
            if( '' != $fee || '{}' != $fee || '[]' != $fee ) {
                $fee_name = "Delivery Charges:";
            } else {
                $fee = 0;
                $fee_name = '';
                foreach( $order->get_items( 'fee' ) as $item_id => $item_fee ) {
                    if( $item_fee->get_total() != '' && $item_fee->get_total() > 0 ) {
                        $fee_name = ( $item_fee->get_name() != '' ) ? $item_fee->get_name() : __( 'Delivery Charges:', 'order-delivery-date' );
                        $fee += $item_fee->get_total();
                    }
                }
                $fee_name = ( ( is_array( $order->get_items( 'fee' ) ) && count( $order->get_items( 'fee' ) ) ) > 1 || $fee_name == '' ) ? "Delivery Charges:" : $fee_name;
            }
            
            
            $disabled = "";
            if( 'auto-draft' == get_post_status( $order_id ) ) {
                $disabled = "disabled";
            } 

            print ( '<table id="admin_delivery_fields" >
                <tr id="admin_delivery_date_field" >
                    <td><label class ="orddd_delivery_date_field_label">' . $date_field_label . ': </label></td>
                    <td>
                        <input type="text" id="' . $field_name . '" name="' . $field_name . '" class="' . $field_name . '" readonly/>
                        <input type="hidden" id="h_deliverydate" name="h_deliverydate" />
                    </td>
                </tr>');
                if( get_option( 'orddd_enable_time_slot' ) == 'on' ) {
                    print( '<tr id="admin_time_slot_field">
                        <td><label for="orddd_time_slot" class="">' . $time_field_label . ': </label></td>
                        <td><select name="orddd_time_slot" id="orddd_time_slot" class="orddd_admin_time_slot" disabled="disabled" placeholder="">
                                <option value="select">Select a time slot</option>
                            </select>
                        </td>
                    </tr>' );
                }
                print( "<tr id='delivery_charges'>
                    <td><label for='del_charges'>$fee_name</label></td>
                    <td><input type='number' min='0' value='$fee' step='0.001' id='del_charges' /></td></tr>" );

                print( "<tr>
                    <td colspan='2'>
                    <small>" . __( 'Any change in Delivery charges here will not change the order total. You will need to update the Item section above for delivery charges to reflect in order total.', 'order-delivery-date' ) . "</small>
                    </td>
                    </tr>" );
                
                print( '<tr id="save_delivery_date_button">
                    <td><input type="button" value="Update" id="save_delivery_date" class="save_button"></td>
                    <td><input type="button" value="Update & Notify Customer" id="save_delivery_date_and_notify" class="save_button"' . $disabled . '></td>
                    <td><font id="orddd_update_notice"></font></td>
                </tr>
            </table>
            <div id="is_virtual_product"></div>' );
        }
    }
    
    /**
     * Save Delivery date and/or Time slot
     *
     * @param int $order_id - Order Id
     * @param array $items - Order items to save
     *
     * @hook woocommerce_saved_order_items
     * @since 3.2
     */
    
    public static function orddd_woocommerce_saved_order_items( $order_id, $items ) {
        if( isset( $items[ 'meta' ] ) ) {
            
            $location = orddd_common::orddd_get_order_location( $order_id );
            $shipping_method = orddd_common::orddd_get_order_shipping_method( $order_id );
            $product_category = orddd_common::orddd_get_cart_product_categories( $order_id );
            $shipping_class = orddd_common::orddd_get_cart_shipping_classes( $order_id );

            $date_field_label = orddd_common::orddd_get_delivery_date_field_label( $shipping_method, $product_category, $shipping_class, $location, $order_id ); 
            $time_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location, $order_id ); 

            $meta = $items[ 'meta' ];
            $previous_time_slot = $previous_date = '';
            foreach( $meta as $key => $value ) {
                if( $value[ 'key' ] == get_option( 'orddd_delivery_date_field_label' ) ) {
                    $previous_date = $value[ 'value' ];
                }
                if( $value[ 'key' ] == get_option( 'orddd_delivery_timeslot_field_label' ) ) {
                    $previous_time_slot = $value[ 'value' ];
                }
                
            }
            
            if ( isset( $items[ 'e_deliverydate' ] ) && $items[ 'e_deliverydate' ] != '' && $items[ 'e_deliverydate' ] != $previous_date ) {
                update_post_meta( $order_id, $date_field_label, esc_attr( $items[ 'e_deliverydate' ] ) );
            }
            
            if ( isset( $items[ 'orddd_time_slot' ] ) && $items[ 'orddd_time_slot' ] != '' && $items[ 'orddd_time_slot' ] != 'select' && $items[ 'orddd_time_slot' ] != 'NA' ) {
                $time_slot = $items[ 'orddd_time_slot' ];
                $order_time_slot = '';
                $time_slot_arr = explode( ' - ' , $time_slot );

                if( $time_slot_arr[0] == 'As Soon As Possible.' ) {
                    $from_time = $time_slot_arr[0];
                }else {
                    $from_time = date( 'H:i', strtotime( $time_slot_arr[ 0 ] ) );
                }

                if( isset( $time_slot_arr[ 1 ] ) ) {
                    $to_time = date( 'H:i', strtotime( $time_slot_arr[ 1 ] ) );
                    $order_time_slot = $from_time . " - " . $to_time;
                } else {
                    $order_time_slot = $from_time;
                }

                if( isset( $items[ 'e_deliverydate' ] ) && $items[ 'e_deliverydate' ] != '' && $items[ 'e_deliverydate' ] != $previous_date ) {
                    update_post_meta( $order_id, $time_field_label , esc_attr( $time_slot ) );
                    update_post_meta( $order_id, '_orddd_time_slot', $order_time_slot );
                } else if( $items[ 'orddd_time_slot' ] != $previous_time_slot ) {
                    update_post_meta( $order_id,  $time_field_label, esc_attr( $time_slot ) );
                    update_post_meta( $order_id, '_orddd_time_slot', $order_time_slot );
                }
            }
        }
    }
    
    /**
     * Save Delivery date and/or Time slot
     * 
     * @globals resource $wpdb
     * @globals array $orddd_weekdays
     * @since 3.2
     */
    
    public static function save_delivery_dates() {
        global $wpdb, $orddd_weekdays;
        
        $delivery_details_updated   = "yes";
        $notes_array                = array();

        $order_id = isset( $_POST[ 'order_id' ] ) ? $_POST[ 'order_id' ] : "";
        
        if ( isset( $order_id ) && $order_id > 0 && false !== get_post_status( $order_id ) ) {
            $order              = new WC_Order( $order_id );
            $order_status       = $order->get_status();
            $location           = orddd_common::orddd_get_order_location( $order_id );
            $shipping_method    = orddd_common::orddd_get_order_shipping_method( $order_id );
            $product_category   = orddd_common::orddd_get_cart_product_categories( $order_id );
            $shipping_class     = orddd_common::orddd_get_cart_shipping_classes( $order_id );

            $orddd_fees = ( isset( $_POST[ 'orddd_charges' ] ) && is_numeric( $_POST[ 'orddd_charges' ] ) ) ? $_POST[ 'orddd_charges' ] : 0;
            
            $date_field_label = orddd_common::orddd_get_delivery_date_field_label( $shipping_method, $product_category, $shipping_class, $location, $order_id ); 
            $time_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location, $order_id ); 
        
            $charges_label              = '';
            $additional_charges_label   = '';
            $time_setting               = array();
    
            $free_coupon_enabled = $add_delivery_charges_for_free_coupon_code = 'no';
            if ( has_filter( 'orddd_add_delivery_charges_for_free_coupon_code' ) ) {
                $add_delivery_charges_for_free_coupon_code = apply_filters( 'orddd_add_delivery_charges_for_free_coupon_code', $add_delivery_charges_for_free_coupon_code );
            }
            
            if ( 'yes' != $add_delivery_charges_for_free_coupon_code ) {
                $applied_coupons = $order->get_used_coupons();
                foreach ( $applied_coupons as $applied_coupons_key => $applied_coupons_value ) {
                    $is_free_coupon = new WC_Coupon( $applied_coupons_value );
                    if ( $is_free_coupon->free_shipping == 'yes' ) {
                            $free_coupon_enabled = 'yes';
                            break;
                    }
                }
            }
            
            $selected_time = '';
            if ( isset( $_POST[ 'orddd_time_settings_selected' ] ) ) {
                $selected_time = date( "H:i", strtotime( $_POST[ 'orddd_time_settings_selected' ] ) );
            }
    
            $date_selected = 'no';
            $timeslot_selected = 'no';
            $delivery_details_updated = 'no';
            if ( ( isset( $_POST[ 'e_deliverydate' ] ) && $_POST[ 'e_deliverydate' ] != '' ) ) {
                $delivery_date = '';
                $time_slot = '';
    
                $previous_order_date = '';
                $previous_order_weekday_check = '';
                $previous_order_h_date = '';
                $previous_order_timeslot = '';
                $previous_charges_label = '';
                $previous_selected_time = '';
                $previous_order_date_check = '';
                $data = get_post_meta( $order_id );
                
                if ( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' ) {
                    $previous_order_h_date          = date( "j-n-Y", $data[ '_orddd_timestamp' ][ 0 ] );
                    $previous_order_date_check      = date( "n-j-Y", $data[ '_orddd_timestamp' ][ 0 ] );
                    $previous_order_weekday_check   = date( "w", $data[ '_orddd_timestamp' ][ 0 ] );
                    $previous_selected_time         = date( "H:i", $data[ '_orddd_timestamp' ][ 0 ] );
                }
                
                if ( isset( $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] ) && 
                    $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] != '' ) {

                    $previous_order_date = $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ];
                    
                    if ( '' == $previous_order_h_date ) {
                        $delivery_date_timestamp        = strtotime( str_replace( ",", " ", $data[ get_option( 'orddd_delivery_date_field_label' ) ][ 0 ] ) );
                        $previous_order_h_date          = date( "j-n-Y", $delivery_date_timestamp );
                        $previous_order_date_check      = date( "n-j-Y", $delivery_date_timestamp );
                        $previous_order_weekday_check   = date( "w", $delivery_date_timestamp );
                        $previous_selected_time         = date( "H:i", $delivery_date_timestamp );
                    }
                }
                
                if ( isset( $data[ $time_field_label ][ 0 ] ) && $data[ $time_field_label ][ 0 ] != '' ) {
                    $previous_order_timeslot = $data[ $time_field_label ][ 0 ];
                }
                
                orddd_common::orddd_cancel_delivery( $order_id );
    
                if ( isset( $_POST[ 'e_deliverydate' ] ) && $_POST[ 'e_deliverydate' ] != '' && 
                     $_POST[ 'e_deliverydate' ] != $previous_order_date ) {
                    $notes_array[] = "$date_field_label is updated from $previous_order_date to ". $_POST[ 'e_deliverydate' ] ;
                    update_post_meta( $order_id, $date_field_label, $_POST[ 'e_deliverydate' ] );
                    $delivery_details_updated = "yes";
                }
                
                if ( isset( $_POST[ 'h_deliverydate' ] ) && $_POST[ 'h_deliverydate' ] != '' ) {
                    $delivery_date  = $_POST[ 'h_deliverydate' ];
                    $date_format    = 'dd-mm-y';
                    if( $previous_order_h_date != $_POST[ 'h_deliverydate' ] || 
                        ( $previous_order_h_date == $_POST[ 'h_deliverydate' ] && 
                        ( $selected_time != $previous_selected_time || $selected_time == '' ) ) ) {

                        $time_setting                       = array();
                        $time_setting[ 'enable' ]           = 'on';
                        $time_setting[ 'time_selected' ]    = $selected_time;
                        
                        $timestamp                          = orddd_common::orddd_get_timestamp( $delivery_date, $date_format, $time_setting );
                        update_post_meta( $order_id, '_orddd_timestamp', $timestamp );
                        orddd_process::orddd_update_lockout_days( $delivery_date );
                    }
                }
                $date_selected = "yes";
            }
    
            if ( isset( $_POST[ 'orddd_time_slot' ] ) && $_POST[ 'orddd_time_slot' ] != '' && 
                 $_POST[ 'orddd_time_slot' ] != 'Select a time slot' && 
                 $_POST[ 'orddd_time_slot' ] != 'No time slots are available.' ) {
                $time_slot = $_POST[ 'orddd_time_slot' ];
                if ( $previous_order_h_date != $_POST[ 'h_deliverydate' ]  ) {
                    $delivery_details_updated = "yes";
                    update_post_meta( $order_id, $time_field_label, esc_attr( $time_slot ) );
                    update_post_meta( $order_id, '_orddd_time_slot', esc_attr( $time_slot ) );
                    orddd_process::orddd_update_time_slot( $time_slot, $delivery_date );
                } else if( $time_slot != $previous_order_timeslot ) {
                    $delivery_details_updated = "yes";

                    if( $time_slot == "As Soon As Possible." ) {
                        update_post_meta( $order_id, $time_slot_label, esc_attr( __( 'As Soon As Possible.', 'order-delivery-date' ) ) );
                        update_post_meta( $order_id, '_orddd_time_slot', esc_attr( __( 'As Soon As Possible.', 'order-delivery-date' ) ) );
                    }else {
                        update_post_meta( $order_id, $time_field_label, esc_attr( $time_slot ) );
                        update_post_meta( $order_id, '_orddd_time_slot', esc_attr( $time_slot ) );
                    }
                    orddd_process::orddd_update_time_slot( $time_slot, $delivery_date );
                }
                $notes_array[]      = "$time_field_label is updated from $previous_order_timeslot to ". $time_slot ;
                $timeslot_selected  = "yes";
            } else if ( isset( $_POST[ 'orddd_time_slot' ] ) && 
                      ( $_POST[ 'orddd_time_slot' ] == 'Select a time slot' || 
                        $_POST[ 'orddd_time_slot' ] == 'No time slots are available.' ) ) {
                $timeslot_selected = "no";
            } else {
                $timeslot_selected = "yes";
            }

            //Update the Delivery Charges
            update_post_meta( $order_id, '_total_delivery_charges', $orddd_fees );

            // Add order notes mentioning the same.
            if ( is_array( $notes_array ) && count( $notes_array ) > 0 ) {
                foreach( $notes_array as $msg ) {
                    $order->add_order_note( __( $msg, 'order-delivery-date' ) );
                }
            }

            if( 'yes' == $delivery_details_updated && isset( $_POST[ 'orddd_notify_customer' ] ) && 
                $_POST[ 'orddd_notify_customer' ] == 'yes' ) {
                ORDDD_Email_Manager::orddd_send_email_on_update( $order_id, 'admin' );
            }
            
            echo $date_selected . "," . $timeslot_selected . "," . $delivery_details_updated;
    
            // Add the Event to the Google Calendar 
            if( 'directly' === get_option( 'orddd_calendar_sync_integration_mode' ) ) {
                if( $order_status == 'processing' || $order_status == 'completed' ) {
                    $gcal = new OrdddGcal();
                    $event_details = orddd_common::orddd_get_event_details( $order_id );
                    $gcal->insert_event( $event_details, $order_id, false );
                    
                    if ( isset( $_POST[ 'orddd_post_type' ] ) && 
                         $_POST[ 'orddd_post_type' ] == 'shop_subscription' ) {
                        
                        if ( class_exists( 'WC_Subscription' ) ) {
                            $subscription_order = new WC_Subscription( $order_id );
                            
                            if ( isset( $subscription_order->order ) ) {
                                $parent_order_id        = $subscription_order->order->id;
                                $gcal->delete_event( $parent_order_id );
                            } 
                        }
                    }
                }
            }
        }
        die();
    }
        
    /**
     * Load JS code for Custom Delivery settings
     *
     * @globals resource $wpdb
     * @since 3.2
     */
    
    public static function orddd_load_delivery_dates() {
        global $wpdb;
        $product_id = '';
        $field_name = "e_deliverydate";
        if( isset( $_POST[ 'order_id' ] ) ) {
            $order_id = $_POST[ 'order_id' ];
        } else {
            $order_id = '';
        }
        
        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        $delivery_enabled = 'yes';
        if ( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' && get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
            foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                    $product = wc_get_product( $product_id );
                } else {
                    $product = get_product( $product_id );
                }
                
                if( $product->is_virtual() == false && $product->is_featured() == false ) {
                    $delivery_enabled = 'yes';
                    break;
                } else {
                    $delivery_enabled = 'no';
                }
            }
        } else if( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' && get_option( 'orddd_no_fields_for_featured_product' ) != 'on' ) {
             foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                    $product = wc_get_product( $product_id );
                } else {
                    $product = get_product( $product_id );
                }
                if( $product->is_virtual() == false ) {
                    $delivery_enabled = 'yes';
                    break;
                } else {
                    $delivery_enabled = 'no';
                }
            }
        } else if( get_option( 'orddd_no_fields_for_virtual_product' ) != 'on' && get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
             foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                    $product = wc_get_product( $product_id );
                } else {
                    $product = get_product( $product_id );
                }
                if( $product->is_featured() == false ) {
                    $delivery_enabled = 'yes';
                    break;
                } else {
                    $delivery_enabled = 'no';
                }
            }
        } else {
            $delivery_enabled = 'yes';
        }

        $enable_delivery_date_for_category = 'on';
        foreach( $items as $key => $value ) {
            $product_id = $value[ 'product_id' ];
            $enable_delivery_date_for_category = orddd_common::orddd_admin_product_has_delivery( $product_id );
            if( $enable_delivery_date_for_category === 'on' ) {
                break;
            }
        }
        
        if( $delivery_enabled == 'yes' && $enable_delivery_date_for_category === 'on'  ) {
            if ( get_option( 'orddd_enable_delivery_date' ) == 'on' ) {
                foreach( $items as $key => $value ) {
                    $product_id = $value[ 'product_id' ];
                    $_product = wc_get_product( $product_id );
                    $shipping_class = $_product->get_shipping_class();

                    $results = orddd_common::orddd_get_shipping_settings();
                    $shipping_settings =  array();
                    if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' && is_array( $results ) && count( $results ) > 0 ) {
                        foreach ( $results as $key => $value ) {
                            $shipping_settings = get_option( $value->option_name );
                            if( isset( $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] ) && $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'shipping_methods' ) {
                                if( isset( $shipping_settings[ 'shipping_methods' ] ) ) {
                                    $shipping_methods = $shipping_settings[ 'shipping_methods' ];
                                    if( in_array( $shipping_class, $shipping_methods ) ) {
                                        $shipping_class_to_send = $shipping_class;
                                        if( isset( $shipping_settings[ 'enable_shipping_based_delivery' ] ) ) {
                                            echo '<script type="text/javascript">
                                                jQuery( "#orddd_shipping_class_settings_to_load" ).val("' . $shipping_class_to_send . '");
                                            </script>';
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                foreach( $items as $key => $value ) {
                    $product_id = $value[ 'product_id' ];
                    $terms = get_the_terms( $product_id , 'product_cat' );
                    if( $terms != '' ) {
                        foreach ( $terms as $term => $val ) {
                            $results = orddd_common::orddd_get_shipping_settings();
                            $shipping_settings =  array();
                            if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' && is_array( $results ) && count( $results ) > 0 ) {
                                foreach ( $results as $key => $value ) {
                                    $shipping_settings = get_option( $value->option_name );
                                    if( isset( $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] ) && $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'product_categories' ) {
                                        if( isset( $shipping_settings[ 'product_categories' ] ) ) {
                                            $product_categories = $shipping_settings[ 'product_categories' ];
                                            if( in_array( $val->slug, $product_categories ) ) {
                                                $category_to_send = $val->slug;
                                                if( isset( $shipping_settings[ 'enable_shipping_based_delivery' ] ) ) {
                                                    echo '<script type="text/javascript">
                                                        jQuery( "#orddd_category_settings_to_load" ).val("' . $category_to_send . '");
                                                        </script>';
                                                    break 3;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                echo '<script type="text/javascript">
                if( jQuery( "#admin_delivery_fields tr" ).length == 0  ) {
                    jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"save_delivery_date_button\"><td><input type=\"button\" value=\"Update\" id=\"save_delivery_date\" class=\"save_button\"></td></tr>" );
                    jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_time_slot_field\"><td> " + jQuery( "#orddd_time_field_name_admin" ).val() + ": </td><td><select name=\"orddd_time_slot\" id=\"orddd_time_slot\" class=\"orddd_admin_time_slot\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">Select a time slot</option></select></td></tr>");
                    jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\"> " + jQuery( "#orddd_field_name_admin" ).val() + ": </label></td><td><input type=\"text\" id=\"' . $field_name . '\" name=\"' . $field_name . '\" class=\"' . $field_name . '\" readonly/><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
                    jQuery( "#is_virtual_product" ).html( "" );
                }';
                if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' ) {
                    echo 'load_delivery_date();';
                }
                echo '</script>';
            } else {
                echo '<script type="text/javascript">
                jQuery( "#admin_time_slot_field" ).remove();
                jQuery( "#admin_delivery_date_field" ).remove()
                jQuery( "#save_delivery_date_button" ).remove();
                jQuery( "#is_virtual_product" ).html( "Delivery date settings is not enabled for this product." );
                </script>';
            } 
        } else {
            echo '<script type="text/javascript">
            jQuery( "#admin_time_slot_field" ).remove();
            jQuery( "#admin_delivery_date_field" ).remove()
            jQuery( "#save_delivery_date_button" ).remove();
            jQuery( "#is_virtual_product" ).html( "Delivery date settings is not enabled for this product." );
            </script>';
        } 
    }
    
    /**
     * Reset Settings on delete of order item
     *
     * @globals resource $wpdb
     * @since 3.2
     */
    public static function orddd_remove_order_item() {
        global $wpdb;
        $product_id = '';
        
        if( isset( $_POST[ 'order_item_ids' ] ) ) {
            $order_item_ids = $_POST[ 'order_item_ids' ];
        } else {
            $order_item_ids = '';
        }

        $order_id = $wpdb->get_results( $wpdb->prepare( "SELECT order_id FROM `" . $wpdb->prefix . "woocommerce_order_items` WHERE order_item_id = %d ", $order_item_ids ), ARRAY_A );
        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        $delivery_enabled = 'yes';
        if ( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' && get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
            foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                    $product = wc_get_product( $product_id );
                } else {
                    $product = get_product( $product_id );
                }
                if( $product->is_virtual() == false && $product->is_featured() == false ) {
                    $delivery_enabled = 'yes';
                    break;
                } else {
                    $delivery_enabled = 'no';
                }
            }
        } else if( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' && get_option( 'orddd_no_fields_for_featured_product' ) != 'on' ) {
             foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                    $product = wc_get_product( $product_id );
                } else {
                    $product = get_product( $product_id );
                }
                if( $product->is_virtual() == false ) {
                    $delivery_enabled = 'yes';
                    break;
                } else {
                    $delivery_enabled = 'no';
                }
            }
        } else if( get_option( 'orddd_no_fields_for_virtual_product' ) != 'on' && get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
             foreach( $items as $key => $value ) {
                $product_id = $value[ 'product_id' ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
                    $product = wc_get_product( $product_id );
                } else {
                    $product = get_product( $product_id );
                }
                if( $product->is_featured() == false ) {
                    $delivery_enabled = 'yes';
                    break;
                } else {
                    $delivery_enabled = 'no';
                }
            }
            
        } else {
            $delivery_enabled = 'yes';
        }
        
        $enable_delivery_date_for_category = 'on';
        foreach( $items as $key => $value ) {
            $product_id = $value[ 'product_id' ];
            $enable_delivery_date_for_category = orddd_common::orddd_admin_product_has_delivery( $product_id );
            if( $enable_delivery_date_for_category === 'on' ) {
                break;
            }
        }
        $enable_delivery_date = "yes";
        if( $delivery_enabled == 'yes' && $enable_delivery_date_for_category === 'on') {
            if (  get_option( 'orddd_enable_delivery_date' ) == 'on' ) {
                if( is_array( $items ) && count( $items ) > 0 ) {
                    foreach( $items as $key => $value ) {
                        $product_id = $value[ 'product_id' ];
                        $_product = wc_get_product( $product_id );
                        $shipping_class = $_product->get_shipping_class(); 
                        if( $shipping_class != '' ) {
                            $results = orddd_common::orddd_get_shipping_settings();
                            $shipping_settings =  array();
                            if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' && is_array( $results ) && count( $results ) > 0 ) {
                                foreach ( $results as $key => $value ) {
                                    $shipping_settings = get_option( $value->option_name );
                                    if( isset( $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] ) && $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'shipping_methods' ) {
                                        if( isset( $shipping_settings[ 'shipping_methods' ] ) ) {
                                            $shipping_methods = $shipping_settings[ 'shipping_methods' ];
                                            if( in_array( $shipping_class, $shipping_methods ) ) {
                                                $shipping_class_to_send = $shipping_class;
                                                if( isset( $shipping_settings[ 'enable_shipping_based_delivery' ] ) ) {
                                                    $enable_delivery_date = "shipping_class_settings";
                                                    $enable_delivery_date .= "," . $shipping_class_to_send;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $terms = get_the_terms( $product_id , 'product_cat' );
                            if( $terms != '' ) {
                                foreach ( $terms as $term => $val ) {
                                    $results = orddd_common::orddd_get_shipping_settings();
                                    $shipping_settings =  array();
                                    if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' && is_array( $results ) && count( $results ) > 0 ) {
                                        foreach ( $results as $key => $value ) {
                                            $shipping_settings = get_option( $value->option_name );
                                            if( isset( $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] ) && $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'product_categories' ) {
                                                if( isset( $shipping_settings[ 'product_categories' ] ) ) {
                                                    $product_categories = $shipping_settings[ 'product_categories' ];
                                                    if( in_array( $val->slug, $product_categories ) ) {
                                                        $category_to_send = $val->slug;
                                                        if( isset( $shipping_settings[ 'enable_shipping_based_delivery' ] ) ) {
                                                            $enable_delivery_date = "category_settings";
                                                            $enable_delivery_date .= "," . $category_to_send;
                                                            break 3;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $enable_delivery_date = 'global_settings';
                            }
                        }
                    }
                } else {
                    $enable_delivery_date = 'global_settings';
                }
            }
        } else {
            $enable_delivery_date = "no";
        } 
        echo $enable_delivery_date;
        die();
    }
}

$orddd_admin_delivery_class = new orddd_admin_delivery_class();
