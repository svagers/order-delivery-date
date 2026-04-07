/**
 * Events added to perform UI changes in the admin
 * 
 * @namespace orddd_admin_js
 * @since 7.5
 */

jQuery( function( $ ) {	
	$(document).on( 'change', '#orddd_delivery_date_1, #orddd_delivery_date_2, #orddd_delivery_date_3, #orddd_delivery_date', function() {
		var ordd_id    = $(this).attr( "id" );
		var ordd_value = this.value.length; 

		if ( "orddd_delivery_date_1" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges_1" ).prop( "disabled", true );
			$( "#specific_charges_label_1" ).prop( "disabled", true );
		} else if ( "orddd_delivery_date_2" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges_2" ).prop( "disabled", true );
			$( "#specific_charges_label_2" ).prop( "disabled", true );
		} else if ( "orddd_delivery_date_3" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges_3" ).prop( "disabled", true );
			$( "#specific_charges_label_3" ).prop( "disabled", true );
		} else if( "orddd_delivery_date" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges" ).prop( "disabled", true );
			$( "#specific_charges_label" ).prop( "disabled", true );
		} else if ( "orddd_delivery_date_1" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges_1" ).prop( "disabled", false );
			$( "#specific_charges_label_1" ).prop( "disabled", false );
		} else if ( "orddd_delivery_date_2" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges_2" ).prop( "disabled", false );
			$( "#specific_charges_label_2" ).prop( "disabled", false );
		} else if( "orddd_delivery_date_3" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges_3" ).prop( "disabled", false );
			$( "#specific_charges_label_3" ).prop( "disabled", false );
		} else if( "orddd_delivery_date" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges" ).prop( "disabled", false );
			$( "#specific_charges_label" ).prop( "disabled", false );
		}
	});

	jQuery( document ).ready( function() {
		// Add Color Picker to all inputs that have 'color-field' class
		jQuery( '.cpa-color-picker' ).wpColorPicker();

		if( typeof jQuery( "#is_shipping_based_page" ).val() != "undefined" && jQuery( "#is_shipping_based_page" ).val() != '' ) {
			if ( jQuery( "input[type=radio][id=\"orddd_delivery_settings_type\"][value=\"product_categories\"]" ).is(":checked") ) {
				jQuery( '.delivery_type_options' ).slideUp();
				jQuery( '.delivery_type_product_categories' ).slideDown();
		        i = 0;
		        var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
		        jQuery( ".form-table" ).each( function() {
		            if( i == 1 ) {
		                k = 0;
		                var row = jQuery( this ).find( "tr" );
		                jQuery.each( row , function() {
		                    if( k == 0 ) {
		                    	if( isChecked == 'true' ) {
		                    		jQuery( this ).fadeIn();            	
		                    	}
		                    }
		                    k++ 
		                });
		            } 
		            i++;
		        }); 
			} else if ( jQuery( "input[type=radio][id=\"orddd_delivery_settings_type\"][value=\"orddd_locations\"]" ).is(":checked") ) { 
				jQuery( '.delivery_type_options' ).slideUp();
				jQuery( '.delivery_type_orddd_locations' ).slideDown();
		        i = 0;
		        jQuery( ".form-table" ).each( function() {
		            if( i == 1 ) {
		                k = 0;
		                var row = jQuery( this ).find( "tr" );
		                jQuery.each( row , function() {
		                    if( k == 0 ) {
		                    	jQuery( this ).fadeOut();
		                    }
		                    k++ 
		                });
		            } 
		            i++;
		        }); 
			} else {
				jQuery( '.delivery_type_options' ).slideDown();
				jQuery( '.delivery_type_product_categories' ).slideUp();
     		    jQuery( '.delivery_type_orddd_locations' ).slideUp();
		        i = 0;
		        jQuery( ".form-table" ).each( function() {
		            if( i == 1 ) {
		                k = 0;
		                var row = jQuery( this ).find( "tr" );
		                jQuery.each( row , function() {
		                    if( k == 0 ) {
		                        jQuery( this ).fadeOut();            
		                    }
		                    k++ 
		                });
		            } 
		            i++;
		        } ); 
			}
		}

		var month_short_names =  ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		var formats = [ "mm-dd-yy", "d.m.y", "d M, yy","MM d, yy" ];

        jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "en-GB" ] );
        jQuery( "#orddd_shipping_based_holiday_from_date" ).width( "160px" );
        jQuery( "#orddd_shipping_based_holiday_to_date" ).width( "160px" );

        jQuery( "#orddd_shipping_based_holiday_from_date" ).val("").datepicker( {
            constrainInput: true,
            dateFormat: formats[0],
            onSelect: function( selectedDate,inst ) {
                var monthValue = inst.selectedMonth+1;
				var dayValue = inst.selectedDay;
				var yearValue = inst.selectedYear;
                var current_dt = dayValue + "-" + monthValue + "-" + yearValue;
                var to_date = jQuery( "#orddd_shipping_based_holiday_to_date" ).val();
                if ( to_date == "" ) {
                    var split = current_dt.split( "-" );
					split[1] = split[1] - 1;
					var minDate = new Date( split[2], split[1], split[0] );
                    jQuery( "#orddd_shipping_based_holiday_to_date" ).datepicker( "setDate",minDate );
                }
			},
			firstDay: jQuery("input[name='orddd_holiday_start_day']").val()
		} );
            
		jQuery( "#orddd_shipping_based_holiday_to_date" ).val("").datepicker( {
		    constrainInput: true,
			dateFormat: formats[0],
			firstDay: jQuery("input[name='orddd_holiday_start_day']").val()
		} );

        jQuery( "table#orddd_holidays_list" ).on( "click", "a.confirmation_holidays", function() {
            var holidays_hidden = jQuery( "#orddd_holiday_hidden" ).val();
            var holiday_name = jQuery( "table#orddd_holidays_list tr#"+ this.id + " td#orddd_holiday_name" ).html();
            var holiday_date = jQuery( "table#orddd_holidays_list tr#"+ this.id + " td#orddd_holiday_date" ).html();
            var recurring_type_text = jQuery( "table#orddd_holidays_list tr#"+ this.id + " td#orddd_allow_recurring_type" ).html();
            if( recurring_type_text == localizeStrings.holidayrecurringText ) {
            	var recurring_type = 'on';
            } else {
            	var recurring_type = '';
            }
            var split_date = holiday_date.split( "-" );            
            var dt = new Date ( split_date[ 0 ] + "/" + split_date[ 1 ] + "/" + split_date[ 2 ] );
            var date = ( dt.getMonth() + 1 ) + "-" + dt.getDate() + "-" + dt.getFullYear();    
            var substring = "{" + holiday_name + ":" + date + ":" + recurring_type + "},";
            var updatedString = holidays_hidden.replace( substring, "" );
            jQuery( "#orddd_holiday_hidden" ).val( updatedString );
            jQuery( "table#orddd_holidays_list tr#"+ this.id ).remove();
        });
        
        jQuery( "#save_holidays" ).click(function() {
            var holidays_row_arr = [];
            var holidays = [];
            
            var row = jQuery( "#orddd_holiday_hidden" ).val();
            if( row != "" ) {
                holidays_row_arr = row.split(",");
                for( i = 0; i < holidays_row_arr.length; i++ ) {
                    if( holidays_row_arr[ i ] != "" ) {
                        var string = holidays_row_arr[ i ].replace( "{", "" );
                        string = string.replace( "}", "" );
                        var string_arr = string.split( ":" );
                        holidays.push( string_arr[ 1 ] );
                    }
                }
            }
                    
	        var split_from_date = jQuery( "#orddd_shipping_based_holiday_from_date" ).val().split( "-" );
	        split_from_date[0] = split_from_date[0] - 1;
	        var from_date = new Date( split_from_date[2], split_from_date[0], split_from_date[1] );
	        
	        var split_to_date = jQuery( "#orddd_shipping_based_holiday_to_date" ).val().split( "-" );
	        split_to_date[0] = split_to_date[0] - 1;
	        var to_date = new Date( split_to_date[2], split_to_date[0], split_to_date[1] );
                    
            var timediff = ( ( to_date.getTime() - from_date.getTime() ) / ( 1000 * 60 * 60 * 24 ) ) + 1;
            var date = jQuery( "#orddd_shipping_based_holiday_from_date" ).val();
            for ( i = 1; i <= timediff; i++ ) {
                if( from_date <= to_date ) {
                    hidden_date = ( from_date.getMonth() + 1 ) + "-" + from_date.getDate() + "-" + from_date.getFullYear();
                    if( jQuery.inArray( hidden_date, holidays ) == -1 ) {  
                        var rowCount = jQuery( "#orddd_holidays_list tr" ).length;
                        if( rowCount == 0 ) {
                            jQuery( "#orddd_holidays_list" ).append( "<tr class=\"orddd_common_list_tr\"><th class=\"orddd_holidays_list\"> " + localizeStrings.holidaynameText + "</th><th class=\"orddd_holidays_list\">" + localizeStrings.holidaydateText + "</th><th class=\"orddd_holidays_list\">" + localizeStrings.holidaytypeText + "</th><th class=\"orddd_holidays_list\">" + localizeStrings.holidayactionText + "</th></tr>" );
                            var rowCount = 1;
                        }

                        rowCount = rowCount - 1;
                        if( from_date.getDate() < 10 ){ 
                            dd = "0" + from_date.getDate();
                        } else {
                            dd = from_date.getDate();
                        }

                        if( ( from_date.getMonth() + 1 ) < 10 ){ 
                            mm = "0" + ( from_date.getMonth() + 1 );
                        } else {
                            mm = ( from_date.getMonth() + 1 );
                        }

                        date =  mm + "-" + dd + "-" + from_date.getFullYear();

                        var recurring_type_text = localizeStrings.holidaycurrentText;
                        var recurring_type = '';
                        var isChecked = jQuery( "#orddd_shipping_based_allow_recurring_holiday" ).is( ":checked" );
                        
                		if( isChecked == true ) {
                        	recurring_type_text = localizeStrings.holidayrecurringText;
                        	recurring_type = 'on';
                        }

                        jQuery( "#orddd_holidays_list tr:last" ).after( "<tr class=\"orddd_common_list_tr\" id=\"orddd_delete_holidays_" + rowCount + "\"><td class=\"orddd_holidays_list\" id=\"orddd_holiday_name\">" + jQuery("#orddd_shipping_based_holiday_name").val() + "</td><td class=\"orddd_holidays_list\" id=\"orddd_holiday_date\">" + date +"</td><td class=\"orddd_holidays_list\" id=\"orddd_allow_recurring_type\">" + recurring_type_text +"</td><td class=\"orddd_holidays_list\"><a href=\"javascript:void(0)\" class=\"confirmation_holidays\" id=\"orddd_delete_holidays_" + rowCount + "\">" + localizeStrings.holidaydeleteText + "</a></td></tr>" );

                        row += "{" + jQuery( "#orddd_shipping_based_holiday_name" ).val() + ":" + hidden_date + ":" + recurring_type + "},";
                    }

                    from_date.setDate( from_date.getDate() + 1 );
                }
            }

            jQuery( "#orddd_holiday_hidden" ).val( row );
            jQuery( "#orddd_shipping_based_holiday_from_date" ).datepicker( "setDate", "" );
            jQuery( "#orddd_shipping_based_holiday_to_date" ).datepicker( "setDate", "" );
            jQuery( "#orddd_shipping_based_holiday_name" ).val( "" );
            jQuery( "#orddd_shipping_based_allow_recurring_holiday" ).prop( "checked", false );
        });
	});

	if( typeof jQuery( "#is_shipping_based_page" ).val() != "undefined" && jQuery( "#is_shipping_based_page" ).val() != '' ) {
	    jQuery( '.orddd_shipping_methods' ).select2();
	    jQuery( '.orddd_shipping_methods' ).css({'width': '300px' });
	    jQuery( "input[type=radio][id=\"orddd_delivery_settings_type\"]" ).on( 'change', function() {
			if ( jQuery( this ).is(':checked') ) {
				var value = jQuery( this ).val();
				jQuery( '.delivery_type_options' ).slideUp();
				jQuery( '.delivery_type_' + value ).slideDown();
				var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
	            if( value == 'product_categories' ) {
	                i = 0;
	                jQuery( ".form-table" ).each( function() {
	                    if( i == 1 ) {
	                        k = 0;
	                        var row = jQuery( this ).find( "tr" );
	                        jQuery.each( row , function() {
	                            if( k == 0 ) {
	                            	if( isChecked == true ) {
	                                	jQuery( this ).fadeIn();            
	                                }
	                            }
	                            k++ 
	                        });
	                    } 
	                    i++;
	                } ); 
	            } else {
	                i = 0;
	                jQuery( ".form-table" ).each( function() {
	                    if( i == 1 ) {
	                        k = 0;
	                        var row = jQuery( this ).find( "tr" );
	                        jQuery.each( row , function() {
	                            if( k == 0 ) {
	                                jQuery( this ).fadeOut();            
	                            }
	                            k++ 
	                        });
	                    } 
	                    i++;
	                } ); 
	            }
			}
		});
	}

	from_value = $( '#orddd_delivery_from_hours' ).val();
	to_value = $( '#orddd_delivery_to_hours' ).val();
	for( i = from_value - 1; i > 0; i-- ) {
		$( '#orddd_delivery_to_hours option[value="'+i+'"]' ).attr( 'disabled', true );		
	}
	
	$( '#orddd_delivery_from_hours' ).on( 'select change', function() {
		from_value = $( '#orddd_delivery_from_hours' ).val();
		to_value = $( '#orddd_delivery_to_hours' ).val();
		for( i = from_value - 1; i >= 0; i-- ) {
			if( i != 0 ) {
				$( '#orddd_delivery_to_hours option[value="'+i+'"]' ).attr( 'disabled', true );		
			}
			$( '#orddd_delivery_to_hours' ).val( from_value );
		}

		for( j = from_value ; j < 24 ; j++ ) {
			$( '#orddd_delivery_to_hours option[value="'+j+'"]' ).attr( 'disabled', false );		
		}
	});
		
});