<?php
/*
Plugin Name: WP Cloudy
Plugin URI: http://wpcloudy.com/
Description: WP Cloudy is a powerful weather plugin for WordPress, based on Open Weather Map API, using Custom Post Types and shortcodes, bundled with a ton of features.
Version: 2.8.4.1
Author: Benjamin DENIS
Author URI: http://wpcloudy.com/
License: GPLv2
*/

/*  Copyright 2013 - 2014  Benjamin DENIS  (email : contact@wpcloudy.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// To prevent calling the plugin directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Please don&rsquo;t call the plugin directly. Thanks :)';
	exit;
}

function weather_activation() {
}
register_activation_hook(__FILE__, 'weather_activation');
function weather_deactivation() {
}
register_deactivation_hook(__FILE__, 'weather_deactivation');

load_plugin_textdomain('wpcloudy', false, basename( dirname( __FILE__ ) ) . '/lang' );

///////////////////////////////////////////////////////////////////////////////////////////////////
//Shortcut settings page
///////////////////////////////////////////////////////////////////////////////////////////////////

add_filter('plugin_action_links', 'wpc_plugin_action_links', 10, 2);

function wpc_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpc-settings-admin">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Admin panel + Dashboard widget
///////////////////////////////////////////////////////////////////////////////////////////////////

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/wpcloudy-admin.php';
    require_once dirname( __FILE__ ) . '/wpcloudy-widget.php';

///////////////////////////////////////////////////////////////////////////////////////////////////
//SVG animations
///////////////////////////////////////////////////////////////////////////////////////////////////

if ( !is_admin() )
	require_once dirname( __FILE__ ) . '/wpcloudy-anim.php';

///////////////////////////////////////////////////////////////////////////////////////////////////
//Translation
///////////////////////////////////////////////////////////////////////////////////////////////////

function wpcloudy_init() {
  load_plugin_textdomain( 'wpcloudy', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
}
add_action('plugins_loaded', 'wpcloudy_init');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Enqueue styles Front-end
///////////////////////////////////////////////////////////////////////////////////////////////////

function wpcloudy_styles() {
	global $post;
		wp_register_style('wpcloudy', plugins_url('css/wpcloudy.min.css', __FILE__));
		wp_enqueue_style('wpcloudy');
		
		wp_register_style('wpcloudy-anim', plugins_url('css/wpcloudy-anim.min.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'wpcloudy_styles');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Loads the JS/CSS for Theme1 - Theme2
///////////////////////////////////////////////////////////////////////////////////////////////////

function wpc_add_themes_scripts() {
	wp_register_style( 'wpc-flexslider-css', plugins_url( 'css/flexslider.css', __FILE__ ));
	wp_register_script( 'wpc-flexslider-js', plugins_url( 'js/jquery.flexslider-min.js', __FILE__ ));	
}
add_action( 'wp_enqueue_scripts', 'wpc_add_themes_scripts', 10, 1 ); 

///////////////////////////////////////////////////////////////////////////////////////////////////
//Loads the JS/CSS in admin
///////////////////////////////////////////////////////////////////////////////////////////////////

//Dashboard
function wpc_add_dashboard_scripts() {
	wp_register_style('wpcloudy', plugins_url('css/wpcloudy.min.css', __FILE__));
	wp_enqueue_style('wpcloudy');
	
	wp_register_style('wpcloudy-anim', plugins_url('css/wpcloudy-anim.min.css', __FILE__));
	wp_enqueue_style('wpcloudy-anim');
	
	require_once dirname( __FILE__ ) . '/wpcloudy-anim.php';
}
add_action('admin_head-index.php', 'wpc_add_dashboard_scripts');

//Admin + Custom Post Type (new, listing)
function wpc_add_admin_scripts( $hook ) {

global $post;
    
	if ( $hook == 'post-new.php' || $hook == 'post.php') {
		
		wp_enqueue_script( 'wpc-tinymce-js', plugins_url('js/wpc-tinymce.js', __FILE__), array( 'wpc-tinymce' ) );
		
        if ( 'wpc-weather' === $post->post_type) { 
			wp_register_style( 'wpcloudy-admin', plugins_url('css/wpcloudy-admin.min.css', __FILE__));
			wp_enqueue_style( 'wpcloudy-admin' );
			
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'color-picker-js', plugins_url('js/color-picker.js', __FILE__), array( 'wp-color-picker' ) );
			wp_enqueue_script( 'tabs-js', plugins_url( 'js/tabs.js', __FILE__ ), array( 'jquery-ui-tabs' ) );
			
			wp_enqueue_script( 'handlebars-js', plugins_url( 'js/handlebars-v1.3.0.js', __FILE__ ), array('typeahead-bundle-js') );
			wp_enqueue_script( 'typeahead-bundle-js', plugins_url( 'js/typeahead.bundle.min.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'autocomplete-js', plugins_url( 'js/wpc-autocomplete.js', __FILE__ ), '', '', true );
			
		}
	}
	
	wp_register_style( 'wpcloudy-admin', plugins_url('css/wpcloudy-admin.min.css', __FILE__));
	wp_enqueue_style( 'wpcloudy-admin' );
}
add_action( 'admin_enqueue_scripts', 'wpc_add_admin_scripts', 10, 1 );

//WP Cloudy Options page
function wpc_add_admin_options_scripts() {
			wp_register_style( 'wpcloudy-admin', plugins_url('css/wpcloudy-admin.min.css', __FILE__));
			wp_enqueue_style( 'wpcloudy-admin' );
			
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'color-picker-js', plugins_url('js/color-picker.js', __FILE__), array( 'wp-color-picker' ) );
			wp_enqueue_script( 'tabs-js', plugins_url( 'js/tabs.js', __FILE__ ), array( 'jquery-ui-tabs' ) );
}

if (isset($_GET['page']) && ($_GET['page'] == 'wpc-settings-admin')) { 

	add_action('admin_enqueue_scripts', 'wpc_add_admin_options_scripts', 10, 1);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Add weather button in tinymce editor
///////////////////////////////////////////////////////////////////////////////////////////////////

//TinyMCE v3.x--------------------------------------------------------------------------------------
// init process for registering our button
 add_action('init', 'wpc_shortcode_button_init');
 function wpc_shortcode_button_init() {

      //Abort early if the user will never see TinyMCE
      if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
           return;

      //Add a callback to regiser our tinymce plugin   
      add_filter("mce_external_plugins", "wpc_register_tinymce_plugin"); 

      // Add a callback to add our button to the TinyMCE toolbar
      add_filter('mce_buttons', 'wpc_add_tinymce_button');
}


//This callback registers our plug-in
function wpc_register_tinymce_plugin($plugin_array) {
    $plugin_array['wpc_button'] = plugins_url( 'js/wpc-tinymce.js', __FILE__ );
    return $plugin_array;
}

//This callback adds our button to the toolbar
function wpc_add_tinymce_button($buttons) {
            //Add the button ID to the $button array
    $buttons[] = "wpc_button";
    return $buttons;
}

//TinyMCE v4.x--------------------------------------------------------------------------------------
add_action('admin_head', 'wpc_add_button_v4');

function wpc_add_button_v4() {
    global $typenow;
    
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
    	return;
    }
    
    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
        return;
	
	if ( get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "wpc_add_button_v4_plugin");
		add_filter('mce_buttons', 'wpc_add_button_v4_register');
	}
}
function wpc_add_button_v4_plugin($plugin_array) {
    $plugin_array['wpc_button_v4'] = plugins_url( 'js/wpc-tinymce.js', __FILE__ );
    return $plugin_array;
}
function wpc_add_button_v4_register($buttons) {
   array_push($buttons, "wpc_button_v4");
   return $buttons;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Display metabox in Weather Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////

add_action('add_meta_boxes','init_metabox');
function init_metabox(){
	add_meta_box('wpcloudy_basic', 'WP Cloudy Settings', 'wpcloudy_basic', 'wpc-weather', 'advanced');
	add_meta_box('wpcloudy_shortcode', 'WP Cloudy Shortcode', 'wpcloudy_shortcode', 'wpc-weather', 'side');
}

function wpcloudy_shortcode($post){
	_e( 'Copy and paste this shortcode anywhere in posts, pages, text widgets: ', 'wpcloudy' );
	echo "<div class='shortcode'>";
	echo "<span class='wpc-highlight'>[wpc-weather id=\"";
	echo get_the_ID();
	echo "\"/]</span>";
	echo "</div>";
	
	echo '<div class="shortcode-php">';
	_e( 'If you need to display this weather anywhere in your theme, simply copy and paste this code snippet in your PHP file like sidebar.php: ', 'wpcloudy' );
	echo "<span class='wpc-highlight'>echo do_shortcode('[wpc-weather id=\"YOUR_ID\"]');</span>";
	echo "</div>";
}

function wpcloudy_basic($post){
	$wpcloudy_city 						= get_post_meta($post->ID,'_wpcloudy_city',true);
	$wpcloudy_city_name					= get_post_meta($post->ID,'_wpcloudy_city_name',true);
	$wpcloudy_country_code 				= get_post_meta($post->ID,'_wpcloudy_country_code',true);
	$wpcloudy_unit 						= get_post_meta($post->ID,'_wpcloudy_unit',true);
	$wpcloudy_date_format				= get_post_meta($post->ID,'_wpcloudy_date_format',true);
	$wpcloudy_lang 						= get_post_meta($post->ID,'_wpcloudy_lang',true);
	$wpcloudy_current_weather			= get_post_meta($post->ID,'_wpcloudy_current_weather',true);
	$wpcloudy_date_temp					= get_post_meta($post->ID,'_wpcloudy_date_temp',true);
	$wpcloudy_weather					= get_post_meta($post->ID,'_wpcloudy_weather',true);
	$wpcloudy_sunrise_sunset 			= get_post_meta($post->ID,'_wpcloudy_sunrise_sunset',true);
	$wpcloudy_wind 						= get_post_meta($post->ID,'_wpcloudy_wind',true);
	$wpcloudy_humidity 					= get_post_meta($post->ID,'_wpcloudy_humidity',true);
	$wpcloudy_pressure					= get_post_meta($post->ID,'_wpcloudy_pressure',true);
	$wpcloudy_cloudiness				= get_post_meta($post->ID,'_wpcloudy_cloudiness',true);
	$wpcloudy_precipitation				= get_post_meta($post->ID,'_wpcloudy_precipitation',true);
	$wpcloudy_hour_forecast				= get_post_meta($post->ID,'_wpcloudy_hour_forecast',true);
	$wpcloudy_hour_forecast_nd			= get_post_meta($post->ID,'_wpcloudy_hour_forecast_nd',true);
	$wpcloudy_temperature_min_max		= get_post_meta($post->ID,'_wpcloudy_temperature_min_max',true);
	$wpcloudy_display_temp_unit			= get_post_meta($post->ID,'_wpcloudy_display_temp_unit',true);
	$wpcloudy_forecast					= get_post_meta($post->ID,'_wpcloudy_forecast',true);
	$wpcloudy_forecast_nd				= get_post_meta($post->ID,'_wpcloudy_forecast_nd',true);
	$wpcloudy_short_days_names			= get_post_meta($post->ID,'_wpcloudy_short_days_names',true);
	$wpcloudy_disable_anims				= get_post_meta($post->ID,'_wpcloudy_disable_anims',true);
	$wpcloudy_meta_bg_color				= get_post_meta($post->ID,'_wpcloudy_meta_bg_color',true);
	$wpcloudy_meta_txt_color			= get_post_meta($post->ID,'_wpcloudy_meta_txt_color',true);
	$wpcloudy_meta_border_color			= get_post_meta($post->ID,'_wpcloudy_meta_border_color',true);
	$wpcloudy_custom_css				= get_post_meta($post->ID,'_wpcloudy_custom_css',true);
	$wpcloudy_size 						= get_post_meta($post->ID,'_wpcloudy_size',true);
	$wpcloudy_map 						= get_post_meta($post->ID,'_wpcloudy_map',true);
	$wpcloudy_map_height				= get_post_meta($post->ID,'_wpcloudy_map_height',true);
	$wpcloudy_map_opacity				= get_post_meta($post->ID,'_wpcloudy_map_opacity',true);
	$wpcloudy_map_zoom					= get_post_meta($post->ID,'_wpcloudy_map_zoom',true);
	$wpcloudy_map_stations				= get_post_meta($post->ID,'_wpcloudy_map_stations',true);
	$wpcloudy_map_clouds				= get_post_meta($post->ID,'_wpcloudy_map_clouds',true);
	$wpcloudy_map_precipitation			= get_post_meta($post->ID,'_wpcloudy_map_precipitation',true);
	$wpcloudy_map_snow					= get_post_meta($post->ID,'_wpcloudy_map_snow',true);
	$wpcloudy_map_wind					= get_post_meta($post->ID,'_wpcloudy_map_wind',true);
	$wpcloudy_map_temperature			= get_post_meta($post->ID,'_wpcloudy_map_temperature',true);
	$wpcloudy_map_pressure				= get_post_meta($post->ID,'_wpcloudy_map_pressure',true);
	  
	echo '<div id="wpcloudy-tabs">
			<ul>
				<li><a href="#tabs-1">'. __( 'Basic settings', 'wpcloudy' ) .'</a></li>
				<li><a href="#tabs-2">'. __( 'Display', 'wpcloudy' ) .'</a></li>
				<li><a href="#tabs-3">'. __( 'Advanced', 'wpcloudy' ) .'</a></li>
				<li><a href="#tabs-4">'. __( 'Map', 'wpcloudy' ) .'</a></li>
			</ul>
			
			<div id="tabs-1">
				<p>
					<label for="wpcloudy_city_meta">'. __( 'City', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_city_meta" class="cities typeahead" type="text" name="wpcloudy_city" placeholder="'.__('Enter your city','wpcloudy').'" value="'.$wpcloudy_city.'" />
				</p>
				<p>
					<label for="wpcloudy_city_name_meta">'. __( 'Custom city title', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_city_name_meta" type="text" name="wpcloudy_city_name" value="'.$wpcloudy_city_name.'" />
				</p>
				<p>
					<label for="wpcloudy_country_meta">'. __( 'Country?', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_country_meta" class="countries typeahead" type="text" name="wpcloudy_country_code" value="'.$wpcloudy_country_code.'" />
				</p>
				<p>
					<label for="unit_meta">'. __( 'Imperial or metric units?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_unit">
						<option ' . selected( 'imperial', $wpcloudy_unit, false ) . ' value="imperial">'. __( 'Imperial', 'wpcloudy' ) .'</option>
						<option ' . selected( 'metric', $wpcloudy_unit, false ) . ' value="metric">'. __( 'Metric', 'wpcloudy' ) .'</option>
					</select>
				</p>
				<p>
					<label for="wpcloudy_date_format_meta">'. __( '12h / 24h date format?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_date_format">
						<option ' . selected( '12', $wpcloudy_date_format, false ) . ' value="12">'. __( '12 h', 'wpcloudy' ) .'</option>
						<option ' . selected( '24', $wpcloudy_date_format, false ) . ' value="24">'. __( '24 h', 'wpcloudy' ) .'</option>
					</select>
				</p>
				<p>
					<label for="wpcloudy_lang_meta">'. __( 'Display language?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_lang">
						<option ' . selected( 'fr', $wpcloudy_lang, false ) . ' value="fr">'. __( 'French', 'wpcloudy' ) .'</option>
						<option ' . selected( 'en', $wpcloudy_lang, false ) . ' value="en">'. __( 'English', 'wpcloudy' ) .'</option>
						<option ' . selected( 'ru', $wpcloudy_lang, false ) . ' value="ru">'. __( 'Russian', 'wpcloudy' ) .'</option>
						<option ' . selected( 'it', $wpcloudy_lang, false ) . ' value="it">'. __( 'Italian', 'wpcloudy' ) .'</option>
						<option ' . selected( 'sp', $wpcloudy_lang, false ) . ' value="sp">'. __( 'Spanish', 'wpcloudy' ) .'</option>
						<option ' . selected( 'ua', $wpcloudy_lang, false ) . ' value="ua">'. __( 'Ukrainian', 'wpcloudy' ) .'</option>
						<option ' . selected( 'de', $wpcloudy_lang, false ) . ' value="de">'. __( 'German', 'wpcloudy' ) .'</option>
						<option ' . selected( 'pt', $wpcloudy_lang, false ) . ' value="pt">'. __( 'Portuguese', 'wpcloudy' ) .'</option>
						<option ' . selected( 'ro', $wpcloudy_lang, false ) . ' value="ro">'. __( 'Romanian', 'wpcloudy' ) .'</option>
						<option ' . selected( 'pl', $wpcloudy_lang, false ) . ' value="pl">'. __( 'Polish', 'wpcloudy' ) .'</option>
						<option ' . selected( 'fi', $wpcloudy_lang, false ) . ' value="fi">'. __( 'Finnish', 'wpcloudy' ) .'</option>
						<option ' . selected( 'nl', $wpcloudy_lang, false ) . ' value="nl">'. __( 'Dutch', 'wpcloudy' ) .'</option>
						<option ' . selected( 'bg', $wpcloudy_lang, false ) . ' value="bg">'. __( 'Bulgarian', 'wpcloudy' ) .'</option>
						<option ' . selected( 'se', $wpcloudy_lang, false ) . ' value="se">'. __( 'Swedish', 'wpcloudy' ) .'</option>
						<option ' . selected( 'zh_tw', $wpcloudy_lang, false ) . ' value="zh_tw">'. __( 'Chinese Traditional', 'wpcloudy' ) .'</option>
						<option ' . selected( 'zh_cn', $wpcloudy_lang, false ) . ' value="zh_cn">'. __( 'Chinese Simplified', 'wpcloudy' ) .'</option>
						<option ' . selected( 'tr', $wpcloudy_lang, false ) . ' value="tr">'. __( 'Turkish', 'wpcloudy' ) .'</option>
						<option ' . selected( 'cz', $wpcloudy_lang, false ) . ' value="cz">'. __( 'Czech', 'wpcloudy' ) .'</option>
						<option ' . selected( 'gl', $wpcloudy_lang, false ) . ' value="gl">'. __( 'Galician', 'wpcloudy' ) .'</option>
						<option ' . selected( 'vi', $wpcloudy_lang, false ) . ' value="vi">'. __( 'Vietnamese', 'wpcloudy' ) .'</option>
						<option ' . selected( 'ar', $wpcloudy_lang, false ) . ' value="ar">'. __( 'Arabic', 'wpcloudy' ) .'</option>
						<option ' . selected( 'mk', $wpcloudy_lang, false ) . ' value="mk">'. __( 'Macedonian', 'wpcloudy' ) .'</option>
						<option ' . selected( 'sk', $wpcloudy_lang, false ) . ' value="sk">'. __( 'Slovak', 'wpcloudy' ) .'</option>
					</select>
				</p>		
			</div>
			<div id="tabs-2">
				<p>				
					<label for="wpcloudy_current_weather_meta">
						<input type="checkbox" name="wpcloudy_current_weather" id="wpcloudy_current_weather_meta" value="yes" '. checked( $wpcloudy_current_weather, 'yes', false ) .' />
							'. __( 'Current weather?', 'wpcloudy' ) .'
					</label>			
				</p>
				<p>				
					<label for="wpcloudy_weather_meta">
						<input type="checkbox" name="wpcloudy_weather" id="wpcloudy_weather_meta" value="yes" '. checked( $wpcloudy_weather, 'yes', false ) .' />
							'. __( 'Short condition?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_date_temp_meta">
						<input type="checkbox" name="wpcloudy_date_temp" id="wpcloudy_date_temp_meta" value="yes" '. checked( $wpcloudy_date_temp, 'yes', false ) .' />
							'. __( 'Date + temperatures?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_sunrise_sunset_meta">
						<input type="checkbox" name="wpcloudy_sunrise_sunset" id="wpcloudy_sunrise_sunset_meta" value="yes" '. checked( $wpcloudy_sunrise_sunset, 'yes', false ) .' />
							'. __( 'Sunrise + sunset? appears only if date + temperatures is checked', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_wind_meta">
						<input type="checkbox" name="wpcloudy_wind" id="wpcloudy_wind_meta" value="yes" '. checked( $wpcloudy_wind, 'yes', false ) .' />
							'. __( 'Wind?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_humidity_meta">
						<input type="checkbox" name="wpcloudy_humidity" id="wpcloudy_humidity_meta" value="yes" '. checked( $wpcloudy_humidity, 'yes', false ) .' />
							'. __( 'Humidity?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_pressure_meta">
						<input type="checkbox" name="wpcloudy_pressure" id="wpcloudy_pressure_meta" value="yes" '. checked( $wpcloudy_pressure, 'yes', false ) .' />
							'. __( 'Pressure?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_cloudiness_meta">
						<input type="checkbox" name="wpcloudy_cloudiness" id="wpcloudy_cloudiness_meta" value="yes" '. checked( $wpcloudy_cloudiness, 'yes', false ) .' />
							'. __( 'Cloudiness?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_precipitation_meta">
						<input type="checkbox" name="wpcloudy_precipitation" id="wpcloudy_precipitation_meta" value="yes" '. checked( $wpcloudy_precipitation, 'yes', false ) .' />
							'. __( 'Precipitation?', 'wpcloudy' ) .'
					</label>
				</p>
				<p class="temperatures">
					'. __( 'Temperatures', 'wpcloudy' ) .'
				</p>
				<p>
					<label for="wpcloudy_display_temp_unit_meta">
						<input type="checkbox" name="wpcloudy_display_temp_unit" id="wpcloudy_display_temp_unit_meta" value="yes" '. checked( $wpcloudy_display_temp_unit, 'yes', false ) .' />
							'. __( 'Temperatures unit (C / F)?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_temperature_min_max_meta">
						<input type="radio" name="wpcloudy_temperature_min_max" id="wpcloudy_temperature_min_max_meta" value="yes" '. checked( $wpcloudy_temperature_min_max, 'yes', false ) .' />
							'. __( 'Today date + Min-Max Temperatures?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_temperature_average_meta">
						<input type="radio" name="wpcloudy_temperature_min_max" id="wpcloudy_temperature_average_meta" value="no" '. checked( $wpcloudy_temperature_min_max, 'no', false ) .' />
							'. __( 'Today date + Average Temperature?', 'wpcloudy' ) .'
					</label>
				</p>
				<p class="hour">
					'. __( 'Hourly Forecast', 'wpcloudy' ) .'
				</p>
				<p>
					<label for="wpcloudy_hour_forecast_meta">
						<input type="checkbox" name="wpcloudy_hour_forecast" id="wpcloudy_hour_forecast_meta" value="yes" '. checked( $wpcloudy_hour_forecast, 'yes', false ) .' />
							'. __( 'Hour Forecast?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_hour_forecast_nd_meta">'. __( 'How hours ranges ?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_hour_forecast_nd">
						<option ' . selected( '1', $wpcloudy_hour_forecast_nd, false ) . ' value="1">'. __( '1', 'wpcloudy' ) .'</option>
						<option ' . selected( '2', $wpcloudy_hour_forecast_nd, false ) . ' value="2">'. __( '2', 'wpcloudy' ) .'</option>
						<option ' . selected( '3', $wpcloudy_hour_forecast_nd, false ) . ' value="3">'. __( '3', 'wpcloudy' ) .'</option>
						<option ' . selected( '4', $wpcloudy_hour_forecast_nd, false ) . ' value="4">'. __( '4', 'wpcloudy' ) .'</option>
						<option ' . selected( '5', $wpcloudy_hour_forecast_nd, false ) . ' value="5">'. __( '5', 'wpcloudy' ) .'</option>
						<option ' . selected( '6', $wpcloudy_hour_forecast_nd, false ) . ' value="6">'. __( '6', 'wpcloudy' ) .'</option>
					</select>
				</p>
				<p class="forecast">
					'. __( '14-Day Forecast', 'wpcloudy' ) .'
				</p>
				<p>
					<label for="wpcloudy_forecast_meta">
						<input type="checkbox" name="wpcloudy_forecast" id="wpcloudy_forecast_meta" value="yes" '. checked( $wpcloudy_forecast, 'yes', false ) .' />
							'. __( '14-Day Forecast?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_forecast_nd_meta">'. __( 'How many days?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_forecast_nd">
						<option ' . selected( '1', $wpcloudy_forecast_nd, false ) . ' value="1">'. __( '1 day', 'wpcloudy' ) .'</option>
						<option ' . selected( '2', $wpcloudy_forecast_nd, false ) . ' value="2">'. __( '2 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '3', $wpcloudy_forecast_nd, false ) . ' value="3">'. __( '3 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '4', $wpcloudy_forecast_nd, false ) . ' value="4">'. __( '4 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '5', $wpcloudy_forecast_nd, false ) . ' value="5">'. __( '5 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '6', $wpcloudy_forecast_nd, false ) . ' value="6">'. __( '6 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '7', $wpcloudy_forecast_nd, false ) . ' value="7">'. __( '7 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '8', $wpcloudy_forecast_nd, false ) . ' value="8">'. __( '8 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '9', $wpcloudy_forecast_nd, false ) . ' value="9">'. __( '9 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '10', $wpcloudy_forecast_nd, false ) . ' value="10">'. __( '10 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '11', $wpcloudy_forecast_nd, false ) . ' value="11">'. __( '11 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '12', $wpcloudy_forecast_nd, false ) . ' value="12">'. __( '12 days', 'wpcloudy' ) .'</option>
						<option ' . selected( '13', $wpcloudy_forecast_nd, false ) . ' value="13">'. __( '13 days', 'wpcloudy' ) .'</option>
					</select>
				</p>
				<p>
					<label for="wpcloudy_short_days_names_yes_meta">
						<input type="radio" name="wpcloudy_short_days_names" id="wpcloudy_short_days_names_yes_meta" value="yes" '. checked( $wpcloudy_short_days_names, 'yes', false ) .' />
							'. __( 'Short days names?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_short_days_names_no_meta">
						<input type="radio" name="wpcloudy_short_days_names" id="wpcloudy_short_days_names_no_meta" value="no" '. checked( $wpcloudy_short_days_names, 'no', false ) .' />
							'. __( 'Normal days names?', 'wpcloudy' ) .'
					</label>
				</p>		
			</div>
			<div id="tabs-3">
				<p>				
					<label for="wpcloudy_disable_anims_meta">
						<input type="checkbox" name="wpcloudy_disable_anims" id="wpcloudy_disable_anims_meta" value="yes" '. checked( $wpcloudy_disable_anims, 'yes', false ) .' />
							'. __( 'Disable CSS3 animations?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_meta_bg_color2">'. __( 'Background color', 'wpcloudy' ) .'</label>
					<input name="wpcloudy_meta_bg_color" type="text" value="'. $wpcloudy_meta_bg_color .'" class="wpcloudy_meta_bg_color_picker" />
				</p>
				<p>
					<label for="wpcloudy_meta_txt_color2">'. __( 'Text color', 'wpcloudy' ) .'</label>
					<input name="wpcloudy_meta_txt_color" type="text" value="'. $wpcloudy_meta_txt_color .'" class="wpcloudy_meta_txt_color_picker" />
				</p>
				<p>
					<label for="wpcloudy_meta_border_color2">'. __( 'Border color', 'wpcloudy' ) .'</label>
					<input name="wpcloudy_meta_border_color" type="text" value="'. $wpcloudy_meta_border_color .'" class="wpcloudy_meta_border_color_picker" />
				</p>
				<p>
					<label for="wpcloudy_custom_css_meta">'. __( 'Custom CSS', 'wpcloudy' ) .'</label>
					<textarea id="wpcloudy_custom_css_meta" name="wpcloudy_custom_css">'.$wpcloudy_custom_css.'</textarea>
				</p>
				<p>
					<label for="size_meta">'. __( 'Weather size?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_size">
						<option ' . selected( 'small', $wpcloudy_size, false ) . ' value="small">'. __( 'Small', 'wpcloudy' ) .'</option>
						<option ' . selected( 'medium', $wpcloudy_size, false ) . ' value="medium">'. __( 'Medium', 'wpcloudy' ) .'</option>
						<option ' . selected( 'large', $wpcloudy_size, false ) . ' value="large">'. __( 'Large', 'wpcloudy' ) .'</option>
					</select>
				</p>
			</div>
			<div id="tabs-4">
				<p>				
					<label for="wpcloudy_map_meta">
						<input type="checkbox" name="wpcloudy_map" id="wpcloudy_map_meta" value="yes" '. checked( $wpcloudy_map, 'yes', false ) .' />
							'. __( 'Display map?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_map_height_meta">'. __( 'Map height (in px)', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_map_height_meta" type="text" name="wpcloudy_map_height" value="'.$wpcloudy_map_height.'" />
				</p>
				<p>
					<label for="wpcloudy_map_opacity_meta">'. __( 'Layers opacity', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_map_opacity">
						<option ' . selected( '0', $wpcloudy_map_opacity, false ) . ' value="0">0%</option>
						<option ' . selected( '0.1', $wpcloudy_map_opacity, false ) . ' value="0.1">10%</option>
						<option ' . selected( '0.2', $wpcloudy_map_opacity, false ) . ' value="0.2">20%</option>
						<option ' . selected( '0.3', $wpcloudy_map_opacity, false ) . ' value="0.3">30%</option>
						<option ' . selected( '0.4', $wpcloudy_map_opacity, false ) . ' value="0.4">40%</option>
						<option ' . selected( '0.5', $wpcloudy_map_opacity, false ) . ' value="0.5">50%</option>
						<option ' . selected( '0.6', $wpcloudy_map_opacity, false ) . ' value="0.6">60%</option>
						<option ' . selected( '0.7', $wpcloudy_map_opacity, false ) . ' value="0.7">70%</option>
						<option ' . selected( '0.8', $wpcloudy_map_opacity, false ) . ' value="0.8">80%</option>
						<option ' . selected( '0.9', $wpcloudy_map_opacity, false ) . ' value="0.9">90%</option>
						<option ' . selected( '1', $wpcloudy_map_opacity, false ) . ' value="1">100%</option>
					</select>
				</p>
				<p>
					<label for="wpcloudy_map_zoom_meta">'. __( 'Zoom', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_map_zoom">
						<option ' . selected( '1', $wpcloudy_map_zoom, false ) . ' value="1">1</option>
						<option ' . selected( '2', $wpcloudy_map_zoom, false ) . ' value="2">2</option>
						<option ' . selected( '3', $wpcloudy_map_zoom, false ) . ' value="3">3</option>
						<option ' . selected( '4', $wpcloudy_map_zoom, false ) . ' value="4">4</option>
						<option ' . selected( '5', $wpcloudy_map_zoom, false ) . ' value="5">5</option>
						<option ' . selected( '6', $wpcloudy_map_zoom, false ) . ' value="6">6</option>
						<option ' . selected( '7', $wpcloudy_map_zoom, false ) . ' value="7">7</option>
						<option ' . selected( '8', $wpcloudy_map_zoom, false ) . ' value="8">8</option>
						<option ' . selected( '9', $wpcloudy_map_zoom, false ) . ' value="9">9</option>
						<option ' . selected( '10', $wpcloudy_map_zoom, false ) . ' value="10">10</option>
						<option ' . selected( '11', $wpcloudy_map_zoom, false ) . ' value="11">11</option>
						<option ' . selected( '12', $wpcloudy_map_zoom, false ) . ' value="12">12</option>
						<option ' . selected( '13', $wpcloudy_map_zoom, false ) . ' value="13">13</option>
						<option ' . selected( '14', $wpcloudy_map_zoom, false ) . ' value="14">14</option>
						<option ' . selected( '15', $wpcloudy_map_zoom, false ) . ' value="15">15</option>
						<option ' . selected( '16', $wpcloudy_map_zoom, false ) . ' value="16">16</option>
						<option ' . selected( '17', $wpcloudy_map_zoom, false ) . ' value="17">17</option>
						<option ' . selected( '18', $wpcloudy_map_zoom, false ) . ' value="18">18</option>
					</select>
				</p>
				<p class="subsection-title">
					'. __( 'Layers', 'wpcloudy' ) .'
				</p>
				<p>				
					<label for="wpcloudy_map_stations_meta">
						<input type="checkbox" name="wpcloudy_map_stations" id="wpcloudy_map_stations_meta" value="yes" '. checked( $wpcloudy_map_stations, 'yes', false ) .' />
							'. __( 'Display stations?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_map_clouds_meta">
						<input type="checkbox" name="wpcloudy_map_clouds" id="wpcloudy_map_clouds_meta" value="yes" '. checked( $wpcloudy_map_clouds, 'yes', false ) .' />
							'. __( 'Display clouds?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_map_precipitation_meta">
						<input type="checkbox" name="wpcloudy_map_precipitation" id="wpcloudy_map_precipitation_meta" value="yes" '. checked( $wpcloudy_map_precipitation, 'yes', false ) .' />
							'. __( 'Display precipitation?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_map_snow_meta">
						<input type="checkbox" name="wpcloudy_map_snow" id="wpcloudy_map_snow_meta" value="yes" '. checked( $wpcloudy_map_snow, 'yes', false ) .' />
							'. __( 'Display snow?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_map_wind_meta">
						<input type="checkbox" name="wpcloudy_map_wind" id="wpcloudy_map_wind_meta" value="yes" '. checked( $wpcloudy_map_wind, 'yes', false ) .' />
							'. __( 'Display wind?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_map_temperature_meta">
						<input type="checkbox" name="wpcloudy_map_temperature" id="wpcloudy_map_temperature_meta" value="yes" '. checked( $wpcloudy_map_temperature, 'yes', false ) .' />
							'. __( 'Display temperature?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>				
					<label for="wpcloudy_map_pressure_meta">
						<input type="checkbox" name="wpcloudy_map_pressure" id="wpcloudy_map_pressure_meta" value="yes" '. checked( $wpcloudy_map_pressure, 'yes', false ) .' />
							'. __( 'Display pressure?', 'wpcloudy' ) .'
					</label>
				</p>
			</div>
	</div>
  ';  
}

add_action('save_post','wpc_save_metabox');
function wpc_save_metabox($post_id){
	if(isset($_POST['wpcloudy_city'])){
	  update_post_meta($post_id, '_wpcloudy_city', esc_html($_POST['wpcloudy_city']));
	}
	if(isset($_POST['wpcloudy_city_name'])){
	  update_post_meta($post_id, '_wpcloudy_city_name', esc_html($_POST['wpcloudy_city_name']));
	}
	if(isset($_POST['wpcloudy_country_code'])){
	  update_post_meta($post_id, '_wpcloudy_country_code', esc_html($_POST['wpcloudy_country_code']));
	}
	if(isset($_POST['wpcloudy_unit'])) {
	  update_post_meta($post_id, '_wpcloudy_unit', $_POST['wpcloudy_unit']);
	}
	if(isset($_POST['wpcloudy_date_format'])) {
	  update_post_meta($post_id, '_wpcloudy_date_format', $_POST['wpcloudy_date_format']);
	}
	if(isset($_POST['wpcloudy_lang'])){
	  update_post_meta($post_id, '_wpcloudy_lang', esc_html($_POST['wpcloudy_lang']));
	}
	if( isset( $_POST[ 'wpcloudy_current_weather' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_current_weather', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_current_weather', '' );
	}
	if( isset( $_POST[ 'wpcloudy_weather' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_weather', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_weather', '' );
	}
	if( isset( $_POST[ 'wpcloudy_date_temp' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_date_temp', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_date_temp', '' );
	}
	if( isset( $_POST[ 'wpcloudy_display_temp_unit' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_display_temp_unit', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_display_temp_unit', '' );
	}
	if( isset( $_POST[ 'wpcloudy_sunrise_sunset' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_sunrise_sunset', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_sunrise_sunset', '' );
	}
	if( isset( $_POST[ 'wpcloudy_wind' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_wind', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_wind', '' );
	}
	if( isset( $_POST[ 'wpcloudy_humidity' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_humidity', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_humidity', '' );
	}
	if( isset( $_POST[ 'wpcloudy_pressure' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_pressure', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_pressure', '' );
	}
	if( isset( $_POST[ 'wpcloudy_cloudiness' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_cloudiness', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_cloudiness', '' );
	}
	if( isset( $_POST[ 'wpcloudy_precipitation' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_precipitation', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_precipitation', '' );
	}
	if( isset( $_POST[ 'wpcloudy_hour_forecast' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_hour_forecast', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_hour_forecast', '' );
	}
	if(isset($_POST['wpcloudy_hour_forecast_nd'])){
	  update_post_meta($post_id, '_wpcloudy_hour_forecast_nd', esc_html($_POST['wpcloudy_hour_forecast_nd']));
	}
	if( isset( $_POST[ 'wpcloudy_temperature_min_max' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_temperature_min_max', $_POST[ 'wpcloudy_temperature_min_max' ] );
	}
	if( isset( $_POST[ 'wpcloudy_short_days_names' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_short_days_names', $_POST[ 'wpcloudy_short_days_names' ] );
	}
	if( isset( $_POST[ 'wpcloudy_forecast' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_forecast', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_forecast', '' );
	}
	if(isset($_POST['wpcloudy_forecast_nd'])){
	  update_post_meta($post_id, '_wpcloudy_forecast_nd', esc_html($_POST['wpcloudy_forecast_nd']));
	}
	if( isset( $_POST[ 'wpcloudy_disable_anims' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_disable_anims', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_disable_anims', '' );
	}
	if( isset( $_POST[ 'wpcloudy_meta_bg_color' ] ) ) {
	  update_post_meta( $post_id, '_wpcloudy_meta_bg_color', $_POST[ 'wpcloudy_meta_bg_color' ] );
	}
	if( isset( $_POST[ 'wpcloudy_meta_txt_color' ] ) ) {
	  update_post_meta( $post_id, '_wpcloudy_meta_txt_color', $_POST[ 'wpcloudy_meta_txt_color' ] );
	}
	if( isset( $_POST[ 'wpcloudy_meta_border_color' ] ) ) {
	  update_post_meta( $post_id, '_wpcloudy_meta_border_color', $_POST[ 'wpcloudy_meta_border_color' ] );
	}
	if(isset($_POST['wpcloudy_custom_css'])){
	  update_post_meta($post_id, '_wpcloudy_custom_css', esc_html($_POST['wpcloudy_custom_css']));
	}
	if(isset($_POST['wpcloudy_size'])) {
	  update_post_meta($post_id, '_wpcloudy_size', $_POST['wpcloudy_size']);
	}
	if( isset( $_POST[ 'wpcloudy_map' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map', '' );
	}
	if(isset($_POST['wpcloudy_map_height'])){
	  update_post_meta($post_id, '_wpcloudy_map_height', esc_html($_POST['wpcloudy_map_height']));
	}
	if(isset($_POST['wpcloudy_map_opacity'])) {
	  update_post_meta($post_id, '_wpcloudy_map_opacity', $_POST['wpcloudy_map_opacity']);
	}
	if(isset($_POST['wpcloudy_map_zoom'])) {
	  update_post_meta($post_id, '_wpcloudy_map_zoom', $_POST['wpcloudy_map_zoom']);
	}
	if( isset( $_POST[ 'wpcloudy_map_stations' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_stations', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_stations', '' );
	}
	if( isset( $_POST[ 'wpcloudy_map_clouds' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_clouds', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_clouds', '' );
	}
	if( isset( $_POST[ 'wpcloudy_map_precipitation' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_precipitation', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_precipitation', '' );
	}
	if( isset( $_POST[ 'wpcloudy_map_snow' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_snow', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_snow', '' );
	}
	if( isset( $_POST[ 'wpcloudy_map_wind' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_wind', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_wind', '' );
	}
	if( isset( $_POST[ 'wpcloudy_map_temperature' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_temperature', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_temperature', '' );
	}
	if( isset( $_POST[ 'wpcloudy_map_pressure' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_map_pressure', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_map_pressure', '' );
	}
}

add_action('save_post','wpc_clear_cache_current');
function wpc_clear_cache_current() {
	delete_transient( "myweather_current_".get_the_ID() );
	delete_transient( "myweather_".get_the_ID() );
	delete_transient( "myweather_sevendays_".get_the_ID() );
}
///////////////////////////////////////////////////////////////////////////////////////////////////
//WPC Languages
///////////////////////////////////////////////////////////////////////////////////////////////////		

//Bypass Lang
function get_admin_bypass_lang() {
	$wpc_admin_bypass_lang_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_lang_option ) ) {
		foreach ($wpc_admin_bypass_lang_option as $key => $wpc_admin_bypass_lang_value)
			$options[$key] = $wpc_admin_bypass_lang_value;
		if (isset($wpc_admin_bypass_lang_option['wpc_basic_bypass_lang'])) {
			return $wpc_admin_bypass_lang_option['wpc_basic_bypass_lang'];
		}
	}
};

function get_admin_lang() {
	$wpc_admin_lang_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_lang_option ) ) {
		foreach ($wpc_admin_lang_option as $key => $wpc_admin_lang_value)
			$options[$key] = $wpc_admin_lang_value;
		return $wpc_admin_lang_option['wpc_basic_lang'];
	}
};

function get_lang($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_lang_value = get_post_meta($id,'_wpcloudy_lang',true);
		return $wpc_lang_value;
};

function get_bypass_lang($attr,$content) {
	if (get_admin_lang() && (get_admin_bypass_lang())) {
		return get_admin_lang(); 
	}
	else {
		return get_lang($attr,$content);
	}
}	

///////////////////////////////////////////////////////////////////////////////////////////////////
//WPC Options Panel
///////////////////////////////////////////////////////////////////////////////////////////////////		
//Bypass Unit
function get_admin_bypass_unit() {
	$wpc_admin_bypass_unit_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_unit_option ) ) {
		foreach ($wpc_admin_bypass_unit_option as $key => $wpc_admin_bypass_unit_value)
			$options[$key] = $wpc_admin_bypass_unit_value;
		 if (isset($wpc_admin_bypass_unit_option['wpc_basic_bypass_unit'])) { 
		 	return $wpc_admin_bypass_unit_option['wpc_basic_bypass_unit'];
		 }
	}
};

function get_admin_unit() {
	$wpc_admin_unit_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_unit_option ) ) {
		foreach ($wpc_admin_unit_option as $key => $wpc_admin_unit_value)
			$options[$key] = $wpc_admin_unit_value;
		if (isset($wpc_admin_unit_option['wpc_basic_unit'])) { 
			return $wpc_admin_unit_option['wpc_basic_unit'];
		}
	}
};

function get_unit($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_unit_value = get_post_meta($id,'_wpcloudy_unit',true);
		return $wpc_unit_value;
};

function get_bypass_unit($attr,$content) {
	if (get_admin_unit() && (get_admin_bypass_unit())) {
		return get_admin_unit(); 
	}
	else {
		return get_unit($attr,$content);
	}
}	
//Bypass Date format
function get_admin_bypass_date() {
	$wpc_admin_bypass_date_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_date_option ) ) {
		foreach ($wpc_admin_bypass_date_option as $key => $wpc_admin_bypass_date_value)
			$options[$key] = $wpc_admin_bypass_date_value;
		 if (isset($wpc_admin_bypass_date_option['wpc_basic_bypass_date'])) { 
		 	return $wpc_admin_bypass_date_option['wpc_basic_bypass_date'];
		 }
	}
};

function get_admin_date() {
	$wpc_admin_date_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_date_option ) ) {
		foreach ($wpc_admin_date_option as $key => $wpc_admin_date_value)
			$options[$key] = $wpc_admin_date_value;
		if (isset($wpc_admin_date_option['wpc_basic_date'])) { 
			return $wpc_admin_date_option['wpc_basic_date'];
		}
	}
};

function get_date($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_date_value = get_post_meta($id,'_wpcloudy_date_format',true);
		return $wpc_date_value;
};

function get_bypass_date($attr,$content) {
	if (get_admin_date() && (get_admin_bypass_date())) {
		return get_admin_date(); 
	}
	else {
		return get_date($attr,$content);
	}
}	
//Bypass Forecast Days
function get_admin_bypass_forecast_nd() {
	$wpc_admin_bypass_forecast_nd_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_forecast_nd_option ) ) {
		foreach ($wpc_admin_bypass_forecast_nd_option as $key => $wpc_admin_bypass_forecast_nd_value)
			$options[$key] = $wpc_admin_bypass_forecast_nd_value;
		if (isset($wpc_admin_bypass_forecast_nd_option['wpc_display_bypass_forecast_nd'])) {
			return $wpc_admin_bypass_forecast_nd_option['wpc_display_bypass_forecast_nd'];
		}
	}
};

function get_admin_forecast_nd() {
	$wpc_admin_forecast_nd_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_forecast_nd_option ) ) {
		foreach ($wpc_admin_forecast_nd_option as $key => $wpc_admin_forecast_nd_value)
			$options[$key] = $wpc_admin_forecast_nd_value;
		if (isset($wpc_admin_forecast_nd_option['wpc_display_forecast_nd'])) {
			return $wpc_admin_forecast_nd_option['wpc_display_forecast_nd'];
		}
	}
};

function get_forecast_nd($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_forecast_nd_value = get_post_meta($id,'_wpcloudy_forecast_nd',true);
		return $wpc_forecast_nd_value;
};

function get_bypass_forecast_nd($attr,$content) {
	if (get_admin_forecast_nd() && (get_admin_bypass_forecast_nd())) {
		return get_admin_forecast_nd(); 
	}
	else {
		return get_forecast_nd($attr,$content);
	}
}	

//Disables CSS3 animations
function get_admin_disable_css3_anims() {
	$wpc_admin_disable_css3_anims_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_disable_css3_anims_option ) ) {
		foreach ($wpc_admin_disable_css3_anims_option as $key => $wpc_admin_disable_css3_anims_value)
			$options[$key] = $wpc_admin_disable_css3_anims_value;
		if (isset($wpc_admin_disable_css3_anims_option['wpc_advanced_disable_css3_anims'])) {
			return $wpc_admin_disable_css3_anims_option['wpc_advanced_disable_css3_anims'];
		}
	}
};
function get_disable_css3_anims($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpcloudy_disable_anims_value = get_post_meta($id,'_wpcloudy_disable_anims',true);
		return $wpcloudy_disable_anims_value;
};

function get_bypass_disable_css3_anims($attr,$content) {
	if (get_admin_disable_css3_anims()) {
		return get_admin_disable_css3_anims(); 
	}
	else {
		return get_disable_css3_anims($attr,$content);
	}
}

//Loads Map JS From...
function get_admin_map_js() {
	$wpc_admin_map_js_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_map_js_option ) ) {
		foreach ($wpc_admin_map_js_option as $key => $wpc_admin_map_js_value)
			$options[$key] = $wpc_admin_map_js_value;
		if (isset($wpc_admin_map_js_option['wpc_map_js'])) {
			return $wpc_admin_map_js_option['wpc_map_js'];
		}
	}
};

//Disables weather cache
function get_admin_disable_cache() {
	$wpc_admin_disable_cache_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_disable_cache_option ) ) {
		foreach ($wpc_admin_disable_cache_option as $key => $wpc_admin_disable_cache_value)
			$options[$key] = $wpc_admin_disable_cache_value;
		if (isset($wpc_admin_disable_cache_option['wpc_advanced_disable_cache'])) {
			return $wpc_admin_disable_cache_option['wpc_advanced_disable_cache'];
		}
	}
};

//Time cache refresh
function get_admin_cache_time() {
	$wpc_admin_cache_time_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_cache_time_option ) ) {
		foreach ($wpc_admin_cache_time_option as $key => $wpc_admin_cache_time_value)
			$options[$key] = $wpc_admin_cache_time_value;
		if (isset($wpc_admin_cache_time_option['wpc_advanced_cache_time'])) {
			return $wpc_admin_cache_time_option['wpc_advanced_cache_time'];
		}
	}
};

//API Key
function get_admin_api_key() {
	$wpc_admin_api_key_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_api_key_option ) ) {
		foreach ($wpc_admin_api_key_option as $key => $wpc_admin_api_key_value)
			$options[$key] = $wpc_admin_api_key_value;
		if (isset($wpc_admin_api_key_option['wpc_advanced_api_key'])) {
			return $wpc_admin_api_key_option['wpc_advanced_api_key'];
		}
	}
};
			
function wpc_get_api_key() {
	if (get_admin_api_key() != '') {
		return get_admin_api_key();
	}
	else {
		return '46c433f6ba7dd4d29d5718dac3d7f035';
	}
}

//Bypass Background Color
function get_admin_color_background() {
	$wpc_admin_bg_color_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_bg_color_option ) ) {
		foreach ($wpc_admin_bg_color_option as $key => $wpc_admin_bg_color_value)
			$options[$key] = $wpc_admin_bg_color_value;
		if (isset($wpc_admin_bg_color_option['wpc_advanced_bg_color'])) {
			return $wpc_admin_bg_color_option['wpc_advanced_bg_color'];
		}
	}
};

function get_color_background($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_bg_color_value = get_post_meta($id,'_wpcloudy_meta_bg_color',true);
		return $wpc_bg_color_value;
};

function get_bypass_color_background($attr,$content) {
	if (get_admin_color_background()) {
		return get_admin_color_background(); 
	}
	else {
		return get_color_background($attr,$content);
	}
}

//Bypass Text Color
function get_admin_color_text() {
	$wpc_admin_text_color_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_text_color_option ) ) {
		foreach ($wpc_admin_text_color_option as $key => $wpc_admin_text_color_value)
			$options[$key] = $wpc_admin_text_color_value;
		if (isset($wpc_admin_text_color_option['wpc_advanced_text_color'])) {
			return $wpc_admin_text_color_option['wpc_advanced_text_color'];
		}
	}
};

function get_color_text($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_text_color_value = get_post_meta($id,'_wpcloudy_meta_txt_color',true);
		return $wpc_text_color_value;
};

function get_bypass_color_text($attr,$content) {
	if (get_admin_color_text()) {
		return get_admin_color_text(); 
	}
	else {
		return get_color_text($attr,$content);
	}
}

//Bypass Border Color
function get_admin_color_border() {
	$wpc_admin_color_border_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_color_border_option ) ) {
		foreach ($wpc_admin_color_border_option as $key => $wpc_admin_color_border_value)
			$options[$key] = $wpc_admin_color_border_value;
		if (isset($wpc_admin_color_border_option['wpc_advanced_border_color'])) {
			return $wpc_admin_color_border_option['wpc_advanced_border_color'];
		}
	}
};

function get_color_border($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_color_border_value = get_post_meta($id,'_wpcloudy_meta_border_color',true);
		return $wpc_color_border_value;
};

function get_bypass_color_border($attr,$content) {
	if (get_admin_color_border()) {
		return get_admin_color_border(); 
	}
	else {
		return get_color_border($attr,$content);
	}
}

//Bypass Current weather
function get_admin_display_current_weather() {
	$wpc_admin_display_current_weather_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_current_weather_option ) ) {
		foreach ($wpc_admin_display_current_weather_option as $key => $wpc_admin_display_current_weather_value)
			$options[$key] = $wpc_admin_display_current_weather_value;
		if (isset($wpc_admin_display_current_weather_option['wpc_display_current_weather'])) {
			return $wpc_admin_display_current_weather_option['wpc_display_current_weather'];
		}
	}
};

function get_display_current_weather($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_current_weather_value = get_post_meta($id,'_wpcloudy_current_weather',true);
		return $wpc_display_current_weather_value;
};

function get_bypass_display_current_weather($attr,$content) {
	if (get_admin_display_current_weather()) {
		return get_admin_display_current_weather(); 
	}
	else {
		return get_display_current_weather($attr,$content);
	}
}

//Bypass Short condition
function get_admin_display_weather() {
	$wpc_admin_display_weather_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_weather_option ) ) {
		foreach ($wpc_admin_display_weather_option as $key => $wpc_admin_display_weather_value)
			$options[$key] = $wpc_admin_display_weather_value;
		if (isset($wpc_admin_display_weather_option['wpc_display_weather'])) {
			return $wpc_admin_display_weather_option['wpc_display_weather'];
		}
	}
};

function get_display_weather($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_weather_value = get_post_meta($id,'_wpcloudy_weather',true);
		return $wpc_display_weather_value;
};

function get_bypass_display_weather($attr,$content) {
	if (get_admin_display_weather()) {
		return get_admin_display_weather(); 
	}
	else {
		return get_display_weather($attr,$content);
	}
}

//Bypass Date Format
function get_admin_display_date_temp() {
	$wpc_admin_display_date_temp_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_date_temp_option ) ) {
		foreach ($wpc_admin_display_date_temp_option as $key => $wpc_admin_display_date_temp_value)
			$options[$key] = $wpc_admin_display_date_temp_value;
		if (isset($wpc_admin_display_date_temp_option['wpc_display_date_temp'])) {
			return $wpc_admin_display_date_temp_option['wpc_display_date_temp'];
		}
	}
};

function get_display_date_temp($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_date_temp_value = get_post_meta($id,'_wpcloudy_date_temp',true);
		return $wpc_display_date_temp_value;
};

function get_bypass_display_date_temp($attr,$content) {
	if (get_admin_display_date_temp()) {
		return get_admin_display_date_temp(); 
	}
	else {
		return get_display_date_temp($attr,$content);
	}
}

//Bypass Sunrise - sunset
function get_admin_display_sunrise_sunset() {
	$wpc_admin_display_sunrise_sunset_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_sunrise_sunset_option ) ) {
		foreach ($wpc_admin_display_sunrise_sunset_option as $key => $wpc_admin_display_sunrise_sunset_value)
			$options[$key] = $wpc_admin_display_sunrise_sunset_value;
		if (isset($wpc_admin_display_sunrise_sunset_option['wpc_display_sunrise_sunset'])) {
			return $wpc_admin_display_sunrise_sunset_option['wpc_display_sunrise_sunset'];
		}
	}
};

function get_display_sunrise_sunset($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_sunrise_sunset_value = get_post_meta($id,'_wpcloudy_sunrise_sunset',true);
		return $wpc_display_sunrise_sunset_value;
};

function get_bypass_display_sunrise_sunset($attr,$content) {
	if (get_admin_display_sunrise_sunset()) {
		return get_admin_display_sunrise_sunset(); 
	}
	else {
		return get_display_sunrise_sunset($attr,$content);
	}
}

//Bypass display temperatures unit
function get_admin_display_temp_unit() {
	$wpc_admin_display_temp_unit_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_temp_unit_option ) ) {
		foreach ($wpc_admin_display_temp_unit_option as $key => $wpc_admin_display_temp_unit_value)
			$options[$key] = $wpc_admin_display_temp_unit_value;
		if (isset($wpc_admin_display_temp_unit_option['wpc_display_temp_unit'])) {
			return $wpc_admin_display_temp_unit_option['wpc_display_temp_unit'];
		}
	}
};

function get_display_temp_unit($attr,$content) {
	extract(shortcode_atts(array( 'id' => ''), $attr));
	$wpc_display_temp_unit_value = get_post_meta($id,'_wpcloudy_display_temp_unit',true);
	return $wpc_display_temp_unit_value;
};

function get_bypass_display_temp_unit($attr,$content) {
	if (get_admin_display_temp_unit()) {
		return get_admin_display_temp_unit(); 
	}
	else {
		return get_display_temp_unit($attr,$content);
	}
}

//Bypass Wind
function get_admin_display_wind() {
	$wpc_admin_display_wind_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_wind_option ) ) {
		foreach ($wpc_admin_display_wind_option as $key => $wpc_admin_display_wind_value)
			$options[$key] = $wpc_admin_display_wind_value;
		if (isset($wpc_admin_display_wind_option['wpc_display_wind'])) {
			return $wpc_admin_display_wind_option['wpc_display_wind'];
		}
	}
};

function get_display_wind($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_wind_value = get_post_meta($id,'_wpcloudy_wind',true);
		return $wpc_display_wind_value;
};

function get_bypass_display_wind($attr,$content) {
	if (get_admin_display_wind()) {
		return get_admin_display_wind(); 
	}
	else {
		return get_display_wind($attr,$content);
	}
}

//Bypass Humidity
function get_admin_display_humidity() {
	$wpc_admin_display_humidity_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_humidity_option ) ) {
		foreach ($wpc_admin_display_humidity_option as $key => $wpc_admin_display_humidity_value)
			$options[$key] = $wpc_admin_display_humidity_value;
		if (isset($wpc_admin_display_humidity_option['wpc_display_current_weather'])) {
			return $wpc_admin_display_humidity_option['wpc_display_current_weather'];
		}
	}
};

function get_display_humidity($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_humidity_value = get_post_meta($id,'_wpcloudy_humidity',true);
		return $wpc_display_humidity_value;
};

function get_bypass_display_humidity($attr,$content) {
	if (get_admin_display_humidity()) {
		return get_admin_display_humidity(); 
	}
	else {
		return get_display_humidity($attr,$content);
	}
}

//Bypass Pressure
function get_admin_display_pressure() {
	$wpc_admin_display_pressure_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_pressure_option ) ) {
		foreach ($wpc_admin_display_pressure_option as $key => $wpc_admin_display_pressure_value)
			$options[$key] = $wpc_admin_display_pressure_value;
		if (isset($wpc_admin_display_pressure_option['wpc_display_pressure'])) {
			return $wpc_admin_display_pressure_option['wpc_display_pressure'];
		}
	}
};

function get_display_pressure($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_pressure_value = get_post_meta($id,'_wpcloudy_pressure',true);
		return $wpc_display_pressure_value;
};

function get_bypass_display_pressure($attr,$content) {
	if (get_admin_display_pressure()) {
		return get_admin_display_pressure(); 
	}
	else {
		return get_display_pressure($attr,$content);
	}
}

//Bypass Cloudiness
function get_admin_display_cloudiness() {
	$wpc_admin_display_cloudiness_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_cloudiness_option ) ) {
		foreach ($wpc_admin_display_cloudiness_option as $key => $wpc_admin_display_cloudiness_value)
			$options[$key] = $wpc_admin_display_cloudiness_value;
		if (isset($wpc_admin_display_cloudiness_option['wpc_display_cloudiness'])) {
			return $wpc_admin_display_cloudiness_option['wpc_display_cloudiness'];
		}
	}
};

function get_display_cloudiness($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_cloudiness_value = get_post_meta($id,'_wpcloudy_cloudiness',true);
		return $wpc_display_cloudiness_value;
};

function get_bypass_display_cloudiness($attr,$content) {
	if (get_admin_display_cloudiness()) {
		return get_admin_display_cloudiness(); 
	}
	else {
		return get_display_cloudiness($attr,$content);
	}
}

//Bypass Precipitation
function get_admin_display_precipitation() {
	$wpc_admin_display_precipitation_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_precipitation_option ) ) {
		foreach ($wpc_admin_display_precipitation_option as $key => $wpc_admin_display_precipitation_value)
			$options[$key] = $wpc_admin_display_precipitation_value;
		if (isset($wpc_admin_display_precipitation_option['wpc_display_precipitation'])) {
			return $wpc_admin_display_precipitation_option['wpc_display_precipitation'];
		}
	}
};

function get_display_precipitation($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_precipitation_value = get_post_meta($id,'_wpcloudy_precipitation',true);
		return $wpc_display_precipitation_value;
};

function get_bypass_display_precipitation($attr,$content) {
	if (get_admin_display_precipitation()) {
		return get_admin_display_precipitation(); 
	}
	else {
		return get_display_precipitation($attr,$content);
	}
}

//Bypass Hour Forecast
function get_admin_display_hour_forecast() {
	$wpc_admin_display_hour_forecast_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_hour_forecast_option ) ) {
		foreach ($wpc_admin_display_hour_forecast_option as $key => $wpc_admin_display_hour_forecast_value)
			$options[$key] = $wpc_admin_display_hour_forecast_value;
		if (isset($wpc_admin_display_hour_forecast_option['wpc_display_hour_forecast'])) {
			return $wpc_admin_display_hour_forecast_option['wpc_display_hour_forecast'];
		}
	}
};

function get_display_hour_forecast($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_hour_forecast_value = get_post_meta($id,'_wpcloudy_hour_forecast',true);
		return $wpc_display_hour_forecast_value;
};

function get_bypass_display_hour_forecast($attr,$content) {
	if (get_admin_display_hour_forecast()) {
		return get_admin_display_hour_forecast(); 
	}
	else {
		return get_display_hour_forecast($attr,$content);
	}
}

//Bypass Range Hours Forecast
function get_admin_bypass_hour_forecast_nd() {
	$wpc_admin_bypass_hour_forecast_nd_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_hour_forecast_nd_option ) ) {
		foreach ($wpc_admin_bypass_hour_forecast_nd_option as $key => $wpc_admin_bypass_hour_forecast_nd_value)
			$options[$key] = $wpc_admin_bypass_hour_forecast_nd_value;
		if (isset($wpc_admin_bypass_hour_forecast_nd_option['wpc_display_bypass_hour_forecast_nd'])) {
			return $wpc_admin_bypass_hour_forecast_nd_option['wpc_display_bypass_hour_forecast_nd'];
		}
	}
};
function get_admin_display_hour_forecast_nd() {
	$wpc_admin_display_hour_forecast_nd_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_hour_forecast_nd_option ) ) {
		foreach ($wpc_admin_display_hour_forecast_nd_option as $key => $wpc_admin_display_hour_forecast_nd_value)
			$options[$key] = $wpc_admin_display_hour_forecast_nd_value;
		if (isset($wpc_admin_display_hour_forecast_nd_option['wpc_display_hour_forecast_nd'])) {
			return $wpc_admin_display_hour_forecast_nd_option['wpc_display_hour_forecast_nd'];
		}
	}
};

function get_display_hour_forecast_nd($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_hour_forecast_nd_value = get_post_meta($id,'_wpcloudy_hour_forecast_nd',true);
		return $wpc_display_hour_forecast_nd_value;
};

function get_bypass_display_hour_forecast_nd($attr,$content) {
	if (get_admin_display_hour_forecast_nd() && (get_admin_bypass_hour_forecast_nd())) {
		return get_admin_display_hour_forecast_nd(); 
	}
	else {
		return get_display_hour_forecast_nd($attr,$content);
	}
}

//Bypass Today Date + Min-Max Temp
function get_admin_bypass_temp() {
	$wpc_display_temperature_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_display_temperature_option ) ) {
		foreach ($wpc_display_temperature_option as $key => $wpc_display_temperature_value)
			$options[$key] = $wpc_display_temperature_value;
		if (isset($wpc_display_temperature_option['wpc_display_bypass_temperature'])) {
			return $wpc_display_temperature_option['wpc_display_bypass_temperature'];
		}
	}
};

function get_admin_display_temp() {
	$wpc_admin_display_temperature_min_max_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_temperature_min_max_option ) ) {
		foreach ($wpc_admin_display_temperature_min_max_option as $key => $wpc_admin_display_temperature_min_max_value)
			$options[$key] = $wpc_admin_display_temperature_min_max_value;
		if (isset($wpc_admin_display_temperature_min_max_option['wpc_display_temperature_min_max'])) {
			return $wpc_admin_display_temperature_min_max_option['wpc_display_temperature_min_max'];
		}
	}
};

function get_display_temp($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_temperature_min_max_value = get_post_meta($id,'_wpcloudy_temperature_min_max',true);
		return $wpc_display_temperature_min_max_value;
};

function get_bypass_temp($attr,$content) {
	if (get_admin_display_temp() && (get_admin_bypass_temp())) {
		return get_admin_display_temp(); 
	}
	else {
		return get_display_temp($attr,$content);
	}
};

//Bypass Length Days Names

function get_admin_bypass_length_days_names() {
	$wpc_display_bypass_short_days_names_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_display_bypass_short_days_names_option ) ) {
		foreach ($wpc_display_bypass_short_days_names_option as $key => $wpc_display_bypass_short_days_names_value)
			$options[$key] = $wpc_display_bypass_short_days_names_value;
		if (isset($wpc_display_bypass_short_days_names_option['wpc_display_bypass_short_days_names'])) {
			return $wpc_display_bypass_short_days_names_option['wpc_display_bypass_short_days_names'];
		}
	}
};

function get_admin_display_length_days_names() {
	$wpc_display_short_days_names_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_display_short_days_names_option ) ) {
		foreach ($wpc_display_short_days_names_option as $key => $wpc_display_short_days_names_value)
			$options[$key] = $wpc_display_short_days_names_value;
		if (isset($wpc_display_short_days_names_option['wpc_display_short_days_names'])) {
			return $wpc_display_short_days_names_option['wpc_display_short_days_names'];
		}
	}
};

function get_display_length_days_names($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpcloudy_short_days_names_value = get_post_meta($id,'_wpcloudy_short_days_names',true);
		return $wpcloudy_short_days_names_value;
};

function get_bypass_length_days_names($attr,$content) {
	if (get_admin_bypass_length_days_names() && get_admin_display_length_days_names()) {
		return get_admin_display_length_days_names(); 
	}
	else {
		return get_display_length_days_names($attr,$content);
	}
};

//Bypass Forecast

function get_admin_display_forecast() {
	$wpc_admin_display_forecast_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_forecast_option ) ) {
		foreach ($wpc_admin_display_forecast_option as $key => $wpc_admin_display_forecast_value)
			$options[$key] = $wpc_admin_display_forecast_value;
			if (isset($wpc_admin_display_forecast_option['wpc_display_forecast'])) {
			return $wpc_admin_display_forecast_option['wpc_display_forecast'];
		}
	}
};

function get_display_forecast($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_forecast_value = get_post_meta($id,'_wpcloudy_forecast',true);
		return $wpc_display_forecast_value;
};

function get_bypass_display_forecast($attr,$content) {
	if (get_admin_display_forecast()) {
		return get_admin_display_forecast(); 
	}
	else {
		return get_display_forecast($attr,$content);
	}
};

//Bypass Weather Size

function get_admin_bypass_size() {
	$wpc_admin_bypass_size_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_size_option ) ) {
		foreach ($wpc_admin_bypass_size_option as $key => $wpc_admin_bypass_size_value)
			$options[$key] = $wpc_admin_bypass_size_value;
		if (isset($wpc_admin_bypass_size_option['wpc_advanced_bypass_size'])) {
			return $wpc_admin_bypass_size_option['wpc_advanced_bypass_size'];
		}
	}
};

function get_admin_size() {
	$wpc_admin_size_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_size_option ) ) {
		foreach ($wpc_admin_size_option as $key => $wpc_admin_size_value)
			$options[$key] = $wpc_admin_size_value;
		if (isset($wpc_admin_size_option['wpc_advanced_size'])) {
			return $wpc_admin_size_option['wpc_advanced_size'];
		}
	}
};

function get_size($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_size_value = get_post_meta($id,'_wpcloudy_size',true);
		return $wpc_size_value;
};

function get_bypass_size($attr,$content) {
	if (get_admin_unit() && (get_admin_bypass_size())) {
		return get_admin_size(); 
	}
	else {
		return get_size($attr,$content);
	}
};

//Bypass Map
function get_admin_bypass_map() {
	$wpc_admin_bypass_map_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_map_option ) ) {
		foreach ($wpc_admin_bypass_map_option as $key => $wpc_admin_bypass_map_value)
			$options[$key] = $wpc_admin_bypass_map_value;
		if (isset($wpc_admin_bypass_map_option['wpc_map_display'])) {
			return $wpc_admin_bypass_map_option['wpc_map_display'];
		}
	}
};

function get_map($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_value = get_post_meta($id,'_wpcloudy_map',true);
		
		if ($wpc_map_value == 'yes') {
			return $wpc_map_value;
		}
};

function get_bypass_map($attr,$content) {
	if (get_admin_bypass_map()) {
		return get_admin_bypass_map(); 
	}
	else {
		return get_map($attr,$content);
	}
};

//Bypass Map Height
function get_admin_bypass_map_height() {
	$wpc_admin_bypass_map_height_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_map_height_option ) ) {
		foreach ($wpc_admin_bypass_map_height_option as $key => $wpc_admin_bypass_map_height_value)
			$options[$key] = $wpc_admin_bypass_map_height_value;
		if (isset($wpc_admin_bypass_map_height_option['wpc_map_height'])) {
			return $wpc_admin_bypass_map_height_option['wpc_map_height'];
		}
	}
};

function get_map_height($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_height_value = get_post_meta($id,'_wpcloudy_map_height',true);
		return $wpc_map_height_value;
};

function get_bypass_map_height($attr,$content) {
	if (get_admin_bypass_map_height()) {
		return get_admin_bypass_map_height(); 
	}
	else {
		return get_map_height($attr,$content);
	}
};

//Bypass Layers opacity
function get_admin_bypass_map_opacity() {
	$wpc_admin_bypass_map_opacity_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_map_opacity_option ) ) {
		foreach ($wpc_admin_bypass_map_opacity_option as $key => $wpc_admin_bypass_map_opacity_value)
			$options[$key] = $wpc_admin_bypass_map_opacity_value;
		if (isset($wpc_admin_bypass_map_opacity_option['wpc_map_bypass_opacity'])) {	
			return $wpc_admin_bypass_map_opacity_option['wpc_map_bypass_opacity'];
		}
	}
};

function get_admin_map_opacity() {
	$wpc_admin_map_opacity_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_opacity_option ) ) {
		foreach ($wpc_admin_map_opacity_option as $key => $wpc_admin_map_opacity_value)
			$options[$key] = $wpc_admin_map_opacity_value;
		if (isset($wpc_admin_map_opacity_option['wpc_map_opacity'])) {	
			return $wpc_admin_map_opacity_option['wpc_map_opacity'];
		}
	}
};

function get_map_opacity($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_opacity_value = get_post_meta($id,'_wpcloudy_map_opacity',true);
		return $wpc_map_opacity_value;
};

function get_bypass_map_opacity($attr,$content) {
	if (get_admin_map_opacity() && (get_admin_bypass_map_opacity())) {
		return get_admin_map_opacity(); 
	}
	else {
		return get_map_opacity($attr,$content);
	}
};

//Bypass Zoom Map
function get_admin_bypass_map_zoom() {
	$wpc_admin_bypass_map_zoom_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_map_zoom_option ) ) {
		foreach ($wpc_admin_bypass_map_zoom_option as $key => $wpc_admin_bypass_map_zoom_value)
			$options[$key] = $wpc_admin_bypass_map_zoom_value;
		if (isset($wpc_admin_bypass_map_zoom_option['wpc_map_bypass_zoom'])) {
			return $wpc_admin_bypass_map_zoom_option['wpc_map_bypass_zoom'];
		}
	}
};

function get_admin_map_zoom() {
	$wpc_admin_map_zoom_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_zoom_option ) ) {
		foreach ($wpc_admin_map_zoom_option as $key => $wpc_admin_map_zoom_value)
			$options[$key] = $wpc_admin_map_zoom_value;
		if (isset($wpc_admin_map_zoom_option['wpc_map_zoom'])) {
			return $wpc_admin_map_zoom_option['wpc_map_zoom'];
		}	
	}
};

function get_map_zoom($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_zoom_value = get_post_meta($id,'_wpcloudy_map_zoom',true);
		return $wpc_map_zoom_value;
};

function get_bypass_map_zoom($attr,$content) {
	if (get_admin_map_zoom() && (get_admin_bypass_map_zoom())) {
		return get_admin_map_zoom(); 
	}
	else {
		return get_map_zoom($attr,$content);
	}
};

//Bypass Layers stations
function get_admin_map_layers_stations() {
	$wpc_admin_map_layers_stations_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_stations_option ) ) {
		foreach ($wpc_admin_map_layers_stations_option as $key => $wpc_admin_map_layers_stations_value)
			$options[$key] = $wpc_admin_map_layers_stations_value;
		if (isset($wpc_admin_map_layers_stations_option['wpc_map_layers_stations'])) {
			return $wpc_admin_map_layers_stations_option['wpc_map_layers_stations'];
		}
	}
};

function get_map_layers_stations($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_stations_value = get_post_meta($id,'_wpcloudy_map_stations',true);
		return $wpc_map_layers_stations_value;
};

function get_bypass_map_layers_stations($attr,$content) {
	if (get_admin_map_layers_stations()) {
		return get_admin_map_layers_stations(); 
	}
	else {
		return get_map_layers_stations($attr,$content);
	}
};

//Bypass Layers clouds
function get_admin_map_layers_clouds() {
	$wpc_admin_map_layers_clouds_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_clouds_option ) ) {
		foreach ($wpc_admin_map_layers_clouds_option as $key => $wpc_admin_map_layers_clouds_value)
			$options[$key] = $wpc_admin_map_layers_clouds_value;
		if (isset($wpc_admin_map_layers_clouds_option['wpc_map_layers_clouds'])) {
			return $wpc_admin_map_layers_clouds_option['wpc_map_layers_clouds'];
		}
	}
};

function get_map_layers_clouds($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_clouds_value = get_post_meta($id,'_wpcloudy_map_clouds',true);
		return $wpc_map_layers_clouds_value;
};

function get_bypass_map_layers_clouds($attr,$content) {
	if (get_admin_map_layers_clouds()) {
		return get_admin_map_layers_clouds(); 
	}
	else {
		return get_map_layers_clouds($attr,$content);
	}
};

//Bypass Layers precipitations
function get_admin_map_layers_precipitation() {
	$wpc_admin_map_layers_precipitation_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_precipitation_option ) ) {
		foreach ($wpc_admin_map_layers_precipitation_option as $key => $wpc_admin_map_layers_precipitation_value)
			$options[$key] = $wpc_admin_map_layers_precipitation_value;
		if (isset($wpc_admin_map_layers_precipitation_option['wpc_map_layers_precipitation'])) {
			return $wpc_admin_map_layers_precipitation_option['wpc_map_layers_precipitation'];
		}
	}
};

function get_map_layers_precipitation($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_precipitation_value = get_post_meta($id,'_wpcloudy_map_precipitation',true);
		return $wpc_map_layers_precipitation_value;
};

function get_bypass_map_layers_precipitation($attr,$content) {
	if (get_admin_map_layers_precipitation()) {
		return get_admin_map_layers_precipitation(); 
	}
	else {
		return get_map_layers_precipitation($attr,$content);
	}
};

//Bypass Layers snow
function get_admin_map_layers_snow() {
	$wpc_admin_map_layers_snow_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_snow_option ) ) {
		foreach ($wpc_admin_map_layers_snow_option as $key => $wpc_admin_map_layers_snow_value)
			$options[$key] = $wpc_admin_map_layers_snow_value;
		if (isset($wpc_admin_map_layers_snow_option['wpc_map_layers_snow'])) {
			return $wpc_admin_map_layers_snow_option['wpc_map_layers_snow'];
		}
	}
};

function get_map_layers_snow($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_snow_value = get_post_meta($id,'_wpcloudy_map_snow',true);
		return $wpc_map_layers_snow_value;
};

function get_bypass_map_layers_snow($attr,$content) {
	if (get_admin_map_layers_snow()) {
		return get_admin_map_layers_snow(); 
	}
	else {
		return get_map_layers_snow($attr,$content);
	}
};

//Bypass Layers wind
function get_admin_map_layers_wind() {
	$wpc_admin_map_layers_wind_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_wind_option ) ) {
		foreach ($wpc_admin_map_layers_wind_option as $key => $wpc_admin_map_layers_wind_value)
			$options[$key] = $wpc_admin_map_layers_wind_value;
		if (isset($wpc_admin_map_layers_wind_option['wpc_map_layers_wind'])) {
			return $wpc_admin_map_layers_wind_option['wpc_map_layers_wind'];
		}
	}
};

function get_map_layers_wind($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_wind_value = get_post_meta($id,'_wpcloudy_map_wind',true);
		return $wpc_map_layers_wind_value;
};

function get_bypass_map_layers_wind($attr,$content) {
	if (get_admin_map_layers_wind()) {
		return get_admin_map_layers_wind(); 
	}
	else {
		return get_map_layers_wind($attr,$content);
	}
};

//Bypass Layers temperature
function get_admin_map_layers_temperature() {
	$wpc_admin_map_layers_temperature_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_temperature_option ) ) {
		foreach ($wpc_admin_map_layers_temperature_option as $key => $wpc_admin_map_layers_temperature_value)
			$options[$key] = $wpc_admin_map_layers_temperature_value;
		if (isset($wpc_admin_map_layers_temperature_option['wpc_map_layers_temperature'])) {
			return $wpc_admin_map_layers_temperature_option['wpc_map_layers_temperature'];
		}
	}
};

function get_map_layers_temperature($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_temperature_value = get_post_meta($id,'_wpcloudy_map_temperature',true);
		return $wpc_map_layers_temperature_value;
};

function get_bypass_map_layers_temperature($attr,$content) {
	if (get_admin_map_layers_temperature()) {
		return get_admin_map_layers_temperature(); 
	}
	else {
		return get_map_layers_temperature($attr,$content);
	}
};

//Bypass Layers pressure
function get_admin_map_layers_pressure() {
	$wpc_admin_map_layers_pressure_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_layers_pressure_option ) ) {
		foreach ($wpc_admin_map_layers_pressure_option as $key => $wpc_admin_map_layers_pressure_value)
			$options[$key] = $wpc_admin_map_layers_pressure_value;
		if (isset($wpc_admin_map_layers_pressure_option['wpc_map_layers_pressure'])) {
			return $wpc_admin_map_layers_pressure_option['wpc_map_layers_pressure'];
		}
	}
};

function get_map_layers_pressure($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_layers_pressure_value = get_post_meta($id,'_wpcloudy_map_pressure',true);
		return $wpc_map_layers_pressure_value;
};

function get_bypass_map_layers_pressure($attr,$content) {
	if (get_admin_map_layers_pressure()) {
		return get_admin_map_layers_pressure(); 
	}
	else {
		return get_map_layers_pressure($attr,$content);
	}
};

///////////////////////////////////////////////////////////////////////////////////////////////////
//Function CSS/Display/Misc
///////////////////////////////////////////////////////////////////////////////////////////////////

function wpc_css_background($wpcloudy_meta_bg_color) {
	if( $wpcloudy_meta_bg_color ) {
			return 'background:'. $wpcloudy_meta_bg_color;
	}
};
function wpc_css_text_color($wpcloudy_meta_text_color) {
	if( $wpcloudy_meta_text_color ) {
			return $wpcloudy_meta_text_color;
	}
};
function wpc_css_border($wpcloudy_meta_border_color) {
	if( $wpcloudy_meta_border_color ) {
			return 'border:1px solid '. $wpcloudy_meta_border_color;
	}
};

function wpcloudy_city_name($wpcloudy_city_name, $wpcloudy_city, $location_name, $wpcloudy_select_city_name, $wpcloudy_enable_geolocation) {

	if ($wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-manualGeolocation']=='1') {	
		return $wpcloudy_select_city_name;
	}
	if ($wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-detectGeolocation']=='1') {
		return $location_name;
	}
	if( $wpcloudy_city_name && $wpcloudy_enable_geolocation == '') {
		return $wpcloudy_city_name;
	}
	if( $wpcloudy_city && $wpcloudy_enable_geolocation == '') {
		return $wpcloudy_city;
	}
};

function display_today_sunrise_sunset($wpcloudy_sunrise_sunset, $sun_rise, $sun_set) {
	if( $wpcloudy_sunrise_sunset ) {
		return '<div class="sun_hours">
					<span class="sunrise">'. $sun_rise .'</span> - <span class="sunset">'. $sun_set .'</span>
				</div>';
	}
}

function wpc_css_webfont($attr,$content) {
	if(function_exists('wpcloudy_google_fonts')) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_css_webfont_value = get_post_meta($id,'_wpcloudy_fonts',true);
			/***Open Sans***/
			if ($wpc_css_webfont_value == 'Open Sans' ) {
				wp_enqueue_style('Open Sans');
			}
			/***Ubuntu***/
			if ($wpc_css_webfont_value == 'Ubuntu' ) {
				wp_enqueue_style('Ubuntu');
			}
			/***Lato***/
			if ($wpc_css_webfont_value == 'Lato' ) {
				wp_enqueue_style('Lato');
			}
			/***Asap***/
			if ($wpc_css_webfont_value == 'Asap' ) {
				wp_enqueue_style('Asap');
			}
			/***Oswald***/
			if ($wpc_css_webfont_value == 'Oswald') { 
				wp_enqueue_style('Oswald');
			}
			/***Exo***/
			if ($wpc_css_webfont_value == '\'Exo 2\'' ) {
				wp_enqueue_style('Exo 2');
			}
			/***Roboto***/
			if ($wpc_css_webfont_value == 'Roboto' ) {
				wp_enqueue_style('Roboto');
			}
			/***Source Sans Pro***/
			if ($wpc_css_webfont_value == 'Source Sans Pro' ) {
				wp_enqueue_style('Source Sans Pro');
			}
			/***Droid Serif***/
			if ($wpc_css_webfont_value == 'Droid Serif' ) {
				wp_enqueue_style('Droid Serif');
			}
			/***Arvo***/
			if ($wpc_css_webfont_value == 'Arvo') { 
				wp_enqueue_style('Arvo');
			}
			/***Bitter***/
			if ($wpc_css_webfont_value == 'Bitter' ) {
				wp_enqueue_style('Bitter');
			}
			/***Francois One***/
			if ($wpc_css_webfont_value == 'Francois One' ) {
				wp_enqueue_style('Francois One');
			}
			/***Nunito***/
			if ($wpc_css_webfont_value == 'Nunito' ) {
				wp_enqueue_style('Nunito');
			}
			/***Josefin***/
			if ($wpc_css_webfont_value == 'Josefin Sans' ) {
				wp_enqueue_style('Josefin Sans');
			}
			/***Signika***/
			if ($wpc_css_webfont_value == 'Signika') { 
				wp_enqueue_style('Signika');
			}
			/***Merriweather Sans***/
			if ($wpc_css_webfont_value == 'Merriweather Sans') { 
				wp_enqueue_style('Merriweather Sans');
			}
			/***Tangerine***/
			if ($wpc_css_webfont_value == 'Tangerine') { 
				wp_enqueue_style('Tangerine');
			}
			/***Pacifico***/
			if ($wpc_css_webfont_value == 'Pacifico') { 
				wp_enqueue_style('Pacifico');
			}
			/***Inconsolata***/
			if ($wpc_css_webfont_value == 'Inconsolata') { 
				wp_enqueue_style('Inconsolata');
			}
		return $wpc_css_webfont_value;
	}
};
///////////////////////////////////////////////////////////////////////////////////////////////////
//Add shortcode Weather
///////////////////////////////////////////////////////////////////////////////////////////////////

