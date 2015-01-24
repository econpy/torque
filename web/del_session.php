<?php
require_once("./creds.php");

session_start();

if (isset($_POST["deletesession"])) {
    $deletesession = preg_replace('/\D/', '', $_POST['deletesession']);
}
elseif (isset($_GET["deletesession"])) {
    $deletesession = preg_replace('/\D/', '', $_GET['deletesession']);
}

if (isset($deletesession) && !empty($deletesession)) {
    // Connect to Database
    $con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
    mysql_select_db($db_name, $con) or die(mysql_error());

    $delresult = mysql_query("DELETE FROM $db_table
                          WHERE session=$deletesession;", $con) or die(mysql_error());

    mysql_free_result($delresult);
    mysql_close($con);
}

?>
