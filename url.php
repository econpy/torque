<?php
session_start();

// Get the Full URL to the session.php file
$thisfile = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parts = strtok("url.php", $thisfile);
if (isset($_GET["makechart"])) {
    $baselink = $parts["0"]."plot.php";
    if (isset($_POST["chartidtag"])) {
        $seshid = strval(mysql_escape_string($_POST["chartidtag"]));
        parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $queries);
        if (array_key_exists("s1", $queries) and array_key_exists("s2", $queries)) {
            $s1data = $queries["s1"];
            $s2data = $queries["s2"];
            $outurl = $baselink."?sid=".$seshid."&s1=".$s1data."&s2=".$s2data;
        }
        else {
            $outurl = $baselink."?sid=".$seshid;
        }
    }
    else {
        $seshid = $_SESSION['recent_session_id'];
        $outurl = $baselink."?sid=".$seshid;
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
