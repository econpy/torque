<?php
require_once ('db.php');
require_once ('auth_app.php');

$newest_table_list = mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and TABLE_NAME like '$db_table%' ORDER BY TABLE_NAME DESC LIMIT 1;");
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
  $current_table_list_query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and TABLE_NAME = '$db_table_full'";
  $current_table_list = mysqli_query($con, $current_table_list_query);
  if ( ! mysqli_fetch_assoc($current_table_list) ) {
    mysqli_query($con, "CREATE TABLE $db_table_full SELECT * FROM $newest_table WHERE 1=0") or die(mysqli_error($con));
  }
  foreach ($_GET as $key => $value) {
    // We will operate on 5 data sets which are defined by 5 "submit values"
    //   0 = Data we aren't dealing with, do nothing
    //   1 = Session data; Any value higher than this requires an entry in the sessions table
    //   2 = Data; There is a column check, then the data is added to the raw table
    //   3 = Profile data; Update the profile data to the sessions table
    //   4 = Notice data; Alert/event data...I'm doing nothing with this yet
    if (in_array($key, array("v", "eml", "time", "id", "session"))) {
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
      if ($key == 'profileName') {
        $sessprofilename = $value;
      }
      if ($key == 'profileFuelType') {
        $sessprofilefueltype = $value;
      }
      if ($key == 'profileWeight') {
        $sessprofileweight = $value;
      }
      if ($key == 'profileVe') {
        $sessprofileve = $value;
      }
      if ($key == 'profileFuelCost') {
        $sessprofilefuelcost = $value;
      }
      $submitval = 3;
    } else if (in_array($key, array("notice", "noticeClass"))) {
      $keys[] = $key;
      $values[] = $value;
      $submitval = 4;
    } else {
      $submitval = 0;
    }
    // If the field is a data field and doesn't already exist, add it to the database
    if (!in_array($key, $dbfields) and $submitval == 2) {
      // If the value isn't already in the latest DB table, we better check every DB table
      $table_list = mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and TABLE_NAME like '$db_table%' ORDER BY TABLE_NAME DESC;");
      while( $row = mysqli_fetch_assoc($table_list) ) {
        $db_table_name = $row["TABLE_NAME"];
        // Create an array of all the existing fields in the database
        $result = mysqli_query($con, "SHOW COLUMNS FROM $db_table_name") or die(mysqli_error($con));
        if (mysqli_num_rows($result) > 0) {
          $dbfields_per_table = array();
          while ($row = mysqli_fetch_assoc($result)) {
            $dbfields_per_table[]=($row['Field']);
          }
        }
        if (!in_array($key, $dbfields_per_table) and $submitval == 2) {
          // In PHP float and double are the same, so start with float as a default
          if ( is_float($value) ) {
            // Add field if it's a float to EVERY raw values table
            $sqlalter = "ALTER TABLE $db_table_name ADD ".quote_name($key)." float NOT NULL default '0'";
          } else {
            // Add field if it's a string to EVERY raw values table, specifically varchar(255)
            $sqlalter = "ALTER TABLE $db_table_name ADD ".quote_name($key)." VARCHAR(255) NOT NULL default 'Not Specified'";
          }
          mysqli_query($con, $sqlalter) or die(mysqli_error($con));
        }
      }
    }
    $sqlkeyquery = "SELECT id FROM $db_keys_table WHERE id=".quote_value($key);
    $result = mysqli_query($con, $sqlkeyquery);
    $row = mysqli_fetch_assoc($result);
    if ( ! $row and $submitval == 2 ) {
      $sqlalterkey = "INSERT INTO $db_keys_table (id, description, type, populated) VALUES (".quote_value($key).", ".quote_value($key).", 'varchar(255)', '1')";
      mysqli_query($con, $sqlalterkey) or die(mysqli_error($con));
    }
  }
  // The way session uploads work, there's a separate HTTP call for each datapoint.  This is why raw logs is
  //  so huge, and has so much repeating data. This is my attempt to flatten the redundant data into the
  //  sessions table; this code checks if there is already a row for the current session, and if there is, only 
  //  update the ending time and the count of datapoints.  If there isn't a row, insert one.

  // No matter what, if the submitval is higher than 0, make sure a session exists.  
  //   If one doesn't, create an entry.  If one does, collect current values
  if ( $submitval >= 1 && (sizeof($sesskeys) === sizeof($sessvalues)) && sizeof($sesskeys) > 0 ) {
    $sessionqrystring = "SELECT session, timestart, timeend, sessionsize FROM $db_sessions_table WHERE session LIKE ".quote_value($sessuploadid);
    $sessionqry = mysqli_query($con, $sessionqrystring) or die(mysqli_error($con));
    $row = mysqli_fetch_assoc($sessionqry);
    if ( ! $row ) {
      $sessioninsertstring = "INSERT INTO $db_sessions_table (".quote_names($sesskeys).", timestart, sessionsize) VALUES (".quote_values($sessvalues).", $sesstime, '1')";
      mysqli_query($con, $sessioninsertstring) or die(mysqli_error($con));
    } else {
      $sessTimeStart = $row['timestart'];
      $sessTimeEnd = $row['timeend'];
      $sessSize = $row['sessionsize'];
    }
  }
  // Prepare for inserting a value into the full data table
  $datakeys = $keys;
  $datavalues = $values;
  $datakeys[] = 'session';
  $datavalues[] = $sessuploadid;
  $datakeys[] = 'time';
  $datavalues[] = $sesstime; 
  if ( $submitval == 2 && ( sizeof($datakeys) === sizeof($datavalues) ) && sizeof($datakeys) > 0 ) {
    // Now insert the data for all the fields into the raw logs table
    $sql = "INSERT INTO $db_table_full (".quote_names($datakeys).") VALUES (".quote_values($datavalues).")";
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
    mysqli_query($con, $dataqrystring) or die(mysqli_error($con));
  }
  if ( $submitval == 3 ) {
    $profileqrystring = "UPDATE $db_sessions_table SET profileName = ".quote_value($sessprofilename).", profileFuelType = ".quote_value($sessprofilefueltype).", profileWeight = ".quote_value($sessprofileweight).", profileVe = ".quote_value($sessprofileve).", profileFuelCost = ".quote_value($sessprofilefuelcost)." WHERE session = ".quote_value($sessuploadid);
    mysqli_query($con, $profileqrystring) or die(mysqli_error($con));
  }
}
mysqli_close($con);

