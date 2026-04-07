/**
 * Ajax to add/remove the Delivery fields for the Virtual products in the Admin order page.
 *
 * @namespace orddd_admin_delivery
 * @since 3.2
 */
jQuery(document).ajaxComplete( function( event, xhr, options ){
	var options_data = options.data;
	if( typeof( options_data ) !== "undefined" ){
		var options_arr = options_data.split( "&" );
		var order_item_ids = "";
		for( var i = 0; i <= options_arr.length; i++ ) {
			if( typeof( options_arr[ i ] ) !== "undefined" ){
				var option_value_arr = options_arr[ i ].split( "=" );
				if( option_value_arr[ 0 ] == "order_item_ids" ) {
					var order_item_ids = option_value_arr[ 1 ];
				}
				if( option_value_arr[ 0 ] == "action" && option_value_arr[ 1 ] == "woocommerce_remove_order_item" ) {
					 var data = {
							 order_item_ids: order_item_ids,
							 action: "orddd_remove_order_item"
					 };
					 jQuery.post( jQuery( "#orddd_admin_url" ).val() + "admin-ajax.php", data, function( response ) {
						 if( response == "yes" ) {
							 if( jQuery( "#admin_delivery_fields tr" ).length == 0 ) {
		                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"save_delivery_date_button\"><td><input type=\"button\" value=\"Update\" id=\"save_delivery_date\" class=\"save_button\"></td></tr>" );
		                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_time_slot_field\"><td>" + jQuery( "#orddd_time_field_name_admin" ).val() + "</td><td><select name=\"orddd_time_slot\" id=\"orddd_time_slot\" class=\"\" disabled=\"disabled\" style=\"cursor:not-allowed !important;width:150px;\" placeholder=\"\"><option value=\"0\">Select</option></select></td></tr>");
		                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\">" + jQuery( "#orddd_field_name_admin" ).val() + "</label></td><td><input type=\"text\" id=\"" + jQuery( "#orddd_field_name" ).val() + "\" name=\"" + jQuery( "#orddd_field_name" ).val() + "\" class=\"" + jQuery( "#orddd_field_name" ).val() + "\" style=\"cursor: text!important;\" readonly/><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
		                         jQuery( "#is_virtual_product" ).html("");
							 } 
							 load_delivery_date();
						 } else if( response == "no" ){
							 jQuery( "#admin_time_slot_field" ).remove();
							 jQuery( "#admin_delivery_date_field" ).remove()
			                 jQuery( "#save_delivery_date_button" ).remove();
							 jQuery( "#is_virtual_product" ).html( "Delivery date settings is not enabled for this product." );
						 } else if( response == "global_settings" ) {
							 if( jQuery( "#admin_delivery_fields tr" ).length == 0 ) {
		                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"save_delivery_date_button\"><td><input type=\"button\" value=\"Update\" id=\"save_delivery_date\" class=\"save_button\"></td></tr>" );
		                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_time_slot_field\"><td>" + jQuery( "#orddd_time_field_name_admin" ).val() + "</td><td><select name=\"orddd_time_slot\" id=\"orddd_time_slot\" class=\"\" disabled=\"disabled\" style=\"cursor:not-allowed !important;width:150px;\" placeholder=\"\"><option value=\"0\">Select</option></select></td></tr>");
		                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\">" + jQuery( "#orddd_field_name_admin" ).val() + "</label></td><td><input type=\"text\" id=\"" + jQuery( "#orddd_field_name" ).val() + "\" name=\"" + jQuery( "#orddd_field_name" ).val() + "\" class=\"" + jQuery( "#orddd_field_name" ).val() + "\" style=\"cursor: text!important;\" readonly/><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
		                         jQuery( "#is_virtual_product" ).html("");
							 } 
							 jQuery( "#orddd_category_settings_to_load" ).val( "" );
							 jQuery( "#orddd_shipping_class_settings_to_load" ).val( "" );
							 load_delivery_date();
						 } else {
							 var response_arr = response.split( "," );
							 if( response_arr[0] == "category_settings" ) {
								 if( jQuery( "#admin_delivery_fields tr" ).length == 0 ) {
			                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"save_delivery_date_button\"><td><input type=\"button\" value=\"Update\" id=\"save_delivery_date\" class=\"save_button\"></td></tr>" );
			                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_time_slot_field\"><td>" + jQuery( "#orddd_time_field_name_admin" ).val() + "</td><td><select name=\"orddd_time_slot\" id=\"orddd_time_slot\" class=\"\" disabled=\"disabled\" style=\"cursor:not-allowed !important;width:150px;\" placeholder=\"\"><option value=\"0\">Select</option></select></td></tr>");
			                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\">" + jQuery( "#orddd_field_name_admin" ).val() + "</label></td><td><input type=\"text\" id=\"" + jQuery( "#orddd_field_name" ).val() + "\" name=\"" + jQuery( "#orddd_field_name" ).val() + "\" class=\"" + jQuery( "#orddd_field_name" ).val() + "\" style=\"cursor: text!important;\" readonly/><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
			                         jQuery( "#is_virtual_product" ).html("");
								 } 
								 jQuery( "#orddd_category_settings_to_load" ).val( response_arr[1] );
								 load_delivery_date();
							 } else if( response_arr[0] == "shipping_class_settings" ) {
								if( jQuery( "#admin_delivery_fields tr" ).length == 0 ) {
			                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"save_delivery_date_button\"><td><input type=\"button\" value=\"Update\" id=\"save_delivery_date\" class=\"save_button\"></td></tr>" );
			                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_time_slot_field\"><td>" + jQuery( "#orddd_time_field_name_admin" ).val() + "</td><td><select name=\"orddd_time_slot\" id=\"orddd_time_slot\" class=\"\" disabled=\"disabled\" style=\"cursor:not-allowed !important;width:150px;\" placeholder=\"\"><option value=\"0\">Select</option></select></td></tr>");
			                         jQuery( "#admin_delivery_fields" ).prepend( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\">" + jQuery( "#orddd_field_name_admin" ).val() + "</label></td><td><input type=\"text\" id=\"" + jQuery( "#orddd_field_name" ).val() + "\" name=\"" + jQuery( "#orddd_field_name" ).val() + "\" class=\"" + jQuery( "#orddd_field_name" ).val() + "\" style=\"cursor: text!important;\" readonly/><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
			                         jQuery( "#is_virtual_product" ).html("");
								 } 
								 jQuery( "#orddd_shipping_class_settings_to_load" ).val( response_arr[1] );
								 load_delivery_date();
							 }	
						 } 
					 });
				}
			}
		}
	}
}); 