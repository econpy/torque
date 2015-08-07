<?php
session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
session_start();

// Get the Full URL to the session.php file
$thisfile = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parts = strtok("url.php", $thisfile);
if (isset($_GET["makechart"])) {
    $baselink = $parts["0"]."session.php";
    if (isset($_GET["seshid"])) {
        $seshid = strval(mysql_escape_string($_GET["seshid"]));
        if (isset($_POST["plotdata"])) {
            $plotdataarray = $_POST["plotdata"];
			// 2015.08.05 - Edit by surfrock66 - code to allow plotting up to 5 variables
            $outurl = $baselink."?id=".$seshid;
			if( isset($plotdataarray[0]) && $plotdataarray[0] <> "Plot!" ) {
	            $s1data = $plotdataarray[0];
				$outurl = $outurl."&s1=$s1data";
			}
			if( isset($plotdataarray[1]) && $plotdataarray[1] <> "Plot!" ) {
	            $s2data = $plotdataarray[1];
				$outurl = $outurl."&s2=$s2data";
			} 
			if( isset($plotdataarray[2]) && $plotdataarray[2] <> "Plot!" ) {
	            $s3data = $plotdataarray[2];
				$outurl = $outurl."&s3=$s3data";
			}
			if( isset($plotdataarray[3]) && $plotdataarray[3] <> "Plot!" ) {
	            $s4data = $plotdataarray[3];
				$outurl = $outurl."&s4=$s4data";
			}
			if( isset($plotdataarray[4]) && $plotdataarray[4] <> "Plot!" ) {
	            $s5data = $plotdataarray[4];
				$outurl = $outurl."&s5=$s5data";
			}
        }
        else {
            $seshid = $_SESSION['recent_session_id'];
            $outurl = $baselink."?id=".$seshid;
        }
    }
    else {
        $seshid = $_SESSION['recent_session_id'];
        $outurl = $baselink."?id=".$seshid;
    }
}
else {
    $baselink = $parts["0"]."session.php";
    if (isset($_POST["seshidtag"])) {
        $seshid = strval(mysql_escape_string($_POST["seshidtag"]));
        $outurl = $baselink."?id=".$seshid;
    }
    else {
        $seshid = $_SESSION['recent_session_id'];
        $outurl = $baselink."?id=".$seshid;
    }
}

header("Location: ".$outurl);

?>
