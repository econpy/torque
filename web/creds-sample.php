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
