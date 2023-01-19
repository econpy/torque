<?php

//echo "<!-- Begin creds.php -->\r\n";
// MySQL Credentials
$db_host = 'localhost';
$db_user = '';     // Enter your MySQL username
$db_pass = '';     // Enter your MySQL password
$db_port  = '3306';    // Enter yout MySql PORT
$db_name = 'torque';
$db_table = 'raw_logs';
$db_keys_table = 'torque_keys';
$db_sessions_table = 'sessions';
$gmapsApiKey = ''; // OPTIONAL: Create a key at https://developers.google.com/maps/documentation/javascript/
$mapboxApiKey = '';  // OPTIONAL: Create a key at https://account.mapbox.com/auth/signup/
$tomtomApiKey = '';  // OPTIONAL: Create a key at https://developer.tomtom.com/user/register
$thunderforestApiKey = ''; // OPTIONAL: Create a key at https://www.thunderforest.com/pricing/
$hereApiKey = '';  // OPTIONAL: Create a key at https://account.here.com/sign-up
$maptilerApiKey = ''; // OPTIONAL: Create a key at https://cloud.maptiler.com/auth/widget

// OPTIONAL set the URI here for your HomeAssistant instance; format: https://SERVER_HOME_ASSITANT:PORT/api/torque Leave blank if unused
$uri_homeassistant = '';
// OPTIONAL Uncomment and paste the bearer token from Home Assistant here to use it directly, rather than pulling it from the Torque App's web request
//$token_homeassistant = '';

//Map options
$mapProvider = 'esri'; // google,esri,stamen,openstreetmap,mapbox,tomtom,thunderforest,here,maptiler
$mapStyleSelect = 'Streets'; //provider specific see README e.g. roadmap for google, Streets for esri, not used for openstreetmap

// Array of user credentials for Browser login
$users = array();
// $users[] = array("user" => "torque", "pass" => "open");      // Sample: 'torque' / 'open'
// $users[] = array("user" => "second", "pass" => "mypass");    // Add additional strings for more users

//If you want to restrict access to upload_data.php, 
// either enter your torque ID as shown in the torque app, 
// or enter the hashed ID as it can found in the uploaded data.
//The hash is simply MD5(ID).
//Leave empty to allow any torque app to upload data to this server.
$torque_id = '';        //Sample: 123456789012345
$torque_id_hash = '';   //Sample: 58b9b9268acaef64ac6a80b0543357e6
//Just 'settings', could be moved to a config file later.
$source_is_fahrenheit = false;
$use_fahrenheit = false;

$source_is_miles = false;
$use_miles = false;

$hide_empty_variables = true;
$show_session_length = true;

#Sessions less than limit will not be shown
$min_session_size = 20;

//echo "<!-- End creds.php -->\r\n";
?>
