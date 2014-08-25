<?php

// Convert a CSV object into our JSON format
function CSVtoJSON($csvFile, $skipheader=True, $userunit=False, $defaultunit=False) {
    $keyidarray = array();
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($csvdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($csvdata);
            if ($skipheader === True) {
                $skip = 1;
            }
            else {
                $skip = 0;
            }
            if (($userunit == False) and ($defaultunit == False)) {
                for ($c=$skip; $c < $num; $c++) {
                    $keyidarray[$csvdata[0]] = $csvdata[1];
                }
            }
            else if (($userunit == True) and ($defaultunit == False)) {
                for ($c=$skip; $c < $num; $c++) {
                    $keyidarray[$csvdata[0]] = $csvdata[2];
                }
            }
            else if (($userunit == False) and ($defaultunit == True)) {
                for ($c=$skip; $c < $num; $c++) {
                    $keyidarray[$csvdata[0]] = $csvdata[3];
                }
            }
        }
        fclose($handle);
    }
    return json_encode($keyidarray);
}

function convertTemp($temperatureval, $celsius=True) {
    if ($celsius == False) {
        $newtemp = floatval($temperatureval)*9/5+32;
        return $newtemp;
    }
    else {
        $newtemp = floatval(floatval($temperatureval)-32)*5/9;
        return $newtemp;
    }
}

function convertSpeed($speedval, $kph=True) {
    if ($kph == False) {
        $newspeed = intval($speedval)*0.621371;
        return $newspeed;
    }
    else {
        $newspeed = intval($speedval)*1.60934;
        return $newspeed;
    }
}

// Function to count uppercase strings
function substri_count($haystack, $needle) {
    return substr_count(strtoupper($haystack), strtoupper($needle));
}

// Calculate average
function average($arr) {
    if (!count($arr)) {
        return 0;
    }
    else {
        $sum = 0;
        for ($i = 0; $i < count($arr); $i++) {
            $sum += $arr[$i];
        }
        return $sum / count($arr);
    }
}


// Calculate percentile
function calc_percentile($data, $percentile) {
    if( 0 < $percentile && $percentile < 1 ) {
        $p = $percentile;
    }else if( 1 < $percentile && $percentile <= 100 ) {
        $p = $percentile * .01;
    }else {
        return "";
    }
    $count = count($data);
    $allindex = ($count-1)*$p;
    $intvalindex = intval($allindex);
    $floatval = $allindex - $intvalindex;
    sort($data);
    if (!is_float($floatval)) {
        $result = $data[$intvalindex];
    }
    else {
        if ($count > $intvalindex+1) {
            $result = $floatval*($data[$intvalindex+1] - $data[$intvalindex]) + $data[$intvalindex];
        }
        else {
            $result = $data[$intvalindex];
        }
    }
    return $result;
}

// Make comma separated string for sparkline data.
function make_spark_data($sparkarry) {
    return implode(",", array_reverse($sparkarray));
}

?>
