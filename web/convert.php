<?php
require 'creds.php';

$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// User Units
$userunitqry = mysql_query("SHOW COLUMNS FROM userunit", $con) or die(mysql_error());
$userrows = array();
if (mysql_num_rows($userunitqry) > 0) {
    while ($row = mysql_fetch_assoc($userunitqry)) {
        if (0 === strpos($row['Field'], 'userUnit')) {
          $userrows[] = $row['Field'];
        }
    }
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
$defaultunitqry = mysql_query("SHOW COLUMNS FROM defaultunit", $con) or die(mysql_error());
$defaultrows = array();
if (mysql_num_rows($defaultunitqry) > 0) {
    while ($row = mysql_fetch_assoc($defaultunitqry)) {
        if (0 === strpos($row['Field'], 'defaultUnit')) {
          $defaultrows[] = $row['Field'];
        }
    }
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

// Close MySQL connection
mysql_close($con);
?>
