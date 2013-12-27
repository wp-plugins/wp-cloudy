jQuery(document).ready(function($) {

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

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('wpc_button', tinymce.plugins.wpc_plugin);
});

