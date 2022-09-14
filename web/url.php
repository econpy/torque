<?php
session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
if (!isset($_SESSION)) { session_start(); }
require("./db.php");
//$con = mysqli_connect($db_host, $db_user, $db_pass,$db_name,$db_port) or die(mysqli_error($con));

// Get the Full URL to the session.php file
$thisfile = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parts = strtok("url.php", $thisfile);
// Capture the session ID we're going to be working with
if (isset($_GET["seshid"])) {
	$seshid = strval(mysqli_escape_string($con, $_GET["seshid"]));
} elseif (isset($_POST["seshidtag"])) {
	$seshid = strval(mysqli_escape_string($con, $_POST["seshidtag"]));
} elseif (isset($_GET["id"])) {
	$seshid = $_GET["id"];
} else {
	$seshid = $_SESSION['recent_session_id'];
}

$baselink = $parts[0]."session.php";
$outurl = $baselink."?id=".$seshid;

// Capture the profile we will be working with
if (isset($_POST["selprofile"])) {
	if ($_POST["selprofile"]) {
		$outurl = $outurl."&profile=".$_POST["selprofile"];
	}
} elseif (isset($_GET["profile"])) {
	if ($_GET["profile"]) {
		$outurl = $outurl."&profile=".$_GET["profile"];
	}
}

// Capture the year/month we will be working with
if (isset($_POST["selyearmonth"])) {
	if ($_POST["selyearmonth"]) {
		$outurl = $outurl."&yearmonth=".$_POST["selyearmonth"];
	}
} elseif (isset($_GET["yearmonth"])) {
	if ($_GET["yearmonth"]) {
		$outurl = $outurl."&yearmonth=".$_GET["yearmonth"];
	}
}

//Capture the start and end time
if (isset($_POST["svdata"])) {
$timestartval = explode(',',$_POST["svdata"]);        
$outurl = $outurl."&tsval=$timestartval[0]&teval=$timestartval[1]";
} 
elseif (isset($_GET['tsval']) && isset($_GET['teval'])) {
 $outurl = $outurl."&tsval=".$_GET['tsval']."&teval=".$_GET['teval'];     
}

//If we're gonna be making a graph, capture the variable IDs
if (isset($_GET["makechart"])) {
    if (isset($_POST["plotdata"])) {
        $plotdataarray = $_POST["plotdata"];
		$i = 1;
		while( isset($plotdataarray[$i-1]) && $plotdataarray[$i-1] <> "Plot!" ) {
            ${'s' . $i . 'data'} = $plotdataarray[$i-1];
			$outurl = $outurl."&s$i=${'s' . $i . 'data'}";
			$i = $i + 1;
		}
    }
}

header("Location: ".$outurl);
?>
