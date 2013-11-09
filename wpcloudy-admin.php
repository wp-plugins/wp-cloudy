<?php

class wpc_options
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	
    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
	
	public function activate() {
        update_option($this->wpc_options, $this->data);
    }

    public function deactivate() {
        delete_option($this->wpc_options);
    }
	
    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'WP Cloudy', 
            'manage_options', 
            'wpc-settings-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'wpc_option_name' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'WP Cloudy settings', 'wpcloudy' ); ?></h2>           
            <form method="post" action="options.php" class="wpcloudy-settings">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'wpc_cloudy_option_group' );   
                do_settings_sections( 'wpc-settings-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
	 
	

    public function page_init()
    {        
        register_setting(
            'wpc_cloudy_option_group', // Option group
            'wpc_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

		//BASIC SECTION============================================================================
		add_settings_section( 
            'wpc_setting_section_basic', // ID
            __("Basic settings","wpcloudy"), // Title
            array( $this, 'print_section_info_basic' ), // Callback
            'wpc-settings-admin' // Page
        ); 	
		
		add_settings_field(
            'wpc_advanced_bypass_unit', // ID
           __("Bypass unit?","wpcloudy"), // Title
            array( $this, 'wpc_basic_bypass_unit_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_basic' // Section           
        );
				
        add_settings_field(
            'wpc_advanced_unit', // ID
            __("Unit","wpcloudy"), // Title 
            array( $this, 'wpc_basic_unit_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_basic' // Section           
        );
		
		add_settings_field(
            'wpc_advanced_bypass_lang', // ID
           __("Bypass language?","wpcloudy"), // Title
            array( $this, 'wpc_basic_bypass_lang_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_basic' // Section           
        );
		
		add_settings_field(
            'wpc_advanced_lang', // ID
            __("Language","wpcloudy"), // Title 
            array( $this, 'wpc_basic_lang_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_basic' // Section           
        );
		
		//DISPLAY SECTION==========================================================================
        add_settings_section( 
            'wpc_setting_section_display', // ID
            __("Display settings","wpcloudy"), // Title
            array( $this, 'print_section_info_display' ), // Callback
            'wpc-settings-admin' // Page
        );
		
        add_settings_field(
            'wpc_display_current_weather', // ID
            __("Current weather?","wpcloudy"), // Title
            array( $this, 'wpc_display_current_weather_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_weather', // ID
            __("Short condition?","wpcloudy"), // Title
            array( $this, 'wpc_display_weather_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_wind', // ID
            __("Wind?","wpcloudy"), // Title 
            array( $this, 'wpc_display_wind_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_humidity', // ID
            __("Humidity?","wpcloudy"), // Title
            array( $this, 'wpc_display_humidity_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_pressure', // ID
           __("Pressure?","wpcloudy"), // Title
            array( $this, 'wpc_display_pressure_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_cloudiness', // ID
            __("Cloudiness?","wpcloudy"), // Title
            array( $this, 'wpc_display_cloudiness_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_hour_forecast', // ID
            __("Hour forecast?","wpcloudy"), // Title 
            array( $this, 'wpc_display_hour_forecast_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_temperature_min_max', // ID
			__("Today date + Min-Max temperatures","wpcloudy"), // Title
            array( $this, 'wpc_display_temperature_min_max_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_forecast', // ID
            __("7-Day Forecast","wpcloudy"), // Title 
            array( $this, 'wpc_display_forecast_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_display' // Section           
        );
		
		//ADVANCED SECTION=========================================================================
        add_settings_section( 
            'wpc_setting_section_advanced', // ID
            __("Advanced settings","wpcloudy"), // Title
            array( $this, 'print_section_info_advanced' ), // Callback
            'wpc-settings-admin' // Page
        );
		
		add_settings_field(
            'wpc_advanced_bg_color', // ID
            __("Background color","wpcloudy"), // Title 
            array( $this, 'wpc_advanced_bg_color_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_advanced' // Section           
        );     
		
        add_settings_field(
            'wpc_advanced_text_color', // ID
            __("Text color","wpcloudy"), // Title
            array( $this, 'wpc_advanced_text_color_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_advanced' // Section   
        ); 	
		
		add_settings_field(
            'wpc_advanced_border_color', // ID
            __("Border color","wpcloudy"), // Title 
            array( $this, 'wpc_advanced_border_color_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_advanced' // Section   
        ); 

		add_settings_field(
            'wpc_advanced_bypass_size', // ID
            __("Bypass size?","wpcloudy"), // Title
            array( $this, 'wpc_advanced_bypass_size_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_advanced' // Section           
        );
				
        add_settings_field(
            'wpc_advanced_size', // ID
           __("Size","wpcloudy"), // Title
            array( $this, 'wpc_advanced_size_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_advanced' // Section           
        );
		
		//MAP SECTION =============================================================================
		add_settings_section( 
            'wpc_setting_section_map', // ID
            __("Map settings","wpcloudy"), // Title
            array( $this, 'print_section_info_map' ), // Callback
            'wpc-settings-admin' // Page
        );

        add_settings_field(
            'wpc_map_display', // ID
            __("Map?","wpcloudy"), // Title
            array( $this, 'wpc_map_display_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_height', // ID
            __("Map height","wpcloudy"), // Title 
            array( $this, 'wpc_map_height_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	
		
		add_settings_field(
            'wpc_map_bypass_opacity', // ID
            __("Bypass layers opacity?","wpcloudy"), // Title 
            array( $this, 'wpc_map_bypass_opacity_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );

        add_settings_field(
            'wpc_map_opacity', // ID
            __("Layers opacity","wpcloudy"), // Title 
            array( $this, 'wpc_map_opacity_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	
		
        add_settings_field(
            'wpc_map_bypass_zoom', // ID
            __("Bypass zoom?","wpcloudy"), // Title
            array( $this, 'wpc_map_bypass_zoom_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_zoom', // ID
            __("Zoom","wpcloudy"), // Title 
            array( $this, 'wpc_map_zoom_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_stations', // ID
            __("Stations?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_stations_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_clouds', // ID
            __("Clouds?","wpcloudy"), // Title 
            array( $this, 'wpc_map_layers_clouds_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_precipitation', // ID
            __("Precipitations?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_precipitation_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_snow', // ID
            __("Snow?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_snow_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_wind', // ID
            __("Wind?","wpcloudy"), // Title 
            array( $this, 'wpc_map_layers_wind_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_temperature', // ID
            __("Temperatures?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_temperature_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_pressure', // ID
            __("Pressure?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_pressure_callback' ), // Callback
            'wpc-settings-admin', // Page
            'wpc_setting_section_map' // Section           
        );			
	
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {	
		if( !empty( $input['wpc_advanced_bg_color'] ) )
		$input['wpc_advanced_bg_color'] = sanitize_text_field( $input['wpc_advanced_bg_color'] );
		
		if( !empty( $input['wpc_advanced_text_color'] ) )
		$input['wpc_advanced_text_color'] = sanitize_text_field( $input['wpc_advanced_text_color'] );
		
		if( !empty( $input['wpc_advanced_border_color'] ) )
		$input['wpc_advanced_border_color'] = sanitize_text_field( $input['wpc_advanced_border_color'] );
		
		if( !empty( $input['wpc_map_height'] ) )
		$input['wpc_map_height'] = sanitize_text_field( $input['wpc_map_height'] );
		
        return $input;
    }

    /** 
     * Print the Section text
     */
	 
	public function print_section_info_basic()
    {
        print __('Basic settings to bypass:', 'wpcloudy');
    }
	
	public function print_section_info_display()
    {
        print __('Display settings to bypass:', 'wpcloudy');
    }
	
    public function print_section_info_advanced()
    {
        print __('Advanced settings to bypass:', 'wpcloudy');
    }
	
	public function print_section_info_map()
    {
        print __('Map settings to bypass:', 'wpcloudy');
    }

    /** 
     * Get the settings option array and print one of its values
     */

	public function wpc_basic_bypass_unit_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_basic_bypass_unit'];
		
        echo '<input id="wpc_basic_bypass_unit" name="wpc_option_name[wpc_basic_bypass_unit]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_basic_bypass_unit">'. __( 'Enable bypass unit on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_basic_bypass_unit']);
    } 
	 
	public function wpc_basic_unit_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$selected = $options['wpc_basic_unit'];
		
		echo ' <select id="wpc_basic_unit" name="wpc_option_name[wpc_basic_unit]"> ';
		echo ' <option '; 
		if ('imperial' == $selected) echo 'selected="selected"'; 
		echo ' value="imperial">'. __( 'Imperial', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('metric' == $selected) echo 'selected="selected"'; 
		echo ' value="metric">'. __( 'Metric', 'wpcloudy' ) .'</option>';
		echo '</select>';
		esc_attr( $this->options['wpc_basic_unit']);
	}
	
	public function wpc_basic_bypass_lang_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_basic_bypass_lang'];
		
        echo '<input id="wpc_basic_bypass_lang" name="wpc_option_name[wpc_basic_bypass_lang]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_basic_bypass_lang">'. __( 'Enable bypass language on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_basic_bypass_lang']);
    }
	
	public function wpc_basic_lang_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$selected = $options['wpc_basic_lang'];
		
		echo ' <select id="wpc_basic_lang" name="wpc_option_name[wpc_basic_lang]"> ';
		
			echo ' <option '; 
			if ('fr' == $selected) echo 'selected="selected"'; 
			echo ' value="fr">'. __( 'French', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('en' == $selected) echo 'selected="selected"'; 
			echo ' value="en">'. __( 'English', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('ru' == $selected) echo 'selected="selected"'; 
			echo ' value="ru">'. __( 'Russian', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('it' == $selected) echo 'selected="selected"'; 
			echo ' value="it">'. __( 'Italian', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('sp' == $selected) echo 'selected="selected"'; 
			echo ' value="sp">'. __( 'Spanish', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('ua' == $selected) echo 'selected="selected"'; 
			echo ' value="ua">'. __( 'Ukrainian', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('de' == $selected) echo 'selected="selected"'; 
			echo ' value="de">'. __( 'German', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('pt' == $selected) echo 'selected="selected"'; 
			echo ' value="pt">'. __( 'Portuguese', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('ro' == $selected) echo 'selected="selected"'; 
			echo ' value="ro">'. __( 'Romanian', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('pl' == $selected) echo 'selected="selected"'; 
			echo ' value="pl">'. __( 'Polish', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('fi' == $selected) echo 'selected="selected"'; 
			echo ' value="fi">'. __( 'Finnish', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('nl' == $selected) echo 'selected="selected"'; 
			echo ' value="nl">'. __( 'Dutch', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('bg' == $selected) echo 'selected="selected"'; 
			echo ' value="bg">'. __( 'Bulgarian', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('se' == $selected) echo 'selected="selected"'; 
			echo ' value="se">'. __( 'Swedish', 'wpcloudy' ) .'</option>';
					
			echo '<option '; 
			if ('zh_tw' == $selected) echo 'selected="selected"'; 
			echo ' value="zh_tw">'. __( 'Chinese Traditional', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('zh_cn' == $selected) echo 'selected="selected"'; 
			echo ' value="zh_cn">'. __( 'Chinese Simplified', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('tr' == $selected) echo 'selected="selected"'; 
			echo ' value="tr">'. __( 'Turkish', 'wpcloudy' ) .'</option>';
			
		echo '</select>';
		esc_attr( $this->options['wpc_basic_lang']);
	}

	public function wpc_display_current_weather_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_current_weather'];
		
        echo '<input id="wpc_display_current_weather" name="wpc_option_name[wpc_display_current_weather]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_current_weather">'. __( 'Display current weather on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_current_weather']);
    }
	
	public function wpc_display_weather_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_weather'];
		
        echo '<input id="wpc_display_weather" name="wpc_option_name[wpc_display_weather]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_weather">'. __( 'Display short condition on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_weather']);
    }
	
	public function wpc_display_wind_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_wind'];
		
        echo '<input id="wpc_display_wind" name="wpc_option_name[wpc_display_wind]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_wind">'. __( 'Display wind on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_wind']);
    }
	
	public function wpc_display_humidity_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_humidity'];
		
        echo '<input id="wpc_display_humidity" name="wpc_option_name[wpc_display_humidity]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_humidity">'. __( 'Display humidity on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_humidity']);
    }
	
	public function wpc_display_pressure_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_pressure'];
		
        echo '<input id="wpc_display_pressure" name="wpc_option_name[wpc_display_pressure]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_pressure">'. __( 'Display pressure on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_pressure']);
    }
	
	public function wpc_display_cloudiness_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_cloudiness'];
		
        echo '<input id="wpc_display_cloudiness" name="wpc_option_name[wpc_display_cloudiness]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_cloudiness">'. __( 'Display cloudiness on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_cloudiness']);
    }
	
	public function wpc_display_hour_forecast_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_hour_forecast'];
		
        echo '<input id="wpc_display_hour_forecast" name="wpc_option_name[wpc_display_hour_forecast]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_hour_forecast">'. __( 'Display hour forecast on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_hour_forecast']);
    }
	
	public function wpc_display_temperature_min_max_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_temperature_min_max'];
		
        echo '<input id="wpc_display_temperature_min_max" name="wpc_option_name[wpc_display_temperature_min_max]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_temperature_min_max">'. __( 'Display Today date + Min-Max temperatures on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_temperature_min_max']);
    }
	
	public function wpc_display_forecast_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_display_forecast'];
		
        echo '<input id="wpc_display_forecast" name="wpc_option_name[wpc_display_forecast]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_forecast">'. __( 'Display 7-day Forecast on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_display_forecast']);
    }

    public function wpc_advanced_bg_color_callback()
    {
        printf(
		'<input name="wpc_option_name[wpc_advanced_bg_color]" type="text" value="%s" class="wpcloudy_admin_color_picker" />',
		esc_attr( $this->options['wpc_advanced_bg_color'])
		
        );
		
    }
	
	public function wpc_advanced_text_color_callback()
    {
			printf(
			'<input name="wpc_option_name[wpc_advanced_text_color]" type="text" value="%s" class="wpcloudy_admin_color_picker" />',
			esc_attr( $this->options['wpc_advanced_text_color'])
			
			);
    }
	
	public function wpc_advanced_border_color_callback()
    {
			printf(
			'<input name="wpc_option_name[wpc_advanced_border_color]" type="text" value="%s" class="wpcloudy_admin_color_picker" />',
			esc_attr( $this->options['wpc_advanced_border_color'])
			
			);
    }
	
	public function wpc_advanced_bypass_size_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_advanced_bypass_size'];
		
        echo '<input id="wpc_advanced_bypass_size" name="wpc_option_name[wpc_advanced_bypass_size]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_advanced_bypass_size">'. __( 'Enable bypass size on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_advanced_bypass_size']);
    } 
	 
	public function wpc_advanced_size_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$selected = $options['wpc_advanced_size'];
		
		echo ' <select id="wpc_advanced_size" name="wpc_option_name[wpc_advanced_size]"> ';
		echo ' <option '; 
			if ('small' == $selected) echo 'selected="selected"'; 
			echo ' value="small">'. __( 'Small', 'wpcloudy' ) .'</option>';
		echo '<option '; 
			if ('medium' == $selected) echo 'selected="selected"'; 
			echo ' value="medium">'. __( 'Medium', 'wpcloudy' ) .'</option>';
		echo '<option '; 
			if ('large' == $selected) echo 'selected="selected"'; 
			echo ' value="large">'. __( 'Large', 'wpcloudy' ) .'</option>';
		echo '</select>';
		esc_attr( $this->options['wpc_advanced_size']);
	}
	
	public function wpc_map_display_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_display'];
		
        echo '<input id="wpc_map_display" name="wpc_option_name[wpc_map_display]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_display">'. __( 'Enable map on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_display']);
    } 
	
	public function wpc_map_height_callback()
    {
		printf(
		'<input name="wpc_option_name[wpc_map_height]" type="text" value="%s" />',
		esc_attr( $this->options['wpc_map_height'])
		
		);
	}

	public function wpc_map_bypass_opacity_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_bypass_opacity'];
		
        echo '<input id="wpc_map_bypass_opacity" name="wpc_option_name[wpc_map_bypass_opacity]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_bypass_opacity">'. __( 'Enable bypass map opacity on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_bypass_opacity']);
    }
	
	public function wpc_map_opacity_callback()
	{
		$options = get_option( 'wpc_option_name' );    
		$selected = $options['wpc_map_opacity'];
		
		echo ' <select id="wpc_map_opacity" name="wpc_option_name[wpc_map_opacity]"> ';
		echo ' <option '; 
			if ('0' == $selected) echo 'selected="selected"'; 
			echo ' value="0">0%</option>';
		echo '<option '; 
			if ('0.1' == $selected) echo 'selected="selected"'; 
			echo ' value="0.1">10%</option>';
		echo '<option '; 
			if ('0.2' == $selected) echo 'selected="selected"'; 
			echo ' value="0.2">20%</option>';
		echo '<option '; 
			if ('0.3' == $selected) echo 'selected="selected"'; 
			echo ' value="0.3">30%</option>';
		echo '<option '; 
			if ('0.4' == $selected) echo 'selected="selected"'; 
			echo ' value="0.4">40%</option>';
		echo '<option '; 
			if ('0.5' == $selected) echo 'selected="selected"'; 
			echo ' value="0.5">50%</option>';
		echo '<option '; 
			if ('0.6' == $selected) echo 'selected="selected"'; 
			echo ' value="0.6">60%</option>';
		echo '<option '; 
			if ('0.7' == $selected) echo 'selected="selected"'; 
			echo ' value="0.7">70%</option>';
		echo '<option '; 
			if ('0.8' == $selected) echo 'selected="selected"'; 
			echo ' value="0.8">80%</option>';
		echo '<option '; 
			if ('0.9' == $selected) echo 'selected="selected"'; 
			echo ' value="0.9">90%</option>';
		echo '<option '; 
			if ('1' == $selected) echo 'selected="selected"'; 
		echo ' value="1">100%</option>';
		echo '</select>';
		esc_attr( $this->options['wpc_map_opacity']);
	} 
	
	public function wpc_map_bypass_zoom_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_bypass_zoom'];
		
        echo '<input id="wpc_map_bypass_zoom" name="wpc_option_name[wpc_map_bypass_zoom]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_bypass_zoom">'. __( 'Enable bypass map zoom on all weather?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_bypass_zoom']);
    }
	
	public function wpc_map_zoom_callback()
	{
		$options = get_option( 'wpc_option_name' );    
		$selected = $options['wpc_map_zoom'];
		
		echo ' <select id="wpc_map_zoom" name="wpc_option_name[wpc_map_zoom]"> ';
		echo ' <option '; 
			if ('1' == $selected) echo 'selected="selected"'; 
			echo ' value="1">1</option>';
		echo ' <option '; 
			if ('2' == $selected) echo 'selected="selected"'; 
			echo ' value="2">2</option>';
		echo ' <option '; 
			if ('3' == $selected) echo 'selected="selected"'; 
			echo ' value="3">3</option>';
		echo ' <option '; 
			if ('4' == $selected) echo 'selected="selected"'; 
			echo ' value="4">4</option>';
		echo ' <option '; 
			if ('5' == $selected) echo 'selected="selected"'; 
			echo ' value="5">5</option>';
		echo ' <option '; 
			if ('6' == $selected) echo 'selected="selected"'; 
			echo ' value="6">6</option>';
		echo ' <option '; 
			if ('7' == $selected) echo 'selected="selected"'; 
			echo ' value="7">7</option>';
		echo ' <option '; 
			if ('8' == $selected) echo 'selected="selected"'; 
			echo ' value="8">8</option>';
		echo ' <option '; 
			if ('9' == $selected) echo 'selected="selected"'; 
			echo ' value="9">9</option>';
		echo ' <option '; 
			if ('10' == $selected) echo 'selected="selected"'; 
			echo ' value="10">10</option>';
		echo ' <option '; 
			if ('11' == $selected) echo 'selected="selected"'; 
			echo ' value="11">11</option>';
		echo ' <option '; 
			if ('12' == $selected) echo 'selected="selected"'; 
			echo ' value="12">12</option>';
		echo ' <option '; 
			if ('13' == $selected) echo 'selected="selected"'; 
			echo ' value="13">13</option>';
		echo ' <option '; 
			if ('14' == $selected) echo 'selected="selected"'; 
			echo ' value="14">14</option>';
		echo ' <option '; 
			if ('15' == $selected) echo 'selected="selected"'; 
			echo ' value="15">15</option>';
		echo ' <option '; 
			if ('16' == $selected) echo 'selected="selected"'; 
			echo ' value="16">16</option>';
		echo ' <option '; 
			if ('17' == $selected) echo 'selected="selected"'; 
			echo ' value="17">17</option>';
		echo ' <option '; 
			if ('18' == $selected) echo 'selected="selected"'; 
			echo ' value="18">18</option>';
		echo '</select>';
		esc_attr( $this->options['wpc_map_zoom']);
	} 
	
	public function wpc_map_layers_stations_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_stations'];
		
        echo '<input id="wpc_map_layers_stations" name="wpc_option_name[wpc_map_layers_stations]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_stations">'. __( 'Display stations on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_stations']);
    } 
	
	public function wpc_map_layers_clouds_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_clouds'];
		
        echo '<input id="wpc_map_layers_clouds" name="wpc_option_name[wpc_map_layers_clouds]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_clouds">'. __( 'Display clouds on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_clouds']);
    }
	
	public function wpc_map_layers_precipitation_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_precipitation'];
		
        echo '<input id="wpc_map_layers_precipitation" name="wpc_option_name[wpc_map_layers_precipitation]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_precipitation">'. __( 'Display precipitations on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_precipitation']);
    }
	
	public function wpc_map_layers_snow_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_snow'];
		
        echo '<input id="wpc_map_layers_snow" name="wpc_option_name[wpc_map_layers_snow]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_snow">'. __( 'Display snow on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_snow']);
    }
	
	public function wpc_map_layers_wind_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_wind'];
		
        echo '<input id="wpc_map_layers_wind" name="wpc_option_name[wpc_map_layers_wind]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_wind">'. __( 'Display wind on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_wind']);
    }
	
	public function wpc_map_layers_temperature_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_temperature'];
		
        echo '<input id="wpc_map_layers_temperature" name="wpc_option_name[wpc_map_layers_temperature]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_temperature">'. __( 'Display temperatures on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_temperature']);
    }
	
	public function wpc_map_layers_pressure_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$check = $options['wpc_map_layers_pressure'];
		
        echo '<input id="wpc_map_layers_pressure" name="wpc_option_name[wpc_map_layers_pressure]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_pressure">'. __( 'Display pressure on all weather maps?', 'wpcloudy' ) .'</label>';

		esc_attr( $this->options['wpc_map_layers_pressure']);
    }

}

if( is_admin() )
    $my_settings_page = new wpc_options();
	
?>