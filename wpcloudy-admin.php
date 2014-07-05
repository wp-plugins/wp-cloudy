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
        <?php $wpc_info_version = get_plugin_data( plugin_dir_path( __FILE__ ).'/wpcloudy.php'); ?>
        
            <div id="wpcloudy-header">
				<div id="wpcloudy-clouds">
					<h3>
						<?php _e( 'WP Cloudy', 'wpcloudy' ); ?>
					</h3>
					<span class="wpc-info-version"><?php print_r($wpc_info_version['Version']); ?></span>
					<div id="wpcloudy-notice">
						<p><?php _e( 'Not just another WordPress Weather plugin!', 'wpcloudy' ); ?></p>
						<p class="small"><a href="http://wordpress.org/support/view/plugin-reviews/wp-cloudy" target="_blank"><?php _e( 'You like WP Cloudy? Don\'t forget to rate it 5 stars!', 'wpcloudy' ); ?></a></p>
					</div>
				</div>
			</div>
			
            <form method="post" action="options.php" class="wpcloudy-settings">
                <?php settings_fields( 'wpc_cloudy_option_group' ); ?>
                
                <div id="wpcloudy-tabs">
	                <h2 class="nav-tab-wrapper hide-if-no-js">
	                	<ul>
							<li><a href="#tab_basic" class="nav-tab"><?php _e( 'Basic options', 'wpcloudy' ); ?></a></li>
							<li><a href="#tab_display" class="nav-tab"><?php _e( 'Display options', 'wpcloudy' ); ?></a></li>
							<li><a href="#tab_advanced" class="nav-tab"><?php _e( 'Advanced options', 'wpcloudy' ); ?></a></li>
							<li><a href="#tab_map" class="nav-tab"><?php _e( 'Map options', 'wpcloudy' ); ?></a></li>
							<li><a href="#tab_support" class="nav-tab"><?php _e( 'Support', 'wpcloudy' ); ?></a></li>
	                	</ul>
					</h2>
	               
					<div id="wpcloudy-tabs-settings">
						<div class="wpc-tab" id="tab_basic"><?php do_settings_sections( 'wpc-settings-admin-basic' ); ?></div>
						<div class="wpc-tab" id="tab_display"><?php do_settings_sections( 'wpc-settings-admin-display' ); ?></div>
						<div class="wpc-tab" id="tab_advanced"><?php do_settings_sections( 'wpc-settings-admin-advanced' ); ?></div>
						<div class="wpc-tab" id="tab_map"><?php do_settings_sections( 'wpc-settings-admin-map' ); ?></div>
						<div class="wpc-tab" id="tab_support"><?php do_settings_sections( 'wpc-settings-admin-support' ); ?></div>
					</div>
                </div>
                
				<?php submit_button(); ?>
            </form>
            <div class="wpcloudy-sidebar">	
            
            	<div id="wpcloudy-cache" class="wpcloudy-module wpcloudy-inactive" style="height: 177px;">
					<h3><?php _e('WP Cloudy cache','wpcloudy'); ?></h3>
					<div class="wpcloudy-module-description">  
						<div class="module-image">
							<div class="dashicons dashicons-trash"></div>
							<p><span class="module-image-badge"><?php _e('cache system','wpcloudy'); ?></span></p>
						</div>
						
						<p><?php _e('Click this button to refresh weather cache.','wpcloudy'); ?></p>
            
		            	<?php
							function wpc_clear_all_cache() {
						    	if (!isset($_GET['wpc_clear_all_cache_nonce']) || !wp_verify_nonce($_GET['wpc_clear_all_cache_nonce'], 'wpc_clear_all_cache_action')) {
							?>
							<div class="wpcloudy-module-actions">
								<p>
								    <a href="<?php print wp_nonce_url(admin_url('options-general.php?page=wpc-settings-admin'), 'wpc_clear_all_cache_action', 'wpc_clear_all_cache_nonce');?>"
								        class="button button-primary">
								        <?php esc_html_e('Clear cache!', 'wpcloudy');?>
									</a>
								</p>
							</div>
							
							<?php
			
						    } else {
						        
							?>
							<div class="wpcloudy-module-actions">
							    <a href="<?php print wp_nonce_url(admin_url('options-general.php?page=wpc-settings-admin'), 'wpc_clear_all_cache_action', 'wpc_clear_all_cache');?>"
							        class="button button-primary">
							        <?php esc_html_e('Clear cache!', 'wpcloudy');?>
								</a>
							</div>
						
							<?php
									
						        // The Query
						        $wpc_cache_query = new WP_Query( array(
									'post_type' => array( 'wpc-weather' )
								) );
						
						        $wpc_cache_query_array = array();
						
								// The Loop
								if ( $wpc_cache_query->have_posts() ) {
									while ( $wpc_cache_query->have_posts() ) {
										$wpc_cache_query->the_post();
										
										array_push( $wpc_cache_query_array, get_the_id());	
									}
								} else {
									// no posts found
								}
								/* Restore original Post Data */
								wp_reset_postdata();
						
						        foreach ($wpc_cache_query_array as $id) {
								    delete_transient( "myweather_current_".$id ); 
								    delete_transient( "myweather_".$id ); 
									delete_transient( "myweather_sevendays_".$id );
								} 
							}
						};
						?>
						<?php echo wpc_clear_all_cache(); ?>    
					</div>    
				</div>
				
            	<div id="wpcloudy-geolocation" class="wpcloudy-module wpcloudy-inactive" style="height: 177px;">
					<h3><?php _e('WP Cloudy Geolocation','wpcloudy'); ?></h3>
					<div class="wpcloudy-module-description">
						<div class="module-image">
							<div class="dashicons dashicons-location-alt"></div>
							<p><span class="module-image-badge"><?php _e('$ 39','wpcloudy'); ?></span></p>
							<?php if ( is_plugin_active( 'wpcloudy-geolocation-addon/wpcloudy-geolocation-addon.php' ) ) {
								echo '<div class="enabled"><div class="dashicons dashicons-yes"></div>'.__('Enabled','').'</div>';
							}; ?>
						</div>

						<p><?php _e('Geolocated weather for your visitors.','wpcloudy'); ?></p>
					</div>
	
					<div class="wpcloudy-module-actions">
						<a target="_blank" href="http://wpcloudy.com/geolocation" onclick="_gaq.push(['_trackEvent', 'WP Cloudy Admin', 'Learn more', 'Geolocation']);" class="button-secondary more-info-link"><?php _e('Learn more','wpcloudy'); ?></a>
					</div>
				</div>
				<div id="wpcloudy-skins" class="wpcloudy-module wpcloudy-inactive" style="height: 177px;">
					<h3><?php _e('WP Cloudy Skins','wpcloudy'); ?></h3>
					<div class="wpcloudy-module-description">
						<div class="module-image">
							<div class="dashicons dashicons-admin-appearance"></div>
							<p><span class="module-image-badge"><?php _e('$ 10','wpcloudy'); ?></span></p>
							<?php if ( is_plugin_active( 'wpcloudy-skin-addon/wpcloudy-skin-addon.php' ) ) {
								echo '<div class="enabled"><div class="dashicons dashicons-yes"></div>'.__('Enabled','').'</div>';
							}; ?>
						</div>

						<p><?php _e('10 beautiful skins for your weather.','wpcloudy'); ?></p>
					</div>
	
					<div class="wpcloudy-module-actions">
						<a target="_blank" href="http://wpcloudy.com/skins" onclick="_gaq.push(['_trackEvent', 'WP Cloudy Admin', 'Learn more', 'Skins']);" class="button-secondary more-info-link"><?php _e('Learn more','wpcloudy'); ?></a>
					</div>
				</div>
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
            'wpc-settings-admin-basic' // Page
        ); 	
		
		add_settings_field(
            'wpc_basic_bypass_unit', // ID
           __("Bypass unit?","wpcloudy"), // Title
            array( $this, 'wpc_basic_bypass_unit_callback' ), // Callback
            'wpc-settings-admin-basic', // Page
            'wpc_setting_section_basic' // Section           
        );
				
        add_settings_field(
            'wpc_basic_unit', // ID
            __("Unit","wpcloudy"), // Title 
            array( $this, 'wpc_basic_unit_callback' ), // Callback
            'wpc-settings-admin-basic', // Page
            'wpc_setting_section_basic' // Section           
        );
        
		add_settings_field(
            'wpc_basic_bypass_date', // ID
           __("Bypass date format?","wpcloudy"), // Title
            array( $this, 'wpc_basic_bypass_date_callback' ), // Callback
            'wpc-settings-admin-basic', // Page
            'wpc_setting_section_basic' // Section           
        );
				
        add_settings_field(
            'wpc_basic_date', // ID
            __("Date","wpcloudy"), // Title 
            array( $this, 'wpc_basic_date_callback' ), // Callback
            'wpc-settings-admin-basic', // Page
            'wpc_setting_section_basic' // Section           
        );
		
		add_settings_field(
            'wpc_basic_bypass_lang', // ID
           __("Bypass language?","wpcloudy"), // Title
            array( $this, 'wpc_basic_bypass_lang_callback' ), // Callback
            'wpc-settings-admin-basic', // Page
            'wpc_setting_section_basic' // Section           
        );
		
		add_settings_field(
            'wpc_basic_lang', // ID
            __("Language","wpcloudy"), // Title 
            array( $this, 'wpc_basic_lang_callback' ), // Callback
            'wpc-settings-admin-basic', // Page
            'wpc_setting_section_basic' // Section           
        );
		
		//DISPLAY SECTION==========================================================================
        add_settings_section( 
            'wpc_setting_section_display', // ID
            __("Display settings","wpcloudy"), // Title
            array( $this, 'print_section_info_display' ), // Callback
            'wpc-settings-admin-display' // Page
        );
		
        add_settings_field(
            'wpc_display_current_weather', // ID
            __("Current weather?","wpcloudy"), // Title
            array( $this, 'wpc_display_current_weather_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_weather', // ID
            __("Short condition?","wpcloudy"), // Title
            array( $this, 'wpc_display_weather_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_date_temp', // ID
            __("Today date + Temperatures?","wpcloudy"), // Title 
            array( $this, 'wpc_display_date_temp_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
        
        add_settings_field(
            'wpc_display_date_temp_unit', // ID
            __("Temperatures unit (C / F)?","wpcloudy"), // Title 
            array( $this, 'wpc_display_date_temp_unit_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_sunrise_sunset', // ID
            __("Sunrise + sunset?","wpcloudy"), // Title 
            array( $this, 'wpc_display_sunrise_sunset_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_wind', // ID
            __("Wind?","wpcloudy"), // Title 
            array( $this, 'wpc_display_wind_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_humidity', // ID
            __("Humidity?","wpcloudy"), // Title
            array( $this, 'wpc_display_humidity_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_pressure', // ID
           __("Pressure?","wpcloudy"), // Title
            array( $this, 'wpc_display_pressure_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_cloudiness', // ID
            __("Cloudiness?","wpcloudy"), // Title
            array( $this, 'wpc_display_cloudiness_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
        
        add_settings_field(
            'wpc_display_precipitation', // ID
            __("Precipitation?","wpcloudy"), // Title
            array( $this, 'wpc_display_precipitation_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_hour_forecast', // ID
            __("Hour forecast?","wpcloudy"), // Title 
            array( $this, 'wpc_display_hour_forecast_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
        
        add_settings_field(
            'wpc_display_bypass_hour_forecast_nd', // ID
            __("Bypass number of hours forecast settings?","wpcloudy"), // Title 
            array( $this, 'wpc_display_bypass_hour_forecast_nd_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
        
        add_settings_field(
            'wpc_display_hour_forecast_nd', // ID
            __("Number of range hours forecast?","wpcloudy"), // Title 
            array( $this, 'wpc_display_hour_forecast_nd_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_bypass_temperature', // ID
            __("Bypass individual temperatures settings?","wpcloudy"), // Title 
            array( $this, 'wpc_display_bypass_temperature_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_temperature_min_max', // ID
			__("Today date + Min-Max temperatures","wpcloudy"), // Title
            array( $this, 'wpc_display_temperature_min_max_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_forecast', // ID
            __("7-Day Forecast","wpcloudy"), // Title 
            array( $this, 'wpc_display_forecast_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_bypass_forecast_nd', // ID
            __("Bypass number of days forecast settings?","wpcloudy"), // Title 
            array( $this, 'wpc_display_bypass_forecast_nd_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_forecast_nd', // ID
            __("Number of days forecast","wpcloudy"), // Title 
            array( $this, 'wpc_display_forecast_nd_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
        
        add_settings_field(
            'wpc_display_bypass_short_days_names', // ID
            __("Bypass the length of name days?","wpcloudy"), // Title 
            array( $this, 'wpc_display_bypass_short_days_names_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		add_settings_field(
            'wpc_display_short_days_names', // ID
			__("Lenght name days:","wpcloudy"), // Title
            array( $this, 'wpc_display_short_days_names_callback' ), // Callback
            'wpc-settings-admin-display', // Page
            'wpc_setting_section_display' // Section           
        );
		
		//ADVANCED SECTION=========================================================================
        add_settings_section( 
            'wpc_setting_section_advanced', // ID
            __("Advanced settings","wpcloudy"), // Title
            array( $this, 'print_section_info_advanced' ), // Callback
            'wpc-settings-admin-advanced' // Page
        );
        
		add_settings_field(
            'wpc_advanced_disable_css3_anims', // ID
            __("CSS 3 Animations","wpcloudy"), // Title 
            array( $this, 'wpc_advanced_disable_css3_anims_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section           
        ); 
        
		add_settings_field(
            'wpc_advanced_bg_color', // ID
            __("Background color","wpcloudy"), // Title 
            array( $this, 'wpc_advanced_bg_color_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section           
        );     
		
        add_settings_field(
            'wpc_advanced_text_color', // ID
            __("Text color","wpcloudy"), // Title
            array( $this, 'wpc_advanced_text_color_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section   
        ); 	
		
		add_settings_field(
            'wpc_advanced_border_color', // ID
            __("Border color","wpcloudy"), // Title 
            array( $this, 'wpc_advanced_border_color_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section   
        ); 

		add_settings_field(
            'wpc_advanced_bypass_size', // ID
            __("Bypass size?","wpcloudy"), // Title
            array( $this, 'wpc_advanced_bypass_size_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section           
        );
				
        add_settings_field(
            'wpc_advanced_size', // ID
           __("Size","wpcloudy"), // Title
            array( $this, 'wpc_advanced_size_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section           
        );
        
        add_settings_field(
            'wpc_advanced_disable_cache', // ID
           __("Disable cache","wpcloudy"), // Title
            array( $this, 'wpc_advanced_disable_cache_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section           
        );
        
        add_settings_field(
            'wpc_advanced_cache_time', // ID
           __("Time cache refresh (in minutes)","wpcloudy"), // Title
            array( $this, 'wpc_advanced_cache_time_callback' ), // Callback
            'wpc-settings-admin-advanced', // Page
            'wpc_setting_section_advanced' // Section           
        );
		
		//MAP SECTION =============================================================================

		add_settings_section( 
            'wpc_setting_section_map', // ID
            __("Map settings","wpcloudy"), // Title
            array( $this, 'print_section_info_map' ), // Callback
            'wpc-settings-admin-map' // Page
        );
        
        add_settings_field(
            'wpc_map_js', // ID
            __("Load JS/CSS from:","wpcloudy"), // Title
            array( $this, 'wpc_map_js_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );

        add_settings_field(
            'wpc_map_display', // ID
            __("Map?","wpcloudy"), // Title
            array( $this, 'wpc_map_display_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_height', // ID
            __("Map height","wpcloudy"), // Title 
            array( $this, 'wpc_map_height_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	
		
		add_settings_field(
            'wpc_map_bypass_opacity', // ID
            __("Bypass layers opacity?","wpcloudy"), // Title 
            array( $this, 'wpc_map_bypass_opacity_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );

        add_settings_field(
            'wpc_map_opacity', // ID
            __("Layers opacity","wpcloudy"), // Title 
            array( $this, 'wpc_map_opacity_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	
		
        add_settings_field(
            'wpc_map_bypass_zoom', // ID
            __("Bypass zoom?","wpcloudy"), // Title
            array( $this, 'wpc_map_bypass_zoom_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_zoom', // ID
            __("Zoom","wpcloudy"), // Title 
            array( $this, 'wpc_map_zoom_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_stations', // ID
            __("Stations?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_stations_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_clouds', // ID
            __("Clouds?","wpcloudy"), // Title 
            array( $this, 'wpc_map_layers_clouds_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_precipitation', // ID
            __("Precipitations?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_precipitation_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_snow', // ID
            __("Snow?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_snow_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_wind', // ID
            __("Wind?","wpcloudy"), // Title 
            array( $this, 'wpc_map_layers_wind_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_temperature', // ID
            __("Temperatures?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_temperature_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );	

        add_settings_field(
            'wpc_map_layers_pressure', // ID
            __("Pressure?","wpcloudy"), // Title
            array( $this, 'wpc_map_layers_pressure_callback' ), // Callback
            'wpc-settings-admin-map', // Page
            'wpc_setting_section_map' // Section           
        );
        
        //SUPPORT SECTION============================================================================
		add_settings_section( 
            'wpc_setting_section_support', // ID
            '', // Title
            array( $this, 'print_section_info_support' ), // Callback
            'wpc-settings-admin-support' // Page
        ); 	
		
		add_settings_field(
            'wpc_support_info', // ID
            '', // Title
            array( $this, 'wpc_support_info_callback' ), // Callback
            'wpc-settings-admin-support', // Page
            'wpc_setting_section_support' // Section           
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
		
		if( !empty( $input['wpc_advanced_cache_time'] ) )
		$input['wpc_advanced_cache_time'] = sanitize_text_field( $input['wpc_advanced_cache_time'] );
		
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
    
    public function print_section_info_support()
    {
        print __('', 'wpcloudy');
    }

    /** 
     * Get the settings option array and print one of its values
     */
	
	public function wpc_basic_bypass_unit_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		  
		$check = isset($options['wpc_basic_bypass_unit']);
		
        echo '<input id="wpc_basic_bypass_unit" name="wpc_option_name[wpc_basic_bypass_unit]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_basic_bypass_unit">'. __( 'Enable bypass unit on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_basic_bypass_unit'])) {
			esc_attr( $this->options['wpc_basic_bypass_unit']);
		}
		
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

		if (isset($this->options['wpc_basic_unit'])) {
			esc_attr( $this->options['wpc_basic_unit']);
		}
	}
	
	public function wpc_basic_bypass_date_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		  
		$check = isset($options['wpc_basic_bypass_date']);
		
        echo '<input id="wpc_basic_bypass_date" name="wpc_option_name[wpc_basic_bypass_date]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_basic_bypass_date">'. __( 'Enable bypass date format on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_basic_bypass_date'])) {
			esc_attr( $this->options['wpc_basic_bypass_date']);
		}
		
    } 
	 
	public function wpc_basic_date_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		$selected = $options['wpc_basic_date'];
		
		echo '<select id="wpc_basic_date" name="wpc_option_name[wpc_basic_date]"> ';
		echo '<option '; 
		if ('12' == $selected) echo 'selected="selected"'; 
		echo ' value="12">'. __( '12 h', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('24' == $selected) echo 'selected="selected"'; 
		echo ' value="24">'. __( '24 h', 'wpcloudy' ) .'</option>';
		echo '</select>';

		if (isset($this->options['wpc_basic_date'])) {
			esc_attr( $this->options['wpc_basic_date']);
		}
	}
	
	public function wpc_basic_bypass_lang_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		 
		$check = isset($options['wpc_basic_bypass_lang']);
		
        echo '<input id="wpc_basic_bypass_lang" name="wpc_option_name[wpc_basic_bypass_lang]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_basic_bypass_lang">'. __( 'Enable bypass language on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_basic_bypass_lang'])) {
			esc_attr( $this->options['wpc_basic_bypass_lang']);
		}
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
			
			echo '<option '; 
			if ('cz' == $selected) echo 'selected="selected"'; 
			echo ' value="cz">'. __( 'Czech', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('gl' == $selected) echo 'selected="selected"'; 
			echo ' value="gl">'. __( 'Galician', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('vi' == $selected) echo 'selected="selected"'; 
			echo ' value="vi">'. __( 'Vietnamese', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('ar' == $selected) echo 'selected="selected"'; 
			echo ' value="ar">'. __( 'Arabic', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('mk' == $selected) echo 'selected="selected"'; 
			echo ' value="mk">'. __( 'Macedonian', 'wpcloudy' ) .'</option>';
			
			echo '<option '; 
			if ('sk' == $selected) echo 'selected="selected"'; 
			echo ' value="sk">'. __( 'Slovak', 'wpcloudy' ) .'</option>';
			
		echo '</select>';
		
		if (isset($this->options['wpc_basic_lang'])) {
			esc_attr( $this->options['wpc_basic_lang']);
		}
	}

	public function wpc_display_current_weather_callback()
    {
		$options = get_option( 'wpc_option_name' );    
	
		$check = isset($options['wpc_display_current_weather']);
		
        echo '<input id="wpc_display_current_weather" name="wpc_option_name[wpc_display_current_weather]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_current_weather">'. __( 'Display current weather on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_current_weather'])) {
			esc_attr( $this->options['wpc_display_current_weather']);
		}
    }
	
	public function wpc_display_weather_callback()
    {
		$options = get_option( 'wpc_option_name' );    

		$check = isset($options['wpc_display_weather']);
		
        echo '<input id="wpc_display_weather" name="wpc_option_name[wpc_display_weather]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_weather">'. __( 'Display short condition on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_weather'])) { 
			esc_attr( $this->options['wpc_display_weather']);
		}
    }
	
	public function wpc_display_date_temp_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
   
		$check = isset($options['wpc_display_date_temp']);
		
        echo '<input id="wpc_display_date_temp" name="wpc_option_name[wpc_display_date_temp]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_date_temp">'. __( 'Display today date + temperatures on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_date_temp'])) { 
			esc_attr( $this->options['wpc_display_date_temp']);
		}
    }
    
    public function wpc_display_date_temp_unit_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
   
		$check = isset($options['wpc_display_date_temp_unit']);
		
        echo '<input id="wpc_display_date_temp_unit" name="wpc_option_name[wpc_display_date_temp_unit]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_date_temp_unit">'. __( 'Display temperatures unit (C / F)?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_date_temp_unit'])) { 
			esc_attr( $this->options['wpc_display_date_temp_unit']);
		}
    }
	
	public function wpc_display_sunrise_sunset_callback()
    {
		$options = get_option( 'wpc_option_name' );
		    
		$check = isset($options['wpc_display_sunrise_sunset']);
		
        echo '<input id="wpc_display_sunrise_sunset" name="wpc_option_name[wpc_display_sunrise_sunset]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_sunrise_sunset">'. __( 'Display sunrise - sunset on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_sunrise_sunset'])) { 
			esc_attr( $this->options['wpc_display_sunrise_sunset']);
		}
    }
	
	public function wpc_display_wind_callback()
    {
		$options = get_option( 'wpc_option_name' );
  
		$check = isset($options['wpc_display_wind']);
		
        echo '<input id="wpc_display_wind" name="wpc_option_name[wpc_display_wind]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_wind">'. __( 'Display wind on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_wind'])) {   
			esc_attr( $this->options['wpc_display_wind']);
		}
    }
	
	public function wpc_display_humidity_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
		    
		$check = isset($options['wpc_display_humidity']);
		
        echo '<input id="wpc_display_humidity" name="wpc_option_name[wpc_display_humidity]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_humidity">'. __( 'Display humidity on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_humidity'])) {  
			esc_attr( $this->options['wpc_display_humidity']);
		}
    }
	
	public function wpc_display_pressure_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
		  
		$check = isset($options['wpc_display_pressure']);
		
        echo '<input id="wpc_display_pressure" name="wpc_option_name[wpc_display_pressure]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_pressure">'. __( 'Display pressure on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_pressure'])) {
			esc_attr( $this->options['wpc_display_pressure']);
		}
    }
	
	public function wpc_display_cloudiness_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		  
		$check = isset($options['wpc_display_cloudiness']);
		
        echo '<input id="wpc_display_cloudiness" name="wpc_option_name[wpc_display_cloudiness]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_cloudiness">'. __( 'Display cloudiness on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_cloudiness'])) { 
			esc_attr( $this->options['wpc_display_cloudiness']);
		}
    }
    
    public function wpc_display_precipitation_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		  
		$check = isset($options['wpc_display_precipitation']);
		
        echo '<input id="wpc_display_precipitation" name="wpc_option_name[wpc_display_precipitation]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_precipitation">'. __( 'Display precipitation on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_precipitation'])) { 
			esc_attr( $this->options['wpc_display_precipitation']);
		}
    }
	
	public function wpc_display_hour_forecast_callback()
    {
		$options = get_option( 'wpc_option_name' );   
		
		$check = isset($options['wpc_display_hour_forecast']);
	
        echo '<input id="wpc_display_hour_forecast" name="wpc_option_name[wpc_display_hour_forecast]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_hour_forecast">'. __( 'Display hour forecast on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_hour_forecast'])) { 
			esc_attr( $this->options['wpc_display_hour_forecast']);
		}
    }
    
    public function wpc_display_bypass_hour_forecast_nd_callback()
    {
		$options = get_option( 'wpc_option_name' );   
		 
		$check = isset($options['wpc_display_bypass_hour_forecast_nd']);
		
        echo '<input id="wpc_display_bypass_hour_forecast_nd" name="wpc_option_name[wpc_display_bypass_hour_forecast_nd]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_bypass_hour_forecast_nd">'. __( 'Enable bypass number of hours forecast on all weather?', 'wpcloudy' ) .'</label>';

		if (isset($this->options['wpc_display_bypass_hour_forecast_nd'])) { 
			esc_attr( $this->options['wpc_display_bypass_hour_forecast_nd']);
		}
    }
    
    public function wpc_display_hour_forecast_nd_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
		 
		$selected = $options['wpc_display_hour_forecast_nd'];
		
		echo ' <select id="wpc_display_hour_forecast_nd" name="wpc_option_name[wpc_display_hour_forecast_nd]"> ';
		echo ' <option '; 
		if ('1' == $selected) echo 'selected="selected"'; 
		echo ' value="1">'. __( '1', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('2' == $selected) echo 'selected="selected"'; 
		echo ' value="2">'. __( '2', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('3' == $selected) echo 'selected="selected"'; 
		echo ' value="3">'. __( '3', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('4' == $selected) echo 'selected="selected"'; 
		echo ' value="4">'. __( '4', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('5' == $selected) echo 'selected="selected"'; 
		echo ' value="5">'. __( '5', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('6' == $selected) echo 'selected="selected"'; 
		echo ' value="6">'. __( '6', 'wpcloudy' ) .'</option>';
		echo '</select>';
		
		if (isset($this->options['wpc_display_hour_forecast_nd'])) { 
			esc_attr( $this->options['wpc_display_hour_forecast_nd']);
		}
	}

	public function wpc_display_bypass_temperature_callback()
    {
		$options = get_option( 'wpc_option_name' );
		  
		$check = isset($options['wpc_display_bypass_temperature']);
		
        echo '<input id="wpc_display_bypass_temperature" name="wpc_option_name[wpc_display_bypass_temperature]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_bypass_temperature">'. __( 'Bypass individual temperatures settings?', 'wpcloudy' ) .'</label>';
		 
		if (isset($this->options['wpc_display_bypass_temperature'])) {  
			esc_attr( $this->options['wpc_display_bypass_temperature']);
		}
    }
	
	public function wpc_display_temperature_min_max_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		 
		$check = $options['wpc_display_temperature_min_max'];
		
        echo '<input id="wpc_display_temperature_min_max" name="wpc_option_name[wpc_display_temperature_min_max]" type="radio"';
		if ('yes' == $check) echo 'checked="yes"'; 
		echo ' value="yes"/>';
		
		echo '<label for="wpc_display_temperature_min_max">'. __( 'Display Today date + Min-Max temperatures on all weather?', 'wpcloudy' ) .'</label>';
		
		echo '<br><br>';
		
		echo '<input id="wpc_display_temperature_average" name="wpc_option_name[wpc_display_temperature_min_max]" type="radio"';
		if ('no' == $check) echo 'checked="yes"'; 
		echo ' value="no"/>';
		
		echo '<label for="wpc_display_temperature_average">'. __( 'Display Today date + average temperature on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_temperature_min_max'])) {
			esc_attr( $this->options['wpc_display_temperature_min_max']);
		}
    }
	
	public function wpc_display_forecast_callback()
    {
		$options = get_option( 'wpc_option_name' );   
		
		$check = isset($options['wpc_display_forecast']);
		
        echo '<input id="wpc_display_forecast" name="wpc_option_name[wpc_display_forecast]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_forecast">'. __( 'Display 7-day Forecast on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_forecast'])) {
			esc_attr( $this->options['wpc_display_forecast']);
		}
    }

	public function wpc_display_bypass_forecast_nd_callback()
    {
		$options = get_option( 'wpc_option_name' );   
		 
		$check = isset($options['wpc_display_bypass_forecast_nd']);
		
        echo '<input id="wpc_display_bypass_forecast_nd" name="wpc_option_name[wpc_display_bypass_forecast_nd]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_bypass_forecast_nd">'. __( 'Enable bypass number of days forecast on all weather?', 'wpcloudy' ) .'</label>';

		if (isset($this->options['wpc_display_bypass_forecast_nd'])) { 
			esc_attr( $this->options['wpc_display_bypass_forecast_nd']);
		}
    } 
	 
	public function wpc_display_forecast_nd_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
		 
		$selected = $options['wpc_display_forecast_nd'];
		
		echo ' <select id="wpc_display_forecast_nd" name="wpc_option_name[wpc_display_forecast_nd]"> ';
		echo ' <option '; 
		if ('1' == $selected) echo 'selected="selected"'; 
		echo ' value="1">'. __( '1 day', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('2' == $selected) echo 'selected="selected"'; 
		echo ' value="2">'. __( '2 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('3' == $selected) echo 'selected="selected"'; 
		echo ' value="3">'. __( '3 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('4' == $selected) echo 'selected="selected"'; 
		echo ' value="4">'. __( '4 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('5' == $selected) echo 'selected="selected"'; 
		echo ' value="5">'. __( '5 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('6' == $selected) echo 'selected="selected"'; 
		echo ' value="6">'. __( '6 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('7' == $selected) echo 'selected="selected"'; 
		echo ' value="7">'. __( '7 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('8' == $selected) echo 'selected="selected"'; 
		echo ' value="8">'. __( '8 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('9' == $selected) echo 'selected="selected"'; 
		echo ' value="9">'. __( '9 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('10' == $selected) echo 'selected="selected"'; 
		echo ' value="10">'. __( '10 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('11' == $selected) echo 'selected="selected"'; 
		echo ' value="11">'. __( '11 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('12' == $selected) echo 'selected="selected"'; 
		echo ' value="12">'. __( '12 days', 'wpcloudy' ) .'</option>';
		echo '<option '; 
		if ('13' == $selected) echo 'selected="selected"'; 
		echo ' value="13">'. __( '13 days', 'wpcloudy' ) .'</option>';
		echo '</select>';
		
		if (isset($this->options['wpc_display_forecast_nd'])) { 
			esc_attr( $this->options['wpc_display_forecast_nd']);
		}
	}
	
	public function wpc_display_bypass_short_days_names_callback()
    {
		$options = get_option( 'wpc_option_name' );
		  
		$check = isset($options['wpc_display_bypass_short_days_names']);
		
        echo '<input id="wpc_display_bypass_short_days_names" name="wpc_option_name[wpc_display_bypass_short_days_names]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_display_bypass_short_days_names">'. __( 'Bypass the length of name days?', 'wpcloudy' ) .'</label>';
		 
		if (isset($this->options['wpc_display_bypass_short_days_names'])) {  
			esc_attr( $this->options['wpc_display_bypass_short_days_names']);
		}
    }
	
	public function wpc_display_short_days_names_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		 
		$check = $options['wpc_display_short_days_names'];
		
        echo '<input id="wpc_display_short_days_names_yes" name="wpc_option_name[wpc_display_short_days_names]" type="radio"';
		if ('yes' == $check) echo 'checked="yes"'; 
		echo ' value="yes"/>';
		
		echo '<label for="wpc_display_short_days_names_yes">'. __( 'Short days names', 'wpcloudy' ) .'</label>';
		
		echo '<br><br>';
		
		echo '<input id="wpc_display_short_days_names_no" name="wpc_option_name[wpc_display_short_days_names]" type="radio"';
		if ('no' == $check) echo 'checked="yes"'; 
		echo ' value="no"/>';
		
		echo '<label for="wpc_display_short_days_names_no">'. __( 'Normal days names', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_display_short_days_names'])) {
			esc_attr( $this->options['wpc_display_short_days_names']);
		}
    }
	
	public function wpc_advanced_disable_css3_anims_callback()
    {
		$options = get_option( 'wpc_option_name' );   
		
		$check = isset($options['wpc_advanced_disable_css3_anims']);
		
        echo '<input id="wpc_advanced_disable_css3_anims" name="wpc_option_name[wpc_advanced_disable_css3_anims]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_advanced_disable_css3_anims">'. __( 'Disable CSS3 animations, transformations and transitions?', 'wpcloudy' ) .'</label>';
		 
		if (isset($this->options['wpc_advanced_disable_css3_anims'])) {  
			esc_attr( $this->options['wpc_advanced_disable_css3_anims']);
		}
		
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
		  
		$check = isset($options['wpc_advanced_bypass_size']);
		
        echo '<input id="wpc_advanced_bypass_size" name="wpc_option_name[wpc_advanced_bypass_size]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_advanced_bypass_size">'. __( 'Enable bypass size on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_advanced_bypass_size'])) {
			esc_attr( $this->options['wpc_advanced_bypass_size']);
		}
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
		
		if (isset($this->options['wpc_advanced_size'])) {
			esc_attr( $this->options['wpc_advanced_size']);
		}
	}
	
	public function wpc_advanced_disable_cache_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		
		$check = isset($options['wpc_advanced_disable_cache']);
		
        echo '<input id="wpc_advanced_disable_cache" name="wpc_option_name[wpc_advanced_disable_cache]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_advanced_disable_cache">'. __( 'Disable weather cache? (not recommended)', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_advanced_disable_cache'])) {
			esc_attr( $this->options['wpc_advanced_disable_cache']);
		}
    }
    
	public function wpc_advanced_cache_time_callback()
    {
		printf(
		'<input name="wpc_option_name[wpc_advanced_cache_time]" type="text" value="%s" />',
		esc_attr( $this->options['wpc_advanced_cache_time'])
		
		);
	}
	
	public function wpc_map_display_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		
		$check = isset($options['wpc_map_display']);
		
        echo '<input id="wpc_map_display" name="wpc_option_name[wpc_map_display]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_display">'. __( 'Enable map on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_display'])) {
			esc_attr( $this->options['wpc_map_display']);
		}
    } 
	
	public function wpc_map_js_callback()
	{
		$options = get_option( 'wpc_option_name' ); 
		  
		$selected = $options['wpc_map_js'];
		
		echo ' <select id="wpc_map_js" name="wpc_option_name[wpc_map_js]"> ';
		echo ' <option '; 
			if ('0' == $selected) echo 'selected="selected"'; 
			echo ' value="0">Your webhost</option>';
		echo '<option '; 
			if ('1' == $selected) echo 'selected="selected"'; 
			echo ' value="1">OpenWeatherMap</option>';
		echo '</select>';
	
		if (isset($this->options['wpc_map_js'])) {
			esc_attr( $this->options['wpc_map_js']);
		}
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
		
		$check = isset($options['wpc_map_bypass_opacity']);
		
        echo '<input id="wpc_map_bypass_opacity" name="wpc_option_name[wpc_map_bypass_opacity]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_bypass_opacity">'. __( 'Enable bypass map opacity on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_bypass_opacity'])) {
			esc_attr( $this->options['wpc_map_bypass_opacity']);
		}
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
	
		if (isset($this->options['wpc_map_opacity'])) {
			esc_attr( $this->options['wpc_map_opacity']);
		}
	} 
	
	public function wpc_map_bypass_zoom_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
		  
		$check = isset($options['wpc_map_bypass_zoom']);
		
        echo '<input id="wpc_map_bypass_zoom" name="wpc_option_name[wpc_map_bypass_zoom]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_bypass_zoom">'. __( 'Enable bypass map zoom on all weather?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_bypass_zoom'])) {
			esc_attr( $this->options['wpc_map_bypass_zoom']);
		}
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
		
		if (isset($this->options['wpc_map_zoom'])) {
			esc_attr( $this->options['wpc_map_zoom']);
		}
	} 
	
	public function wpc_map_layers_stations_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		
		$check = isset($options['wpc_map_layers_stations']);
		
        echo '<input id="wpc_map_layers_stations" name="wpc_option_name[wpc_map_layers_stations]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_stations">'. __( 'Display stations on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_stations'])) {
			esc_attr( $this->options['wpc_map_layers_stations']);
		}
    } 
	
	public function wpc_map_layers_clouds_callback()
    {
		$options = get_option( 'wpc_option_name' );
    
		$check = isset($options['wpc_map_layers_clouds']);
		
        echo '<input id="wpc_map_layers_clouds" name="wpc_option_name[wpc_map_layers_clouds]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_clouds">'. __( 'Display clouds on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_clouds'])) {
			esc_attr( $this->options['wpc_map_layers_clouds']);
		}
    }
	
	public function wpc_map_layers_precipitation_callback()
    {
		$options = get_option( 'wpc_option_name' );  
		  
		$check = isset($options['wpc_map_layers_precipitation']);
		
        echo '<input id="wpc_map_layers_precipitation" name="wpc_option_name[wpc_map_layers_precipitation]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_precipitation">'. __( 'Display precipitations on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_precipitation'])) {
			esc_attr( $this->options['wpc_map_layers_precipitation']);
		}

    }
	
	public function wpc_map_layers_snow_callback()
    {
		$options = get_option( 'wpc_option_name' );
		
		$check = isset($options['wpc_map_layers_snow']);
		
        echo '<input id="wpc_map_layers_snow" name="wpc_option_name[wpc_map_layers_snow]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_snow">'. __( 'Display snow on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_snow'])) {
			esc_attr( $this->options['wpc_map_layers_snow']);
		}
    }
	
	public function wpc_map_layers_wind_callback()
    {
		$options = get_option( 'wpc_option_name' );
		
		$check = isset($options['wpc_map_layers_wind']);
		
        echo '<input id="wpc_map_layers_wind" name="wpc_option_name[wpc_map_layers_wind]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_wind">'. __( 'Display wind on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_wind'])) {
			esc_attr( $this->options['wpc_map_layers_wind']);
		}
    }
	
	public function wpc_map_layers_temperature_callback()
    {
		$options = get_option( 'wpc_option_name' ); 
		  
		$check = isset($options['wpc_map_layers_temperature']);
		
        echo '<input id="wpc_map_layers_temperature" name="wpc_option_name[wpc_map_layers_temperature]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_temperature">'. __( 'Display temperatures on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_temperature'])) {
			esc_attr( $this->options['wpc_map_layers_temperature']);
		}
    }
    
	public function wpc_map_layers_pressure_callback()
    {
		$options = get_option( 'wpc_option_name' );    
		
		$check = isset($options['wpc_map_layers_pressure']);
		
        echo '<input id="wpc_map_layers_pressure" name="wpc_option_name[wpc_map_layers_pressure]" type="checkbox"';
		if ('1' == $check) echo 'checked="yes"'; 
		echo ' value="1"/>';
		echo '<label for="wpc_map_layers_pressure">'. __( 'Display pressure on all weather maps?', 'wpcloudy' ) .'</label>';
		
		if (isset($this->options['wpc_map_layers_pressure'])) {
			esc_attr( $this->options['wpc_map_layers_pressure']);
		}
    }
    
    public function wpc_support_info_callback()
    {
		echo '
			<h3>'. __("Problem with WP Cloudy?", "wpcloudy").'</h3>
			<p><a href="http://www.wpcloudy.com/support/faq/" target="_blank" title="'. __("FAQ", "wpcloudy").'">'. __("Read our FAQ", "wpcloudy").'</a></p>
			<p><a href="http://www.wpcloudy.com/support/guides/" target="_blank" title="'. __("Guides", "wpcloudy").'">'.__("Read our Guides", "wpcloudy").'</a></p>
			<p><a href="http://www.wpcloudy.com/support/forums/" target="_blank" title="'. __("Forum", "wpcloudy").'">'. __("WP Cloudy Forum", "wpcloudy").'</a></p>
			<p><a href="http://wordpress.org/plugins/wp-cloudy/" target="_blank" title="'. __("WP Cloudy Forum on WordPress.org", "wpcloudy").'">'. __("WP Cloudy Forum on WordPress.org", "wpcloudy").'</a></p>
			';
		
    } 
	
}
	
if( is_admin() )
    $my_settings_page = new wpc_options();
	
?>