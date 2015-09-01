<?php
require_once("./creds.php");
require_once("./get_sessions.php");

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if (isset($_POST["mergesession"])) {
    $mergesession = preg_replace('/\D/', '', $_POST['mergesession']);
}
elseif (isset($_GET["mergesession"])) {
    $mergesession = preg_replace('/\D/', '', $_GET['mergesession']);
}

if (isset($_POST["mergesessionwith"])) {
    $mergesessionwith = preg_replace('/\D/', '', $_POST['mergesessionwith']);
}
elseif (isset($_GET["mergesessionwith"])) {
    $mergesessionwith = preg_replace('/\D/', '', $_GET['mergesessionwith']);
}

if (isset($mergesession) && !empty($mergesession) && isset($mergesessionwith) && !empty($mergesessionwith) ) {
    //Sessions to be merged must be direct neighbors. 'With' must be younger, thus have a lower array index in $sids
    $idx1 = array_search( $mergesession, $sids);
    $idx2 = array_search( $mergesessionwith, $sids);
    if($idx1 != ($idx2+1)) {
        die("Invalid sessions to be merged. Aborted.");
    }

    // Connect to Database
    $con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
    mysql_select_db($db_name, $con) or die(mysql_error());

    $mergeresult = mysql_query("UPDATE $db_table
                          SET session=$mergesession
                          WHERE session=$mergesessionwith;", $con) or die(mysql_error());

    mysql_free_result($mergeresult);
    mysql_close($con);

    //Show merged session
    $session_id = $mergesession;
}

?>
