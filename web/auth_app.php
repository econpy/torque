<?php
require_once ('creds.php');
require_once ('auth_functions.php');

//This variable will be evaluated at the end of this file to check if a user is authenticated
$logged_in = false;


//Session makes no sense for the torque app, I assume it to have no cookie handling integrated
//session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
//session_start();

//if (!isset($_SESSION['torque_logged_in'])) {
//    $_SESSION['torque_logged_in'] = false;
//}
//$logged_in = (boolean)$_SESSION['torque_logged_in'];

//There are two ways to authenticate for Open Torque Viewer
//The uploading data provider running on Android transfers its torque ID, while the User Interface uses User/Password.
//Which method will be chosen depends on the variable set before including this file
// Set "$auth_user_with_torque_id" for Authetification with ID
// Set "$auth_user_with_user_pass" for Authetification with User/Password

// Default is authentication for App is the ID

if(!isset($auth_user_with_user_pass)) {
    $auth_user_with_user_pass = false;
}

if (!$logged_in && $auth_user_with_user_pass)
{
    if ( auth_user() ) {
        $logged_in = true;
    }
}

//ATTENTION:
//The Torque App has no way to provide other authentication information than its torque ID.
//So, if no restriction of Torque IDs was defined in "creds.php", access to the file "upload_data.php" is always possible.

if(!isset($auth_user_with_torque_id)) {
    $auth_user_with_torque_id = true;
}

if (!$logged_in && $auth_user_with_torque_id)
{
    if ( auth_id() )
    {
        $session_id = get_id();
        $logged_in = true;
    }
}



if (!$logged_in) {
    $txt  = "ERROR. Please authenticate with ";
    $txt .= ($auth_user_with_user_pass?"User/Password":"");
    $txt .= ( ($auth_user_with_user_pass && $auth_user_with_torque_id)?" or ":"");
    $txt .= ($auth_user_with_torque_id?"Torque-ID":"");
    echo $txt;
    exit(0);
}

?>