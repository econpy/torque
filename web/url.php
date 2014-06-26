<?php
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
            $s1data = $plotdataarray[0];
            $s2data = $plotdataarray[1];
            $outurl = $baselink."?id=".$seshid."&s1=".$s1data."&s2=".$s2data;
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
