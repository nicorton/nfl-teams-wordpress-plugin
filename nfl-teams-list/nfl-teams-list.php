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

 function fetch_and_search_teams() {
   // Get the search query from the URL parameters
   $search_query = isset($_GET['team_name']) ? sanitize_text_field($_GET['team_name']) : '';

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

   // Check if data is present
   if (empty($data['results']['data']['team'])) {
       return 'No team data found.';
   }

   // Initialize an empty string to hold the output
   $output = '<ul>';

   // Filter and build the output string based on the search query
   foreach ($data['results']['data']['team'] as $team) {
       if (stripos($team['name'], $search_query) !== false || stripos($team['nickname'], $search_query) !== false) {
           $output .= '<li>';
           $output .= '<strong>Team Name:</strong> ' . esc_html($team['name']) . '<br>';
           $output .= '<strong>Team Nickname:</strong> ' . esc_html($team['nickname']) . '<br>';
           $output .= '<strong>Conference:</strong> ' . esc_html($team['conference']) . '<br>';
           $output .= '<strong>Division:</strong> ' . esc_html($team['division']) . '<br>';
           $output .= '</li><br>';
       }
   }

   $output .= '</ul>';

   // If no teams match the search query
   if (empty($output)) {
       $output = 'No teams found matching your search query.';
   }

   // Add a search form above the results
   $form = '<form method="GET">
               <label for="team_name">Search for a team: </label>
               <input type="text" name="team_name" id="team_name" value="' . esc_attr($search_query) . '">
               <input type="submit" value="Search">
            </form><br>';

   // Return the search form and the filtered list
   return $form . $output;
}

add_shortcode('fetch_api_data', 'fetch_and_search_teams');



?>