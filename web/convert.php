<?php
require 'creds.php';

$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// User Units
$userunitqry = mysql_query("SELECT column_name AS userkey
                      FROM information_schema.columns
                      WHERE table_name='userunit'
                      AND column_name LIKE 'userUnit%'", $con) or die(mysql_error());
$userrows = array();
while($ur = mysql_fetch_assoc($userunitqry)) {
    $userrows[] = $ur['userkey'];
}
mysql_free_result($userunitqry);
$userunitlist = implode(",", $userrows);
$usersqlstr = 'SELECT '.$userunitlist.' from userunit';
$getuserunits = mysql_query($usersqlstr, $con);
$userData = array();
while($u = mysql_fetch_assoc($getuserunits)) {
    $userData[] = $u;
}
mysql_free_result($getuserunits);
$userData = $userData['0'];

// Default Units
$defaultunitqry = mysql_query("SELECT column_name AS defaultkey
                      FROM information_schema.columns
                      WHERE table_name='defaultunit'
                      AND column_name LIKE 'defaultUnit%'", $con) or die(mysql_error());
$defaultrows = array();
while($dr = mysql_fetch_assoc($defaultunitqry)) {
    $defaultrows[] = $dr['defaultkey'];
}
mysql_free_result($defaultunitqry);
$defaultunitlist = implode(",", $defaultrows);

$defaultsqlstr = 'SELECT '.$defaultunitlist.' from defaultunit';
$getdefaultunits = mysql_query($defaultsqlstr, $con);
$defaultData = array();
while($d = mysql_fetch_assoc($getdefaultunits)) {
    $defaultData[] = $d;
}
mysql_free_result($getdefaultunits);
$defaultData = $defaultData['0'];

mysql_close($con);
?>
