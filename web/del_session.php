<?php
//echo "<!-- Begin del_session.php at ".date("H:i:s", microtime(true))." -->\r\n";
// this page relies on being included from another page that has already connected to db

if (!isset($_SESSION)) { session_start(); }

if (isset($_POST["deletesession"])) {
    $deletesession = preg_replace('/\D/', '', $_POST['deletesession']);
}
elseif (isset($_GET["deletesession"])) {
    $deletesession = preg_replace('/\D/', '', $_GET['deletesession']);
}

if (isset($deletesession) && !empty($deletesession)) {
    $delresult = mysql_query("DELETE FROM $db_table
                          WHERE session=$deletesession;", $con) or die(mysql_error());

    mysql_free_result($delresult);

    $delresult = mysql_query("DELETE FROM $db_sessions_table
                          WHERE session=$deletesession;", $con) or die(mysql_error());

    mysql_free_result($delresult);
}
//echo "<!-- End del_session.php at ".date("H:i:s", microtime(true))." -->\r\n";
?>
