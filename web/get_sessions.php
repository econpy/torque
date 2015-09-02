<?php
require_once("./creds.php");

session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
if ( isset($_SESSION['time'] ) ) {
	$timezone = $_SESSION['time'];
}
// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

if ( isset($_POST["selyear"]) ) {
	$filteryear = $_POST["selyear"];
} elseif ( isset($_GET["year"])) {
	$filteryear = $_GET["year"];
} else {
	$filteryear = date('Y');
}
if ( $filteryear == "ALL" ) {
	$filteryear = "%";
}

if ( isset($_POST["selmonth"]) ) {
	$filtermonth = $_POST["selmonth"];
} elseif ( isset($_GET["month"])) {
	$filtermonth = $_GET["month"];
} else {
	$filtermonth = date('F');
}
if ( $filtermonth == "ALL" ) {
	$filtermonth = "%";
}

$orselector = "";
$sessionqrystring = "SELECT COUNT(*) as `Session Size`, MIN(time) as `MinTime`, MAX(time) as `MaxTime`, session FROM $db_table WHERE";
if ( $filteryear <> "ALL" ) {
	$sessionqrystring = $sessionqrystring . "( ";
	$orselector = " OR ";
	$sessionqrystring = $sessionqrystring . "YEAR(FROM_UNIXTIME(session/1000)) LIKE '" . $filteryear . "' ";
	if ( $filtermonth <> "ALL" ) {
		$sessionqrystring = $sessionqrystring . "AND MONTHNAME(FROM_UNIXTIME(session/1000)) LIKE '" . $filtermonth . "' ";
	}
	$sessionqrystring = $sessionqrystring . " )";
} elseif ( $filtermonth <> "ALL" ) {
	$sessionqrystring = $sessionqrystring . "( MONTHNAME(FROM_UNIXTIME(session/1000)) LIKE '" . $filtermonth . "' )";
	$orselector = " OR ";
}
if ( isset($_GET['id'])) {
	$sessionqrystring = $sessionqrystring . $orselector . "( session LIKE '" . $_GET['id'] . "' )";
}
$sessionqrystring = $sessionqrystring . " GROUP BY session ORDER BY time DESC";
// Get list of unique session IDs
$sessionqry = mysql_query($sessionqrystring, $con) or die(mysql_error());

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
