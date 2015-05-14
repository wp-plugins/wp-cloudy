jQuery(document).ready(function(){
	jQuery.ajax({
		url : wpcAjax,
		method : 'POST',
		data : {
			action : 'get_my_weather',
		},
		statusCode: {
        500: function() {
	            alert("500 data still loading");
	            console.log('500 ');
	        }
	    },
		success : function( data ) {
			if ( data.success ) {
				var wpc_weather = jQuery( data.data.wpc_weather );
				jQuery ('#content' ).html( wpc_weather );
				
			} else {
				console.log( data.data );
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
});
			