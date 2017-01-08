<?php
require_once ('db.php');
require_once ('auth_app.php');


// Create an array of all the existing fields in the database
$result = mysqli_query($con, "SHOW COLUMNS FROM $db_table") or die(mysqli_error());
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
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
      $sessvalues[] = $value;
      $submitval = 1;
    } else if (in_array($key, array("notice", "noticeClass"))) {
      $keys[] = $key;
      $values[] = $value;
      $submitval = 1;
    } else if (preg_match("/^profile/", $key)) {
      $sessprofilequery = $sessprofilequery.", ".quote_name($key)."=".quote_value($value);
      $sessprofilekeys[] = $key;
      $sessprofilevalues[] = $value;
      $submitval = 1;
    } else {
      $submitval = 0;
    }
    // If the field doesn't already exist, add it to the database
    if (!in_array($key, $dbfields) and $submitval == 1) {
      if ( is_float($value) ) {
        // Add field if it's a float
        $sqlalter = "ALTER TABLE $db_table ADD ".quote_name($key)." float NOT NULL default '0'";
        $sqlalterkey = "INSERT INTO $db_keys_table (id, description, type, populated) VALUES (".quote_value($key).", ".quote_value($key).", 'float', '1')";
      } else {
        // Add field if it's a string, specifically varchar(255)
        $sqlalter = "ALTER TABLE $db_table ADD ".quote_name($key)." VARCHAR(255) NOT NULL default 'Not Specified'";
        $sqlalterkey = "INSERT INTO $db_keys_table (id, description, type, populated) VALUES (".quote_value($key).", ".quote_value($key).", 'varchar(255)', '1')";
      }
      mysqli_query($con, $sqlalter) or die(mysqli_error());
      mysqli_query($con, $sqlalterkey) or die(mysqli_error());
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
    $sql = "INSERT INTO $db_table (".quote_names($rawkeys).") VALUES (".quote_values($rawvalues).")";
//echo $sql;
    mysqli_query($con, $sql) or die(mysqli_error());
    // See if there is already an entry in the sessions table for this session
    $sessionqry = mysqli_query($con, "SELECT session, sessionsize FROM $db_sessions_table WHERE session LIKE ".quote_value($sessuploadid)) or die(mysqli_error());
    $sesssizecount=1;
    // If there's an entry in the session table for this session, update the session end time and the datapoint count
    while($row = mysqli_fetch_assoc($sessionqry)) {
      $sesssizecount = $row["sessionsize"] + 1;
    }
    $sessionqrystring = "INSERT INTO $db_sessions_table (".quote_names($sesskeys).", timestart, sessionsize) VALUES (".quote_values($sessvalues).", $sesstime, '1') ON DUPLICATE KEY UPDATE timeend='$sesstime', sessionsize='$sesssizecount'$sessprofilequery";
//echo $sessionqrystring;
    mysqli_query($con, $sessionqrystring) or die(mysqli_error());
  }
}
mysqli_close($con);

// Return the response required by Torque
echo "OK!";
?>
