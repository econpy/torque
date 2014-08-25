<?php
require 'creds.php';
require 'parse_functions.php';
require 'convert.php';

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// Grab the session number
if (isset($_GET["id"]) and in_array($_GET["id"], $sids)) {
    $session_id = mysql_real_escape_string($_GET['id']);

    // Get the torque PID->Description mappings
    $jsnames = CSVtoJSON("./data/torque_keys.csv");
    $jslabelarr = json_decode($jsnames, TRUE);
    // Get the torque PID->userUnit mappings
    $jsuser = CSVtoJSON("./data/torque_keys.csv", $skipheader=True, $userunit=True, $defaultunit=False);
    $jsuserarr = json_decode($jsuser, TRUE);
    // Get the torque PID->defaultUnit mappings
    $jsdefault = CSVtoJSON("./data/torque_keys.csv", $skipheader=True, $userunit=False, $defaultunit=True);
    $jsdefaultarr = json_decode($jsdefault, TRUE);

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
    $v1_label = '"'.$jslabelarr[$v1].'"';
    $v2_label = '"'.$jslabelarr[$v2].'"';

    // Get data for session
    $sessionqry = mysql_query("SELECT time,$v1,$v2
                          FROM $db_table
                          WHERE session=$session_id
                          ORDER BY time DESC", $con) or die(mysql_error());

    // Convert data units
    // TODO: Use the userDefault fields to do these conversions dynamically
    while($row = mysql_fetch_assoc($sessionqry)) {
        // data column #1
        if (substri_count($jslabelarr[$v1], "Speed") > 0) {
            $userUnit_speedVal1 = $userData[$jsuserarr[$v1]];
            $defaultUnit_speedVal1 = $defaultData[$jsdefaultarr[$v1]];
            if ($userUnit_speedVal1 == $defaultUnit_speedVal1) {
                $x = intval($row[$v1]);
            }
            else if ($userUnit_speedVal1 != $defaultUnit_speedVal1) {
                if ($userUnit_speedVal1 == 'mph') {
                    $x = convertSpeed($row[$v1], $kph=False);
                }
                else if (substri_count($userUnit_speedVal1, 'km') > 0) {
                    $x = convertSpeed($row[$v1], $kph=True);
                }
                else {
                    $speed_error = 'A) Error converting speed for '.$v1;
                    die($speed_error);
                }
            }
            else {
                $speed_error = 'B) Error converting speed for '.$v1;
                die($speed_error);
            }
        }
        elseif (substri_count($jslabelarr[$v1], "Temp") > 0) {

            $userUnit_tempVal1 = $userData[$jsuserarr[$v1]];
            $defaultUnit_tempVal1 = $defaultData[$jsdefaultarr[$v1]];
            if ($userUnit_tempVal1 == $defaultUnit_tempVal1) {
                $x = floatval($row[$v1]);
            }
            else if ($userUnit_tempVal1 != $defaultUnit_tempVal1) {
                if (substri_count($userUnit_tempVal1, 'F') > 0) {
                    $x = convertTemp($row[$v1], $celsius=False);
                }
                else if (substri_count($userUnit_tempVal1, 'C') > 0) {
                    $x = convertTemp($row[$v1], $celsius=True);
                }
                else {
                    $temp_error = 'A) Error converting temperature for '.$v1;
                    die($temp_error);
                }
            }
            else {
                $temp_error = 'B) Error converting temperature for '.$v1;
                die($temp_error);
            }
        }
        else {
            $x = intval($row[$v1]);
        }
        $d1[] = array($row['time'], $x);
        $spark1[] = $x;
        // data column #2
        if (substri_count($jslabelarr[$v2], "Speed") > 0) {
            $userUnit_speedVal2 = $userData[$jsuserarr[$v2]];
            $defaultUnit_speedVal2 = $defaultData[$jsdefaultarr[$v2]];
            if ($userUnit_speedVal2 == $defaultUnit_speedVal2) {
                $x = intval($row[$v2]);
            }
            else if ($userUnit_speedVal2 != $defaultUnit_speedVal2) {
                if ($userUnit_speedVal2 == 'mph') {
                    $x = convertSpeed($row[$v2], $kph=False);
                }
                else if (substri_count($userUnit_speedVal2, 'km') > 0) {
                    $x = convertSpeed($row[$v2], $kph=True);
                }
                else {
                    $speed_error = 'A) Error converting speed for '.$v2;
                    die($speed_error);
                }
            }
            else {
                $speed_error = 'B) Error converting speed for '.$v2;
                die($speed_error);
            }
        }
        elseif (substri_count($jslabelarr[$v2], "Temp") > 0) {

            $userUnit_tempVal2 = $userData[$jsuserarr[$v2]];
            $defaultUnit_tempVal2 = $defaultData[$jsdefaultarr[$v2]];
            if ($userUnit_tempVal2 == $defaultUnit_tempVal2) {
                $x = floatval($row[$v2]);
            }
            else if ($userUnit_tempVal2 != $defaultUnit_tempVal2) {
                if (substri_count($userUnit_tempVal2, 'F') > 0) {
                    $x = convertTemp($row[$v2], $celsius=False);
                }
                else if (substri_count($userUnit_tempVal2, 'C') > 0) {
                    $x = convertTemp($row[$v2], $celsius=True);
                }
                else {
                    $temp_error = 'A) Error converting temperature for '.$v2;
                    die($temp_error);
                }
            }
            else {
                $temp_error = 'B) Error converting temperature for '.$v2;
                die($temp_error);
            }
        }
        else {
            $x = intval($row[$v2]);
        }
        $d2[] = array($row['time'], $x);
        $spark2[] = $x;
    }

    mysql_free_result($sessionqry);

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

mysql_close($con);

?>