// Based on if the Home Assistant config variable is set, forward data to Home Assistant
if ( ( sizeof($_SERVER) > 0 ) and ( $uri_homeassistant != '' ) ) {
  // start the URI from
  $uri = strstr($_SERVER['REQUEST_URI'], '?');
  // If the bearer token is set in config use it, if not extract it from the app request
  if ( isset( $token_homeassistant ) and ( $token_homeassistant != '' ) ) {
    $token = $token_homeassistant;
  } else {
    $headers = null;
    $token = null;
    if ( isset( $_SERVER['Authorization'] ) ) {
      $headers = trim( $_SERVER["Authorization"] );
    } elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { //Nginx or fast CGI
      $headers = trim( $_SERVER["HTTP_AUTHORIZATION"] );
    } elseif ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
      $headers = trim( $_SERVER["REDIRECT_HTTP_AUTHORIZATION"] );
    } elseif ( function_exists( 'apache_request_headers' ) ) {
      $requestHeaders = apache_request_headers();
      // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
      $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
      if ( isset( $requestHeaders['Authorization'] ) ) {
        $headers = trim( $requestHeaders['Authorization'] );
      }
    }
    if ( !empty( $headers ) ) {
      if ( preg_match( '/Bearer\s(\S+)/', $headers, $matches ) ) {
        $token = $matches[1];
      }
    }
  }
  // If we have a new URI and the token, we upload the data:
  if ($token && $uri) {
    // Make a curl request including the Bearer token in https
    // This initializes a new cURL session
    $ch = curl_init();
    // set the request URL
    curl_setopt($ch, CURLOPT_URL, $uri_homeassistant.$uri);
    // return the response instead of printing it directly
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // set the Authorization header with the "Bearer" token and verify
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
    // the server identity using a valid certificate
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    curl_close($ch);
  }       
}

echo "OK!";
?>