add_shortcode("wpc-weather", "wpcloudy_display_weather");

function wpcloudy_display_weather($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
			$wpc_id 								= $id;
			$wpcloudy_city 							= get_post_meta($id,'_wpcloudy_city',true);
			$wpcloudy_city_name 					= get_post_meta($id,'_wpcloudy_city_name',true);
			$wpcloudy_country_code					= get_post_meta($id,'_wpcloudy_country_code',true);
			$wpcloudy_unit 							= get_bypass_unit($attr,$content);
			$wpcloudy_lang							= get_bypass_lang($attr,$content);
			$wpcloudy_map_height					= get_bypass_map_height($attr,$content);
			$wpcloudy_map_opacity					= get_bypass_map_opacity($attr,$content);
			$wpcloudy_map_zoom						= get_bypass_map_zoom($attr,$content);
			$wpcloudy_map_stations					= get_bypass_map_layers_stations($attr,$content);
			$wpcloudy_map_clouds					= get_bypass_map_layers_clouds($attr,$content);
			$wpcloudy_map_precipitation				= get_bypass_map_layers_precipitation($attr,$content);
			$wpcloudy_map_snow						= get_bypass_map_layers_snow($attr,$content);
			$wpcloudy_map_wind						= get_bypass_map_layers_wind($attr,$content);
			$wpcloudy_map_temperature				= get_bypass_map_layers_temperature($attr,$content);
			$wpcloudy_map_pressure					= get_bypass_map_layers_pressure($attr,$content);
			$wpcloudy_meta_border_color				= get_bypass_color_border($attr,$content);
			$wpcloudy_meta_bg_color					= get_bypass_color_background($attr,$content);
			$wpcloudy_meta_text_color				= get_bypass_color_text($attr,$content);
			$wpcloudy_date_format					= get_bypass_date($attr,$content);
			$wpcloudy_sunrise_sunset				= get_bypass_display_sunrise_sunset($attr,$content);
			$wpcloudy_display_temp_unit				= get_bypass_display_temp_unit($attr,$content);
			$wpcloudy_display_length_days_names		= get_bypass_length_days_names($attr,$content);
			$wpcloudy_enable_geolocation 			= get_post_meta($id,'_wpcloudy_enable_geolocation',true);
			$wpc_advanced_set_cache_time			= get_admin_cache_time();
			$wpc_advanced_set_disable_cache 		= get_admin_disable_cache();
			$wpc_advanced_api_key					= wpc_get_api_key();
			
			//variable declarations
			$wpcloudy_select_city_name				= null;
			$display_today_min_max_day				= null;
			$display_today_sun 						= null;
			$display_today_min_max_start 	 		= null;
			$display_today_time_temp_min 			= null;
			$display_today_time_temp_max 			= null;
			$display_today_min_max_end 				= null;
			$display_today_ave_start 				= null;
			$display_today_ave_day 					= null;
			$display_today_ave_sun 					= null;
			$display_today_ave_time_ave 			= null;
			$display_today_ave_end 					= null;
			$wpc_html_now_start 					= null;
			$wpc_html_now_location_name 			= null;
			$wpc_html_display_now_time_symbol 		= null;
			$wpc_html_display_now_time_temperature 	= null;
			$wpc_html_now_end 						= null;
			$wpc_html_custom_css 					= null;
			$wpc_html_css3_anims 					= null;
			$wpc_html_temp_unit_metric 				= null;
			$wpc_html_container_end 				= null;
			$wpc_html_weather 						= null;
			$wpc_html_today_temp_start 				= null;
			$wpc_html_today_temp_day 				= null;
			$wpc_html_today_time_temp_min 			= null;
			$wpc_html_today_time_temp_max 			= null;
			$wpc_html_today_ave_time_ave			= null;
			$wpc_html_today_sun 					= null;
			$wpc_html_today_temp_end 				= null;
			$wpc_html_infos_start 					= null;
			$wpc_html_infos_wind 					= null;
			$wpc_html_infos_humidity 				= null;
			$wpc_html_infos_pressure 				= null;
			$wpc_html_infos_cloudiness 				= null;
			$wpc_html_infos_precipitation 			= null;
			$wpc_html_infos_end 					= null;
			$wpc_html_hour 							= null;
			$wpc_html_hour_start 					= null;
			$wpc_html_hour_end 						= null;
			$wpc_html_forecast 						= null;
			$wpc_html_map 							= null;
			$wpc_html_temp_unit_imperial 			= null;
			$wpcloudy_select_city_name 				= null;
			$display_today_min_max_day 				= null;
			$display_today_sun 						= null;
			$display_today_time_temp_min 			= null;
			$display_today_time_temp_max 			= null;
			$display_today_min_max_end 				= null;
			$display_today_ave_start 				= null;
			$display_today_ave_day 					= null;
			$display_today_ave_sun 					= null;
			$display_today_ave_time_ave 			= null;
			$display_today_ave_end 					= null;
			$wpc_html_now_start 					= null;
			$wpc_html_now_location_name 			= null;
			$wpc_html_display_now_time_symbol 		= null;
			$wpc_html_display_now_time_temperature 	= null;
			$wpc_html_now_end 						= null;
			$wpc_html_weather 						= null;
			$wpc_html_today_temp_start 				= null;
			$wpc_html_today_temp_day 				= null;
			$wpc_html_today_sun 					= null;
			$wpc_html_today_time_temp_min 			= null;
			$wpc_html_today_time_temp_max 			= null;
			$wpc_html_today_temp_end 				= null;
			$wpc_html_forecast_start 				= null;
			$wpc_html_forecast_end 					= null;
			$wpc_html_css3_anims 					= null;
			$wpc_html_temp_unit_metric 				= null;
			$wpc_html_container_end 				= null;
			$wpc_html_geolocation					= null;
			
			if (isset($_COOKIE['wpc-posLat'])) {
				$wpcloudy_lat 				= $_COOKIE['wpc-posLat'];
			}
			if (isset($_COOKIE['wpc-posLon'])) {
				$wpcloudy_lon 				= $_COOKIE['wpc-posLon'];
			}
			
			if (isset($_COOKIE['wpc-posCityId'])) {
				$wpcloudy_select_city_id 	= $_COOKIE['wpc-posCityId'];
			}
			
			if (isset($_COOKIE['wpc-posCityName'])) {
				$wpcloudy_select_city_name 	= $_COOKIE['wpc-posCityName'];
			}
			
			switch ($wpcloudy_lang) {
				case "fr":
					$wpcloudy_lang_owm 			= 'fr';
					$wpcloudy_lang_host 		= 'fr_FR';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "en":
					$wpcloudy_lang_owm 			= 'en';
					$wpcloudy_lang_host 		= 'en_US';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "ru":
					$wpcloudy_lang_owm 			= 'ru';
					$wpcloudy_lang_host 		= 'ru_RU';
					$wpcloudy_lang_encoding 	= 'ISO-8859-5';
					break;
				case "it":
					$wpcloudy_lang_owm 			= 'it';
					$wpcloudy_lang_host 		= 'it_IT';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "sp":
					$wpcloudy_lang_owm 			= 'sp';
					$wpcloudy_lang_host 		= 'es_ES';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "ua":
					$wpcloudy_lang_owm 			= 'ua';
					$wpcloudy_lang_host 		= 'ru_UA';
					$wpcloudy_lang_encoding 	= 'ISO-8859-5';
					break;
				case "de":
					$wpcloudy_lang_owm 			= 'de';
					$wpcloudy_lang_host 		= 'de_DE';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "pt":
					$wpcloudy_lang_owm 			= 'pt';
					$wpcloudy_lang_host 		= 'pt_PT';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "ro":
					$wpcloudy_lang_owm 			= 'ro';
					$wpcloudy_lang_host 		= 'ro_RO';
					$wpcloudy_lang_encoding 	= 'ISO-8859-2';
					break;
				case "pl":
					$wpcloudy_lang_owm 			= 'pl';
					$wpcloudy_lang_host 		= 'po_PO';
					$wpcloudy_lang_encoding 	= 'ISO-8859-2';
					break;
				case "fi":
					$wpcloudy_lang_owm 			= 'fi';
					$wpcloudy_lang_host 		= 'fi';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "nl":
					$wpcloudy_lang_owm 			= 'nl';
					$wpcloudy_lang_host 		= 'nl_NL';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "bg":
					$wpcloudy_lang_owm 			= 'bg';
					$wpcloudy_lang_host 		= 'bg_BG';
					$wpcloudy_lang_encoding 	= 'ISO-8859-5';
					break;
				case "se":
					$wpcloudy_lang_owm 			= 'se';
					$wpcloudy_lang_host 		= 'sv_SE';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "zh_tw":
					$wpcloudy_lang_owm 			= 'zh_tw';
					$wpcloudy_lang_host 		= 'zh_TW';
					$wpcloudy_lang_encoding 	= 'Big5';
					break;
				case "zh_cn":
					$wpcloudy_lang_owm 			= 'zh_cn';
					$wpcloudy_lang_host 		= 'zh_CN';
					$wpcloudy_lang_encoding 	= 'GB18030';
					break;
				case "tr":
					$wpcloudy_lang_owm 			= 'tr';
					$wpcloudy_lang_host 		= 'tr_TR';
					$wpcloudy_lang_encoding 	= 'ISO-8859-9';
					break;
				case "cz":
					$wpcloudy_lang_owm 			= 'cz';
					$wpcloudy_lang_host 		= 'cs_CZ';
					$wpcloudy_lang_encoding 	= 'ISO-8859-2';
					break;
				case "gl":
					$wpcloudy_lang_owm 			= 'gl';
					$wpcloudy_lang_host 		= 'gl_ES';
					$wpcloudy_lang_encoding 	= 'ISO-8859-1';
					break;
				case "vi":
					$wpcloudy_lang_owm 			= 'vi';
					$wpcloudy_lang_host 		= 'vi';
					$wpcloudy_lang_encoding 	= 'WINDOWS-1258';
					break;
				case "ar":
					$wpcloudy_lang_owm 			= 'ar';
					$wpcloudy_lang_host 		= 'ar';
					$wpcloudy_lang_encoding 	= 'ISO-8859-6';
					break;
				case "mk":
					$wpcloudy_lang_owm 			= 'mk';
					$wpcloudy_lang_host 		= 'mk_MK';
					$wpcloudy_lang_encoding 	= 'ISO-8859-5';
					break;
				case "sk":
					$wpcloudy_lang_owm 			= 'sk';
					$wpcloudy_lang_host 		= 'sk_SK';
					$wpcloudy_lang_encoding 	= 'ISO-8859-2';
					break;
			}
				
			//XML : real time weather
			
			if( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-detectGeolocation']=='1' ) { 
				$myweather_current = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/weather?lat=$wpcloudy_lat&lon=$wpcloudy_lon&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm"));
			}
			
			elseif( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-manualGeolocation']=='1' )  {
				$myweather_current = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/weather?id=$wpcloudy_select_city_id&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm"));
			}
			
			else {
				if ($wpc_advanced_set_disable_cache == '1') {
					$myweather_current = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/weather?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm"));
				}
				else {		
					if ( false === ( $myweather_current = @simplexml_load_string(get_transient( 'myweather_current_'.$wpc_id ) ) ) ) {	
						$myweather_current = wp_remote_fopen("http://api.openweathermap.org/data/2.5/weather?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm");
						set_transient( 'myweather_current_'.$wpc_id, (string)$myweather_current, $wpc_advanced_set_cache_time * MINUTE_IN_SECONDS );
						$myweather_current = @simplexml_load_string(get_transient( 'myweather_current_'.$wpc_id ) );
					}
					else {
						$myweather_current = @simplexml_load_string(get_transient( 'myweather_current_'.$wpc_id ) );
					}
				}
			}
			
			//XML : Hourly weather			
							
			if( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-detectGeolocation']=='1' ) {
				$myweather = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/weather?lat=$wpcloudy_lat&lon=$wpcloudy_lon&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm"));
			}
			elseif( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-manualGeolocation']=='1' )  {
				$myweather = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/weather?id=$wpcloudy_select_city_id&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm"));
			}
			else {
				if ($wpc_advanced_set_disable_cache == '1') {
					$myweather = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/weather?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm"));
				
				}
				else {	
					if ( false === ( $myweather = @simplexml_load_string(get_transient( 'myweather_'.$wpc_id ) ) ) ) {
						$myweather = wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/weather?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm");
						set_transient( 'myweather_'.$wpc_id, (string)$myweather, $wpc_advanced_set_cache_time * MINUTE_IN_SECONDS );
						$myweather = @simplexml_load_string(get_transient( 'myweather_'.$wpc_id ) );
					}
					else {
						$myweather = @simplexml_load_string(get_transient( 'myweather_'.$wpc_id ) );
					}
				}
			}
			
			//XML : 14-days forecast
				
			if( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-detectGeolocation']=='1' ) {
				$myweather_sevendays = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/daily?lat=$wpcloudy_lat&lon=$wpcloudy_lon&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm&cnt=14"));

			}
			elseif( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-manualGeolocation']=='1' )  {
				$myweather_sevendays = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/daily?id=$wpcloudy_select_city_id&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm&cnt=14"));
			}
			else {
				if ($wpc_advanced_set_disable_cache == '1') {
					$myweather_sevendays = @simplexml_load_string(wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/daily?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm&cnt=14"));	
					
				}
				else {		
					if ( false === ( $myweather_sevendays = @simplexml_load_string(get_transient( 'myweather_sevendays_'.$wpc_id ) ) ) ) {	
						$myweather_sevendays = wp_remote_fopen("http://api.openweathermap.org/data/2.5/forecast/daily?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=$wpc_advanced_api_key&lang=$wpcloudy_lang_owm&cnt=14");
						set_transient( 'myweather_sevendays_'.$wpc_id, (string)$myweather_sevendays, $wpc_advanced_set_cache_time * MINUTE_IN_SECONDS );
						$myweather_sevendays = @simplexml_load_string(get_transient( 'myweather_sevendays_'.$wpc_id ) );
					}
					else {
						$myweather_sevendays = @simplexml_load_string(get_transient( 'myweather_sevendays_'.$wpc_id ) );
					}
				}
			}
			
			setlocale(LC_TIME, "$wpcloudy_lang_host");
			
			
			$location_name 			= $myweather_current->city[0]['name'];	
			$location_latitude 		= $myweather_current->city[0]->coord[0]['lat'];
			$location_longitude 	= $myweather_current->city[0]->coord[0]['lon'];
			$time_symbol 			= $myweather_current->weather[0]['value'];
			$time_symbol_number		= $myweather_current->weather[0]['number'];
			$time_wind_direction 	= $myweather_current->wind[0]->direction[0]['code'];
			$time_wind_speed 		= $myweather_current->wind[0]->speed[0]['value'];
			$time_humidity 			= $myweather_current->humidity[0]['value'];
			$time_pressure 			= $myweather_current->pressure[0]['value'];
			$time_cloudiness		= $myweather_current->clouds[0]['value'];
			$time_precipitation		= $myweather_current->precipitation[0]['value'];
			$time_temperature		= (ceil($myweather_current->temperature[0]['value']));
			$time_temperature_min 	= (ceil($myweather_sevendays->forecast[0]->time[0]->temperature[0]['min']));
			$time_temperature_max 	= (ceil($myweather_sevendays->forecast[0]->time[0]->temperature[0]['max']));

			if ($wpcloudy_date_format =='12') {
				$wpcloudy_date_php_sun 		= 'h:i A';
				$wpcloudy_date_php_hours = 'h A';
			}
			
			if ($wpcloudy_date_format =='24') {	
				$wpcloudy_date_php_sun 		= 'H:i';
				$wpcloudy_date_php_hours 	= 'H';		
			}
			
			$utc_time_wp 			= get_option('gmt_offset') * 60;			
			 
			$sun_rise				= (string)date("$wpcloudy_date_php_sun", strtotime($myweather_current->city[0]->sun[0]['rise'])+60*$utc_time_wp);
			$sun_set				= (string)date("$wpcloudy_date_php_sun", strtotime($myweather_current->city[0]->sun[0]['set'])+60*$utc_time_wp);
									
			$today_day_feed			= strftime("%A", strtotime($myweather_current->lastupdate[0]['value']));
			$today_day				= iconv("$wpcloudy_lang_encoding", 'UTF-8', $today_day_feed);
			
			
			$hour_temp_0			= (ceil($myweather->forecast[0]->time[0]->temperature[0]['value']));
			$hour_symbol_0			= $myweather->forecast[0]->time[0]->symbol[0]['name'];
			$hour_symbol_number_0	= $myweather->forecast[0]->time[0]->symbol[0]['number'];
			
			
			//Hours loop
			$i=1;
			
			while ($i<=5) {
				$hour_time_[$i]				= date("$wpcloudy_date_php_hours", strtotime($myweather->forecast[0]->time[$i]['from']));
				$hour_temp_[$i]				= (ceil($myweather->forecast[0]->time[$i]->temperature[0]['value']));
				$hour_symbol_[$i]			= $myweather->forecast[0]->time[$i]->symbol[0]['name'];
				$hour_symbol_number_[$i]	= $myweather->forecast[0]->time[$i]->symbol[0]['number'];
				$i++;

			} 
			
			//Forecast loop
			
			
			if ($wpcloudy_display_length_days_names == 'yes') {
				$wpcloudy_display_length_days_names_php = "%a";
			}
			elseif ($wpcloudy_display_length_days_names == 'no') {
				$wpcloudy_display_length_days_names_php = "%A";
			}
			else {
				$wpcloudy_display_length_days_names_php = "%A";
			}	
					
			$i=1;
			
			while ($i<=13) {
				$forecast_day_feed			= strftime("$wpcloudy_display_length_days_names_php", strtotime($myweather_sevendays->forecast[0]->time[$i]['day']));
				$forecast_day_[$i]			= iconv("$wpcloudy_lang_encoding", 'UTF-8', $forecast_day_feed);
				$forecast_number_[$i]		= $myweather_sevendays->forecast[0]->time[$i]->symbol[0]['number'];
				$forecast_temp_min_[$i]		= (round($myweather_sevendays->forecast[0]->time[$i]->temperature[0]['min']));
				$forecast_temp_max_[$i]		= (round($myweather_sevendays->forecast[0]->time[$i]->temperature[0]['max']));
				$i++;
			} 
			
			$time_temperature_mid 	= $time_temperature_min + $time_temperature_max;
			$time_temperature_ave 	= $time_temperature_mid / 2;
			
			switch ($time_symbol_number) {
			
				//sun
				case "800":
					$time_symbol_svg = sun();
					break;
				case "801":
					$time_symbol_svg = cloudSun();
					break;
				case "802":
					$time_symbol_svg = cloud();
					break;
				case "803":
					$time_symbol_svg = cloudFill();
					break;
				case "804":
					$time_symbol_svg = cloudFill();
					break;
					
				//rain
				case "500":
					$time_symbol_svg = cloudDrizzleSun();
					break;
				case "501":
					$time_symbol_svg = cloudDrizzleSun();
					break;
				case "502":
					$time_symbol_svg = cloudDrizzle();
					break;
				case "503":
					$time_symbol_svg = cloudDrizzleSunAlt();
					break;
				case "504":
					$time_symbol_svg = cloudDrizzleAlt();
					break;
				case "511":
					$time_symbol_svg = cloudRainSun();
					break;
				case "520":
					$time_symbol_svg = cloudRain();
					break;
				case "521":
					$time_symbol_svg = cloudSunRainAlt();
					break;
				case "522":
					$time_symbol_svg = cloudRainAlt();
					break;
					
				//drizzle
				case "300":
					$time_symbol_svg = cloudRainAlt();
					break;
				case "301":
					$time_symbol_svg = cloudRainAlt();
					break;
				case "302":
					$time_symbol_svg = cloudRainAlt();
					break;
				case "310":
					$time_symbol_svg = cloudRainAlt();
					break;
				case "311":
					$time_symbol_svg = cloudRainAlt();
					break;
				case "312":
					$time_symbol_svg = cloudRainAlt();
					break;
				case "321":
					$time_symbol_svg = cloudRainAlt();
					break;
					
				//snow
				case "600":
					$time_symbol_svg = cloudSnowSun();
					break;
				case "601":
					$time_symbol_svg = cloudSnow();
					break;
				case "602":
					$time_symbol_svg = cloudSnowSunAlt();
					break;
				case "611":
					$time_symbol_svg = cloudSnow();
					break;
				case "621":
					$time_symbol_svg = cloudSnowAlt();
					break;
					
				//atmosphere
				case "701":
					$time_symbol_svg = cloudFogSunAlt();
					break;
				case "711":
					$time_symbol_svg = cloudFogAlt();
					break;
				case "721":
					$time_symbol_svg = cloudFogAlt();
					break;
				case "731":
					$time_symbol_svg = cloudFogSun();
					break;
				case "741":
					$time_symbol_svg = cloudFog();
					break;
					
				//extreme
				case "900":
					$time_symbol_svg = tornado();
					break;
				case "901":
					$time_symbol_svg = wind();
					break;
				case "902":
					$time_symbol_svg = wind();
					break;
				case "905":
					$time_symbol_svg = wind();
					break;
				case "906":
					$time_symbol_svg = cloudHailAlt();
					break;
					
				//thunderstorm
				case "200":
					$time_symbol_svg = cloudLightning();
					break;
			}
		
			$wpcloudy_custom_css	= get_post_meta($id,'_wpcloudy_custom_css',true);
			
			if ($wpcloudy_custom_css) {
				$display_custom_css 	= '
					<style>
						'. $wpcloudy_custom_css .'
					</style>
				';
			}
			
			$display_now_start = '<div class="now">';
				$display_now_location_name = '<div class="location_name">'. wpcloudy_city_name($wpcloudy_city_name, $wpcloudy_city, $location_name, $wpcloudy_select_city_name, $wpcloudy_enable_geolocation)  .'</div>';
				$display_now_time_symbol = '<div class="time_symbol climacon" style="fill:'. wpc_css_text_color($wpcloudy_meta_text_color) .'">'. $time_symbol_svg .'</div>';
				$display_now_time_temperature = '<div class="time_temperature">'. $time_temperature .'</div>';
			$display_now_end = '</div>';
			
			$display_weather = '
				<div class="short_condition">'. $time_symbol .'</div>
			';
			
			$display_today_min_max_start 	.=	'<div class="today">';
			$display_today_min_max_day 		.=	'<div class="day"><span class="wpc-highlight">'. $today_day .'</span> '. __( 'Today', 'wpcloudy' ) .'</div>';
			$display_today_sun 				.=	display_today_sunrise_sunset($wpcloudy_sunrise_sunset, $sun_rise, $sun_set);
			$display_today_time_temp_min 	.=	'<div class="time_temperature_min">'. $time_temperature_min .'</div>';
			$display_today_time_temp_max	.=	'<div class="time_temperature_max"><span class="wpc-highlight">'. $time_temperature_max .'</span></div>';
			$display_today_min_max_end 		.=	'</div>';
			
			$display_today_ave_start 		.=	'<div class="today">';
			$display_today_ave_day 			.=	'<div class="day"><span class="wpc-highlight">'. $today_day .'</span> '. __( 'Today', 'wpcloudy' ) .'</div>';
			$display_today_ave_sun 			.=	display_today_sunrise_sunset($wpcloudy_sunrise_sunset, $sun_rise, $sun_set);
			$display_today_ave_time_ave 	.=	'<div class="time_temperature_ave"><span class="wpc-highlight">'.round($time_temperature_ave).'</span></div>';
			$display_today_ave_end			.=  '</div>';
			
			$display_wind = '
				<div class="wind">'. __( 'Wind', 'wpcloudy' ) .'<span class="wpc-highlight">'. $time_wind_direction .' '. $time_wind_speed .'</span></div>
			';
			$display_humidity = '
				<div class="humidity">'. __( 'Humidity', 'wpcloudy' ) .'<span class="wpc-highlight">'. $time_humidity .' %</span></div>
			';
			$display_pressure = '
				<div class="pressure">'. __( 'Pressure', 'wpcloudy' ) .'<span class="wpc-highlight">'. $time_pressure .' hPa</span></div>
			';
			$display_cloudiness = '
				<div class="cloudiness">'. __( 'Cloudiness', 'wpcloudy' ) .'<span class="wpc-highlight">'. $time_cloudiness .' %</span></div>
			';
			if ($time_precipitation != '') {
				$display_precipitation = '
					<div class="precipitation">'. __( 'Precipitation', 'wpcloudy' ) .'<span class="wpc-highlight">'. $time_precipitation .' mm</span></div>
				';
			}
			elseif ($time_precipitation == '') {
				$display_precipitation = '
					<div class="precipitation">'. __( 'Precipitation', 'wpcloudy' ) .'<span class="wpc-highlight">0 mm</span></div>
				';
			}
			
			//Hours loop
			$display_hours_0 = '
					<div class="first">
						<div class="hour"><span class="wpc-highlight">'. __( 'Now', 'wpcloudy' ) .'</span></div>
						<div class="symbol climacon w'. $hour_symbol_number_0 .'"><span>'. $hour_symbol_0 .'</span></div>
						<div class="temperature"><span class="wpc-highlight">'. $hour_temp_0 .'</span></div>
					</div>
			';
			
			$wpcloudy_class_hours = array(1 => "second", 2 => "third", 3 => "fourth", 4 => "fifth", 5 => "sixth");
			
			$i=1;
			while ($i<=5) { 
				$display_hours_[$i] = '
					<div class="'. $wpcloudy_class_hours[$i].'">
						<div class="hour">'. $hour_time_[$i] .'</div>
						<div class="symbol climacon w'. $hour_symbol_number_[$i] .'"><span>'. $hour_symbol_[$i] .'</span></div>
						<div class="temperature">'. $hour_temp_[$i]. '</div>
					</div>
				';
			
				$i++;

			} 
			
			//Forecast loop
			$wpcloudy_class_days = array(1 => "first", 2 => "second", 3 => "third", 4 => "fourth", 5 => "fifth", 6 => "sixth", 7 => "seventh", 8 => "eighth", 9 => "ninth", 10 => "tenth", 11 => "eleventh", 12 => "twelfth", 13 => "thirteenth");
			
			$i=1;
			while ($i<=13) { 
				$display_forecast_[$i] = '	
					<div class="'. $wpcloudy_class_days[$i].'">
						<div class="day">'. $forecast_day_[$i] .'</div>
						<div class="symbol climacon w'. $forecast_number_[$i] .'"></div>
						<div class="temp_min">'. $forecast_temp_min_[$i] .'</div>
						<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_[$i] .'</span></div>
					</div>
				';
			
				$i++;

			}

			if( $wpcloudy_map_stations ) {
				$display_map_stations					= 'var stations = new OpenLayers.Layer.Vector.OWMStations("Stations");';
				$display_map_stations_layers			= 'stations,';
			}
			else {
				$display_map_stations					= '';
				$display_map_stations_layers	 		= '';
			};
			if( $wpcloudy_map_clouds ) {
				$display_map_clouds					= 'var layer_cloud = new OpenLayers.Layer.XYZ(
															"clouds",
																"http://${s}.tile.openweathermap.org/map/clouds/${z}/${x}/${y}.png",
																{
																	isBaseLayer: false,
																	opacity: '. $wpcloudy_map_opacity .',
																	sphericalMercator: true
																}
															);
														';
				$display_map_clouds_layers				= 'layer_cloud,';
			}
			else {
				$display_map_clouds						= '';
				$display_map_clouds_layers		 		= '';
			};
			if( $wpcloudy_map_precipitation ) {
				$display_map_precipitation				= 'var layer_precipitation = new OpenLayers.Layer.XYZ(
															"precipitation",
																"http://${s}.tile.openweathermap.org/map/precipitation/${z}/${x}/${y}.png",
																{
																	isBaseLayer: false,
																	opacity: '. $wpcloudy_map_opacity .',
																	sphericalMercator: true
																}
															);
														';
				$display_map_precipitation_layers		= 'layer_precipitation,';
			}
			else {
				$display_map_precipitation				= '';
				$display_map_precipitation_layers 		= '';
			};
			if( $wpcloudy_map_snow ) {
				$display_map_snow						= 'var layer_snow = new OpenLayers.Layer.XYZ(
															"snow",
																"http://${s}.tile.openweathermap.org/map/snow/${z}/${x}/${y}.png", 
																{
																	isBaseLayer: false,
																	opacity: '. $wpcloudy_map_opacity .',
																	sphericalMercator: true
																}
															);
														';
				$display_map_snow_layers				= 'layer_snow,';
			}
			else {
				$display_map_snow						= '';
				$display_map_snow_layers		 		= '';
			};
			if( $wpcloudy_map_wind ) {
				$display_map_wind 						= 'var layer_wind = new OpenLayers.Layer.XYZ(
															"wind",
																"http://${s}.tile.openweathermap.org/map/wind/${z}/${x}/${y}.png",
																{
																	isBaseLayer: false,
																	opacity: '. $wpcloudy_map_opacity .',
																	sphericalMercator: true
																}
															);
														';
				$display_map_wind_layers				= 'layer_wind,';
			}
			else {
				$display_map_wind						= '';
				$display_map_wind_layers		 		= '';
			};
			if( $wpcloudy_map_temperature ) {
				$display_map_temperature				= 'var layer_temp = new OpenLayers.Layer.XYZ(
															"temp",
																"http://${s}.tile.openweathermap.org/map/temp/${z}/${x}/${y}.png",
																{
																	isBaseLayer: false,
																	opacity: '. $wpcloudy_map_opacity .',
																	sphericalMercator: true
																}
															);
														';
				$display_map_temperature_layers			= 'layer_temp,';
			}
			else {
				$display_map_temperature				= '';
				$display_map_temperature_layers 		= '';
			};
			if( $wpcloudy_map_pressure ) {
				$display_map_pressure					= 'var layer_pressure = new OpenLayers.Layer.XYZ(
															"pressure",
																"http://${s}.tile.openweathermap.org/map/pressure/${z}/${x}/${y}.png",
																{
																	isBaseLayer: false,
																	opacity: '. $wpcloudy_map_opacity .',
																	sphericalMercator: true
																}
															);
														';
				$display_map_pressure_layers			= 'layer_pressure,';
			}
			else {
				$display_map_pressure 					= '';
				$display_map_pressure_layers 			= '';
			};
			
			if( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-detectGeolocation']=='1' ) { 

				$wpcloudy_map_lat = $_COOKIE['wpc-posLat'];
				$wpcloudy_map_lon = $_COOKIE['wpc-posLon'];
			
			}
			
			if( $wpcloudy_enable_geolocation == 'yes' && $_COOKIE['wpc-manualGeolocation']=='1' ) { 
				$wpcloudy_map_lat = $_COOKIE['wpc-posLat'];
				$wpcloudy_map_lon = $_COOKIE['wpc-posLon'];
			}
			
			else {
				$wpcloudy_map_lat = $location_latitude;
				$wpcloudy_map_lon = $location_longitude;
			}
				
			$display_map = '				
				<div id="wpc-map-container">	
					<div id="wpc-map" style="height: '. $wpcloudy_map_height .'px"></div>
				</div>
				<script type="text/javascript">
					window.onload = function init() {
						//Center of map
						var lat = '. $wpcloudy_map_lat .'; 
						var lon = '. $wpcloudy_map_lon .';
						var lonlat = new OpenLayers.LonLat(lon, lat);
							var map = new OpenLayers.Map("wpc-map");
						// Create overlays
						//  OSM
							var mapnik = new OpenLayers.Layer.OSM();
						
						// Stations
						'. $display_map_stations .'

						// Current weather
						var city = new OpenLayers.Layer.Vector.OWMWeather("Weather");
						
						// Clouds
						'. $display_map_clouds .'
						
						// Precipitation
						'. $display_map_precipitation .'
						
						// Snow
						'. $display_map_snow .'
						
						// Wind
						'. $display_map_wind .'
						
						// Temperature
						'. $display_map_temperature .'
						
						// Pressure
						'. $display_map_pressure .'
						
						//Addind maps
						map.addLayers([
						mapnik, 
						'. $display_map_stations_layers .' 
						'. $display_map_clouds_layers .' 
						'. $display_map_precipitation_layers .' 
						'. $display_map_snow_layers .' 
						'. $display_map_wind_layers .' 
						'. $display_map_temperature_layers .' 
						'. $display_map_pressure_layers .' 
						city]);
						map.setCenter(
							new OpenLayers.LonLat(lon, lat).transform(
								new OpenLayers.Projection("EPSG:4326"),
								map.getProjectionObject()
							), '. $wpcloudy_map_zoom .'
						);    
					}
				</script>
			';

			
			$wpcloudy_current_weather		= 	get_bypass_display_current_weather($attr,$content);
			$wpcloudy_weather				= 	get_bypass_display_weather($attr,$content);
			$wpcloudy_date_temp				= 	get_bypass_display_date_temp($attr,$content);
			$wpcloudy_wind 					= 	get_bypass_display_wind($attr,$content);
			$wpcloudy_humidity				= 	get_bypass_display_humidity($attr,$content);
			$wpcloudy_pressure				= 	get_bypass_display_pressure($attr,$content);
			$wpcloudy_cloudiness			= 	get_bypass_display_cloudiness($attr,$content);
			$wpcloudy_precipitation			= 	get_bypass_display_precipitation($attr,$content);
			$wpcloudy_temperature_min_max	=	get_bypass_temp($attr,$content);
			$wpcloudy_hour_forecast			=	get_bypass_display_hour_forecast($attr,$content);
			$wpcloudy_hour_forecast_nd		=	get_bypass_display_hour_forecast_nd($attr,$content);
			$wpcloudy_forecast				=	get_bypass_display_forecast($attr,$content);
			$wpcloudy_forecast_nd			=	get_bypass_forecast_nd($attr,$content);
			$wpcloudy_size					=	get_bypass_size($attr,$content);
			$wpcloudy_map 					= 	get_bypass_map($attr,$content);			
			$wpcloudy_skin 				    =   get_post_meta($id,'_wpcloudy_skin',true);
			$wpcloudy_css3_anims			=	get_bypass_disable_css3_anims($attr,$content);
			$wpcloudy_map_js 				= 	get_admin_map_js();
						
			$wpc_html_container_start = '
			<!-- WP Cloudy : WordPress weather plugin - http://wpcloudy.com/ -->
			<div id="wpc-weather" class="wpc-'.$id.' '. $wpcloudy_size .' '. $wpcloudy_skin .'" style="'. wpc_css_background($wpcloudy_meta_bg_color) .'; color:'. wpc_css_text_color($wpcloudy_meta_text_color) .';'. wpc_css_border($wpcloudy_meta_border_color) .'; font-family:'. wpc_css_webfont($attr,$content) .'">';
			
			if ( $wpcloudy_enable_geolocation == 'yes' ) { 
				$wpc_html_geolocation .=  wpc_geolocation_form($attr,$content);
			}
			
			if( $wpcloudy_current_weather ) {
				$wpc_html_now_start 					.= $display_now_start;
				$wpc_html_now_location_name 			.= $display_now_location_name;
				$wpc_html_display_now_time_symbol 		.= $display_now_time_symbol;
				$wpc_html_display_now_time_temperature 	.= $display_now_time_temperature;
				$wpc_html_now_end 						.= $display_now_end;
				
			}
			
			if( $wpcloudy_weather ) {
				$wpc_html_weather .= $display_weather;
			}
	
			if( $wpcloudy_date_temp && $wpcloudy_temperature_min_max == 'yes' ) {
				$wpc_html_today_temp_start 	.= $display_today_min_max_start;
				$wpc_html_today_temp_day 	.= $display_today_min_max_day;
				$wpc_html_today_sun 			.= $display_today_sun;
				$wpc_html_today_time_temp_min 	.= $display_today_time_temp_min;
				$wpc_html_today_time_temp_max 	.= $display_today_time_temp_max;
				$wpc_html_today_temp_end 	.= $display_today_min_max_end;

			}	
			elseif( $wpcloudy_date_temp && $wpcloudy_temperature_min_max == 'no' ) {
				$wpc_html_today_temp_start	 	.= $display_today_ave_start;
				$wpc_html_today_temp_day 		.= $display_today_ave_day;
				$wpc_html_today_sun 			.= $display_today_ave_sun;
				$wpc_html_today_ave_time_ave 	.= $display_today_ave_time_ave;
				$wpc_html_today_temp_end 		.= $display_today_ave_end;
			}				
			
			if( $wpcloudy_wind || $wpcloudy_humidity || $wpcloudy_pressure || $wpcloudy_cloudiness || $wpcloudy_precipitation ) {
				
				$wpc_html_infos_start .= '<div class="infos">';

				if( $wpcloudy_wind ) {
					$wpc_html_infos_wind 			.= $display_wind;
				}
				
				if( $wpcloudy_humidity ) {
					$wpc_html_infos_humidity 		.= $display_humidity;
				} 
				
				if( $wpcloudy_pressure ) {
					$wpc_html_infos_pressure 		.= $display_pressure;
				} 
				
				if( $wpcloudy_cloudiness ) {
					$wpc_html_infos_cloudiness 		.= $display_cloudiness;
				}
				
				if( $wpcloudy_precipitation ) {
					$wpc_html_infos_precipitation 	.= $display_precipitation;
				}  
				
				$wpc_html_infos_end .= '</div>';
			
			};
			if( $wpcloudy_hour_forecast && !$wpcloudy_hour_forecast_nd =='' ) {
				
				$wpc_html_hour_start .= '<div class="hours" style="border-color:'. $wpcloudy_meta_border_color .';">';
							
				$wpc_html_hour = array( $display_hours_[1], $display_hours_[2], $display_hours_[3], $display_hours_[4], $display_hours_[5] );
				
				$wpc_html_hour_end .= '</div>';
			} 

			if ($wpcloudy_forecast && !$wpcloudy_forecast_nd == '' ) {
			
				$wpc_html_forecast_start .= '<div class="forecast">';
				
				$wpc_html_forecast = array( $display_forecast_[1], $display_forecast_[2], $display_forecast_[3], $display_forecast_[4], $display_forecast_[5], $display_forecast_[6], $display_forecast_[7], $display_forecast_[8], $display_forecast_[9], $display_forecast_[10], $display_forecast_[11], $display_forecast_[12], $display_forecast_[13] );
				
				$wpc_html_forecast_end .= '</div>';

			}
			
			if (isset($wpcloudy_map)) {
			
				if ($wpcloudy_map_js == '0') { //Webhost
				
					wp_register_script("openlayers_js", plugins_url('js/OpenLayers.js', __FILE__), array(), "1.0", false);
					wp_register_script("owm_js", plugins_url('js/OWM.OpenLayers.1.3.4.js', __FILE__), array(), "1.0", false);	
					wp_register_style("openlayers_css", plugins_url('css/wpcloudy-map.min.css', __FILE__), array(), "1.0", false);
					wp_enqueue_script("openlayers_js");			
					wp_enqueue_script("owm_js");
					wp_enqueue_style("openlayers_css");

				}
				if ($wpcloudy_map_js == '1') { //OpenWeatherMap
					wp_register_script("openlayers_js_owm", "http://openlayers.org/api/OpenLayers.js", array(), "1.0", false);
					wp_register_script("owm_js_owm", "http://openweathermap.org/js/OWM.OpenLayers.1.3.4.js", array(), "1.0", false);	
					wp_register_style("openlayers_css_owm", "http://openlayers.org/api/theme/default/style.css", array(), "1.0", false);
					wp_enqueue_script("openlayers_js_owm");			
					wp_enqueue_script("owm_js_owm");
					wp_enqueue_style("openlayers_css_owm");
				} 
				
				$wpc_html_map .= $display_map;
			}

			if (isset($display_custom_css)) {
				$wpc_html_custom_css .= $display_custom_css;
			}
			
			if ($wpcloudy_css3_anims == '1') {
				$wpc_html_css3_anims .= '<style>
							.wpc-'.$id.' * {
								/*CSS transitions*/
								-o-transition-property: none !important;
								-moz-transition-property: none !important;
								-ms-transition-property: none !important;
								-webkit-transition-property: none !important;
								transition-property: none !important;
								/*CSS transforms*/
								-o-transform: none !important;
								-moz-transform: none !important;
								-ms-transform: none !important;
								-webkit-transform: none !important;
								transform: none !important;
								/*CSS animations*/
								-webkit-animation: none !important;
								-moz-animation: none !important;
								-o-animation: none !important;
								-ms-animation: none !important;
								animation: none !important;
							}
							</style>
						';
			}
			if (!$wpcloudy_css3_anims == '1') {
				wp_enqueue_style('wpcloudy-anim');
			}
			
			if ($wpcloudy_display_temp_unit == 'yes' && $wpcloudy_unit == 'metric') {
				$wpc_html_temp_unit_metric .= '<style>
						  #wpc-weather.small .now .time_temperature:after,
						  #wpc-weather .forecast .temp_max:after,
						  #wpc-weather .forecast .temp_min:after,
						  #wpc-weather .hours .temperature:after,
						  #wpc-weather .today .time_temperature_max:after,
						  #wpc-weather .today .time_temperature_min:after,
						  #wpc-weather .now .time_temperature:after,
						  #wpc-weather .today .time_temperature_ave:after {
						  	content:"\e03e";
						  	font-family: "Climacons-Font";
						    font-size: 24px;
						    margin-left: 2px;
						    vertical-align: top;
						  }
						  </style>
				';
			}
			
			if ($wpcloudy_display_temp_unit == 'yes' && $wpcloudy_unit == 'imperial') {
				$wpc_html_temp_unit_imperial .= '<style>
						  #wpc-weather.small .now .time_temperature:after,
						  #wpc-weather .forecast .temp_max:after,
						  #wpc-weather .forecast .temp_min:after,
						  #wpc-weather .hours .temperature:after,
						  #wpc-weather .today .time_temperature_max:after,
						  #wpc-weather .today .time_temperature_min:after,
						  #wpc-weather .now .time_temperature:after,
						  #wpc-weather .today .time_temperature_ave:after {
						  	content: "\e03f";
						  	font-family: "Climacons-Font";
						    font-size: 24px;
						    margin-left: 2px;
						    vertical-align: top;
						  }
						  </style>
				';
			}

		$wpc_html_container_end .= '</div>';
		
		$wpc_theme_files = array('wp-cloudy/content-wpcloudy.php');
		$wpc_exists_in_theme = locate_template($wpc_theme_files, false);
					
		if ( $wpc_exists_in_theme != '' ) {//Bypass dans theme actif
			ob_start();
			include get_template_directory() . '/wp-cloudy/content-wpcloudy.php';
			return ob_get_clean();
		}
		elseif ( $wpcloudy_skin == 'theme1' ) {//Theme1 actif
			ob_start();
			include dirname( __FILE__ ) . '/template/content-wpcloudy-theme1.php';
			return ob_get_clean();
		}
		elseif ( $wpcloudy_skin == 'theme2' ) {//Theme2 actif
			ob_start();
			include dirname( __FILE__ ) . '/template/content-wpcloudy-theme2.php';
			return ob_get_clean();
		} 
		else { //Default
			ob_start();
			include ( dirname( __FILE__ ) . '/template/content-wpcloudy.php');
			return ob_get_clean();

		}
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Fix shortcode bug in widget text
///////////////////////////////////////////////////////////////////////////////////////////////////
add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode', 11);

