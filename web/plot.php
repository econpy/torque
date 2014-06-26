<?php
require("./creds.php");
require("./parse_functions.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// Grab the session number
if (isset($_GET["id"]) and in_array($_GET["id"], $sids)) {
    $session_id = intval(mysql_real_escape_string($_GET['id']));

    // Get the torque key->val mappings
    $js = CSVtoJSON("./data/torque_keys.csv");
    $jsarr = json_decode($js, TRUE);

    // The columns to plot -- if no PIDs are specified I default to intake temp and OBD speed
    if (isset($_GET["s1"])) {
        $v1 = mysql_real_escape_string($_GET['s1']);
    }
    else {
        $v1 = "kd"; // OBD Speed
    }
    if (isset($_GET["s2"])) {
        $v2 = mysql_real_escape_string($_GET['s2']);
    }
    else {
        $v2 = "kf";   // Intake Air Temp
    }

    // Grab the label for each PID to be used in the plot
    $v1_label = '"'.$jsarr[$v1].'"';
    $v2_label = '"'.$jsarr[$v2].'"';

    // Get data for session
    $sessionqry = mysql_query("SELECT time,$v1,$v2
                          FROM $db_table
                          WHERE session=$session_id
                          ORDER BY time DESC;") or die(mysql_error());

    // Convert data units
    // TODO: Use the userDefault fields to do these conversions dynamically
    while($row = mysql_fetch_assoc($sessionqry)) {
        // data column #1
        if (substri_count($jsarr[$v1], "Speed") > 0) {
            $x = intval($row[$v1])*0.621371;
        }
        elseif (substri_count($jsarr[$v1], "Temp") > 0) {
            $x = floatval($row[$v1])*9/5+32;
        }
        else {
            $x = intval($row[$v1]);
        }
        $d1[] = array(intval($row['time']), $x);
        $spark1[] = $x;
        // data column #2
        if (substri_count($jsarr[$v2], "Speed") > 0) {
            $x = intval($row[$v2])*0.621371;
        }
        elseif (substri_count($jsarr[$v2], "Temp") > 0) {
            $x = floatval($row[$v2])*9/5+32;
        }
        else {
            $x = intval($row[$v2]);
        }
        $d2[] = array(intval($row['time']), $x);
        $spark2[] = $x;
    }

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

else {

}

?>
