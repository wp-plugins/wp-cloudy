<?php
/*
Plugin Name: WP Cloudy
Plugin URI: http://wpcloudy.com/
Description: WP Cloudy is a powerful weather plugin for WordPress, based on Open Weather Map API, using Custom Post Types and shortcodes, bundled with a ton of features.
Version: 1.0
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
    global $typenow;
    if( $typenow == 'wpc-weather' ) {
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
  $wpcloudy_size 					= get_post_meta($post->ID,'_wpcloudy_size',true);
  
  echo '<div id="wpcloudy-tabs">
			<ul>
				<li><a href="#tabs-1">Basic settings</a></li>
				<li><a href="#tabs-2">Display</a></li>
				<li><a href="#tabs-3">Advanced</a></li>
			</ul>
			
			<div id="tabs-1">
				<p>
					<label for="wpcloudy_city_meta">City</label>
					<input id="wpcloudy_city_meta" type="text" name="wpcloudy_city" value="'.$wpcloudy_city.'" />
				</p>
				<p>
					<label for="wpcloudy_country_meta">Country? (you can enter your country code as well as the country, in your own language, eg: "fr" or "france" or "francia"...)</label>
					<input id="wpcloudy_country_meta" type="text" name="wpcloudy_country_code" value="'.$wpcloudy_country_code.'" />
				</p>
				<p>
					<label for="unit_meta">Imperial or metric units?</label>
					<select name="wpcloudy_unit">
						<option ' . selected( 'imperial', $wpcloudy_unit, false ) . ' value="imperial">Imperial</option>
						<option ' . selected( 'metric', $wpcloudy_unit, false ) . ' value="metric">Metric</option>
					</select>
				</p>
				<p>
					<label for="wpcloudy_lang_meta">Display language (eg: english, french, spanish, german... Note: depends on the languages ​​supported by your webhost server.)</label>
					<input id="wpcloudy_lang_meta" type="text" name="wpcloudy_lang" value="'.$wpcloudy_lang.'" />
				</p>
			</div>
			<div id="tabs-2">
				<p>				
					<label for="wpcloudy_wind_meta">
						<input type="checkbox" name="wpcloudy_wind" id="wpcloudy_wind_meta" value="yes" '. checked( $wpcloudy_wind, 'yes', false ) .' />
							Wind?
					</label>
				</p>
				<p>
					<label for="wpcloudy_humidity_meta">
						<input type="checkbox" name="wpcloudy_humidity" id="wpcloudy_humidity_meta" value="yes" '. checked( $wpcloudy_humidity, 'yes', false ) .' />
							Humidity?
					</label>
				</p>
				<p>
					<label for="wpcloudy_pressure_meta">
						<input type="checkbox" name="wpcloudy_pressure" id="wpcloudy_pressure_meta" value="yes" '. checked( $wpcloudy_pressure, 'yes', false ) .' />
							Pressure?
					</label>
				</p>
				<p>
					<label for="wpcloudy_cloudiness_meta">
						<input type="checkbox" name="wpcloudy_cloudiness" id="wpcloudy_cloudiness_meta" value="yes" '. checked( $wpcloudy_cloudiness, 'yes', false ) .' />
							Cloudiness?
					</label>
				</p>
				<p>
					<label for="wpcloudy_hour_forecast_meta">
						<input type="checkbox" name="wpcloudy_hour_forecast" id="wpcloudy_hour_forecast_meta" value="yes" '. checked( $wpcloudy_hour_forecast, 'yes', false ) .' />
							Hour Forecast?
					</label>
				</p>
				<p>
					<label for="wpcloudy_temperature_min_max_meta">
						<input type="checkbox" name="wpcloudy_temperature_min_max" id="wpcloudy_temperature_min_max_meta" value="yes" '. checked( $wpcloudy_temperature_min_max, 'yes', false ) .' />
							Today date + Min-Max Temperatures?
					</label>
				</p>
				<p>
					<label for="wpcloudy_forecast_meta">
						<input type="checkbox" name="wpcloudy_forecast" id="wpcloudy_forecast_meta" value="yes" '. checked( $wpcloudy_forecast, 'yes', false ) .' />
							7-Day Forecast?
					</label>
				</p>
			</div>
			<div id="tabs-3">
				<p>
					<label for="wpcloudy_meta_bg_color2">Background color</label>
					<input name="wpcloudy_meta_bg_color" type="text" value="'. $wpcloudy_meta_bg_color .'" class="wpcloudy_meta_bg_color_picker" />
				</p>
				<p>
					<label for="wpcloudy_meta_txt_color2">Text color</label>
					<input name="wpcloudy_meta_txt_color" type="text" value="'. $wpcloudy_meta_txt_color .'" class="wpcloudy_meta_txt_color_picker" />
				</p>
				<p>
					<label for="wpcloudy_meta_border_color2">Border color</label>
					<input name="wpcloudy_meta_border_color" type="text" value="'. $wpcloudy_meta_border_color .'" class="wpcloudy_meta_border_color_picker" />
				</p>
				<p>
					<label for="size_meta">Weather size?</label>
					<select name="wpcloudy_size">
						<option ' . selected( 'small', $wpcloudy_size, false ) . ' value="small">Small</option>
						<option ' . selected( 'medium', $wpcloudy_size, false ) . ' value="medium">Medium</option>
						<option ' . selected( 'large', $wpcloudy_size, false ) . ' value="large">Large</option>
					</select>
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
	if(isset($_POST['wpcloudy_size'])) {
	  update_post_meta($post_id, '_wpcloudy_size', $_POST['wpcloudy_size']);
	}
}


///////////////////////////////////////////////////////////////////////////////////////////////////
//Add shortcode Weather
///////////////////////////////////////////////////////////////////////////////////////////////////

add_shortcode("wpc-weather", "wpcloudy_display_weather");

function wpcloudy_display_weather($attr,$content) {

    extract(shortcode_atts(array(

               'id' => ''

                   ), $attr));

			$wpcloudy_city 				= get_post_meta($id,'_wpcloudy_city',true);
			$wpcloudy_country_code		= get_post_meta($id,'_wpcloudy_country_code',true);
			$wpcloudy_unit 				= get_post_meta($id,'_wpcloudy_unit',true);
			$wpcloudy_lang 				= get_post_meta($id,'_wpcloudy_lang',true);
			
			$wpcloudy_meta_border_color	= get_post_meta($id,'_wpcloudy_meta_border_color',true);
			
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
		
		
			$display_now = '
				<div class="now">
					<div class="location_name">'. $location_name .'</div>		
					<div class="time_symbol climacon w'. $time_symbol_number .'"><span>'. $time_symbol .'</span></div>
					<div class="time_temperature">'. $time_temperature .'°</div>
				</div>
			';
			$display_today = '
				<div class="today">	
					<div class="day"><span class="highlight">'. $today_day .'</span> Today</div>
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
			
			$wpcloudy_wind 					= 	get_post_meta($id,'_wpcloudy_wind',true);
			$wpcloudy_humidity				= 	get_post_meta($id,'_wpcloudy_humidity',true);
			$wpcloudy_pressure				= 	get_post_meta($id,'_wpcloudy_pressure',true);
			$wpcloudy_cloudiness			= 	get_post_meta($id,'_wpcloudy_cloudiness',true);
			$wpcloudy_temperature_min_max	=	get_post_meta($id,'_wpcloudy_temperature_min_max',true);
			$wpcloudy_hour_forecast			=	get_post_meta($id,'_wpcloudy_hour_forecast',true);
			$wpcloudy_forecast				=	get_post_meta($id,'_wpcloudy_forecast',true);
			$wpcloudy_meta_bg_color			=	get_post_meta($id,'_wpcloudy_meta_bg_color',true);
			$wpcloudy_meta_txt_color		=	get_post_meta($id,'_wpcloudy_meta_txt_color',true);
			$wpcloudy_size					=	get_post_meta($id,'_wpcloudy_size',true);
			
			 echo '<div id="wpc-weather" class="'. $wpcloudy_size .'" style="background:'. $wpcloudy_meta_bg_color .';color:'. $wpcloudy_meta_txt_color .';">';

				echo $display_now;
				
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

			 echo '</div>';
}

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
		'name'                => _x( 'Weather', 'Post Type General Name', 'wpcloudy_weather' ),
		'singular_name'       => _x( 'Weather', 'Post Type Singular Name', 'wpcloudy_weather' ),
		'menu_name'           => __( 'Weather', 'wpcloudy_weather' ),
		'parent_item_colon'   => __( 'Parent Weather:', 'wpcloudy_weather' ),
		'all_items'           => __( 'All Weather', 'wpcloudy_weather' ),
		'view_item'           => __( 'View Weather', 'wpcloudy_weather' ),
		'add_new_item'        => __( 'Add New Weather', 'wpcloudy_weather' ),
		'add_new'             => __( 'New Weather', 'wpcloudy_weather' ),
		'edit_item'           => __( 'Edit Weather', 'wpcloudy_weather' ),
		'update_item'         => __( 'Update Weather', 'wpcloudy_weather' ),
		'search_items'        => __( 'Search Weather', 'wpcloudy_weather' ),
		'not_found'           => __( 'No weather found', 'wpcloudy_weather' ),
		'not_found_in_trash'  => __( 'No weather found in Trash', 'wpcloudy_weather' ),
	);

	$args = array(
		'label'               => __( 'weather', 'wpcloudy_weather' ),
		'description'         => __( 'Listing weather', 'wpcloudy_weather' ),
		'labels'              => $labels,
		'supports'            => array( 'title', ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'menu_icon' 		  => plugins_url( 'wpcloudy/img/icon-admin-wpc.png' , dirname(__FILE__) ),
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