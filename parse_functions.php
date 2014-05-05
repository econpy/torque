<?php

// Convert a CSV object into our JSON format
function CSVtoJSON($csvFile){
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle) ) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    $columns = array();
    foreach ($line_of_text as $line) {
        if($line["0"] != "") {
            $columns[$line["0"]] = $line["1"];
        }
    }
    return json_encode($columns);
}

// Function to count uppercase strings
function substri_count($haystack, $needle) {
    return substr_count(strtoupper($haystack), strtoupper($needle));
}

?>
