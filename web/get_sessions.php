<?php
//echo "<!-- Begin get_sessions.php at ".date("H:i:s", microtime(true))." -->\r\n";
// this page relies on being included from another page that has already connected to db

session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
if (!isset($_SESSION)) { session_start(); }

// Process the possibilities for the year and month filter: Set in POST, Set in GET, select all possible year/months, or the default: select the current year/month
if ( isset($_POST["selyearmonth"]) ) {
	$filteryearmonth = $_POST["selyearmonth"];
} elseif ( isset($_GET["yearmonth"])) {
	$filteryearmonth = $_GET["yearmonth"];
} else {
	$filteryearmonth = date('Y_m');
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


// Build the MySQL select string based on the inputs (year_month or session id)
$sessionqrystring = "SELECT timestart, timeend, session, profileName, sessionsize FROM $db_sessions_table ";
$sqlqryyearmonth = "CONCAT(YEAR(FROM_UNIXTIME(session/1000)), '_', DATE_FORMAT(FROM_UNIXTIME(session/1000),'%m')) LIKE " . quote_value($filteryearmonth) . " ";
$sqlqryprofile = "profileName LIKE " . quote_value($filterprofile) . " " ;
$orselector = "WHERE ";
$andselector = "";
if ( $filteryearmonth <> "%" || $filterprofile <> "%") {
	$orselector = " OR ";
	$sessionqrystring = $sessionqrystring . "WHERE ( ";
	if ( $filteryearmonth <> "%" ) {
		$sessionqrystring = $sessionqrystring . $sqlqryyearmonth;
		$andselector = " AND ";
	}
	if ( $filterprofile <> "%" ) {
		$sessionqrystring = $sessionqrystring . $andselector . $sqlqryprofile;
	}
	$sessionqrystring = $sessionqrystring . " ) ";
}
if ( isset($_GET['id'])) {
	$sessionqrystring = $sessionqrystring . $orselector . "( session LIKE " . quote_value($_GET['id']) . " )";
}
$sessionqrystring = $sessionqrystring . " GROUP BY session, profileName, timestart, timeend, sessionsize ORDER BY session DESC";
// Get list of unique session IDs
$sessionqry = mysqli_query($con, $sessionqrystring) or die(mysqli_error($con));

// If you get no results, just pull the last 20
if ( mysqli_num_rows( $sessionqry ) == 0 ) {
	$sessionqry = mysqli_query($con, "SELECT timestart, timeend, session, profileName, sessionsize FROM $db_sessions_table GROUP BY session, profileName, timestart, timeend, sessionsize ORDER BY session DESC LIMIT 20") or die(mysqli_error($con));
}

// Create an array mapping session IDs to date strings
$seshdates = array();
$seshsizes = array();
$seshprofile = array();
while($row = mysqli_fetch_assoc($sessionqry)) {
    $session_duration_str = gmdate("H:i:s", ($row["timeend"] - $row["timestart"])/1000);
    $session_profileName = $row["profileName"];
    $session_size = $row["sessionsize"];

    // Do not show sessions smaller than $min_session_size
    if ($session_size >= $min_session_size) {
        $sid = $row["session"];
        $sids[] = preg_replace('/\D/', '', $sid);
        $seshdates[$sid] = date("F d, Y  h:ia", substr($sid, 0, -3));
        $seshsizes[$sid] = " (Length $session_duration_str)";
        $seshprofile[$sid] = " ($session_profileName Profile)"; 
    }
    else {}
}

mysqli_free_result($sessionqry);
//echo "<!-- End get_sessions.php at ".date("H:i:s", microtime(true))." -->\r\n";

?>
