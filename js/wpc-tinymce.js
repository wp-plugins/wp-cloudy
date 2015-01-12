jQuery(document).ready(function($) {
	//TinyMCE v3.x
    tinymce.create('tinymce.plugins.wpc_plugin', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('wpc_insert_shortcode', function() {
                    var number = prompt("Enter the id of the weather to display: "), 
                    shortcode;
					if (number !== null) {
						number = parseInt(number);
						shortcode = '[wpc-weather id="' + number + '"/]';
						ed.execCommand('mceInsertContent', 0, shortcode);
					}
                });

            // Register buttons - trigger above command when clicked
            ed.addButton('wpc_button', {title : 'Insert Weather', cmd : 'wpc_insert_shortcode' });
        },   
    });

    //TinyMCE v4.x
	(function() {
	    tinymce.PluginManager.add('wpc_button_v4', function( editor, url ) {
	        editor.addButton( 'wpc_button_v4', {
	            title: 'WP Cloudy',
	            type: 'button',
	            icon: 'icon mceIcon mce_wpc_button',
	            onclick: function() {
				    editor.windowManager.open( {
				        title: 'Insert Weather',
				        body: [{
				            type: 'textbox',
				            name: 'title',
				            label: 'Enter the id of the weather to display:'
				        }],
				        onsubmit: function( e ) {
				            editor.insertContent( '[wpc-weather id="' + e.data.title + '"/]');
				        }
				    });
				}
			});
		});
	})();      
});

