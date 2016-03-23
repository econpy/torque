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

// Process the 4 possibilities for the year filter: Set in POST, Set in GET, select all possible years, or the default: select the current year
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

// Process the 4 possibilities for the month filter: Set in POST, Set in GET, select all possible months, or the default: select the current month
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

// Process the 4 possibilities for the profile filter: Set in POST, Set in GET, select all possible profiles, or no filter as default
if ( isset($_POST["selprofile"]) ) {
	$filterprofile = $_POST["selprofile"];
} elseif ( isset($_GET["profile"])) {
	$filterprofile = $_GET["profile"];
} else {
	$filterprofile = "%";
}
if ( $filterprofile == "ALL" ) {
	$filterprofile = "%";
}

// Build the MySQL select string based on the inputs (year, month, or session id)
$sessionqrystring = "SELECT timestart as `MinTime`, timeend as `MaxTime`, session, profileName, sessionsize FROM $db_sessions_table ";
$sqlqryyear = "YEAR(FROM_UNIXTIME(session/1000)) LIKE '" . $filteryear . "' ";
$sqlqrymonth = "MONTHNAME(FROM_UNIXTIME(session/1000)) LIKE '" . $filtermonth . "' ";
$sqlqryprofile = "profileName LIKE '" . $filterprofile . "' ";
$orselector = "WHERE ";
$andselector = "";
if ( $filteryear <> "%" || $filtermonth <> "%" || $filterprofile <> "%") {
	$orselector = " OR ";
	$sessionqrystring = $sessionqrystring . "WHERE ( ";
	if ( $filteryear <> "%" ) {
		$sessionqrystring = $sessionqrystring . $sqlqryyear;
		$andselector = " AND ";
	}
	if ( $filtermonth <> "%" ) {
		$sessionqrystring = $sessionqrystring . $andselector . $sqlqrymonth;
		$andselector = " AND ";
	}
	if ( $filterprofile <> "%" ) {
		$sessionqrystring = $sessionqrystring . $andselector . $sqlqryprofile;
	}
	$sessionqrystring = $sessionqrystring . " ) ";
}
if ( isset($_GET['id'])) {
	$sessionqrystring = $sessionqrystring . $orselector . "( session LIKE '" . $_GET['id'] . "' )";
}
$sessionqrystring = $sessionqrystring . " GROUP BY session ORDER BY time DESC";
echo $sessionqrystring;
// Get list of unique session IDs
$sessionqry = mysql_query($sessionqrystring, $con) or die(mysql_error());

// Create an array mapping session IDs to date strings
$seshdates = array();
$seshsizes = array();
$seshprofile = array();
while($row = mysql_fetch_assoc($sessionqry)) {
    $session_size = $row["sessionsize"];
    $session_duration = $row["MaxTime"] - $row["MinTime"];
    $session_duration_str = gmdate("H:i:s", $session_duration/1000);
    $session_profileName = $row["profileName"];

    // Drop sessions smaller than 60 data points
    if ($row["sessionsize"] >= 60) {
        $sid = $row["session"];
        $sids[] = preg_replace('/\D/', '', $sid);
        $seshdates[$sid] = date("F d, Y  h:ia", substr($sid, 0, -3));
        $seshsizes[$sid] = " (Length $session_duration_str)";
        $seshprofile[$sid] = " ($session_profileName Profile)"; 
    }
    else {}
}

mysql_free_result($sessionqry);
mysql_close($con);

?>
