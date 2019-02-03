<?php
  require_once ('db.php');
  require_once ('auth_app.php');

  mysqli_query($con, "ALTER TABLE $db_keys_table ADD COLUMN favorite TINYINT(1) NOT NULL DEFAULT 0") or die(mysqli_error($con));
  // Update existing tables to handle new data structures
  $table_list = mysqli_query($con, "SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and table_name like '$db_table%' ORDER BY table_name DESC;");
  while( $row = mysqli_fetch_assoc($table_list) ) {
    $db_table_name = $row["table_name"];
    // Change the GPS Latitude and Longitude datapoints from Float to Double to improve accuracy
    $sqlLatQuery = "ALTER TABLE $db_table_name MODIFY kff1006 DOUBLE NOT NULL DEFAULT '0'";
    mysqli_query($con, $sqlLatQuery) or die(mysqli_error($con));
    $sqlLongQuery = "ALTER TABLE $db_table_name MODIFY kff1005 DOUBLE NOT NULL DEFAULT '0'";
    mysqli_query($con, $sqlLongQuery) or die(mysqli_error($con));
    // Delete columns which are now redundant and just stored with the session
    $sqlVQuery = "ALTER TABLE $db_table_name DROP COLUMN v";
    mysqli_query($con, $sqlVQuery) or die(mysqli_error($con));
    $sqlIdQuery = "ALTER TABLE $db_table_name DROP COLUMN id";
    mysqli_query($con, $sqlIdQuery) or die(mysqli_error($con));
    $sqlEmlQuery = "ALTER TABLE $db_table_name DROP COLUMN eml";
    mysqli_query($con, $sqlEmlQuery) or die(mysqli_error($con));
    $sqlProfileNameQuery = "ALTER TABLE $db_table_name DROP COLUMN profileName";
    mysqli_query($con, $sqlProfileNameQuery) or die(mysqli_error($con));
    $sqlProfileFuelTypeQuery = "ALTER TABLE $db_table_name DROP COLUMN profileFuelType";
    mysqli_query($con, $sqlProfileFuelTypeQuery) or die(mysqli_error($con));
    $sqlProfileWeightQuery = "ALTER TABLE $db_table_name DROP COLUMN profileWeight";
    mysqli_query($con, $sqlProfileWeightQuery) or die(mysqli_error($con));
    $sqlProfileVeQuery = "ALTER TABLE $db_table_name DROP COLUMN profileVe";
    mysqli_query($con, $sqlProfileVeQuery) or die(mysqli_error($con));
    $sqlProfileFuelCostQuery = "ALTER TABLE $db_table_name DROP COLUMN profileFuelCost";
    mysqli_query($con, $sqlProfileFuelCostQuery) or die(mysqli_error($con));
    
  }

  // Split the raw logs table into per-month tables 
  $sessionYears = mysqli_query($con, "SELECT DISTINCT CONCAT(YEAR(FROM_UNIXTIME(session/1000)), '_', DATE_FORMAT(FROM_UNIXTIME(session/1000),'%m')) as Suffix, YEAR(FROM_UNIXTIME(session/1000)) as Year, MONTH(FROM_UNIXTIME(session/1000)) as Month FROM $db_table");
  while( $row = mysqli_fetch_assoc( $sessionYears ) ) {
    $suffix = $row['Suffix'];
    $year = $row['Year'];
    $month = $row['Month'];
    $new_table_name = "{$db_table}_test_{$suffix}";
    $table_create_query = "CREATE TABLE $new_table_name SELECT * FROM $db_table WHERE YEAR(FROM_UNIXTIME(session/1000)) LIKE '$year' and MONTH(FROM_UNIXTIME(session/1000)) LIKE '$month'";
    mysqli_query($con, $table_create_query) or die(mysqli_error($con));
  }

  // Clear the raw_logs table; we still want it as a shell, just empty
  mysqli_query($con, "DELETE FROM $db_table") or die(mysqli_error($con));
?>

