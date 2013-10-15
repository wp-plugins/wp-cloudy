jQuery('img[alt].wpc_help').qtip({
    show: 'click',
    hide: 'click',
    content: {
        attr: 'alt'
    },
	style: {
		classes: 'qtip-blue',
		tip: {
            border: 2,
            width: 10,
            height: 10,
			offset: 5
        },
		position: {
			my: 'bottom left',  
			at: 'bottom right', 
			target: jQuery('.wpc_help') 
		}
	}
});
