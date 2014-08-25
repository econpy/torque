<?php
require 'creds.php';

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// Create an array of all the existing fields in the raw_logs table
$result1 = mysql_query("SHOW COLUMNS FROM raw_logs", $con) or die(mysql_error());
if (mysql_num_rows($result1) > 0) {
    while ($row = mysql_fetch_assoc($result1)) {
        $rawlog_fields[]=($row['Field']);
    }
}
mysql_free_result($result1);

// Create an array of all the existing fields in the profile table
$result2 = mysql_query("SHOW COLUMNS FROM profile", $con) or die(mysql_error());
if (mysql_num_rows($result2) > 0) {
    while ($row = mysql_fetch_assoc($result2)) {
        $profile_fields[]=($row['Field']);
    }
}
mysql_free_result($result2);

// Create an array of all the existing fields in the defaultunit table
$result3 = mysql_query("SHOW COLUMNS FROM defaultunit", $con) or die(mysql_error());
if (mysql_num_rows($result3) > 0) {
    while ($row = mysql_fetch_assoc($result3)) {
        $defaultunit_fields[]=($row['Field']);
    }
}
mysql_free_result($result3);

// Create an array of all the existing fields in the userunit table
$result4 = mysql_query("SHOW COLUMNS FROM userunit", $con) or die(mysql_error());
if (mysql_num_rows($result4) > 0) {
    while ($row = mysql_fetch_assoc($result4)) {
        $userunit_fields[]=($row['Field']);
    }
}
mysql_free_result($result4);

// Iterate over all the k* _GET arguments to check that a field exists
if (sizeof($_GET) > 0) {
    $pid_keys = array();
    $pid_values = array();
    $userid_keys = array();
    $userid_values = array();
    $profile_keys = array();
    $profile_values = array();
    $userUnit_keys = array();
    $userUnit_values = array();
    $defaultUnit_keys = array();
    $defaultUnit_values = array();
    foreach ($_GET as $key => $value) {
        // Keep columns starting with k
        if (preg_match("/^k/", $key)) {
            $pid_keys[] = mysql_real_escape_string($key);
            $pid_values[] = mysql_real_escape_string($value);
            $submitval = 1;
        }
        else if (in_array($key, array("v", "eml", "time", "id", "session"))) {
            $userid_keys[] = mysql_real_escape_string($key);
            $userid_values[] = "'".mysql_real_escape_string($value)."'";
        }
        else if (preg_match("/^profile/", $key)) {
            $profile_keys[] = mysql_real_escape_string($key);
            $profile_values[] = "'".mysql_real_escape_string($value)."'";
            $submitval = 2;
        }
        else if (preg_match("/^userUnit/", $key)) {
            $userUnit_keys[] = mysql_real_escape_string($key);
            $userUnit_values[] = "'".mysql_real_escape_string($value)."'";
            $submitval = 3;
        }
        else if (preg_match("/^defaultUnit/", $key)) {
            $defaultUnit_keys[] = mysql_real_escape_string($key);
            $defaultUnit_values[] = "'".mysql_real_escape_string($value)."'";
            $submitval = 4;
        }
        else {
            $submitval = 0;
        }
        if (!in_array($key, $rawlog_fields) and $submitval == 1) {
            $sqlalter = "ALTER TABLE $db_table ADD $key VARCHAR(255) NOT NULL default '0'";
            $alterqry = mysql_query($sqlalter, $con) or die(mysql_error());
            mysql_free_result($alterqry);
        }
        else if (!in_array($key, $profile_fields) and $submitval == 2) {
            $sqlalter = "ALTER TABLE profile ADD $key VARCHAR(255) NOT NULL default '0'";
            $alterqry = mysql_query($sqlalter, $con) or die(mysql_error());
            mysql_free_result($alterqry);
        }
        else if (!in_array($key, $userunit_fields) and $submitval == 3) {
            $sqlalter = "ALTER TABLE userunit ADD $key VARCHAR(255) NOT NULL default '0'";
            $alterqry = mysql_query($sqlalter, $con) or die(mysql_error());
            mysql_free_result($alterqry);
        }
        else if (!in_array($key, $defaultunit_fields) and $submitval == 4) {
            $sqlalter = "ALTER TABLE defaultunit ADD $key VARCHAR(255) NOT NULL default '0'";
            $alterqry = mysql_query($sqlalter, $con) or die(mysql_error());
            mysql_free_result($alterqry);
        }
    }
    // Insert data for raw log key/value pairs
    if ((sizeof($pid_keys) === sizeof($pid_values)) && sizeof($pid_keys) > 0) {
        if ((sizeof($userid_keys) === sizeof($userid_values)) && sizeof($userid_keys) > 0) {
            for($i = 0; $i<count($userid_keys); $i++) {
                $pid_keys[] = $userid_keys[$i];
                $pid_values[] = $userid_values[$i];
            }
        }
        $sql = "INSERT INTO $db_table (".implode(",", $pid_keys).") VALUES (".implode(",", $pid_values).")";
        $qry = mysql_query($sql, $con) or die(mysql_error());
        mysql_free_result($qry);
    }
    // Insert data for profile key/value pairs
    else if ((sizeof($profile_keys) === sizeof($profile_values)) && sizeof($profile_keys) > 0) {
        if ((sizeof($userid_keys) === sizeof($userid_values)) && sizeof($userid_keys) > 0) {
            for($i = 0; $i<count($userid_keys); $i++) {
                $profile_keys[] = $userid_keys[$i];
                $profile_values[] = $userid_values[$i];
            }
        }
        $sql = "INSERT INTO profile (".implode(",", $profile_keys).") VALUES (".implode(",", $profile_values).")";
        $qry = mysql_query($sql, $con) or die(mysql_error());
        mysql_free_result($qry);
    }
    // Insert data for userUnit key/value pairs
    else if ((sizeof($userUnit_keys) === sizeof($userUnit_values)) && sizeof($userUnit_keys) > 0) {
        if ((sizeof($userid_keys) === sizeof($userid_values)) && sizeof($userid_keys) > 0) {
            for($i = 0; $i<count($userid_keys); $i++) {
                $userUnit_keys[] = $userid_keys[$i];
                $userUnit_values[] = $userid_values[$i];
            }
        }
        $sql = "INSERT INTO userunit (".implode(",", $userUnit_keys).") VALUES (".implode(",", $userUnit_values).")";
        $qry = mysql_query($sql, $con) or die(mysql_error());
        mysql_free_result($qry);
    }
    // Insert data for defaultUnit key/value pairs
    else if ((sizeof($defaultUnit_keys) === sizeof($defaultUnit_values)) && sizeof($defaultUnit_keys) > 0) {
        if ((sizeof($userid_keys) === sizeof($userid_values)) && sizeof($userid_keys) > 0) {
            for($i = 0; $i<count($userid_keys); $i++) {
                $defaultUnit_keys[] = $userid_keys[$i];
                $defaultUnit_values[] = $userid_values[$i];
            }
        }
        $sql = "INSERT INTO defaultunit (".implode(",", $defaultUnit_keys).") VALUES (".implode(",", $defaultUnit_values).")";
        $qry = mysql_query($sql, $con) or die(mysql_error());
        mysql_free_result($qry);
    }
}

mysql_close($con);

// Return the response required by Torque
echo "OK!";

?>
