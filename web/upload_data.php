<?php
require_once ('creds.php');
require_once ('auth_app.php');

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// Create an array of all the existing fields in the database
$result = mysql_query("SHOW COLUMNS FROM $db_table", $con) or die(mysql_error());
if (mysql_num_rows($result) > 0) {
  while ($row = mysql_fetch_assoc($result)) {
    $dbfields[]=($row['Field']);
  }
}

// Iterate over all the k* _GET arguments to check that a field exists
if (sizeof($_GET) > 0) {
  $keys = array();
  $values = array();
  $sesskeys = array();
  $sessvalues = array();
  $sessprofilekeys = array();
  $sessprofilevalues = array();
  $sessuploadid = "";
  $sesstime = "0";
  $sessprofilequery = "";
//print_r($_GET);
  foreach ($_GET as $key => $value) {
    if (preg_match("/^k/", $key)) {
      // Keep columns starting with k
      $keys[] = $key;
      // My Torque app tries to pass "Infinity" in for some values...catch that error, set to -1
      if ($value == 'Infinity') {
        $values[] = -1;
      } else {
        $values[] = $value;
      }
      $submitval = 1;
    } else if (in_array($key, array("v", "eml", "time", "id", "session"))) {
      // Keep non k* columns listed here
      if ($key == 'session') {
        $sessuploadid = $value;
      }
      if ($key == 'time') {
        $sesstime = $value;
      }
      $sesskeys[] = $key;
      $sessvalues[] = "'".$value."'";
      $submitval = 1;
    } else if (in_array($key, array("notice", "noticeClass"))) {
      $keys[] = $key;
      $values[] = "'".$value."'";
      $submitval = 1;
    } else if (preg_match("/^profile/", $key)) {
      $sessprofilequery = $sessprofilequery.", ".$key."='".$value."'";
      $sessprofilekeys[] = $key;
      $sessprofilevalues[] = "'".$value."'";
      $submitval = 1;
    } else {
      $submitval = 0;
    }
    // If the field doesn't already exist, add it to the database
    if (!in_array($key, $dbfields) and $submitval == 1) {
      if ( is_float($value) ) {
        // Add field if it's a float
        $sqlalter = "ALTER TABLE $db_table ADD $key float NOT NULL default '0'";
        $sqlalterkey = "INSERT INTO $db_keys_table (id, description, type, populated) VALUES ('$key', '$key', 'float', '1')";
      } else {
        // Add field if it's a string, specifically varchar(255)
        $sqlalter = "ALTER TABLE $db_table ADD $key VARCHAR(255) NOT NULL default 'Not Specified'";
        $sqlalterkey = "INSERT INTO $db_keys_table (id, description, type, populated) VALUES ('$key', '$key', 'varchar(255)', '1')";
      }
      mysql_query($sqlalter, $con) or die(mysql_error());
      mysql_query($sqlalterkey, $con) or die(mysql_error());
    }
  }
  // The way session uploads work, there's a separate HTTP call for each datapoint.  This is why raw logs is
  //  so huge, and has so much repeating data. This is my attempt to flatten the redundant data into the
  //  sessions table; this code checks if there is already a row for the current session, and if there is, only 
  //  update the ending time and the count of datapoints.  If there isn't a row, insert one.
  $rawkeys = array_merge($keys, $sesskeys, $sessprofilekeys);
  $rawvalues = array_merge($values, $sessvalues, $sessprofilevalues);
  if ((sizeof($rawkeys) === sizeof($rawvalues)) && sizeof($rawkeys) > 0 && (sizeof($sesskeys) === sizeof($sessvalues)) && sizeof($sesskeys) > 0) {
    // Now insert the data for all the fields into the raw logs table
    $sql = "INSERT INTO $db_table (".implode(",", $rawkeys).") VALUES (".implode(",", $rawvalues).")";
//echo $sql;
    mysql_query($sql, $con) or die(mysql_error());
    // See if there is already an entry in the sessions table for this session
    $sessionqry = mysql_query("SELECT session, sessionsize FROM $db_sessions_table WHERE session LIKE '$sessuploadid'", $con) or die(mysql_error());
    $sesssizecount=1;
//    if (mysql_num_rows($sessionqry) > 0) {
      // If there's an entry in the session table for this session, update the session end time and the datapoint count
      while($row = mysql_fetch_assoc($sessionqry)) {
        $sesssizecount = $row["sessionsize"] + 1;
//        $sessionqrystring = "UPDATE $db_sessions_table SET timeend='$sesstime', sessionsize='$sesssizecount'$sessprofilequery WHERE session LIKE '$sessuploadid'";
      }
//    } else {
//      // If this is a new session, insert an entry in the sessions table and then update the start time and datapoint count
//      $sessionqrystring = "INSERT INTO $db_sessions_table (".implode(",", $sesskeys).", timestart, sessionsize) VALUES (".implode(",", $sessvalues).", $sesstime, '1')";
//    }
    $sessionqrystring = "INSERT INTO $db_sessions_table (".implode(",", $sesskeys).", timestart, sessionsize) VALUES (".implode(",", $sessvalues).", $sesstime, '1') ON DUPLICATE KEY UPDATE timeend='$sesstime', sessionsize='$sesssizecount'$sessprofilequery";
//echo $sessionqrystring;
    mysql_query($sessionqrystring, $con) or die(mysql_error());
  }
}
mysql_close($con);

// Return the response required by Torque
echo "OK!";
?>
