<?php
require("./creds.php");

session_start();
$timezone = ini_get('date.timezone');

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// Get list of unique session IDs
$sessionqry = mysql_query("SELECT COUNT(*) as `Session Size`, session
                      FROM $db_table
                      GROUP BY session
                      ORDER BY time DESC", $con) or die(mysql_error());

// Create an array mapping session IDs to date strings
$seshdates = array();
while($row = mysql_fetch_assoc($sessionqry)) {
    $session_size = $row["Session Size"];
    // Drop sessions smaller than 60 data points
    if ($session_size >= 10) {
        $sid = $row["session"];
        $sids[] = preg_replace('/\D/', '', $sid);
        $seshdates[$sid] = date("Y-m-d D h:ia", substr($sid, 0, -3));
    }
    else {}
}

mysql_free_result($sessionqry);
mysql_close($con);

?>
