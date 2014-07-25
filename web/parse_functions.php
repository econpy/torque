<?php

// Convert a CSV object into our JSON format
function CSVtoJSON($csvFile, $skipheader=True) {
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
            for ($c=$skip; $c < $num; $c++) {
                $keyidarray[$csvdata[0]] = $csvdata[1];
            }
        }
        fclose($handle);
    }
    return json_encode($keyidarray);
}

// Function to count uppercase strings
function substri_count($haystack, $needle) {
    return substr_count(strtoupper($haystack), strtoupper($needle));
}

// Calculate average
function average($arr)
{
    if (!count($arr)) return 0;

    $sum = 0;
    for ($i = 0; $i < count($arr); $i++)
    {
        $sum += $arr[$i];
    }

    return $sum / count($arr);
}


// Calculate percentile
function calc_percentile($data, $percentile){
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
    if(!is_float($floatval)){
        $result = $data[$intvalindex];
    }else {
        if($count > $intvalindex+1)
            $result = $floatval*($data[$intvalindex+1] - $data[$intvalindex]) + $data[$intvalindex];
        else
            $result = $data[$intvalindex];
    }
    return $result;
}

// Make comma separated string for sparkline data.
function make_spark_data($sparkarry) {
    return implode(",", array_reverse($sparkarray));
}


?>
