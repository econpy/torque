<?php
require_once ('db.php');
require_once ('auth_app.php');

$newest_table_list = mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and table_name like '$db_table%' ORDER BY table_name DESC LIMIT 1;");
$newest_table = "";
while( $row = mysqli_fetch_assoc($newest_table_list) ) {
  $newest_table = $row["TABLE_NAME"];
}
// Create an array of all the existing fields in the database
$result = mysqli_query($con, "SHOW COLUMNS FROM $newest_table") or die(mysqli_error($con));
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
  $datakeys = array();
  $datavalues = array();
  $sessuploadid = "";
  $sesstime = "0";
  $sessprofilename = "";
  $sessprofilefueltype = "";
  $sessprofileweight = "0";
  $sessprofileve = "0";
  $sessprofilefuelcost = "0";
  // From the sessionID, extrapolate the year and month so you can name the correct table from the prefix
  $session_id = $_GET["session"];
  $tableYear = date( "Y", $session_id/1000 );
  $tableMonth = date( "m", $session_id/1000 );
  $db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";
  // If the desired table name doesn't exist, create it copying columns from the previous month's table
  $current_table_list_query = "SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and table_name = '$db_table_full'";
#echo "<br />Debug 01 $current_table_list_query<br />";
  $current_table_list = mysqli_query($con, $current_table_list_query);
#echo "<br />Debug 02<br />";
  if ( ! mysqli_fetch_assoc($current_table_list) ) {
#echo "<br />Debug 03<br />";
    mysqli_query($con, "CREATE TABLE $db_table_full SELECT * FROM $newest_table WHERE 1=0") or die(mysqli_error($con));
  }
#echo "<br />Debug 04<br />";
  foreach ($_GET as $key => $value) {
    // We will operate on 5 data sets which are defined by 5 "submit values"
    //   0 = Data we aren't dealing with, do nothing
    //   1 = Session data; Any value higher than this requires an entry in the sessions table
    //   2 = Data; There is a column check, then the data is added to the raw table
    //   3 = Profile data; Update the profile data to the sessions table
    //   4 = Notice data; Alert/event data...I'm doing nothing with this yet
    if (in_array($key, array("v", "eml", "time", "id", "session"))) {
#echo "<br />Debug 05<br />";
      // Keep non k*,  non profile, and non notice columns listed here
      if ($key == 'session') {
        $sessuploadid = $value;
      }
      if ($key == 'time') {
        $sesstime = $value;
      }
      $sesskeys[] = $key;
      $sessvalues[] = $value;
      $submitval = 1;
    } else if (preg_match("/^k/", $key)) {
#echo "<br />Debug 06 $key<br />";
      // Keep columns starting with k
      $keys[] = $key;
      // My Torque app tries to pass "Infinity" in for some values...catch that error, set to -1
      if ($value == 'Infinity') {
        $values[] = -1;
      } else {
        $values[] = $value;
      }
      $submitval = 2;
    } else if (preg_match("/^profile/", $key)) {
#echo "<br />Debug 07 $key<br />";
      if ($key == 'profileName') {
        $sessprofilename = $value;
      }
#echo "<br />Debug 08 $sessprofilename<br />";
      if ($key == 'profileFuelType') {
        $sessprofilefueltype = $value;
      }
#echo "<br />Debug 09 $sessprofilefueltype<br />";
      if ($key == 'profileWeight') {
        $sessprofileweight = $value;
      }
#echo "<br />Debug 10 $sessprofileweight<br />";
      if ($key == 'profileVe') {
        $sessprofileve = $value;
      }
#echo "<br />Debug 11 $sessprofileve<br />";
      if ($key == 'profileFuelCost') {
        $sessprofilefuelcost = $value;
      }
#echo "<br />Debug 12 $sessprofilefuelcost<br />";
      $submitval = 3;
    } else if (in_array($key, array("notice", "noticeClass"))) {
#echo "<br />Debug 13<br />";
      $keys[] = $key;
      $values[] = $value;
      $submitval = 4;
    } else {
      $submitval = 0;
    }
#echo "<br />Debug 14<br />"; print_r($dbfields);
    // If the field is a data field and doesn't already exist, add it to the database
    if (!in_array($key, $dbfields) and $submitval == 2) {
#echo "<br />Debug 15<br />";
      // If the value isn't already in the latest DB table, we better check every DB table
      $table_list = mysqli_query($con, "SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and table_name like '$db_table%' ORDER BY table_name DESC;");
      while( $row = mysqli_fetch_assoc($table_list) ) {
        $db_table_name = $row["table_name"];
#echo "<br />Debug 16 $db_table_name<br />";
        // Create an array of all the existing fields in the database
        $result = mysqli_query($con, "SHOW COLUMNS FROM $db_table_name") or die(mysqli_error($con));
        if (mysqli_num_rows($result) > 0) {
          $dbfields_per_table = array();
          while ($row = mysqli_fetch_assoc($result)) {
            $dbfields_per_table[]=($row['Field']);
#echo "<br />Debug 17<br />"; 
          }
        }
#echo "<br />Debug 18 $key $submitval<br />"; print_r($dbfields_per_table);
        if (!in_array($key, $dbfields_per_table) and $submitval == 2) {
#echo "<br />Debug 19<br />";
          // In PHP float and double are the same, so start with float as a default
          if ( is_float($value) ) {
            // Add field if it's a float to EVERY raw values table
            $sqlalter = "ALTER TABLE $db_table_name ADD ".quote_name($key)." float NOT NULL default '0'";
#echo "<br />Debug 20 $sqlalter<br />";
          } else {
            // Add field if it's a string to EVERY raw values table, specifically varchar(255)
            $sqlalter = "ALTER TABLE $db_table_name ADD ".quote_name($key)." VARCHAR(255) NOT NULL default 'Not Specified'";
#echo "<br />Debug 21 $sqlalter<br />";
          }
          mysqli_query($con, $sqlalter) or die(mysqli_error($con));
#echo "<br />Debug 22<br />";
        }
      }
    }
    $sqlkeyquery = "SELECT id FROM $db_keys_table WHERE id=".quote_value($key);
    $result = mysqli_query($con, $sqlkeyquery);
    $row = mysqli_fetch_assoc($result);
    if ( ! $row and $submitval == 2 ) {
      $sqlalterkey = "INSERT INTO $db_keys_table (id, description, type, populated) VALUES (".quote_value($key).", ".quote_value($key).", 'varchar(255)', '1')";
#echo "<br />Debug 23 $sqlalterkey<br />";
      mysqli_query($con, $sqlalterkey) or die(mysqli_error($con));
#echo "<br />Debug 24<br />";
    }
  }
  // The way session uploads work, there's a separate HTTP call for each datapoint.  This is why raw logs is
  //  so huge, and has so much repeating data. This is my attempt to flatten the redundant data into the
  //  sessions table; this code checks if there is already a row for the current session, and if there is, only 
  //  update the ending time and the count of datapoints.  If there isn't a row, insert one.

  // No matter what, if the submitval is higher than 0, make sure a session exists.  
  //   If one doesn't, create an entry.  If one does, collect current values
  if ( $submitval >= 1 && (sizeof($sesskeys) === sizeof($sessvalues)) && sizeof($sesskeys) > 0 ) {
#echo "<br />Debug 25<br />";
    $sessionqrystring = "SELECT session, timestart, timeend, sessionsize FROM $db_sessions_table WHERE session LIKE ".quote_value($sessuploadid);
#echo "<br />Debug 26 $sessionqrystring<br />";
    $sessionqry = mysqli_query($con, $sessionqrystring) or die(mysqli_error($con));
    $row = mysqli_fetch_assoc($sessionqry);
    if ( ! $row ) {
#echo "<br />Debug 27<br />";
      $sessioninsertstring = "INSERT INTO $db_sessions_table (".quote_names($sesskeys).", timestart, sessionsize) VALUES (".quote_values($sessvalues).", $sesstime, '1')";
#echo "<br />Debug 28 $sessioninsertstring<br />";
      mysqli_query($con, $sessioninsertstring) or die(mysqli_error($con));
    } else {
#echo "<br />Debug 29<br />";
      $sessTimeStart = $row['timestart'];
      $sessTimeEnd = $row['timeend'];
      $sessSize = $row['sessionsize'];
    }
  }
  // Prepare for inserting a value into the full data table
