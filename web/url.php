<?php
session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
session_start();

// Get the Full URL to the session.php file
$thisfile = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parts = strtok("url.php", $thisfile);
if (isset($_GET["seshid"])) {
	$seshid = strval(mysql_escape_string($_GET["seshid"]));
} elseif (isset($_POST["seshidtag"])) {
	$seshid = strval(mysql_escape_string($_POST["seshidtag"]));
} elseif (isset($_GET["id"])) {
	$seshid = $_GET["id"];
} else {
	$seshid = $_SESSION['recent_session_id'];
}
$baselink = $parts["0"]."session.php";
$outurl = $baselink."?id=".$seshid;
if (isset($_POST["selyear"])) {
	if ($_POST["selyear"]) {
		$outurl = $outurl."&year=".$_POST["selyear"];
	}
} elseif (isset($_GET["year"])) {
	if ($_GET["year"]) {
		$outurl = $outurl."&year=".$_GET["year"];
	}
}
if (isset($_POST["selmonth"])) {
	if ($_POST["selmonth"] <> "") {
		$outurl = $outurl."&month=".$_POST["selmonth"];
	}
} elseif (isset($_GET["month"])) {
	if ($_GET["month"]) {
		$outurl = $outurl."&month=".$_GET["month"]; 
	}
}
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
