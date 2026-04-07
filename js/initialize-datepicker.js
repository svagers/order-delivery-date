/**
 * Allows to initiliaze/load the settings in the calendar on frontend.
 *
 * @namespace orddd_initialize
 * @since 1.0
 */
jQuery( document ).ready(function() {    
    //Select Woo class for time alot and pickup locations field. 
    var local_storage_postcode = localStorage.getItem( "orddd_availability_postcode" );
    if( local_storage_postcode != '' && local_storage_postcode != 'undefined' && local_storage_postcode != null ) {
        jQuery( '#billing_postcode' ).val( local_storage_postcode );    
    }
    
    var woo_version = jsL10n.wooVersion;
    if ( woo_version >= '3.2.0' ) {
        if ( jQuery().selectWoo ) {
            jQuery( '#orddd_time_slot' ).selectWoo();
            jQuery( '#orddd_locations' ).selectWoo();
        }
    }

    //Hide pickup location field if the shipping method is not selected. 
    var shipping_method = orddd_get_selected_shipping_method();
    if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
        jQuery( "#orddd_locations_field" ).hide();
        jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
    }

    
    jQuery( "#orddd_unique_custom_settings" ).val( "" );

    //Validate the time field if set to mandatory
    var parent =  jQuery( '#orddd_time_slot' ).closest( '.form-row' );
    validated = true;
    jQuery( 'form.checkout' ).on( 'input validate change','#orddd_time_slot', function( e ){
        if ( 'validate' === e.type || 'change' === e.type ) {
            if( jQuery('#orddd_time_slot').val() == 'select' && jQuery( '#orddd_timeslot_field_mandatory' ).val() == 'checked' ) {
                jQuery(parent).removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
                validated = false;
            }
        }
        
        if( validated ) {
            jQuery(parent).removeClass( 'woocommerce-invalid woocommerce-invalid-required-field' ).addClass( 'woocommerce-validated' );
        }
    });

    //Clear local storage for the selected delivery date in next 2 hours. 
    var orddd_last_check_date = localStorage.getItem( "orddd_storage_next_time" );
    var current_date = jQuery( "#orddd_current_day" ).val();
    
    if( current_date != '' && typeof( current_date ) != 'undefined' ) {
        var split_current_date = current_date.split( '-' );
        var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );
    } else {
        var ordd_next_date = new Date();
    }

    if ( null != orddd_last_check_date ) {
        if ( ordd_next_date.getTime() > orddd_last_check_date ) {
            localStorage.removeItem( "orddd_storage_next_time" );
            localStorage.removeItem( "e_deliverydate_session" );
            localStorage.removeItem( "h_deliverydate_session" );
            localStorage.removeItem( "orddd_time_slot" );
            localStorage.removeItem( "orddd_availability_postcode" );
        }
    }

    jQuery(document).on( "ajaxComplete", function( event, xhr, options ) {
        if( options.url.indexOf( "wc-ajax=checkout" ) !== -1 ) {
            if( xhr.statusText != "abort" ) {
                localStorage.removeItem( "orddd_storage_next_time" );
                localStorage.removeItem( "e_deliverydate_session" );
                localStorage.removeItem( "h_deliverydate_session" );
                localStorage.removeItem( "orddd_time_slot" );
                localStorage.removeItem( "orddd_availability_postcode" );
            }
        }
    });

    
    var startDaysDisabled = [];
    jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).prop( "disabled", true );
    //Assign options to delivery date on checkout page. 
    window.onload = load_general_settings();

    var orddd_available_dates_color = jQuery( "#orddd_available_dates_color" ).val() + '59';
    var orddd_booked_dates_color    = jQuery( "#orddd_booked_dates_color" ).val() + '59';

    jQuery( ".partially-booked" ).children().attr( 'style', 'background: linear-gradient(to bottom right, ' + orddd_booked_dates_color + ' 0%, ' + orddd_booked_dates_color + ' 50%, ' + orddd_available_dates_color + ' 50%, ' + orddd_available_dates_color + ' 100%);' );
    jQuery( ".available-deliveries" ).children().attr( 'style', 'background: ' + orddd_available_dates_color + ' !important;' );

    jQuery( document ).on( "change", "#orddd_time_slot", function() {
        var shipping_method = orddd_get_selected_shipping_method();
        jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
        jQuery( "#hidden_h_deliverydate" ).val( jQuery( "#h_deliverydate" ).val() );
        jQuery( "#hidden_timeslot" ).val( jQuery(this).find(":selected").val() );
        jQuery( "#hidden_shipping_method" ).val( shipping_method );
        jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );

        if( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
            var selected_val = jQuery(this).val();
            jQuery(this).find('option[value="'+ selected_val + '"]').prop( 'selected', true );
            localStorage.setItem( "e_deliverydate_session", jQuery( "#e_deliverydate" ).val() );
            localStorage.setItem( "h_deliverydate_session", jQuery( "#h_deliverydate" ).val() );
            localStorage.setItem( "orddd_time_slot", selected_val );

            var current_date = jQuery( "#orddd_current_day" ).val();
            var split_current_date = current_date.split( '-' );
            var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );

            ordd_next_date.setHours( ordd_next_date.getHours() + 2 );
            localStorage.setItem( "orddd_storage_next_time", ordd_next_date.getTime() );
        }

        jQuery( "body" ).trigger( "update_checkout" );
        if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && jQuery( '#orddd_is_cart' ).val() == '1' ) {
            jQuery( "body" ).trigger( "wc_update_cart" );
        }
    });

    if ( jQuery( "#orddd_field_note_text" ).val() != '' ) {
        jQuery( "#e_deliverydate_field" ).append( "<br><small class='orddd_field_note'>" + jQuery( "#orddd_field_note_text" ).val() + "</small>" );
    }

    
    jQuery(document).on( "change", "select[name=\"orddd_locations\"]", function() {
        if ( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == 'on' ) {
            var update_settings = load_delivery_date();
            if( update_settings == 'yes' && jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == 'on' ) {
                orddd_autofil_date_time();
            }
        }
        localStorage.setItem( "orddd_location_session", jQuery(this).val() );
    });
    
       
    jQuery(document).on( "change", "input[name=\"shipping_method[0]\"]", function() {
        if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
            localStorage.removeItem( "orddd_storage_next_time" );
            localStorage.removeItem( "e_deliverydate_session" );
            localStorage.removeItem( "h_deliverydate_session" );
            localStorage.removeItem( "orddd_time_slot" );  
        }
        orddd_update_delivery_session();
    });
        
    jQuery(document).on( "change", "select[name=\"shipping_method[0]\"]", function() {
        orddd_update_delivery_session();
    });

    jQuery(document).on( "change", "input[name=\"shipping_method_[0]\"]", function() {
        orddd_update_delivery_session();
    });

    jQuery(document).on( "change", '#ship-to-different-address input', function() {
        orddd_update_delivery_session();
    });

    if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).width( "150px" );
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).attr( "readonly", true );
    }

    var formats = ["d.m.y", "d MM, yy","MM d, yy"];
    jQuery.extend( jQuery.datepicker, { afterShow: function( event ) {
        var z_index = 9999;
        if( jQuery.datepicker._getInst( event.target ).dpDiv.css('z-index') > z_index ) {
            z_index = jQuery.datepicker._getInst( event.target ).dpDiv.css('z-index');
        }
        jQuery.datepicker._getInst( event.target ).dpDiv.css( "z-index", z_index );
        
        // If the device is mobile then make the calendar appear below the input field.
        if( screen.width < 600 ) {
            jQuery.datepicker._getInst( event.target ).dpDiv.css( { top: jQuery("#e_deliverydate").offset().top + 35, left: jQuery("#e_deliverydate").offset().left} );
        }
            if( jQuery( "#orddd_number_of_months" ).val() == "1" && '1' == jQuery( "#orddd_is_admin" ).val() ) {
                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "17em" );
            } else if ( jQuery( "#orddd_number_of_months" ).val() == "1" ) {
                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "300px" );
            } else {
                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "41em" );
            }
        }
    });

    // If the device is mobile then the input field will move to the top.
    if( screen.width < 600 ) {
        jQuery("#e_deliverydate").focus(function () {    
            jQuery('html, body').animate({ scrollTop: jQuery(this).offset().top - 25 }, 10);
        });
    }
    
    jQuery(document).on( 'change', '.address-field input.input-text, .update_totals_on_change input.input-text, .address-field select', function( e ) {
        if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" &&  jQuery( '#orddd_disable_delivery_fields' ).val() == 'yes' ) {
            jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", true );    
            jQuery( "#orddd_time_slot" ).attr( "disabled", "disabled" );
        }
    } );

    var old_zone_id = "";
    var old_shipping_method = "";
    jQuery(document).on( "ajaxComplete", function( event, xhr, options ) {
        if ( options.url.indexOf( "wc-ajax=get_refreshed_fragments" ) !== -1 ) {
            return;
        }
        var update_settings = 'no';
        
        var new_shipping_postcode = "";
        var new_shipping_country = "";
        var new_shipping_state = "";

        var is_shipping_checked = true;
        if( options.url.indexOf( "wc-ajax=update_order_review" ) !== -1 ) {
            var new_billing_postcode = jQuery( "#billing_postcode" ).val();
            var new_billing_country = jQuery( "#billing_country" ).val();
            var new_billing_state = jQuery( "#billing_state" ).val();

            var new_shipping_postcode = jQuery( "#shipping_postcode" ).val();
            var new_shipping_country = jQuery( "#shipping_country" ).val();
            var new_shipping_state = jQuery( "#shipping_state" ).val();
            if( xhr.statusText != "abort" ) {
                update_settings = 'yes';
            }
            var is_shipping_checked = jQuery( '#ship-to-different-address input' ).is( ":checked" );
        } else if ( options.url.indexOf( "cart/?remove_item=" ) !== -1 ||
            options.url.indexOf( "cart/?undo_item=" ) !== -1 ) {
            if( xhr.statusText != "abort" ) {
                //Sent cart_delete as a parameter in this function, as we want to update orddd_hidden_vars_str variable only on 
                //Cart item deletion or added again using Undo option on the cart page.
                orddd_update_delivery_session( 'cart_delete' );
            }
        } else if( options.url.indexOf( "wc-ajax=update_shipping_method" ) !== -1 ) {

            var new_billing_postcode = jQuery( "#calc_shipping_postcode" ).val();
            var new_billing_country = jQuery( "#calc_shipping_country" ).val();
            var new_billing_state = jQuery( "#calc_shipping_state" ).val();
            if( xhr.statusText != "abort" ) {
                update_settings = 'yes';
            }
            var is_shipping_checked = false;
        }

        if( update_settings == 'yes' ) {
            var shipping_method = orddd_get_selected_shipping_method();
            if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
                jQuery( "#orddd_locations_field" ).hide();
                jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
            } else {
                jQuery( "#orddd_locations_field" ).show();    
            }

            if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                var data = {
                    action: 'orddd_get_zone_id',
                    billing_postcode: new_billing_postcode,
                    billing_country: new_billing_country,
                    billing_state: new_billing_state,
                    shipping_postcode: new_shipping_postcode,
                    shipping_country: new_shipping_country,
                    shipping_state: new_shipping_state,
                    shipping_checkbox: is_shipping_checked
                };

                if( ( new_billing_postcode != '' && new_billing_country != '' ) && 
                    ( false == is_shipping_checked || 
                    ( true == is_shipping_checked && '' != new_shipping_country && '' != new_shipping_postcode ) ) 
                    ) {

                    jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                        var zone_id = 0;
                        if( "" != response ) {
                            var zone_shipping_details = response.split('-');
                            var zone_id = zone_shipping_details[ 0 ];
                            var orddd_shipping_id = zone_shipping_details[ 1 ];
                        }
                        jQuery( "#orddd_zone_id" ).val( zone_id );
                        jQuery( "#orddd_shipping_id" ).val( orddd_shipping_id );

                        if ( old_zone_id != zone_id || old_shipping_method != orddd_shipping_id ) {

                            jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );
                            jQuery( "#orddd_time_slot" ).removeAttr( "disabled", "disabled" );
                            load_delivery_date();

                            // running the session related code only if auto-populate is not set to ON
                            // because orddd_autofil_date_time() already runs the session code too
                            if ( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
                                orddd_autofil_date_time();
                            } else { 
                                orddd_set_date_from_session();
                            }
                            old_zone_id = zone_id;
                            old_shipping_method = orddd_shipping_id; 
                        } else {
                            jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );    
                            jQuery( "#orddd_time_slot" ).removeAttr( "disabled", "disabled" );
                        }
                    });
                } else {
                    if( jQuery( '#orddd_disable_delivery_fields' ).val() == 'yes' ) {
                        jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", true );    
                    } else {
                        jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );    
                    }
                    jQuery( "#orddd_time_slot" ).removeAttr( "disabled", "disabled" );
                }
            }
        }
    });
              
    if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( "#save_delivery_date" ).click(function() {
        	save_delivery_dates( 'no' );
        }); 

        jQuery( "#save_delivery_date_and_notify" ).click(function() {
        	save_delivery_dates( 'yes' );
        });        
    }

    if( '1' == jQuery( "#orddd_is_account_page" ).val() ) {
        window.onload = orddd_my_account_init;
    } else if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        window.onload = orddd_init;
    } else {
        window.onload = load_functions;
    }

    jQuery( '#edit_delivery_date' ).on( 'click', function() {
        jQuery( '#orddd_edit_div' ).toggle();
    });
    jQuery( '#cancel_delivery_date' ).on( 'click', function() {
        jQuery( '#orddd_edit_div' ).fadeOut();
    });
    
    jQuery( '#update_date' ).on( 'click', function() {
        var ordd_date_and_time_validation = "allow";

        var ordd_is_delivery_date_mandatory = jQuery( '#orddd_date_field_mandatory' ).val();
        var ordd_is_delivery_time_mandatory = jQuery( '#orddd_timeslot_field_mandatory' ).val();
        
        var ordd_get_delivery_date = jQuery( '#e_deliverydate' ).val();
        var ordd_get_delivery_time = jQuery( '#orddd_time_slot' ).val();

        var ordd_date_label        = jQuery( '#orddd_field_label' ).val();
        var ordd_time_label        = jQuery( '#orddd_timeslot_field_label' ).val();

        var ordd_validation_message = "";
        if ( "checked" == ordd_is_delivery_date_mandatory && "checked" == ordd_is_delivery_time_mandatory ) {
            ordd_validation_message =  ordd_date_label + " is a required field." + ordd_time_label + " is a required field.";
            if ( ordd_get_delivery_date.length == 0 ||  "select" == ordd_get_delivery_time ) {
                ordd_date_and_time_validation = "no";
            }
        }else if ( "checked" == ordd_is_delivery_date_mandatory ) {
            ordd_validation_message = ordd_date_label +" is a required field.";
            if ( ordd_get_delivery_date.length == 0 ) {
                ordd_date_and_time_validation = "no";
            }
        } else if ( "checked" == ordd_is_delivery_time_mandatory ) {
            ordd_validation_message = ordd_time_label + " is a required field.";
            if ( "select" == ordd_get_delivery_time ) {
                ordd_date_and_time_validation = "no";
            }
        }

        if ( "no" == ordd_date_and_time_validation ) {
            jQuery( "#display_update_message" ).css( "color","red" );
            jQuery( "#display_update_message" ).html( ordd_validation_message );
            jQuery( "#display_update_message" ).fadeIn();
            var delay = 2000; 
            setTimeout(function() {
                jQuery( "#display_update_message" ).fadeOut();
            }, delay );
        }

        if ( "allow" == ordd_date_and_time_validation ) {
            var data = {
                order_id: jQuery( "#orddd_my_account_order_id" ).val(),
                e_deliverydate: jQuery( '#e_deliverydate' ).val(),
                h_deliverydate: jQuery( '#h_deliverydate' ).val(),
                shipping_method: jQuery( '#shipping_method' ).val(),
                orddd_category_settings_to_load: jQuery( '#orddd_category_settings_to_load' ).val(),
                time_setting_enable_for_shipping_method: jQuery( '#time_setting_enable_for_shipping_method' ).val(),
                orddd_time_settings_selected: jQuery( '#orddd_time_settings_selected' ).val(),
                orddd_time_slot: jQuery( '#orddd_time_slot' ).val(),
                is_my_account: jQuery( '#orddd_is_account_page' ).val(),
                action: 'orddd_update_delivery_date'
            };
            jQuery( '#display_update_message' ).html( '<b>Saving...</b>' );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + 'admin-ajax.php', data, function( response, status ) {
                jQuery( '#display_update_message' ).html( jsL10n.success_delivery_date_message );
                var delay = 500; //10 second
                setTimeout(function() {
                      location.reload();
                }, delay);
            });
        }
    });

    //For flatsome theme
    if( jQuery('#e_deliverydate').parent().hasClass('fl-wrap') ) {
        jQuery('#e_deliverydate').parent().addClass('fl-is-active');
    }

    if( jQuery( '#orddd_time_slot' ).parent().hasClass('fl-wrap') ) {
        jQuery( '#orddd_time_slot' ).parent().addClass('fl-is-active');
    }

    if( jQuery( '#orddd_locations' ).parent().hasClass('fl-wrap') ) {
        jQuery( '#orddd_locations' ).parent().addClass('fl-is-active');
    }
});
