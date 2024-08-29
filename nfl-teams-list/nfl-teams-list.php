<?php

/**
 * Plugin Name: NFL Teams List
 * Description: A simple plugin that allows you to access and search a list of NFL teams
 * Version: 1.0.0
 * Text Domain: options-plugin
 * Author: Nic Orton
 */


// security measure - stops people from being able to access this plugin file unless it's specfically the path WP uses to access plugins
if (!defined('ABSPATH')) {
    die('you cannot be here, please leave now');
}

if (!class_exists('nflPlugin')){
    class nflPlugin {
 
       public function __construct()
       {
          define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
       }
 
       public function initialize()
       { 
          include_once MY_PLUGIN_PATH . 'includes/script.js';
       }
 
 
    }
    
    $nflPlugin = new nflPlugin; // instantiate the class
    // $nflPlugin -> initialize(); // run the initialize method once class has been instantiated
 }

 function fetch_json_from_api($atts) {
   // Get the search query from the URL (if provided)
   $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

   $api_url = 'https://delivery.oddsandstats.co/team_list/NFL.JSON?api_key=74db8efa2a6db279393b433d97c2bc843f8e32b0';

   // Use wp_remote_get to fetch the data
   $response = wp_remote_get($api_url);

   // Check for errors
   if (is_wp_error($response)) {
       return 'Failed to retrieve data from API.';
   }

   // Get the body of the response
   $body = wp_remote_retrieve_body($response);

   // Decode the JSON string into an array
   $data = json_decode($body, true);

   // Ensure that 'results' and the necessary structure exist
   if (isset($data['results']) && is_array($data['results'])) {
       $filtered_data = array_filter($data['results'], function($item) use ($search_query) {
           return isset($item['data']['team']['name']) && stripos($item['data']['team']['name'], $search_query) !== false;
       });
   } else {
       $filtered_data = []; // Default to an empty array if the structure isn't as expected
   }

   // Re-encode the array with pretty print formatting
   $pretty_json = json_encode($filtered_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

   // Build the search form
   $search_form = '<form method="get" action="">
       <input type="text" name="search" value="' . esc_attr($search_query) . '" placeholder="Search by team name">
       <input type="submit" value="Search">
   </form>';

   // Return the search form and the formatted JSON content as a string
   return $search_form . '<pre>' . esc_html($pretty_json) . '</pre>';
}

add_shortcode('fetch_api_data', 'fetch_json_from_api');










?>