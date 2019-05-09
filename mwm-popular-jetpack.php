<?php
/**
 * @package MWM_Popular_Jetpack
 * @version 1.0
 */
/*
Plugin Name: Most Popular Posts from Jetpack
Plugin URI: https://www.makeworthymedia.com/
Description: Adds the [popular-jetpack] shortcode which displays the most popular posts as tracked by the Jetpack plugin.
Author: Makeworthy Media
Version: 1.0
Author URI: https://www.makeworthymedia.com/
License: GPL2
*/

/*  Copyright 2019 Jennette Fulda  (email : contact@makeworthymedia.com)

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

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');


// Set Shortcode for [popular-jetpack]
function mwm_popular_jetpack_func( $atts = [], $content = null, $tag = '' ) {
    $a = shortcode_atts( array(
		'timespan' => 'all',
		'limit' => 10,
		'posttypes' => 'post',
		'cache' => 86400,
  		'ssl' => 0,
		'exclude' => '',
		'api_limit' => 30,
  ), $atts, $tag );
	
	$output = '';
	
	// Check that Jetpack is installed and activated
	if (function_exists('stats_get_csv')) {
		
		// Configure output settings and set defaults
		$timespan = 'all'; // options include: all, year, month, week, day
		$days = '-1';
		$list_limit = 10;
		$displayed_post_types = array('post');
		$cache_age_in_seconds = 86400; // 1 day
		//$cache_age_in_seconds = 3600; // 1 hour
		//$cache_age_in_seconds = 180;    // 3 minutes
		//$cache_age_in_seconds = 60;   // 1 minute
		//$cache_age_in_seconds = 10;   // 10 seconds
		$force_ssl_links = 0;
		$excluded_posts = array();
		$api_limit = 30; // How many results to return from Jetpack. Needs to be small number so results can be fetched quickly.
		
	
		// These arrays specify the user input we allow for these attributes.
		$allowed_timespans = array('all', 'year', 'month', 'week', 'day');
		
		// Set attributes inputted in shortcode
		if ( in_array($a['timespan'], $allowed_timespans) ) {
			$timespan = $a['timespan'];
		}
		if ( is_numeric($a['limit']) ) {
			$list_limit = $a['limit'];
		}
		if ($a['posttypes']) {
			$displayed_post_types = explode(',', $a['posttypes']);
		}
		if ($a['cache'] && is_numeric($a['cache']) ) {
			$cache_age_in_seconds = $a['cache'];
		}
		if ($a['exclude']) {
			$excluded_posts = explode(',', $a['exclude']);
		}
		if ($a['ssl'] == 1) {
			$force_ssl_links = 1;
		}
		if ( is_numeric($a['api_limit']) ) {
			$api_limit = $a['api_limit'];
		}
		
		// Make sure enough posts are pulled from the API to fill all the list positions
		if ($api_limit <= $list_limit) {
			$api_limit = $list_limit * 2;
		}
			
		// Start naming transient
		$transient_name = 'mwm-popular-jetpack';
		
		// Set timespan
		if ($timespan == 'all') {
			$transient_name .= '-all';
			$days = '-1';
		} elseif ($timespan == 'year') {
			$transient_name .= '-year';
			$days = '365';
		} elseif ($timespan == 'month') {
			$transient_name .= '-month';
			$days = '30';
		} elseif ($timespan == 'week') {
			$transient_name .= '-week';
			$days = '7';
		} elseif ($timespan == 'day') {
			$transient_name .= '-day';
			$days = '1';
		}	
			
		$transient_name .= '-limit' . $list_limit;
		$transient_name .= '-' . implode('-', $displayed_post_types);
		if ($excluded_posts[0]) {
			$transient_name .= '-excluded-' . implode('-', $excluded_posts);
		}
		$transient_name .= '-ssl-' . $force_ssl_links;
		$transient_name .= '-cache-' . $cache_age_in_seconds;
		
		
		$output .= '<!-- Timespan: ' . $timespan . ' -->';
		$output .= "\n";
	
		if ( !get_transient($transient_name) ) {

			// Get stats from JetPack
			/*
			$popular = stats_get_csv( 'postviews', array( 
				'days' => $days,
				'limit' => -1,
				) 
			);
			*/
			$api_key = akismet_get_key();
			if (!$api_key) {
				return '<p>You need to get an API key from Aksimet for this plugin to work. Look under Jetpack -> Akismet for more info.</p>';
			}
			
			$blog_id = Jetpack_Options::get_option( 'id' );
			
			$csv_url = sprintf('https://stats.wordpress.com/csv.php?api_key=%s&blog_id=%s&table=postviews&days=%s&limit=%s&format=json&summarize', $api_key, $blog_id, $days, $api_limit);
			
			$response = wp_remote_get( $csv_url );
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			$popular = $data[0]->postviews;
					
			$count = 0;
						
			if ($popular) {
				//$output .= '<!-- CSV URL: ' . $csv_url . ' -->';
				$output .= '<ul class="most-popular-jetpack">';
				foreach ($popular as $key => $object) {
					
					$postID = $object->post_id;
					$postTitle = $object->post_title;
					$postViews = $object->views;
					
					// If this is an excluded posts, skip and go to next result
					if (in_array($postID, $excluded_posts)) {
						continue;
					}
					
					// Check to see if post is in the allowed post types.
					$temp_args = array(
						'p' => $postID,
						'post_type' => $displayed_post_types,
					);
					
					$temp_query = new WP_Query($temp_args);
					$temp_count = '';
					$temp_count = $temp_query->post_count;
					
					// Post with the id of 0 is the home page of posts
					if ($temp_count > 0 && ($postID != 0) ) {
						
						$permalink = get_permalink($postID);
						
						// Force SSL links if requested
						if ($force_ssl_links) {
							$permalink = str_replace('http://', 'https://', $permalink);
						}
						
						// Output post title and link
						$output .= sprintf('<li><a href="%s">%s</a><!-- Views: %d --></li>', $permalink, $postTitle, $postViews, $postID );
						$count++;
						
						if ($count == $list_limit) {
							break;
						}
					}
					
					wp_reset_postdata();
				}
				$output .= '</ul>';
				
				set_transient( $transient_name, $output, $cache_age_in_seconds );
			} else {
				$output .= '<!-- The plugin hasn\'t collected enough data yet. -->';
			}

			$output .= '<!-- Data loaded via external query. -->';
			
		} else {
					
			$output = get_transient( $transient_name );
			$output .= '<!-- Data loaded from the transient. -->';
			
		} // END if file_exists or cached file has expired

	} else {
		$output .= '<p>Please install the JetPack plugin and enable "Site Stats" under the Traffic tab. It will take several hours before stats appear.</p>';
	} // END if function_exists('stats_get_csv')
	

	return $output;
}

function mwm_popular_jetpack_shortcodes_init() {
    add_shortcode( 'popular-jetpack', 'mwm_popular_jetpack_func' );
}
 
add_action('init', 'mwm_popular_jetpack_shortcodes_init');


