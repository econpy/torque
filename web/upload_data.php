<?php
require 'creds.php';

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// Create an array of all the existing fields in the database
$result = mysql_query("SHOW COLUMNS FROM raw_logs", $con) or die(mysql_error());
if (mysql_num_rows($result) > 0) {
    while ($row = mysql_fetch_assoc($result)) {
        $dbfields[]=($row['Field']);
    }
}

// Iterate over all the k* _GET arguments to check that a field exists
if (sizeof($_GET) > 0) {
    $keys = array();
    $values = array();
    foreach ($_GET as $key => $value) {
        // Keep columns starting with k
        if (preg_match("/^k/", $key)) {
            $keys[] = $key;
            $values[] = $value;
            $submitval = 1;
        }
        else if (in_array($key, array("v", "eml", "time", "id", "session"))) {
            $keys[] = $key;
            $values[] = "'".$value."'";
            $submitval = 1;
        }
        // Skip columns matching userUnit*, defaultUnit*, and profile*
        else if (preg_match("/^userUnit/", $key) or preg_match("/^defaultUnit/", $key) or (preg_match("/^profile/", $key) and (!preg_match("/^profileName/", $key)))) {
            $submitval = 0;
        }
        else {
            $submitval = 0;
        }
        // NOTE: Use the following "else" statement instead of the one above
        //       if you want to keep anything else.
        //else {
        //    $keys[] = $key;
        //    $values[] = "'".$value."'";
        //    $submitval = 1;
        //}
        // If the field doesn't already exist, add it to the database
        if (!in_array($key, $dbfields) and $submitval == 1) {
            $sqlalter = "ALTER TABLE $db_table ADD $key VARCHAR(255) NOT NULL default '0'";
            mysql_query($sqlalter, $con) or die(mysql_error());
        }
    }
    if ((sizeof($keys) === sizeof($values)) && sizeof($keys) > 0) {
        // Now insert the data for all the fields
        $sql = "INSERT INTO $db_table (".implode(",", $keys).") VALUES (".implode(",", $values).")";
        mysql_query($sql, $con) or die(mysql_error());
    }
}

mysql_close($con);

// Return the response required by Torque
echo "OK!";

?>
