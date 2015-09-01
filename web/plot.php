<?php
require_once("./creds.php");
require_once("./parse_functions.php");
// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());
// Convert data units
// TODO: Use the userDefault fields to do these conversions dynamically
//Speed conversion
if (!$source_is_miles && $use_miles)
{
    $speed_factor = 0.621371;
    $speed_measurand = ' (mph)';
}
elseif ($source_is_miles && $use_miles)
{
    $speed_factor = 1.0;
    $speed_measurand = ' (mph)';
}
elseif ($source_is_miles && !$use_miles)
{
    $speed_factor = 1.609344;
    $speed_measurand = ' (km/h)';
}
else
{
    $speed_factor = 1.0;
    $speed_measurand = ' (km/h)';
}
//Temperature Conversion
//From Celsius to Fahrenheit
if (!$source_is_fahrenheit && $use_fahrenheit)
{
    $temp_func = function ($temp) { return $temp*9.0/5.0+32.0; };
    $temp_measurand = ' (&deg;F)';
}
//Just Fahrenheit
elseif ($source_is_fahrenheit && $use_fahrenheit)
{
    $temp_func = function ($temp) { return $temp; };
    $temp_measurand = ' (&deg;F)';
}
//From Fahrenheit to Celsius
elseif ($source_is_fahrenheit && !$use_fahrenheit)
{
    $temp_func = function ($temp) { return ($temp-32.0)*5.0/9.0; };
    $temp_measurand = ' (&deg;C)';
}
//Just Celsius
else
{
    $temp_func = function ($temp) { return $temp; };
    $temp_measurand = ' (&deg;C)';
}
// Grab the session number
if (isset($_GET["id"]) and in_array($_GET["id"], $sids)) {
    $session_id = mysql_real_escape_string($_GET['id']);
    // Get the torque key->val mappings
//    $js = CSVtoJSON("./data/torque_keys.csv");
//    $jsarr = json_decode($js, TRUE);
    $keyquery = mysql_query("SELECT id,description,units FROM $db_name.$db_keys_table;") or die(mysql_error());
    $keyarr = [];
    while($row = mysql_fetch_assoc($keyquery)) {
      $keyarr[$row['id']] = array($row['description'], $row['units']);
    }
	// 2015.08.04 - edit by surfrock66 - Adding experimental support for unlimited vars, 
	//   while requiring no default PID
	$selectstring = "time";
	$i = 1;
	while ( isset($_GET["s$i"]) ) {
		${'v' . $i} = mysql_real_escape_string($_GET["s$i"]);
//		${'v' . $i . '_label'} = '"'.$jsarr[${'v' . $i}].'"';
//		${'v' . $i . '_label'} = '"'.$keyarr[${'v' . $i}][0]." (".$keyarr[${'v' . $i}][1].")".'"';
		$selectstring = $selectstring.",${'v' . $i}";
		$i = $i + 1;
	}
	// Get data for session
	$sessionqry = mysql_query("SELECT $selectstring FROM $db_table WHERE session=$session_id ORDER BY time DESC;") or die(mysql_error());
	while($row = mysql_fetch_assoc($sessionqry)) {
	    $i = 1;
		while (isset(${'v' . $i})) {
	        if (substri_count($keyarr[${'v' . $i}][0], "Speed") > 0) {
	            $x = intval($row[${'v' . $i}]) * $speed_factor;
	            ${'v' . $i . '_measurand'} = $speed_measurand;
	        }
	        elseif (substri_count($keyarr[${'v' . $i}][0], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[${'v' . $i}]) );
	            ${'v' . $i . '_measurand'} = $temp_measurand;
	        }
	        else {
	            $x = intval($row[${'v' . $i}]);
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
?>
