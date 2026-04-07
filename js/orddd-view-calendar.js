/**
 * File for configuring Full Calendar
 *
 * @namespace orddd_delivery_calendar
 * @since 2.8.7
 */
jQuery(document).ready( function() {
    
    // Prevent the right click event on the Print button click on Delivery Calendar Page. 
    jQuery( "#orddd_print_orders" ).on("contextmenu", function(e) {
        e.preventDefault();
    });   

    // The auxclick event is fired at an Element when a non-primary pointing device button (any mouse button other than the primary—usually leftmost—button) 
    // has been pressed and released both within the same element. This is added for the mouse roller click.
    // This event is not supported on the Safari browser and IE. 
    jQuery( "#orddd_print_orders" ).on("auxclick", function(e) {
		e.preventDefault();
    }); 

	var date = new Date();
	var d = date.getDate(); var m = date.getMonth(); var y = date.getFullYear();
	var load_previous_view_url = orddd.pluginurl;

	// Fields with id prev_order_status and prev_event_type are hidden fields.

	// Check for the selected status and pre populate that statuses in the dropdown
	// when the admin visits again. By default, Processing, Completed, On-hold and Pending payment will come.
	var prev_order_status = jQuery( "#prev_order_status" ).val();
	if ( localStorage.getItem( "delivery_calendar_last_order_statuses" ) != null ) {
		// Remove all selected options from the dropdown.
		jQuery( ".orddd_filter_by_order_status option:selected" ).removeAttr( "selected" );

		//Reselect the previously selected options in the dropdown.
		prev_order_status = localStorage.getItem( "delivery_calendar_last_order_statuses" );
		prev_order_status_arr = prev_order_status.split( "," );
		for( i = 0; i < prev_order_status_arr.length; i++ ) {
			jQuery( ".orddd_filter_by_order_status option[value='" + prev_order_status_arr[i] + "']").attr( "selected", 1 );
        }

        //Set the previous order status as the previously selected status.
		jQuery( "#prev_order_status" ).val( prev_order_status );
	}
	
	//Fetch the last view of the Delivery Calendar from the Local Storage.
	if ( localStorage.getItem( "delivery_calendar_last_view" ) == 'order' ) {

		// Pass the order type as the previous order statuses and event type as order to fetch events by default on page load.
		// Pass the previous order statuses for which the events should be displayed in the Calendar. 
		// This is required as when the default type is Orders and when an admin changes the order statuses,
		// the url for refetching events does not have statues and hence it displays the default events and not 
		// with the selected statuses.
		load_previous_view_url = orddd.pluginurl + '&eventType=order' + '&orderType=' + prev_order_status;
		
		//Set the filter value to Orders.
		jQuery( '.orddd_filter_delivery_calendar' ).val( 'order' );

		//Set the previous event type as orders.
		jQuery( "#prev_event_type" ).val( "order" );
	} else {
		// Pass the order type as the previous order statuses and event type as order to fetch events by default on page load.
		load_previous_view_url = orddd.pluginurl + '&eventType=product' + '&orderType=' + prev_order_status;
		
		//Set the filter value to product.
		jQuery( '.orddd_filter_delivery_calendar' ).val( 'product' );

		//Set the previous event type as products.
		jQuery( "#prev_event_type" ).val( "product" );
	}

    jQuery( '#calendar' ).fullCalendar({
    	/* SETTINGS */ 
    	header: {
    		left: 'prev, next today',
    		center: 'title',
    		right: 'month, agendaWeek, agendaDay',
    		ignoreTimezone: false    		
    	},
    	lang: jsArgs.calendar_language,
    	month: true,
    	week: true,
    	day: true,
    	agenda: false,
    	basic: true,
    	eventLimit: true, // If you set a number it will hide the itens
        eventLimitText: "More", // Default is `more` (or "more" in the lang you pick in the option)
        theme: true,
    	/*
    	 * defaultView option used to define which view to show by default, for example we have used agendaWeek.
		 */
		defaultView: 'agendaWeek',
		/*
		 * selectable:true will enable user to select datetime slot selectHelper will add helpers for selectable.
		 */
		selectable: false,
		selectHelper: false,
		
		/*
		 * editable: true allow user to edit events.
		 */
		editable: false,
		
		/*
		 * events is the main option for calendar. for demo we have added predefined events in json object.
		 */
		
		//options
		aspectRatio: 1.9,

		events: load_previous_view_url,
		
	    loading: function( isLoading, view ) {
	    	if( isLoading == true ) {
	    		jQuery( "#orddd_events_loader" ).show();
	    	} else if( isLoading == false ) {
	    		jQuery( "#orddd_events_loader" ).hide();
	    	}
	    },
	    
	    timeFormat: {
	    	agenda: 'h(:mm)t'
	    },
		
		eventRender: function( event, element ) {
			element.css('cursor', 'pointer'); // this is used to disply the cursor as a hand for events.
			var event_data = { 
				action : 'orddd_order_calendar_content', 
				event_product_id: event.event_product_id,
				event_product_qty: event.event_product_qty,
				order_id : event.id,
				event_value : event.value,
				event_date : event.delivery_date, 
				event_timeslot: event.time_slot,
				event_type : event.eventtype
			};
			element.qtip({
				content:{
					text : 'Loading...',
					button: 'Close', // It will disply Close button on the tool tip.
					ajax : {
						url : orddd.ajaxurl,
						type : "POST",
						data : event_data
					}
				},
				show: {
                    event: 'click', // Show tooltip only on click of the vent
                    solo: true // Disply only One tool tip at time, hide other all tool tip
                 },
				position: {
					my: 'bottom right', // this is for the botton v shape icon position.
					at: 'top right' // this is for the content box position

					},
					hide: 'unfocus', //this is used to keep the hover effect untill click outside on calender. For clicking the order number
					style: {
						classes: 'qtip-light qtip-shadow'
					}
			});
		},
		dayRender: function( date, cell ) {
			var dateobj = date.toDate();
			var holidays = eval( '[' + jsArgs.orddd_holidays + ']' );
			var m = dateobj.getMonth(), d = dateobj.getDate(), y = dateobj.getFullYear();
			if( jQuery.inArray( ( m+1 ) + '-' + d + '-' + y, holidays ) != -1 || jQuery.inArray( ( m+1 ) + '-' + d, holidays ) != -1 ) {
				cell.css( "background", jsArgs.orddd_holiday_color );
			}
	    },
	});
   
    jQuery( '.orddd_filter_delivery_calendar' ).select2({ minimumResultsForSearch: -1, width: '160px' });
    jQuery( '.orddd_filter_delivery_calendar' ).on( 'change', function(){
    	var prev_order_status = jQuery( "#prev_order_status" ).val();
        if ( jQuery( this ).val() == 'order') {
            jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl );
            jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl + "&eventType=order" + "&orderType=" + prev_order_status );
            jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl + "&eventType=product" + "&orderType=" + prev_order_status );
            jQuery( '#calendar' ).fullCalendar( 'refetchEvents');
        
            jQuery( '#calendar' ).fullCalendar( 'addEventSource', orddd.pluginurl + "&eventType=order" + "&orderType=" + prev_order_status );
            jQuery( '#calendar' ).fullCalendar( 'refetchEvents');
            jQuery( "#prev_event_type" ).val( "order" );
    		localStorage.setItem( "delivery_calendar_last_view", jQuery( this ).val() );
        } else {
        	jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl );
        	jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl + "&eventType=order" + "&orderType=" + prev_order_status );
        	jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl + "&eventType=product" + "&orderType=" + prev_order_status );
        	jQuery( '#calendar' ).fullCalendar( 'refetchEvents');
	        
        	jQuery( '#calendar' ).fullCalendar( 'addEventSource', orddd.pluginurl + "&eventType=product" + "&orderType=" + prev_order_status );
        	jQuery( '#calendar' ).fullCalendar( 'refetchEvents');
        	jQuery( "#prev_event_type" ).val( "product" );
    		localStorage.setItem( "delivery_calendar_last_view", 'product' );
        }
    });
    
    jQuery( '.orddd_filter_by_order_status' ).select2();
    jQuery( '.orddd_filter_by_order_status' ).select2({ width: '310px' });

    jQuery( '.orddd_filter_by_order_status' ).on( 'change', function() {
        var prev_order_status = jQuery( "#prev_order_status" ).val();
    	var order_status = jQuery( this ).val();
    	var event_type = jQuery( "#prev_event_type" ).val();
    	
    	jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl );
    	jQuery( '#calendar' ).fullCalendar( 'removeEventSource', orddd.pluginurl + "&eventType=" + event_type + "&orderType=" + prev_order_status );
    	
    	jQuery( '#calendar' ).fullCalendar( 'addEventSource', orddd.pluginurl + "&eventType=" + event_type + "&orderType=" + order_status );
    	jQuery( '#calendar' ).fullCalendar( 'refetchEvents' );
    	jQuery( "#prev_order_status" ).val( order_status );

    	// Store the selected statuses in the local storage.
    	localStorage.setItem( "delivery_calendar_last_order_statuses", order_status );
    });
});