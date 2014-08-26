<?php

ini_set('memory_limit', '-1');
require ("./creds.php");
require ("./get_sessions.php");
require ("./get_columns.php");
require ("./plot.php");

foreach ($_GET as $key => $value) {
	if (! isset($_POST[$key]))
		$_POST[$key] = $value;
}

foreach ($_COOKIE as $key => $value) {
	if (! isset($_POST[$key]))
		$_POST[$key] = $value;
}

$_SESSION['recent_session_id'] = strval(max($sids));

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

if (! isset($_POST["id"])) die("no session_id");
if (! isset($_POST["lat"])) die("no latitude");
if (! isset($_POST["lng"])) die("no longitude");

$session_id = preg_replace('/\D/', '', $_POST['id']);
$lat = sprintf("%1.10f", doubleval($_POST["lat"]));
$lng = sprintf("%1.10f", doubleval($_POST["lng"]));

$speedinfo = mysql_query("
SELECT 
  abs( kff1005 - $lng ) + abs( kff1006 - $lat ) dist,
  kff1005, kff1006, time, kff1001, kc, kd
FROM $db_table
WHERE session=$session_id
ORDER BY dist ASC
LIMIT 1", $con) or die(mysql_error());

$speedinfo_array = mysql_fetch_array($speedinfo, MYSQL_ASSOC);
echo json_encode($speedinfo_array);
?>
