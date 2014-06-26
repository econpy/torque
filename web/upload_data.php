<?php
require("./creds.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// Create an array of all the existing fields in the database
$result = mysql_query("SHOW COLUMNS FROM raw_logs");
if (mysql_num_rows($result) > 0) {
    while ($row = mysql_fetch_assoc($result)) {
        $dbfields[]=($row['Field']);
    }
}

// Iterate over all the k* _GET arguments to check that a field exists
foreach ($_GET as $key => $value) {
    // Keep columns starting with k
    if (preg_match("/^k/", $key)) {
        $keys[] = $key;
        $values[] = $value;
        $submitval = 1;
    }
    // Skip columns matching userUnit*, defaultUnit*, and profile*
    else if (preg_match("/^userUnit/", $key) or preg_match("/^defaultUnit/", $key) or (preg_match("/^profile/", $key) and (!preg_match("/^profileName/", $key)))) {
        $submitval = 0;
    }
    // Keep anything else
    else {
        $keys[] = $key;
        $values[] = "'".$value."'";
        $submitval = 1;
    }
    // If the field doesn't already exist, add it to the database
    if (!in_array($key, $dbfields) and $submitval == 1) {
        mysql_query("ALTER TABLE $db_table ADD $key VARCHAR(255) NOT NULL default '0';");// || print mysql_error()."\n";
    }
}

// Now insert the data for all the fields
$sql = "INSERT INTO $db_table (".join(",", $keys).") VALUES (".join(",", $values).")";
mysql_query($sql);

// Return the response required by Torque
print "OK!";

?>