#  $datakeys = array_merge($keys, 'session', 'time');
#  $datavalues = array_merge($values, $sessuploadid, $sesstime);
  $datakeys = $keys;
  $datavalues = $values;
  $datakeys[] = 'session';
  $datavalues[] = $sessuploadid;
  $datakeys[] = 'time';
  $datavalues[] = $sesstime; 
#echo "<br />Debug 30 $submitval ".sizeof($datakeys)." ".sizeof($datavalues)."<br />";
  if ( $submitval == 2 && ( sizeof($datakeys) === sizeof($datavalues) ) && sizeof($datakeys) > 0 ) {
#echo "<br />Debug 31<br />";
    // Now insert the data for all the fields into the raw logs table
    $sql = "INSERT INTO $db_table_full (".quote_names($datakeys).") VALUES (".quote_values($datavalues).")";
#echo "<br />Debug 32 $sql<br />";
    mysqli_query($con, $sql) or die(mysqli_error($con));
    // Update session variables
    // If this is the earliest timestamp for this session, update the "Session start time"
    if ( $sessTimeStart > $sesstime ) {
      $sessTimeStart = $sesstime;
    }
    // If this is the latest timestamp for this session, update the "Session end time"
    if ( $sessTimeEnd < $sesstime ) {
      $sessTimeEnd = $sesstime;
    }
    // Increment the session size counter
    $sessSize = $sessSize + 1;
    // Update the session table
    $dataqrystring = "UPDATE $db_sessions_table SET timestart = ".quote_value($sessTimeStart).", timeend = ".quote_value($sessTimeEnd).", sessionsize = ".quote_value($sessSize)." WHERE session = ".quote_value($sessuploadid);
#echo "<br />Debug 33 $dataqrystring<br />";
    mysqli_query($con, $dataqrystring) or die(mysqli_error($con));
  }
  if ( $submitval == 3 ) {
#echo "<br />Debug 34<br />";
    $profileqrystring = "UPDATE $db_sessions_table SET profileName = ".quote_value($sessprofilename).", profileFuelType = ".quote_value($sessprofilefueltype).", profileWeight = ".quote_value($sessprofileweight).", profileVe = ".quote_value($sessprofileve).", profileFuelCost = ".quote_value($sessprofilefuelcost)." WHERE session = ".quote_value($sessuploadid);
#echo "<br />Debug 35 $profileqrystring<br />";
    mysqli_query($con, $profileqrystring) or die(mysqli_error($con));
  }
}
mysqli_close($con);

// Return the response required by Torque
#echo "<br />OK!<br />";
echo "OK!";
?>
