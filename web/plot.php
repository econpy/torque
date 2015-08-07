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
    $speed_measurand = ' [mph]';
}
elseif ($source_is_miles && $use_miles)
{
    $speed_factor = 1.0;
    $speed_measurand = ' [mph]';
}
elseif ($source_is_miles && !$use_miles)
{
    $speed_factor = 1.609344;
    $speed_measurand = ' [km/h]';
}
else
{
    $speed_factor = 1.0;
    $speed_measurand = ' [km/h]';
}
//Temperature Conversion
//From Celsius to Fahrenheit
if (!$source_is_fahrenheit && $use_fahrenheit)
{
    $temp_func = function ($temp) { return $temp*9.0/5.0+32.0; };
    $temp_measurand = ' [&deg;F]';
}
//Just Fahrenheit
elseif ($source_is_fahrenheit && $use_fahrenheit)
{
    $temp_func = function ($temp) { return $temp; };
    $temp_measurand = ' [&deg;F]';
}
//From Fahrenheit to Celsius
elseif ($source_is_fahrenheit && !$use_fahrenheit)
{
    $temp_func = function ($temp) { return ($temp-32.0)*5.0/9.0; };
    $temp_measurand = ' [&deg;C]';
}
//Just Celsius
else
{
    $temp_func = function ($temp) { return $temp; };
    $temp_measurand = ' [&deg;C]';
}
// Grab the session number
if (isset($_GET["id"]) and in_array($_GET["id"], $sids)) {
    $session_id = mysql_real_escape_string($_GET['id']);

    // Get the torque key->val mappings
    $js = CSVtoJSON("./data/torque_keys.csv");
    $jsarr = json_decode($js, TRUE);

	// 2015.08.04 - edit by surfrock66 - Adding experimental support for up to 5 vars, 
	//   while requiring no default PID
	$selectstring = "time";
	$v1 = "";
    if (isset($_GET["s1"])) {
        $v1 = mysql_real_escape_string($_GET['s1']);
	    $v1_label = '"'.$jsarr[$v1].'"';
		$selectstring = $selectstring.",$v1";
    }
	$v2 = "";
    if (isset($_GET["s2"])) {
        $v2 = mysql_real_escape_string($_GET['s2']);
	    $v2_label = '"'.$jsarr[$v2].'"';
		$selectstring = $selectstring.",$v2";
    }
    $v3 = "";
    if (isset($_GET["s3"])) {
        $v3 = mysql_real_escape_string($_GET['s3']);
	    $v3_label = '"'.$jsarr[$v3].'"';
		$selectstring = $selectstring.",$v3";
    }
	$v4 = "";
    if (isset($_GET["s4"])) {
        $v4 = mysql_real_escape_string($_GET['s4']);
	    $v4_label = '"'.$jsarr[$v4].'"';
		$selectstring = $selectstring.",$v4";
    }
    $v5 = "";
	if (isset($_GET["s5"])) {
        $v5 = mysql_real_escape_string($_GET['s5']);
	    $v5_label = '"'.$jsarr[$v5].'"';
		$selectstring = $selectstring.",$v5";
    }

	// Get data for session
	$sessionqry = mysql_query("SELECT $selectstring
		                  FROM $db_table
	                      WHERE session=$session_id
		                  ORDER BY time DESC;") or die(mysql_error());
	while($row = mysql_fetch_assoc($sessionqry)) {
	    // data column #1
		if ( $v1 <> "" ) {
	        if (substri_count($jsarr[$v1], "Speed") > 0) {
	            $x = intval($row[$v1]) * $speed_factor;
	            $v1_measurand = $speed_measurand;
	        }
	        elseif (substri_count($jsarr[$v1], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[$v1]) );
	            $v1_measurand = $temp_measurand;
	        }
	        else {
	            $x = intval($row[$v1]);
	            $v1_measurand = '';
	        }
	        $d1[] = array($row['time'], $x);
			$spark1[] = $x;
		}
	
	    // data column #2
		if ( $v2 <> "" ) { 
	        if (substri_count($jsarr[$v2], "Speed") > 0) {
	            $x = intval($row[$v2]) * $speed_factor;
	            $v2_measurand = $speed_measurand;
	        }
	        elseif (substri_count($jsarr[$v2], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[$v2]) );
	            $v2_measurand = $temp_measurand;
	        }
	        else {
	            $x = intval($row[$v2]);
	            $v2_measurand = '';
	        }
	        $d2[] = array($row['time'], $x);
	        $spark2[] = $x;
		}
	
	    // data column #3
		if ( $v3 <> "" ) {
	        if (substri_count($jsarr[$v3], "Speed") > 0) {
	            $x = intval($row[$v3]) * $speed_factor;
	            $v3_measurand = $speed_measurand;
	        }
	        elseif (substri_count($jsarr[$v3], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[$v3]) );
	            $v3_measurand = $temp_measurand;
	        }
	        else {
	            $x = intval($row[$v3]);
	            $v3_measurand = '';
	        }
	        $d3[] = array($row['time'], $x);
	        $spark3[] = $x;
		}
	
	    // data column #4
		if ( $v4 <> "" ) {
	        if (substri_count($jsarr[$v4], "Speed") > 0) {
	            $x = intval($row[$v4]) * $speed_factor;
	            $v4_measurand = $speed_measurand;
	        }
	        elseif (substri_count($jsarr[$v4], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[$v4]) );
	            $v4_measurand = $temp_measurand;
	        }
	        else {
	            $x = intval($row[$v4]);
	            $v4_measurand = '';
	        }
	        $d4[] = array($row['time'], $x);
	        $spark4[] = $x;
		}
	
	    // data column #5
		if ( $v5 <> "" ) {
	        if (substri_count($jsarr[$v5], "Speed") > 0) {
	            $x = intval($row[$v5]) * $speed_factor;
	            $v5_measurand = $speed_measurand;
	        }
	        elseif (substri_count($jsarr[$v5], "Temp") > 0) {
	            $x = $temp_func ( floatval($row[$v5]) );
	            $v5_measurand = $temp_measurand;
	        }
	        else {
	            $x = intval($row[$v5]);
	            $v5_measurand = '';
	        }
	        $d5[] = array($row['time'], $x);
	        $spark5[] = $x;
		}
	}
	
	if ( $v1 <> "" ) {
	    $v1_label = '"'.$jsarr[$v1].$v1_measurand.'"';
	    $sparkdata1 = implode(",", array_reverse($spark1));
	    $max1 = round(max($spark1), 1);
	    $min1 = round(min($spark1), 1);
	    $avg1 = round(average($spark1), 1);
	    $pcnt25data1 = round(calc_percentile($spark1, 25), 1);
	    $pcnt75data1 = round(calc_percentile($spark1, 75), 1);
	}

	if ( $v2 <> "" ) {
	    $v2_label = '"'.$jsarr[$v2].$v2_measurand.'"';
	    $sparkdata2 = implode(",", array_reverse($spark2));
	    $max2 = round(max($spark2), 1);
	    $min2 = round(min($spark2), 1);
	    $avg2 = round(average($spark2), 1);
	    $pcnt25data2 = round(calc_percentile($spark2, 25), 1);
	    $pcnt75data2 = round(calc_percentile($spark2, 75), 1);
	}

	if ( $v3 <> "" ) {
	    $v3_label = '"'.$jsarr[$v3].$v3_measurand.'"';
	    $sparkdata3 = implode(",", array_reverse($spark3));
	    $max3 = round(max($spark3), 1);
	    $min3 = round(min($spark3), 1);
	    $avg3 = round(average($spark3), 1);
	    $pcnt25data3 = round(calc_percentile($spark3, 25), 1);
	    $pcnt75data3 = round(calc_percentile($spark3, 75), 1);
	}

	if ( $v4 <> "" ) {
	    $v4_label = '"'.$jsarr[$v4].$v4_measurand.'"';
	    $sparkdata4 = implode(",", array_reverse($spark4));
	    $max4 = round(max($spark4), 1);
	    $min4 = round(min($spark4), 1);
	    $avg4 = round(average($spark4), 1);
	    $pcnt25data4 = round(calc_percentile($spark4, 25), 1);
	    $pcnt75data4 = round(calc_percentile($spark4, 75), 1);
	}

	if ( $v5 <> "" ) {
	    $v5_label = '"'.$jsarr[$v5].$v5_measurand.'"';
	    $sparkdata5 = implode(",", array_reverse($spark5));
	    $max5 = round(max($spark5), 1);
	    $min5 = round(min($spark5), 1);
	    $avg5 = round(average($spark5), 1);
	    $pcnt25data5 = round(calc_percentile($spark5, 25), 1);
	    $pcnt75data5 = round(calc_percentile($spark5, 75), 1);
	}
}
//}

//else {

//}

?>
