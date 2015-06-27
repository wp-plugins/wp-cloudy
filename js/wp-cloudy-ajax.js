jQuery(document).ready(function(){
	var wpc_weather_id = document.getElementsByClassName('wpc-weather-id');
	for(var i = 0; i < wpc_weather_id.length; i++) {	
		if (jQuery('#wpc-weather-id').attr("data_id")) {
			jQuery.ajax({
				url : wpcAjax,
				method : 'POST',
				data : {
					action: 'wpc_get_my_weather',
					wpc_param : wpc_weather_id[i].attributes.data_id.value,
					wpc_param2 : jQuery('#wpc-weather-id').attr("data-map"),
					wpc_param3 : jQuery('#wpc-weather-id').attr("data-detect-geolocation"),
					wpc_param4 : jQuery('#wpc-weather-id').attr("data-manual-geolocation"),
					wpc_param5 : jQuery('#wpc-weather-id').attr("data-wpc-lat"),
					wpc_param6 : jQuery('#wpc-weather-id').attr("data-wpc-lon"),
					wpc_param7 : jQuery('#wpc-weather-id').attr("data-wpc-city-id"),
					wpc_param8 : jQuery('#wpc-weather-id').attr("data-wpc-city-name"),
					wpc_param9 : jQuery('#wpc-weather-id').attr("data-custom-font"),
				},
				success : function( data ) {
					if ( data ) {
						jQuery('#wpc-weather-id').append(data);
					} else {
						console.log( data );
					}
				},
				beforeSend: function(){
			       jQuery(".wpc-loading-spinner").show();
			       jQuery("#wpc-weather").hide();
			    },
			    complete: function(){
			       jQuery(".wpc-loading-spinner").hide();
			       jQuery("#wpc-weather").show();
			    },
			});
		};
	}
});