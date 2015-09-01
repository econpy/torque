<?php
require_once("./creds.php");

session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$timezone = $_SESSION['time'];
$sqlid = "%";
$sqlyearfilter = date('Y');
$sqlmonthfilter = date('F');
if (isset($_GET['id'])) {
	$sqlid = $_GET['id'];
}
if (isset($_POST['selyear'])) {
	$sqlyearfilter = $_POST['selyear'];
}
if (isset($_GET['year'])) {
	$sqlyearfilter = $_GET['year'];
}
if (isset($_POST['selmonth'])) {
	$sqlmonthfilter = $_POST['selmonth'];
}
if (isset($_GET['month'])) {
	$sqlmonthfilter = $_GET['month'];
}
// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// Get list of unique session IDs
$sessionqry = mysql_query("SELECT COUNT(*) as `Session Size`, MIN(time) as `MinTime`, MAX(time) as `MaxTime`, session
                      FROM $db_table
                      WHERE ( MONTHNAME(FROM_UNIXTIME(session/1000)) LIKE '$sqlmonthfilter' AND YEAR(FROM_UNIXTIME(session/1000)) LIKE '$sqlyearfilter' ) OR ( session LIKE '$sqlid' )
                      GROUP BY session
                      ORDER BY time DESC", $con) or die(mysql_error());

// Create an array mapping session IDs to date strings
$seshdates = array();
$seshsizes = array();
while($row = mysql_fetch_assoc($sessionqry)) {
    $session_size = $row["Session Size"];
    $session_duration = $row["MaxTime"] - $row["MinTime"];
    $session_duration_str = gmdate("H:i:s", $session_duration/1000);

    // Drop sessions smaller than 60 data points
    if ($session_size >= 60) {
        $sid = $row["session"];
        $sids[] = preg_replace('/\D/', '', $sid);
        $seshdates[$sid] = date("F d, Y  h:ia", substr($sid, 0, -3));
        $seshsizes[$sid] = " (Length $session_duration_str)";
    }
    else {}
}

mysql_free_result($sessionqry);
mysql_close($con);

?>
