<?php
/*
Plugin Name: WP Cloudy
Plugin URI: http://wpcloudy.com/
Description: WP Cloudy is a powerful weather plugin for WordPress, based on Open Weather Map API, using Custom Post Types and shortcodes, bundled with a ton of features.
Version: 2.6.2
Author: Benjamin DENIS
Author URI: http://wpcloudy.com/
License: GPLv2
*/

/*  Copyright 2013  Benjamin DENIS  (email : contact@wpcloudy.com)

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
		wp_register_style('wpcloudy', plugins_url('css/wpcloudy.css', __FILE__));
		wp_enqueue_style('wpcloudy');
		
		wp_register_style('wpcloudy-anim', plugins_url('css/wpcloudy-anim.css', __FILE__));
		wp_enqueue_style('wpcloudy-anim');
}
add_action('wp_enqueue_scripts', 'wpcloudy_styles');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Loads the JS/CSS in admin
///////////////////////////////////////////////////////////////////////////////////////////////////

//Dashboard
function add_dashboard_scripts() {
	wp_register_style('wpcloudy', plugins_url('css/wpcloudy.css', __FILE__));
	wp_enqueue_style('wpcloudy');
	
	wp_register_style('wpcloudy-anim', plugins_url('css/wpcloudy-anim.css', __FILE__));
	wp_enqueue_style('wpcloudy-anim');
	
	require_once dirname( __FILE__ ) . '/wpcloudy-anim.php';
}
add_action('admin_head-index.php', 'add_dashboard_scripts');

//Admin + Custom Post Type (new, listing)
function add_admin_scripts( $hook ) {

global $post;
    
	if ( $hook == 'post-new.php' || $hook == 'post.php') {
		
		wp_enqueue_script( 'wpc-tinymce-js', plugins_url('js/wpc-tinymce.js', __FILE__), array( 'wpc-tinymce' ) );
		
        if ( 'wpc-weather' === $post->post_type) { 
			wp_register_style( 'wpcloudy-admin', plugins_url('css/wpcloudy-admin.css', __FILE__));
			wp_enqueue_style( 'wpcloudy-admin' );
			
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'color-picker-js', plugins_url('js/color-picker.js', __FILE__), array( 'wp-color-picker' ) );
			wp_enqueue_script( 'tabs-js', plugins_url( 'js/tabs.js', __FILE__ ), array( 'jquery-ui-tabs' ) );
		}
	}
	
	wp_register_style( 'wpcloudy-admin', plugins_url('css/wpcloudy-admin.css', __FILE__));
	wp_enqueue_style( 'wpcloudy-admin' );
}
add_action( 'admin_enqueue_scripts', add_admin_scripts, 10, 1 );

//WP Cloudy Options page
function add_admin_options_scripts() {
			wp_register_style( 'wpcloudy-admin', plugins_url('css/wpcloudy-admin.css', __FILE__));
			wp_enqueue_style( 'wpcloudy-admin' );
			
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'color-picker-js', plugins_url('js/color-picker.js', __FILE__), array( 'wp-color-picker' ) );
			wp_enqueue_script( 'tabs-js', plugins_url( 'js/tabs.js', __FILE__ ), array( 'jquery-ui-tabs' ) );
}

if (isset($_GET['page']) && ($_GET['page'] == 'wpc-settings-admin')) { 

	add_action('admin_enqueue_scripts', add_admin_options_scripts, 10, 1);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Add weather button in tinymce editor
///////////////////////////////////////////////////////////////////////////////////////////////////

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
  $wpcloudy_city 					= get_post_meta($post->ID,'_wpcloudy_city',true);
  $wpcloudy_city_name				= get_post_meta($post->ID,'_wpcloudy_city_name',true);
  $wpcloudy_country_code 			= get_post_meta($post->ID,'_wpcloudy_country_code',true);
  $wpcloudy_unit 					= get_post_meta($post->ID,'_wpcloudy_unit',true);
  $wpcloudy_lang 					= get_post_meta($post->ID,'_wpcloudy_lang',true);
  $wpcloudy_current_weather			= get_post_meta($post->ID,'_wpcloudy_current_weather',true);
  $wpcloudy_date_temp				= get_post_meta($post->ID,'_wpcloudy_date_temp',true);
  $wpcloudy_weather					= get_post_meta($post->ID,'_wpcloudy_weather',true);
  $wpcloudy_sunrise_sunset 			= get_post_meta($post->ID,'_wpcloudy_sunrise_sunset',true);
  $wpcloudy_wind 					= get_post_meta($post->ID,'_wpcloudy_wind',true);
  $wpcloudy_humidity 				= get_post_meta($post->ID,'_wpcloudy_humidity',true);
  $wpcloudy_pressure				= get_post_meta($post->ID,'_wpcloudy_pressure',true);
  $wpcloudy_cloudiness				= get_post_meta($post->ID,'_wpcloudy_cloudiness',true);
  $wpcloudy_hour_forecast			= get_post_meta($post->ID,'_wpcloudy_hour_forecast',true);
  $wpcloudy_temperature_min_max		= get_post_meta($post->ID,'_wpcloudy_temperature_min_max',true);
  $wpcloudy_forecast				= get_post_meta($post->ID,'_wpcloudy_forecast',true);
  $wpcloudy_forecast_nd				= get_post_meta($post->ID,'_wpcloudy_forecast_nd',true);
  $wpcloudy_meta_bg_color			= get_post_meta($post->ID,'_wpcloudy_meta_bg_color',true);
  $wpcloudy_meta_txt_color			= get_post_meta($post->ID,'_wpcloudy_meta_txt_color',true);
  $wpcloudy_meta_border_color		= get_post_meta($post->ID,'_wpcloudy_meta_border_color',true);
  $wpcloudy_custom_css				= get_post_meta($post->ID,'_wpcloudy_custom_css',true);
  $wpcloudy_size 					= get_post_meta($post->ID,'_wpcloudy_size',true);
  $wpcloudy_map 					= get_post_meta($post->ID,'_wpcloudy_map',true);
  $wpcloudy_map_height				= get_post_meta($post->ID,'_wpcloudy_map_height',true);
  $wpcloudy_map_opacity				= get_post_meta($post->ID,'_wpcloudy_map_opacity',true);
  $wpcloudy_map_zoom				= get_post_meta($post->ID,'_wpcloudy_map_zoom',true);
  $wpcloudy_map_stations			= get_post_meta($post->ID,'_wpcloudy_map_stations',true);
  $wpcloudy_map_clouds				= get_post_meta($post->ID,'_wpcloudy_map_clouds',true);
  $wpcloudy_map_precipitation		= get_post_meta($post->ID,'_wpcloudy_map_precipitation',true);
  $wpcloudy_map_snow				= get_post_meta($post->ID,'_wpcloudy_map_snow',true);
  $wpcloudy_map_wind				= get_post_meta($post->ID,'_wpcloudy_map_wind',true);
  $wpcloudy_map_temperature			= get_post_meta($post->ID,'_wpcloudy_map_temperature',true);
  $wpcloudy_map_pressure			= get_post_meta($post->ID,'_wpcloudy_map_pressure',true);
  
  
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
					<input id="wpcloudy_city_meta" type="text" name="wpcloudy_city" value="'.$wpcloudy_city.'" />
				</p>
				<p>
					<label for="wpcloudy_city_name_meta">'. __( 'Custom city title', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_city_name_meta" type="text" name="wpcloudy_city_name" value="'.$wpcloudy_city_name.'" />
				</p>
				<p>
					<label for="wpcloudy_country_meta">'. __( 'Country? (you can enter your country code as well as the country, in your own language, eg: "fr" or "france" or "francia"...)', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_country_meta" type="text" name="wpcloudy_country_code" value="'.$wpcloudy_country_code.'" />
				</p>
				<p>
					<label for="unit_meta">'. __( 'Imperial or metric units?', 'wpcloudy' ) .'</label>
					<select name="wpcloudy_unit">
						<option ' . selected( 'imperial', $wpcloudy_unit, false ) . ' value="imperial">'. __( 'Imperial', 'wpcloudy' ) .'</option>
						<option ' . selected( 'metric', $wpcloudy_unit, false ) . ' value="metric">'. __( 'Metric', 'wpcloudy' ) .'</option>
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
					<label for="wpcloudy_hour_forecast_meta">
						<input type="checkbox" name="wpcloudy_hour_forecast" id="wpcloudy_hour_forecast_meta" value="yes" '. checked( $wpcloudy_hour_forecast, 'yes', false ) .' />
							'. __( 'Hour Forecast?', 'wpcloudy' ) .'
					</label>
				</p>
				<p class="temperatures">
					'. __( 'Temperatures', 'wpcloudy' ) .'
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
			</div>
			<div id="tabs-3">
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

add_action('save_post','save_metabox');
function save_metabox($post_id){
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
	if( isset( $_POST[ 'wpcloudy_hour_forecast' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_hour_forecast', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_hour_forecast', '' );
	}
	if( isset( $_POST[ 'wpcloudy_temperature_min_max' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_temperature_min_max', $_POST[ 'wpcloudy_temperature_min_max' ] );
	}
	if( isset( $_POST[ 'wpcloudy_forecast' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_forecast', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_forecast', '' );
	}
	if(isset($_POST['wpcloudy_forecast_nd'])){
	  update_post_meta($post_id, '_wpcloudy_forecast_nd', esc_html($_POST['wpcloudy_forecast_nd']));
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

///////////////////////////////////////////////////////////////////////////////////////////////////
//WPC Languages
///////////////////////////////////////////////////////////////////////////////////////////////////		

//Bypass Lang
function get_admin_bypass_lang() {
	$wpc_admin_bypass_lang_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_lang_option ) ) {
		foreach ($wpc_admin_bypass_lang_option as $key => $wpc_admin_bypass_lang_value)
			$options[$key] = $wpc_admin_bypass_lang_value;
		return $wpc_admin_bypass_lang_option['wpc_basic_bypass_lang'];
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
		return $wpc_admin_bypass_unit_option['wpc_basic_bypass_unit'];
	}
};

function get_admin_unit() {
	$wpc_admin_unit_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_unit_option ) ) {
		foreach ($wpc_admin_unit_option as $key => $wpc_admin_unit_value)
			$options[$key] = $wpc_admin_unit_value;
		return $wpc_admin_unit_option['wpc_basic_unit'];
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

//Bypass Forecast Days
function get_admin_bypass_forecast_nd() {
	$wpc_admin_bypass_forecast_nd_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_forecast_nd_option ) ) {
		foreach ($wpc_admin_bypass_forecast_nd_option as $key => $wpc_admin_bypass_forecast_nd_value)
			$options[$key] = $wpc_admin_bypass_forecast_nd_value;
		return $wpc_admin_bypass_forecast_nd_option['wpc_display_bypass_forecast_nd'];
	}
};

function get_admin_forecast_nd() {
	$wpc_admin_forecast_nd_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_forecast_nd_option ) ) {
		foreach ($wpc_admin_forecast_nd_option as $key => $wpc_admin_forecast_nd_value)
			$options[$key] = $wpc_admin_forecast_nd_value;
		return $wpc_admin_forecast_nd_option['wpc_display_forecast_nd'];
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
//Bypass Background Color
	
function get_admin_color_background() {
	$wpc_admin_bg_color_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_bg_color_option ) ) {
		foreach ($wpc_admin_bg_color_option as $key => $wpc_admin_bg_color_value)
			$options[$key] = $wpc_admin_bg_color_value;
		return $wpc_admin_bg_color_option['wpc_advanced_bg_color'];
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
		return $wpc_admin_text_color_option['wpc_advanced_text_color'];
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
		return $wpc_admin_color_border_option['wpc_advanced_border_color'];
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
		return $wpc_admin_display_current_weather_option['wpc_display_current_weather'];
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
		return $wpc_admin_display_weather_option['wpc_display_weather'];
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

//Bypass Date Temp

function get_admin_display_date_temp() {
	$wpc_admin_display_date_temp_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_date_temp_option ) ) {
		foreach ($wpc_admin_display_date_temp_option as $key => $wpc_admin_display_date_temp_value)
			$options[$key] = $wpc_admin_display_date_temp_value;
		return $wpc_admin_display_date_temp_option['wpc_display_date_temp'];
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
		return $wpc_admin_display_sunrise_sunset_option['wpc_display_sunrise_sunset'];
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

//Bypass Wind

function get_admin_display_wind() {
	$wpc_admin_display_wind_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_wind_option ) ) {
		foreach ($wpc_admin_display_wind_option as $key => $wpc_admin_display_wind_value)
			$options[$key] = $wpc_admin_display_wind_value;
		return $wpc_admin_display_wind_option['wpc_display_wind'];
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
		return $wpc_admin_display_humidity_option['wpc_display_current_weather'];
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
		return $wpc_admin_display_pressure_option['wpc_display_pressure'];
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
		return $wpc_admin_display_cloudiness_option['wpc_display_cloudiness'];
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

//Bypass Hour Forecast

function get_admin_display_hour_forecast() {
	$wpc_admin_display_hour_forecast_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_hour_forecast_option ) ) {
		foreach ($wpc_admin_display_hour_forecast_option as $key => $wpc_admin_display_hour_forecast_value)
			$options[$key] = $wpc_admin_display_hour_forecast_value;
		return $wpc_admin_display_hour_forecast_option['wpc_display_hour_forecast'];
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

//Bypass Today Date + Min-Max Temp

function get_admin_bypass_temp() {
	$wpc_display_temperature_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_display_temperature_option ) ) {
		foreach ($wpc_display_temperature_option as $key => $wpc_display_temperature_value)
			$options[$key] = $wpc_display_temperature_value;
		return $wpc_display_temperature_option['wpc_display_bypass_temperature'];
	}
};

function get_admin_display_temp() {
	$wpc_admin_display_temperature_min_max_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_temperature_min_max_option ) ) {
		foreach ($wpc_admin_display_temperature_min_max_option as $key => $wpc_admin_display_temperature_min_max_value)
			$options[$key] = $wpc_admin_display_temperature_min_max_value;
		return $wpc_admin_display_temperature_min_max_option['wpc_display_temperature_min_max'];
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

//Bypass Forecast

function get_admin_display_forecast() {
	$wpc_admin_display_forecast_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_forecast_option ) ) {
		foreach ($wpc_admin_display_forecast_option as $key => $wpc_admin_display_forecast_value)
			$options[$key] = $wpc_admin_display_forecast_value;
		return $wpc_admin_display_forecast_option['wpc_display_forecast'];
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
		return $wpc_admin_bypass_size_option['wpc_advanced_bypass_size'];
	}
};

function get_admin_size() {
	$wpc_admin_size_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_size_option ) ) {
		foreach ($wpc_admin_size_option as $key => $wpc_admin_size_value)
			$options[$key] = $wpc_admin_size_value;
		return $wpc_admin_size_option['wpc_advanced_size'];
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
		return $wpc_admin_bypass_map_option['wpc_map_display'];
	}
};

function get_map($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_map_value = get_post_meta($id,'_wpcloudy_map',true);
		return $wpc_map_value;
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
		return $wpc_admin_bypass_map_height_option['wpc_map_height'];
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
		return $wpc_admin_bypass_map_opacity_option['wpc_map_bypass_opacity'];
	}
};

function get_admin_map_opacity() {
	$wpc_admin_map_opacity_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_opacity_option ) ) {
		foreach ($wpc_admin_map_opacity_option as $key => $wpc_admin_map_opacity_value)
			$options[$key] = $wpc_admin_map_opacity_value;
		return $wpc_admin_map_opacity_option['wpc_map_opacity'];
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
		return $wpc_admin_bypass_map_zoom_option['wpc_map_bypass_zoom'];
	}
};

function get_admin_map_zoom() {
	$wpc_admin_map_zoom_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_map_zoom_option ) ) {
		foreach ($wpc_admin_map_zoom_option as $key => $wpc_admin_map_zoom_value)
			$options[$key] = $wpc_admin_map_zoom_value;
		return $wpc_admin_map_zoom_option['wpc_map_zoom'];
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
		return $wpc_admin_map_layers_stations_option['wpc_map_layers_stations'];
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
		return $wpc_admin_map_layers_clouds_option['wpc_map_layers_clouds'];
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
		return $wpc_admin_map_layers_precipitation_option['wpc_map_layers_precipitation'];
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
		return $wpc_admin_map_layers_snow_option['wpc_map_layers_snow'];
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
		return $wpc_admin_map_layers_wind_option['wpc_map_layers_wind'];
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
		return $wpc_admin_map_layers_temperature_option['wpc_map_layers_temperature'];
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
		return $wpc_admin_map_layers_pressure_option['wpc_map_layers_pressure'];
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

function wpcloudy_city_name($wpcloudy_city_name, $wpcloudy_city) {
	if( $wpcloudy_city_name ) {
			return $wpcloudy_city_name;
	}
	else {
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

			$wpcloudy_city 				= get_post_meta($id,'_wpcloudy_city',true);
			$wpcloudy_city_name 		= get_post_meta($id,'_wpcloudy_city_name',true);
			$wpcloudy_country_code		= get_post_meta($id,'_wpcloudy_country_code',true);
			$wpcloudy_unit 				= get_bypass_unit($attr,$content);
			$wpcloudy_lang				= get_bypass_lang($attr,$content);
			$wpcloudy_map_height		= get_bypass_map_height($attr,$content);
			$wpcloudy_map_opacity		= get_bypass_map_opacity($attr,$content);
			$wpcloudy_map_zoom			= get_bypass_map_zoom($attr,$content);
			$wpcloudy_map_stations		= get_bypass_map_layers_stations($attr,$content);
			$wpcloudy_map_clouds		= get_bypass_map_layers_clouds($attr,$content);
			$wpcloudy_map_precipitation	= get_bypass_map_layers_precipitation($attr,$content);
			$wpcloudy_map_snow			= get_bypass_map_layers_snow($attr,$content);
			$wpcloudy_map_wind			= get_bypass_map_layers_wind($attr,$content);
			$wpcloudy_map_temperature	= get_bypass_map_layers_temperature($attr,$content);
			$wpcloudy_map_pressure		= get_bypass_map_layers_pressure($attr,$content);
			$wpcloudy_meta_border_color	= get_bypass_color_border($attr,$content);
			$wpcloudy_meta_bg_color		= get_bypass_color_background($attr,$content);
			$wpcloudy_meta_text_color	= get_bypass_color_text($attr,$content);
			$wpcloudy_sunrise_sunset	= get_bypass_display_sunrise_sunset($attr,$content);
	
			switch ($wpcloudy_lang) {
				case "fr":
					$wpcloudy_lang_owm = 'fr';
					$wpcloudy_lang_host = 'french';
					break;
				case "en":
					$wpcloudy_lang_owm = 'en';
					$wpcloudy_lang_host = 'english';
					break;
				case "ru":
					$wpcloudy_lang_owm = 'ru';
					$wpcloudy_lang_host = 'russian';
					break;
				case "it":
					$wpcloudy_lang_owm = 'it';
					$wpcloudy_lang_host = 'italian';
					break;
				case "sp":
					$wpcloudy_lang_owm = 'sp';
					$wpcloudy_lang_host = 'spanish';
					break;
				case "ua":
					$wpcloudy_lang_owm = 'ua';
					$wpcloudy_lang_host = 'ukrainian';
					break;
				case "de":
					$wpcloudy_lang_owm = 'de';
					$wpcloudy_lang_host = 'german';
					break;
				case "pt":
					$wpcloudy_lang_owm = 'pt';
					$wpcloudy_lang_host = 'portuguese';
					break;
				case "ro":
					$wpcloudy_lang_owm = 'ro';
					$wpcloudy_lang_host = 'romanian';
					break;
				case "pl":
					$wpcloudy_lang_owm = 'pl';
					$wpcloudy_lang_host = 'polish';
					break;
				case "fi":
					$wpcloudy_lang_owm = 'fi';
					$wpcloudy_lang_host = 'finnish';
					break;
				case "nl":
					$wpcloudy_lang_owm = 'nl';
					$wpcloudy_lang_host = 'dutch';
					break;
				case "bg":
					$wpcloudy_lang_owm = 'bg';
					$wpcloudy_lang_host = 'bulgarian';
					break;
				case "se":
					$wpcloudy_lang_owm = 'se';
					$wpcloudy_lang_host = 'swedish';
					break;
				case "zh_tw":
					$wpcloudy_lang_owm = 'zh_tw';
					$wpcloudy_lang_host = 'chinese_china';
					break;
				case "zh_cn":
					$wpcloudy_lang_owm = 'zh_cn';
					$wpcloudy_lang_host = 'chinese_taiwan';
					break;
				case "tr":
					$wpcloudy_lang_owm = 'tr';
					$wpcloudy_lang_host = 'turkish';
					break;
			}


			//$myweather = simplexml_load_string(get_transient( 'myweather' ));
	
			//if ( false === $myweather || '' === $myweather ){
				
		
				$myweather_current		= simplexml_load_file("http://api.openweathermap.org/data/2.5/weather?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=46c433f6ba7dd4d29d5718dac3d7f035&lang=$wpcloudy_lang_owm");
	
				$myweather				= simplexml_load_file("http://api.openweathermap.org/data/2.5/forecast/weather?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=46c433f6ba7dd4d29d5718dac3d7f035&lang=$wpcloudy_lang_owm");
				//set_transient( 'myweather', $myweather->asXML(), 10 * MINUTE_IN_SECONDS );
			//}
	
			//$myweather_sevendays = simplexml_load_string(get_transient( 'myweather_sevendays' ));
	
			//if ( false === $myweather_sevendays || '' === $myweather_sevendays ){
				
				$myweather_sevendays	= simplexml_load_file("http://api.openweathermap.org/data/2.5/forecast/daily?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&cnt=7&APPID=46c433f6ba7dd4d29d5718dac3d7f035&lang=$wpcloudy_lang_owm&cnt=14");
				//set_transient( 'myweather_sevendays', $myweather_sevendays->asXML(), 10 * MINUTE_IN_SECONDS );
			//}
			
			setlocale(LC_TIME, "$wpcloudy_lang_host");
			
			$location_name 			= $myweather_current->city[0][name];	
			$location_latitude 		= $myweather_current->city[0]->coord[0]['lat'];
			$location_longitude 	= $myweather_current->city[0]->coord[0]['lon'];
			$time_symbol 			= $myweather_current->weather[0]['value'];
			$time_symbol_number		= $myweather_current->weather[0]['number'];
			$time_wind_direction 	= $myweather_current->wind[0]->direction[0]['code'];
			$time_wind_speed 		= $myweather_current->wind[0]->speed[0]['value'];
			$time_humidity 			= $myweather_current->humidity[0]['value'];
			$time_pressure 			= $myweather_current->pressure[0]['value'];
			$time_cloudiness		= $myweather_current->clouds[0]['value'];
			$time_temperature		= (round($myweather_current->temperature[0]['value']));
			$time_temperature_min 	= (round($myweather_current->temperature[0]['min']));
			$time_temperature_max 	= (round($myweather_current->temperature[0]['max']));

			$sun_rise 				= (string)date("h:i", strtotime($myweather_current->city[0]->sun[0]['rise']));
			$sun_set 				= (string)date("h:i", strtotime($myweather_current->city[0]->sun[0]['set']));		
			
			$today_day				= strftime("%A", strtotime($myweather_current->lastupdate[0]['value']));
			
			$hour_temp_0			= (round($myweather->forecast[0]->time[0]->temperature[0]['value']));
			$hour_symbol_0			= $myweather->forecast[0]->time[0]->symbol[0]['name'];
			$hour_symbol_number_0	= $myweather->forecast[0]->time[0]->symbol[0]['number'];
			
			$hour_time_1			= date("H A", strtotime($myweather->forecast[0]->time[1]['from']));
			$hour_temp_1			= (round($myweather->forecast[0]->time[1]->temperature[0]['value']));
			$hour_symbol_1			= $myweather->forecast[0]->time[1]->symbol[0]['name'];
			$hour_symbol_number_1	= $myweather->forecast[0]->time[1]->symbol[0]['number'];
			
			$hour_time_2			= date("H A", strtotime($myweather->forecast[0]->time[2]['from']));
			$hour_temp_2			= (round($myweather->forecast[0]->time[2]->temperature[0]['value']));
			$hour_symbol_2			= $myweather->forecast[0]->time[2]->symbol[0]['name'];
			$hour_symbol_number_2	= $myweather->forecast[0]->time[2]->symbol[0]['number'];
			
			$hour_time_3			= date("H A", strtotime($myweather->forecast[0]->time[3]['from']));
			$hour_temp_3			= (round($myweather->forecast[0]->time[3]->temperature[0]['value']));
			$hour_symbol_3			= $myweather->forecast[0]->time[3]->symbol[0]['name'];
			$hour_symbol_number_3	= $myweather->forecast[0]->time[3]->symbol[0]['number'];
			
			$hour_time_4			= date("H A", strtotime($myweather->forecast[0]->time[4]['from']));
			$hour_temp_4			= (round($myweather->forecast[0]->time[4]->temperature[0]['value']));
			$hour_symbol_4			= $myweather->forecast[0]->time[4]->symbol[0]['name'];
			$hour_symbol_number_4	= $myweather->forecast[0]->time[4]->symbol[0]['number'];
			
			$hour_time_5			= date("H A", strtotime($myweather->forecast[0]->time[5]['from']));
			$hour_temp_5			= (round($myweather->forecast[0]->time[5]->temperature[0]['value']));
			$hour_symbol_5			= $myweather->forecast[0]->time[5]->symbol[0]['name'];
			$hour_symbol_number_5	= $myweather->forecast[0]->time[5]->symbol[0]['number'];

			$forecast_day_1			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[1]['day']));
			$forecast_number_1		= $myweather_sevendays->forecast[0]->time[1]->symbol[0]['number'];
			$forecast_temp_min_1	= (round($myweather_sevendays->forecast[0]->time[1]->temperature[0]['min']));
			$forecast_temp_max_1	= (round($myweather_sevendays->forecast[0]->time[1]->temperature[0]['max']));	
			
			$forecast_day_2			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[2]['day']));
			$forecast_number_2		= $myweather_sevendays->forecast[0]->time[2]->symbol[0]['number'];
			$forecast_temp_min_2	= (round($myweather_sevendays->forecast[0]->time[2]->temperature[0]['min']));
			$forecast_temp_max_2	= (round($myweather_sevendays->forecast[0]->time[2]->temperature[0]['max']));
			
			$forecast_day_3			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[3]['day']));
			$forecast_number_3		= $myweather_sevendays->forecast[0]->time[3]->symbol[0]['number'];
			$forecast_temp_min_3	= (round($myweather_sevendays->forecast[0]->time[3]->temperature[0]['min']));
			$forecast_temp_max_3	= (round($myweather_sevendays->forecast[0]->time[3]->temperature[0]['max']));
			
			$forecast_day_4			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[4]['day']));
			$forecast_number_4		= $myweather_sevendays->forecast[0]->time[4]->symbol[0]['number'];
			$forecast_temp_min_4	= (round($myweather_sevendays->forecast[0]->time[4]->temperature[0]['min']));
			$forecast_temp_max_4	= (round($myweather_sevendays->forecast[0]->time[4]->temperature[0]['max']));
			
			$forecast_day_5			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[5]['day']));
			$forecast_number_5		= $myweather_sevendays->forecast[0]->time[5]->symbol[0]['number'];
			$forecast_temp_min_5	= (round($myweather_sevendays->forecast[0]->time[5]->temperature[0]['min']));
			$forecast_temp_max_5	= (round($myweather_sevendays->forecast[0]->time[5]->temperature[0]['max']));
			
			$forecast_day_6			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[6]['day']));
			$forecast_number_6		= $myweather_sevendays->forecast[0]->time[6]->symbol[0]['number'];
			$forecast_temp_min_6	= (round($myweather_sevendays->forecast[0]->time[6]->temperature[0]['min']));
			$forecast_temp_max_6	= (round($myweather_sevendays->forecast[0]->time[6]->temperature[0]['max']));	
	
			$forecast_day_7			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[7]['day']));
			$forecast_number_7		= $myweather_sevendays->forecast[0]->time[7]->symbol[0]['number'];
			$forecast_temp_min_7	= (round($myweather_sevendays->forecast[0]->time[7]->temperature[0]['min']));
			$forecast_temp_max_7	= (round($myweather_sevendays->forecast[0]->time[7]->temperature[0]['max']));
	
			$forecast_day_8			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[8]['day']));
			$forecast_number_8		= $myweather_sevendays->forecast[0]->time[8]->symbol[0]['number'];
			$forecast_temp_min_8	= (round($myweather_sevendays->forecast[0]->time[8]->temperature[0]['min']));
			$forecast_temp_max_8	= (round($myweather_sevendays->forecast[0]->time[8]->temperature[0]['max']));
	
			$forecast_day_9			= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[9]['day']));
			$forecast_number_9		= $myweather_sevendays->forecast[0]->time[9]->symbol[0]['number'];
			$forecast_temp_min_9	= (round($myweather_sevendays->forecast[0]->time[9]->temperature[0]['min']));
			$forecast_temp_max_9	= (round($myweather_sevendays->forecast[0]->time[9]->temperature[0]['max']));
	
			$forecast_day_10		= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[10]['day']));
			$forecast_number_10		= $myweather_sevendays->forecast[0]->time[10]->symbol[0]['number'];
			$forecast_temp_min_10	= (round($myweather_sevendays->forecast[0]->time[10]->temperature[0]['min']));
			$forecast_temp_max_10	= (round($myweather_sevendays->forecast[0]->time[10]->temperature[0]['max']));
	
			$forecast_day_11		= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[11]['day']));
			$forecast_number_11		= $myweather_sevendays->forecast[0]->time[11]->symbol[0]['number'];
			$forecast_temp_min_11	= (round($myweather_sevendays->forecast[0]->time[11]->temperature[0]['min']));
			$forecast_temp_max_11	= (round($myweather_sevendays->forecast[0]->time[11]->temperature[0]['max']));
	
			$forecast_day_12		= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[12]['day']));
			$forecast_number_12		= $myweather_sevendays->forecast[0]->time[12]->symbol[0]['number'];
			$forecast_temp_min_12	= (round($myweather_sevendays->forecast[0]->time[12]->temperature[0]['min']));
			$forecast_temp_max_12	= (round($myweather_sevendays->forecast[0]->time[12]->temperature[0]['max']));
	
			$forecast_day_13		= strftime("%A", strtotime($myweather_sevendays->forecast[0]->time[13]['day']));
			$forecast_number_13		= $myweather_sevendays->forecast[0]->time[13]->symbol[0]['number'];
			$forecast_temp_min_13	= (round($myweather_sevendays->forecast[0]->time[13]->temperature[0]['min']));
			$forecast_temp_max_13	= (round($myweather_sevendays->forecast[0]->time[13]->temperature[0]['max']));

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
		
			$display_now = '
				<div class="now">
					<div class="location_name">'. wpcloudy_city_name($wpcloudy_city_name, $wpcloudy_city) .'</div>		
					<div class="time_symbol climacon" style="fill:'. wpc_css_text_color($wpcloudy_meta_text_color) .'">'. $time_symbol_svg .'</div>
					<div class="time_temperature">'. $time_temperature .'&deg;</div>
				</div>
			';
			$display_weather = '
				<div class="short_condition">'. $time_symbol .'</div>
			';
			
				
	
			$display_today_min_max = '
				<div class="today">	
					<div class="day"><span class="wpc-highlight">'. $today_day .'</span> '. __( 'Today', 'wpcloudy' ) .'</div>
					'. display_today_sunrise_sunset($wpcloudy_sunrise_sunset, $sun_rise, $sun_set) .'
					<div class="time_temperature_min">'. $time_temperature_min .'</div>
					<div class="time_temperature_max"><span class="wpc-highlight">'. $time_temperature_max .'</span></div>
				</div>
			';
			$display_today_ave = '
				<div class="today">	
					<div class="day"><span class="wpc-highlight">'. $today_day .'</span> '. __( 'Today', 'wpcloudy' ) .'</div>
					'. display_today_sunrise_sunset($wpcloudy_sunrise_sunset, $sun_rise, $sun_set) .'
					<div class="time_temperature_ave"><span class="wpc-highlight">'.round($time_temperature_ave).'</span></div>
				</div>
			';
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
			$display_hours = '
				<div class="hours" style="border-color:'. $wpcloudy_meta_border_color .';">	
					<div class="first">
						<div class="hour"><span class="wpc-highlight">'. __( 'Now', 'wpcloudy' ) .'</span></div>
						<div class="symbol climacon w'. $hour_symbol_number_0 .'"><span>'. $hour_symbol_0 .'</span></div>
						<div class="temperature"><span class="wpc-highlight">'. $hour_temp_0 .'</span></div>
					</div>
					<div class="second">
						<div class="hour">'. $hour_time_1 .'</div>
						<div class="symbol climacon w'. $hour_symbol_number_1 .'"><span>'. $hour_symbol_1 .'</span></div>
						<div class="temperature">'. $hour_temp_1 .'</div>
					</div>
					<div class="third">
						<div class="hour">'. $hour_time_2 .'</div>
						<div class="symbol climacon w'. $hour_symbol_number_2 .'"><span>'. $hour_symbol_2 .'</span></div>
						<div class="temperature">'. $hour_temp_2 .'</div>
					</div>
					<div class="fourth">
						<div class="hour">'. $hour_time_3 .'</div>
						<div class="symbol climacon w'. $hour_symbol_number_3 .'"><span>'. $hour_symbol_3 .'</span></div>
						<div class="temperature">'. $hour_temp_3 .'</div>
					</div>
					<div class="fifth">
						<div class="hour">'. $hour_time_4 .'</div>
						<div class="symbol climacon w'. $hour_symbol_number_4 .'"><span>'. $hour_symbol_4 .'</span></div>
						<div class="temperature">'. $hour_temp_4 .'</div>
					</div>
					<div class="sixth">
						<div class="hour">'. $hour_time_5 .'</div>
						<div class="symbol climacon w'. $hour_symbol_number_5 .'"><span>'. $hour_symbol_5 .'</span></div>
						<div class="temperature">'. $hour_temp_5 .'</div>
					</div>
				</div>	
			';	
		
			$display_forecast_1 = '	
				<div class="first">
					<div class="day">'. $forecast_day_1 .'</div>
					<div class="symbol climacon w'. $forecast_number_1 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_1 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_1 .'</span></div>
				</div>
			';
			$display_forecast_2 = '	
				<div class="second">
					<div class="day">'. $forecast_day_2 .'</div>
					<div class="symbol climacon w'. $forecast_number_2 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_2 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_2 .'</span></div>
				</div>
			';
			$display_forecast_3 = '	
				<div class="third">
					<div class="day">'. $forecast_day_3 .'</div>
					<div class="symbol climacon w'. $forecast_number_3 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_3 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_3 .'</span></div>
				</div>
			';
			$display_forecast_4 = '	
				<div class="fourth">
					<div class="day">'. $forecast_day_4 .'</div>
					<div class="symbol climacon w'. $forecast_number_4 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_4 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_4 .'</span></div>
				</div>
			';
			$display_forecast_5 = '	
				<div class="fifth">
					<div class="day">'. $forecast_day_5 .'</div>
					<div class="symbol climacon w'. $forecast_number_5 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_5 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_5 .'</span></div>
				</div>
			';
			$display_forecast_6 = '	
				<div class="sixth">
					<div class="day">'. $forecast_day_6 .'</div>
					<div class="symbol climacon w'. $forecast_number_6 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_6 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_6 .'</span></div>
				</div>
			';
			$display_forecast_7 = '	
				<div class="seventh">
					<div class="day">'. $forecast_day_7 .'</div>
					<div class="symbol climacon w'. $forecast_number_7 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_7 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_7 .'</span></div>
				</div>
			';
			$display_forecast_8 = '	
				<div class="eighth">
					<div class="day">'. $forecast_day_8 .'</div>
					<div class="symbol climacon w'. $forecast_number_8 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_8 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_8 .'</span></div>
				</div>
			';
			$display_forecast_9 = '	
				<div class="ninth">
					<div class="day">'. $forecast_day_9 .'</div>
					<div class="symbol climacon w'. $forecast_number_9 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_9 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_9 .'</span></div>
				</div>
			';
			$display_forecast_10 = '	
				<div class="tenth">
					<div class="day">'. $forecast_day_10 .'</div>
					<div class="symbol climacon w'. $forecast_number_10 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_10 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_10 .'</span></div>
				</div>
			';
			$display_forecast_11 = '	
				<div class="eleventh">
					<div class="day">'. $forecast_day_11 .'</div>
					<div class="symbol climacon w'. $forecast_number_11 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_11 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_11 .'</span></div>
				</div>
			';
			$display_forecast_12 = '	
				<div class="twelfth">
					<div class="day">'. $forecast_day_12 .'</div>
					<div class="symbol climacon w'. $forecast_number_12 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_12 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_12 .'</span></div>
				</div>
			';
			$display_forecast_13 = '	
				<div class="thirteenth">
					<div class="day">'. $forecast_day_13 .'</div>
					<div class="symbol climacon w'. $forecast_number_13 .'"></div>
					<div class="temp_min">'. $forecast_temp_min_13 .'</div>
					<div class="temp_max"><span class="wpc-highlight">'. $forecast_temp_max_13 .'</span></div>
				</div>
			';

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
				
			$display_map = '
				<script type="text/javascript"
					src="http://maps.google.com/maps/api/js?sensor=true">
				</script>				
				<div id="wpc-map-container">	
					<div id="wpc-map" style="height: '. $wpcloudy_map_height .'px"></div>
				</div>
				<script type="text/javascript">
					window.onload = function init() {
						//Center of map
						var lat = '. $location_latitude .'; 
						var lon = '. $location_longitude .';
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
			$wpcloudy_temperature_min_max	=	get_bypass_temp($attr,$content);
			$wpcloudy_hour_forecast			=	get_bypass_display_hour_forecast($attr,$content);
			$wpcloudy_forecast				=	get_bypass_display_forecast($attr,$content);
			$wpcloudy_forecast_nd			=	get_bypass_forecast_nd($attr,$content);
			$wpcloudy_size					=	get_bypass_size($attr,$content);
			$wpcloudy_map 					= 	get_bypass_map($attr,$content);			
			$wpcloudy_skin 				    =   get_post_meta($id,'_wpcloudy_skin',true);
			
			$html = '
			<!-- WP Cloudy : WordPress weather plugin - http://wpcloudy.com/ -->
			<div id="wpc-weather" class="'. $wpcloudy_size .' '. $wpcloudy_skin .'" style="'. wpc_css_background($wpcloudy_meta_bg_color) .'; color:'. wpc_css_text_color($wpcloudy_meta_text_color) .';'. wpc_css_border($wpcloudy_meta_border_color) .'; font-family:'. wpc_css_webfont($attr,$content) .'">';
			
			if( $wpcloudy_current_weather ) {
				$html .= $display_now;
			}
			
			if( $wpcloudy_weather ) {
				$html .= $display_weather;
			}
	
			if( $wpcloudy_date_temp && $wpcloudy_temperature_min_max == yes ) {
				$html .= $display_today_min_max;
			}	
	
			if( $wpcloudy_date_temp && $wpcloudy_temperature_min_max == no ) {
				$html .= $display_today_ave;
			}				
			 
			if( $wpcloudy_wind || $wpcloudy_humidity || $wpcloudy_pressure || $wpcloudy_cloudiness ) {
				$html .= '<div class="infos">';
			}
			
			if( $wpcloudy_wind ) {
				$html .= $display_wind;
			}
			
			if( $wpcloudy_humidity ) {
				$html .= $display_humidity;
			} 
			
			if( $wpcloudy_pressure ) {
				$html .= $display_pressure;
			} 
			
			if( $wpcloudy_cloudiness ) {
				$html .= $display_cloudiness;
			} 
			
			if( $wpcloudy_wind || $wpcloudy_humidity || $wpcloudy_pressure || $wpcloudy_cloudiness ) {
				$html .= '</div>';
			}
			
			if( $wpcloudy_hour_forecast ) {
				$html .= $display_hours;
			} 

			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 1 ) {
				$html .= '<div class="forecast">'.$display_forecast_1.'</div>';
			}
			
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 2 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2.'</div>';
			}
			
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 3 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3.'</div>';
			}
			
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 4 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4.'</div>';
			}
			
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 5 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5.'</div>';
			}
			
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 6 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6.'</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 7 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7.'</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 8 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7 . $display_forecast_8.'</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 9 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7 . $display_forecast_8 . $display_forecast_9.'</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 10 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7 . $display_forecast_8 . $display_forecast_9 . $display_forecast_10. '</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 11 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7 . $display_forecast_8 . $display_forecast_9 . $display_forecast_10 . $display_forecast_11.'</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 12 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7 . $display_forecast_8 . $display_forecast_9 . $display_forecast_10 . $display_forecast_11 . $display_forecast_12.'</div>';
			}
			if( $wpcloudy_forecast && $wpcloudy_forecast_nd == 13 ) {
				$html .= '<div class="forecast">'.$display_forecast_1 . $display_forecast_2 . $display_forecast_3 . $display_forecast_4 . $display_forecast_5 . $display_forecast_6 . $display_forecast_7 . $display_forecast_8 . $display_forecast_9 . $display_forecast_10 . $display_forecast_11 . $display_forecast_12 . $display_forecast_13.'</div>';
			}
			
			if( $wpcloudy_map ) {
			
				wp_register_script("openlayers js", "http://openlayers.org/api/OpenLayers.js", array(), "1.0", false);
				wp_register_script("owm js", "http://openweathermap.org/js/OWM.OpenLayers.1.3.4.js", array(), "1.0", false);	
				wp_register_style("openlayers css", "http://openlayers.org/api/theme/default/style.css", array(), "1.0", false);
				wp_enqueue_script("openlayers js");			
				wp_enqueue_script("owm js");
				wp_enqueue_style("openlayers css"); 
				
				$html .= $display_map;
			}
			if ($display_custom_css) {
				$html .= $display_custom_css;
			}

		 $html .= '</div>';
		 return $html;
			 
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

function set_messages($messages) {
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

add_filter('post_updated_messages', 'set_messages' );


?>