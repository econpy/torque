<?php
//echo "<!-- Begin plot.php at ".date("H:i:s", microtime(true))." -->\r\n";
require_once("./db.php");
require_once("./parse_functions.php");

// Convert data units
// TODO: Use the userDefault fields to do these conversions dynamically

//Speed conversion
if (!$source_is_miles && $use_miles) {
    $speed_factor = 0.621371;
    $speed_measurand = ' (mph)';
} elseif ($source_is_miles && $use_miles) {
    $speed_factor = 1.0;
    $speed_measurand = ' (mph)';
} elseif ($source_is_miles && !$use_miles) {
    $speed_factor = 1.609344;
    $speed_measurand = ' (km/h)';
} else {
    $speed_factor = 1.0;
    $speed_measurand = ' (km/h)';
}

//Temperature Conversion
if (!$source_is_fahrenheit && $use_fahrenheit) { //From Celsius to Fahrenheit
    $temp_func = function ($temp) { return $temp*9.0/5.0+32.0; };
    $temp_measurand = ' (&deg;F)';
} elseif ($source_is_fahrenheit && $use_fahrenheit) { //Just Fahrenheit
    $temp_func = function ($temp) { return $temp; };
    $temp_measurand = ' (&deg;F)';
} elseif ($source_is_fahrenheit && !$use_fahrenheit) { //From Fahrenheit to Celsius
    $temp_func = function ($temp) { return ($temp-32.0)*5.0/9.0; };
    $temp_measurand = ' (&deg;C)';
} else { //Just Celsius
    $temp_func = function ($temp) { return $temp; };
    $temp_measurand = ' (&deg;C)';
}

// Grab the session number
if (isset($_GET["id"]) and in_array($_GET["id"], $sids)) {
    $session_id = mysqli_real_escape_string($con, $_GET['id']);
    // Get the torque key->val mappings
    $keyquery = mysqli_query($con, "SELECT id,description,units FROM $db_name.$db_keys_table;") or die(mysqli_error($con));
    $keyarr = [];
    while($row = mysqli_fetch_assoc($keyquery)) {
      $keyarr[$row['id']] = array($row['description'], $row['units']);
    }
	// 2015.08.04 - edit by surfrock66 - Adding experimental support for unlimited vars, 
	//   while requiring no default PID
	$selectstring = "time";
	$i = 1;
	while ( isset($_GET["s$i"]) ) {
		${'v' . $i} = $_GET["s$i"];
		$selectstring = $selectstring.",".quote_name(${'v' . $i});
		$i = $i + 1;
	}
	// Get data for session
	$tableYear = date( "Y", $session_id/1000 );
	$tableMonth = date( "m", $session_id/1000 );
	$db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";
	$sessionqry = mysqli_query($con, "SELECT $selectstring FROM $db_table_full WHERE session=".quote_value($session_id)." ORDER BY time DESC;") or die(mysqli_error($con));
	while($row = mysqli_fetch_assoc($sessionqry)) {
	    $i = 1;
		while (isset(${'v' . $i})) {
	        if (substri_count($keyarr[${'v' . $i}][0], "Speed") > 0) {
	            $x = intval($row[${'v' . $i}]) * $speed_factor;
	            ${'v' . $i . '_measurand'} = $speed_measurand;
	        } elseif (substri_count($keyarr[${'v' . $i}][0], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[${'v' . $i}]) );
	            ${'v' . $i . '_measurand'} = $temp_measurand;
	        } else {
	            $x = $row[${'v' . $i}];
	            ${'v' . $i . '_measurand'} = ' ('.$keyarr[${'v' . $i}][1].')';
	        }
	        ${'d' . $i}[] = array($row['time'], $x);
			${'spark' . $i}[] = $x;
			$i = $i + 1;
		}
	}
	$i = 1;	
	while (isset(${'v' . $i})) {
	    ${'v' . $i . '_label'} = '"'.$keyarr[${'v' . $i}][0].${'v' . $i . '_measurand'}.'"';
	    ${'sparkdata' . $i} = implode(",", array_reverse(${'spark' . $i}));
	    ${'max' . $i} = round(max(${'spark' . $i}), 1);
	    ${'min' . $i} = round(min(${'spark' . $i}), 1);
	    ${'avg' . $i} = round(average(${'spark' . $i}), 1);
	    ${'pcnt25data' . $i} = round(calc_percentile(${'spark' . $i}, 25), 1);
	    ${'pcnt75data' . $i} = round(calc_percentile(${'spark' . $i}, 75), 1);
		$i = $i + 1;
	}
}
//echo "<!-- End plot.php at ".date("H:i:s", microtime(true))." -->\r\n";
?>
