<?php
/*
Plugin Name: WP Cloudy
Plugin URI: http://wpcloudy.com/
Description: WP Cloudy is a powerful weather plugin for WordPress, based on Open Weather Map API, using Custom Post Types and shortcodes, bundled with a ton of features.
Version: 2.2.2
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
//Admin panel
///////////////////////////////////////////////////////////////////////////////////////////////////

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/wpcloudy-admin.php';

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

add_action('wp_enqueue_scripts', 'wpcloudy_styles');

function wpcloudy_styles() {

    wp_register_style('wpcloudy', plugins_url('css/wpcloudy.css', __FILE__));
    wp_enqueue_style('wpcloudy');
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Loads the JS/CSS in admin
///////////////////////////////////////////////////////////////////////////////////////////////////
add_action( 'admin_enqueue_scripts', 'wpcloudy_admin_enqueue' );

function wpcloudy_admin_enqueue() {
	if(is_admin()){
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'color-picker-js', plugins_url('js/color-picker.js', __FILE__), array( 'wp-color-picker' ) );
		wp_register_style('wpcloudy-admin', plugins_url('css/wpcloudy-admin.css', __FILE__));
		wp_enqueue_style( 'wpcloudy-admin' );
		wp_enqueue_script( 'tabs-js', plugins_url( 'js/tabs.js', __FILE__ ), array( 'jquery-ui-tabs' ) );
    }
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
	echo "[wpc-weather id=\"";
	echo get_the_ID();
	echo "\"/]";
	echo "</div>";
	
	echo '<div class="shortcode-php">';
	_e( 'If you need to display this weather anywhere in your theme, simply copy and paste this code snippet in your PHP file like sidebar.php: ', 'wpcloudy' );
	echo "<span class='highlight'>echo do_shortcode('[wpc-weather id=\"YOUR_ID\"]');</span>";
	echo "</div>";
}

function wpcloudy_basic($post){
  $wpcloudy_city 					= get_post_meta($post->ID,'_wpcloudy_city',true);
  $wpcloudy_country_code 			= get_post_meta($post->ID,'_wpcloudy_country_code',true);
  $wpcloudy_unit 					= get_post_meta($post->ID,'_wpcloudy_unit',true);
  $wpcloudy_lang 					= get_post_meta($post->ID,'_wpcloudy_lang',true);
  $wpcloudy_current_weather			= get_post_meta($post->ID,'_wpcloudy_current_weather',true);
  $wpcloudy_weather					= get_post_meta($post->ID,'_wpcloudy_weather',true);
  $wpcloudy_wind 					= get_post_meta($post->ID,'_wpcloudy_wind',true);
  $wpcloudy_humidity 				= get_post_meta($post->ID,'_wpcloudy_humidity',true);
  $wpcloudy_pressure				= get_post_meta($post->ID,'_wpcloudy_pressure',true);
  $wpcloudy_cloudiness				= get_post_meta($post->ID,'_wpcloudy_cloudiness',true);
  $wpcloudy_hour_forecast			= get_post_meta($post->ID,'_wpcloudy_hour_forecast',true);
  $wpcloudy_temperature_min_max		= get_post_meta($post->ID,'_wpcloudy_temperature_min_max',true);
  $wpcloudy_forecast				= get_post_meta($post->ID,'_wpcloudy_forecast',true);
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
					<label for="wpcloudy_lang_meta">'. __( 'Display language: english, french, spanish, german etc. ', 'wpcloudy' ) .'</label>
					<input id="wpcloudy_lang_meta" type="text" name="wpcloudy_lang" value="'.$wpcloudy_lang.'" />
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
				<p>
					<label for="wpcloudy_temperature_min_max_meta">
						<input type="checkbox" name="wpcloudy_temperature_min_max" id="wpcloudy_temperature_min_max_meta" value="yes" '. checked( $wpcloudy_temperature_min_max, 'yes', false ) .' />
							'. __( 'Today date + Min-Max Temperatures?', 'wpcloudy' ) .'
					</label>
				</p>
				<p>
					<label for="wpcloudy_forecast_meta">
						<input type="checkbox" name="wpcloudy_forecast" id="wpcloudy_forecast_meta" value="yes" '. checked( $wpcloudy_forecast, 'yes', false ) .' />
							'. __( '7-Day Forecast?', 'wpcloudy' ) .'
					</label>
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
		update_post_meta( $post_id, '_wpcloudy_temperature_min_max', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_temperature_min_max', '' );
	}
	if( isset( $_POST[ 'wpcloudy_forecast' ] ) ) {
		update_post_meta( $post_id, '_wpcloudy_forecast', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpcloudy_forecast', '' );
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
		$wpc_unit_value = get_post_meta($id,'_wpcloudy_meta_unit',true);
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

function get_admin_display_temperature_min_max() {
	$wpc_admin_display_temperature_min_max_option = get_option("wpc_option_name");

	if ( ! empty ( $wpc_admin_display_temperature_min_max_option ) ) {
		foreach ($wpc_admin_display_temperature_min_max_option as $key => $wpc_admin_display_temperature_min_max_value)
			$options[$key] = $wpc_admin_display_temperature_min_max_value;
		return $wpc_admin_display_temperature_min_max_option['wpc_display_temperature_min_max'];
	}
};

function get_display_temperature_min_max($attr,$content) {
		extract(shortcode_atts(array( 'id' => ''), $attr));
		$wpc_display_temperature_min_max_value = get_post_meta($id,'_wpcloudy_temperature_min_max',true);
		return $wpc_display_temperature_min_max_value;
};

function get_bypass_display_temperature_min_max($attr,$content) {
	if (get_admin_display_temperature_min_max()) {
		return get_admin_display_temperature_min_max(); 
	}
	else {
		return get_display_temperature_min_max($attr,$content);
	}
}

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
}

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
}

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
}

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
}

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
}

//Bypass Zoom Map
function get_admin_bypass_map_zoom() {
	$wpc_admin_bypass_map_zoom_option = get_option("wpc_option_name");
	if ( ! empty ( $wpc_admin_bypass_map_zoom_option ) ) {
		foreach ($wpc_admin_bypass_map_zoom_option as $key => $wpc_admin_bypass_map_zoom_value)
			$options[$key] = $wpc_admin_bypass_map_zoom_value;
		return $wpc_admin_bypass_map_zoom_option['wpc_map_zoom'];
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
}

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
}

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
}

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
}	

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
}

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
}

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
}

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
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Styles CSS
///////////////////////////////////////////////////////////////////////////////////////////////////

function wpc_css_background($wpcloudy_meta_bg_color) {
	if( $wpcloudy_meta_bg_color ) {
			return 'background:'. $wpcloudy_meta_bg_color;
	}
}
function wpc_css_text_color($wpcloudy_meta_text_color) {
	if( $wpcloudy_meta_text_color ) {
			return 'color:'. $wpcloudy_meta_text_color;
	}
}
function wpc_css_border($wpcloudy_meta_border_color) {
	if( $wpcloudy_meta_border_color ) {
			return 'border:1px solid '. $wpcloudy_meta_border_color;
	}
}
			
///////////////////////////////////////////////////////////////////////////////////////////////////
//Add shortcode Weather
///////////////////////////////////////////////////////////////////////////////////////////////////

add_shortcode("wpc-weather", "wpcloudy_display_weather");

function wpcloudy_display_weather($attr,$content) {

		extract(shortcode_atts(array( 'id' => ''), $attr));

			$wpcloudy_city 				= get_post_meta($id,'_wpcloudy_city',true);
			$wpcloudy_country_code		= get_post_meta($id,'_wpcloudy_country_code',true);
			$wpcloudy_unit 				= get_bypass_unit($attr,$content);
			$wpcloudy_lang 				= get_post_meta($id,'_wpcloudy_lang',true);
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
			
			$myweather 				= simplexml_load_file("http://api.openweathermap.org/data/2.5/forecast?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&APPID=46c433f6ba7dd4d29d5718dac3d7f035");
			$myweather_sevendays 	= simplexml_load_file("http://api.openweathermap.org/data/2.5/forecast/daily?q=$wpcloudy_city,$wpcloudy_country_code&mode=xml&units=$wpcloudy_unit&cnt=7&APPID=46c433f6ba7dd4d29d5718dac3d7f035");
			
			setlocale(LC_TIME, "$wpcloudy_lang");
			
			$location_name 			= $myweather->location[0]->name;	
			$location_latitude 		= $myweather->location[0]->location[0]['latitude'];
			$location_longitude 	= $myweather->location[0]->location[0]['longitude'];
			$time_symbol 			= $myweather->forecast[0]->time[0]->symbol[0]['name'];
			$time_symbol_number		= $myweather->forecast[0]->time[0]->symbol[0]['number'];
			$time_wind_direction 	= $myweather->forecast[0]->time[0]->windDirection[0]['code'];
			$time_wind_speed 		= $myweather->forecast[0]->time[0]->windSpeed[0]['mps'];
			$time_humidity 			= $myweather->forecast[0]->time[0]->humidity[0]['value'];
			$time_pressure 			= $myweather->forecast[0]->time[0]->pressure[0]['value'];
			$time_cloudiness		= $myweather->forecast[0]->time[0]->clouds[0]['all'];
			$time_temperature		= (round($myweather->forecast[0]->time[0]->temperature[0]['value']));
			$time_temperature_min 	= (round($myweather->forecast[0]->time[0]->temperature[0]['min']));
			$time_temperature_max 	= (round($myweather->forecast[0]->time[0]->temperature[0]['max']));
			$sun_rise 				= date("h:m", strtotime($myweather->sun[0]['rise']));
			$sun_set 				= date("h:m", strtotime($myweather->sun[0]['set']));		
			
			$today_day				= date("l", strtotime($myweather->meta[0]->lastupdate));
			
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
		
			$wpcloudy_custom_css	= get_post_meta($id,'_wpcloudy_custom_css',true);
			
			$display_custom_css 	= '
				<style>
					'. $wpcloudy_custom_css .'
				</style>
			';
		
			$display_now = '
				<div class="now">
					<div class="location_name">'. $location_name .'</div>		
					<div class="time_symbol climacon w'. $time_symbol_number .'"></div>
					<div class="time_temperature">'. $time_temperature .'&deg;</div>
				</div>
			';
			$display_weather = '
				<div class="short_condition">'. $time_symbol .'</div>
			';
			$display_today = '
				<div class="today">	
					<div class="day"><span class="highlight">'. $today_day .'</span> '. __( 'Today', 'wpcloudy' ) .'</div>
					<div class="time_temperature_min">'. $time_temperature_min .'</div>
					<div class="time_temperature_max"><span class="highlight">'. $time_temperature_max .'</span></div>
				</div>		
			';
			$display_wind = '
				<div class="wind">
					<div class="wind-direction">'. __( 'Wind', 'wpcloudy' ) .'<span class="highlight">'. $time_wind_direction .' '. $time_wind_speed .'</span></div>
				</div>
			';
			$display_humidity = '
				<div class="humidity">'. __( 'Humidity', 'wpcloudy' ) .'<span class="highlight">'. $time_humidity .' %</span></div>
			';
			$display_pressure = '
				<div class="pressure">'. __( 'Pressure', 'wpcloudy' ) .'<span class="highlight">'. $time_pressure .' hPa</span></div>
			';
			$display_cloudiness = '
				<div class="cloudiness">'. __( 'Cloudiness', 'wpcloudy' ) .'<span class="highlight">'. $time_cloudiness .' %</span></div>
			';			
			$display_hours = '
				<div class="hours" style="border-color:'. $wpcloudy_meta_border_color .';">	
					<div class="first">
						<div class="hour"><span class="highlight">'. __( 'Now', 'wpcloudy' ) .'</span></div>
						<div class="symbol climacon w'. $hour_symbol_number_0 .'"><span>'. $hour_symbol_0 .'</span></div>
						<div class="temperature"><span class="highlight">'. $hour_temp_0 .'</span></div>
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
			$display_forecast = '
				<div class="forecast">	
					<div class="first">
						<div class="day">'. $forecast_day_1 .'</div>
						<div class="symbol climacon w'. $forecast_number_1 .'"></div>
						<div class="temp_min">'. $forecast_temp_min_1 .'</div>
						<div class="temp_max"><span class="highlight">'. $forecast_temp_max_1 .'</span></div>
					</div>
					<div class="second">
						<div class="day">'. $forecast_day_2 .'</div>
						<div class="symbol climacon w'. $forecast_number_2 .'"></div>
						<div class="temp_min">'. $forecast_temp_min_2 .'</div>
						<div class="temp_max"><span class="highlight">'. $forecast_temp_max_2 .'</span></div>
					</div>
					<div class="third">
						<div class="day">'. $forecast_day_3 .'</div>
						<div class="symbol climacon w'. $forecast_number_3 .'"></div>
						<div class="temp_min">'. $forecast_temp_min_3 .'</div>
						<div class="temp_max"><span class="highlight">'. $forecast_temp_max_3 .'</span></div>
					</div>
					<div class="fourth">
						<div class="day">'. $forecast_day_4 .'</div>
						<div class="symbol climacon w'. $forecast_number_4 .'"></div>
						<div class="temp_min">'. $forecast_temp_min_4 .'</div>
						<div class="temp_max"><span class="highlight">'. $forecast_temp_max_4 .'</span></div>
					</div>
					<div class="fifth">
						<div class="day">'. $forecast_day_5 .'</div>
						<div class="symbol climacon w'. $forecast_number_5 .'"></div>
						<div class="temp_min">'. $forecast_temp_min_5 .'</div>
						<div class="temp_max"><span class="highlight">'. $forecast_temp_max_5 .'</span></div>
					</div>
					<div class="sixth">
						<div class="day">'. $forecast_day_6 .'</div>
						<div class="symbol climacon w'. $forecast_number_6 .'"></div>
						<div class="temp_min">'. $forecast_temp_min_6 .'</div>
						<div class="temp_max"><span class="highlight">'. $forecast_temp_max_6 .'</span></div>
					</div>
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
				'. wp_enqueue_script( "openlayers js", "http://openlayers.org/api/OpenLayers.js", array(), "1.0", true) .'
				'. wp_enqueue_script( "owm js", "http://openweathermap.org/js/OWM.OpenLayers.1.3.4.js", array(), "1.0", true) .'
				'. wp_register_style("openlayers css", "http://openlayers.org/api/theme/default/style.css", array(), "1.0", true) .'
				'. wp_enqueue_style("openlayers css") .'
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
			$wpcloudy_wind 					= 	get_bypass_display_wind($attr,$content);
			$wpcloudy_humidity				= 	get_bypass_display_humidity($attr,$content);
			$wpcloudy_pressure				= 	get_bypass_display_pressure($attr,$content);
			$wpcloudy_cloudiness			= 	get_bypass_display_cloudiness($attr,$content);
			$wpcloudy_temperature_min_max	=	get_bypass_display_temperature_min_max($attr,$content);
			$wpcloudy_hour_forecast			=	get_bypass_display_hour_forecast($attr,$content);
			$wpcloudy_forecast				=	get_bypass_display_forecast($attr,$content);
			$wpcloudy_size					=	get_bypass_size($attr,$content);
			$wpcloudy_map 					= 	get_bypass_map($attr,$content);
			
			if ($display_custom_css) {
				echo $display_custom_css;
			}			

				
			echo '<div id="wpc-weather" class="'. $wpcloudy_size .'" style="'. wpc_css_background($wpcloudy_meta_bg_color) .';'. wpc_css_text_color($wpcloudy_meta_text_color) .';'. wpc_css_border($wpcloudy_meta_border_color) .'">';
				
				if( $wpcloudy_current_weather ) {
					echo $display_now;
				}
				
				if( $wpcloudy_weather ) {
					echo $display_weather;
				}
				
				if( $wpcloudy_temperature_min_max ) {
					echo $display_today;
				}
				 
				if( $wpcloudy_wind || $wpcloudy_humidity || $wpcloudy_pressure || $wpcloudy_cloudiness ) {
					echo '<div class="infos">';
				}
				
				if( $wpcloudy_wind ) {
				   echo $display_wind;
				}
				
				if( $wpcloudy_humidity ) {
				   echo $display_humidity;
				} 
				
				if( $wpcloudy_pressure ) {
				   echo $display_pressure;
				} 
				
				if( $wpcloudy_cloudiness ) {
				   echo $display_cloudiness;
				} 
				
				if( $wpcloudy_wind || $wpcloudy_humidity || $wpcloudy_pressure || $wpcloudy_cloudiness ) {
					echo '</div>';
				}
				
				if( $wpcloudy_hour_forecast ) {
				   echo $display_hours;
				} 

				if( $wpcloudy_forecast ) {
				   echo $display_forecast;
				}
				
				if( $wpcloudy_map ) {
				   echo $display_map;
				}

			 echo '</div>';
			 
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
            echo "[wpc-weather id='$post_id' /]";
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
		'supports'            => array( 'title', ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'menu_icon' 		  => plugins_url( 'wp-cloudy/img/icon-admin-wpc.png' , dirname(__FILE__) ),
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