///////////////////////////////////////////////////////////////////////////////////////////////////
//Display shortcode in listing view
///////////////////////////////////////////////////////////////////////////////////////////////////

add_filter('manage_edit-wpc-weather_columns', 'set_custom_edit_wpc_weather_columns');
add_action('manage_wpc-weather_posts_custom_column', 'custom_wpc_weather_column', 10, 2);

function set_custom_edit_wpc_weather_columns($columns) {
    return $columns
    + array('wpc-weather' => __('Shortcode'));
}

function custom_wpc_weather_column($column, $post_id) {

    $wpc_weather_meta = get_post_meta($post_id, "_wpc-weather_meta", true);
    $wpc_weather_meta = ($wpc_weather_meta != '') ? json_decode($wpc_weather_meta) : array();

    switch ($column) {
        case 'wpc-weather':
            echo "[wpc-weather id=\"$post_id\" /]";
            break;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Weather Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////

// Register Custom Post Type
function wpcloudy_weather() {
	$labels = array(
		'name'                => _x( 'Weather', 'Post Type General Name', 'wpcloudy' ),
		'singular_name'       => _x( 'Weather', 'Post Type Singular Name', 'wpcloudy' ),
		'menu_name'           => __( 'Weather', 'wpcloudy' ),
		'parent_item_colon'   => __( 'Parent Weather:', 'wpcloudy' ),
		'all_items'           => __( 'All Weather', 'wpcloudy' ),
		'view_item'           => __( 'View Weather', 'wpcloudy' ),
		'add_new_item'        => __( 'Add New Weather', 'wpcloudy' ),
		'add_new'             => __( 'New Weather', 'wpcloudy' ),
		'edit_item'           => __( 'Edit Weather', 'wpcloudy' ),
		'update_item'         => __( 'Update Weather', 'wpcloudy' ),
		'search_items'        => __( 'Search Weather', 'wpcloudy' ),
		'not_found'           => __( 'No weather found', 'wpcloudy' ),
		'not_found_in_trash'  => __( 'No weather found in Trash', 'wpcloudy' ),
	);

	$args = array(
		'label'               => __( 'weather', 'wpcloudy' ),
		'description'         => __( 'Listing weather', 'wpcloudy' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);

	register_post_type( 'wpc-weather', $args );
}

// Hook into the 'init' action
add_action( 'init', 'wpcloudy_weather', 0 );

///////////////////////////////////////////////////////////////////////////////////////////////////
//Weather Custom Post Type Messages
///////////////////////////////////////////////////////////////////////////////////////////////////

function wpc_set_messages($messages) {
	global $post, $post_ID;
	$post_type = 'wpc-weather';

	$obj = get_post_type_object($post_type);
	$singular = $obj->labels->singular_name;

	$messages[$post_type] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __($singular.' updated.'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __($singular.' updated.'),
		5 => isset($_GET['revision']) ? sprintf( __($singular.' restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __($singular.' published.'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Page saved.'),
		8 => sprintf( __($singular.' submitted.'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __($singular.' scheduled for: <strong>%1$s</strong>. '), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __($singular.' draft updated.'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}

add_filter('post_updated_messages', 'wpc_set_messages' );


?>