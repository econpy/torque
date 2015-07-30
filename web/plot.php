<?php
require_once("./creds.php");
require_once("./parse_functions.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// Grab the session number
if (isset($_GET["id"]) and in_array($_GET["id"], $sids)) {
    $session_id = mysql_real_escape_string($_GET['id']);

    // Get the torque key->val mappings
    $js = CSVtoJSON("./data/torque_keys.csv");
    $jsarr = json_decode($js, TRUE);

    // The columns to plot -- if no PIDs are specified I default to intake temp and OBD speed
    if (isset($_GET["s1"])) {
        $v1 = mysql_real_escape_string($_GET['s1']);
    }
    else {
		// 2015.07.22 - edit by surfrock66 - Dsiable choosing of default variable when
		//   none is selected
        //$v1 = "kd"; // OBD Speed
        $v1 = "";
    }
    if (isset($_GET["s2"])) {
        $v2 = mysql_real_escape_string($_GET['s2']);
    }
    else {
		// 2015.07.22 - edit by surfrock66 - Dsiable choosing of default variable when
		//   none is selected
        //$v2 = "kf";   // Intake Air Temp
        $v2 = "";
    }

	if ( $v1 <> "" or $v2 <> "" ) {
		// Grab the label for each PID to be used in the plot
	    $v1_label = '"'.$jsarr[$v1].'"';
	    $v2_label = '"'.$jsarr[$v2].'"';
	
	    // Get data for session
	    $sessionqry = mysql_query("SELECT time,$v1,$v2
	                          FROM $db_table
	                          WHERE session=$session_id
	                          ORDER BY time DESC;") or die(mysql_error());
	
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
	
	    // Convert data units
	    // TODO: Use the userDefault fields to do these conversions dynamically
	    while($row = mysql_fetch_assoc($sessionqry)) {
	        // data column #1
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
	
	        // data column #2
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
	
	    $v1_label = '"'.$jsarr[$v1].$v1_measurand.'"';
	    $v2_label = '"'.$jsarr[$v2].$v2_measurand.'"';
	
	    $sparkdata1 = implode(",", array_reverse($spark1));
	    $sparkdata2 = implode(",", array_reverse($spark2));
	    $max1 = round(max($spark1), 1);
	    $max2 = round(max($spark2), 1);
	    $min1 = round(min($spark1), 1);
	    $min2 = round(min($spark2), 1);
	    $avg1 = round(average($spark1), 1);
	    $avg2 = round(average($spark2), 1);
	    $pcnt25data1 = round(calc_percentile($spark1, 25), 1);
	    $pcnt25data2 = round(calc_percentile($spark2, 25), 1);
	    $pcnt75data1 = round(calc_percentile($spark1, 75), 1);
	    $pcnt75data2 = round(calc_percentile($spark2, 75), 1);
	}
}

else {

}

?>
