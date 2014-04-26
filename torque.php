<?php
require("./creds.php");

$link = mysql_connect($db_host, $db_user, $db_pass);
if (!$link) {
 die("Database connection error: ".mysql_error());
}
$db = mysql_select_db($db_name);
if (!$db) {
 die("Database error: ".mysql_error());
}

// Get all the fields from the data
$result = mysql_query("SHOW COLUMNS FROM $db_table");
if (mysql_num_rows($result) > 0) {
    while ($row = mysql_fetch_assoc($result)) {
        $dbfields[]=($row['Field']);
    }
}

// Go through the _GET args matching `k*` and ensure there is a field to
// insert the data into.
$keys = array();
$values = array();
foreach ($_GET as $key => $value) {
	$keys[] = $key;
	if (preg_match("/^k/", $key)) {
		$values[] = $value;
	} else {
		$values[] = "'".$value."'";
	}
	if (!in_array($key, $dbfields)) {
		// Add field if it doesn't exist.
		mysql_query("ALTER TABLE $db_table ADD $key VARCHAR(255) NOT NULL default '0';");
	}
}

// There are 2 incoming arrays:
//      (1) the fields($keys) array
//      (2) the data($values) array
// Put them into the db.
$sql = "INSERT INTO $db_table (" . join(",", $keys) . ") VALUES (" . join(",", $values) . ")";
mysql_query($sql);

// Torque expects the page to return `OK!` so this should be the only output
// of this page in production.
print "OK!";
?>
