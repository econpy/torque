<?php
require_once ('creds.php');
require_once ('auth_functions.php');

//session.cookie_path = "/torque/";
session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME'])); 
if (!isset($_SESSION)) { session_start(); }

//This variable will be evaluated at the end of this file to check if a user is authenticated
$logged_in = false;

if (!isset($_SESSION['torque_logged_in'])) {
    $_SESSION['torque_logged_in'] = false;
}
$logged_in = (boolean)$_SESSION['torque_logged_in'];

//There are two ways to authenticate for Open Torque Viewer
//The uploading data provider running on Android uses its torque ID, while the User Interface uses User/Password.
//Which method will be chosed depends on the variable set before including this file
// Set "$auth_user_with_torque_id" for Authetification with ID
// Set "$auth_user_with_user_pass" for Authetification with User/Password
// Default is authentication with user/pass

if(!isset($auth_user_with_user_pass)) {
    $auth_user_with_user_pass = true;
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
    $auth_user_with_torque_id = false;
}

if (!$logged_in && $auth_user_with_torque_id)
{
    if ( auth_id() )
    {
        $session_id = get_id();
        $logged_in = true;
    }
}


$_SESSION['torque_logged_in'] = $logged_in;

if (!$logged_in) {
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Open Torque Viewer</title>
        <meta name="description" content="Open Torque Viewer">
        <meta name="author" content="Matt Nicklay">
        <!--<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">-->
        <link rel="stylesheet" href="static/css/bootstrap.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
        <link rel="stylesheet" href="static/css/torque.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
    </head>
    <body>
        <div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="auth_user.php">Open Torque Viewer</a>
                </div>
                <div id="map-container" class="col-md-7 col-xs-12">
                    &nbsp;
                </div>
                <div id="right-container" class="col-md-5 col-xs-12">
                    <div id="right-cell">

                        <h4>Login</h4>
                        <div class="row center-block" style="padding-bottom:4px;">
                            <form method="post" class="form-horizontal" role="form" action="session.php" id="formlogin">
                                <input class="btn btn-info btn-sm" type="text" name="user" value="" placeholder="(Username)" />
                                <input class="btn btn-info btn-sm" type="password" name="pass" value="" placeholder="(Password)" />
                                <input class="btn btn-info btn-sm" type="submit" id="formlogin" name="Login" value="Login" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<?php
    exit(0);
}
else
{
    //Prepare session
    
    //Connect to Sql, ...
}

?>
