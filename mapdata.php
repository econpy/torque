<?php
require("./creds.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// Get Latitude/Longitude data from MySQL
$geoqry = mysql_query("SELECT kff1006, kff1005
                      FROM $db_table
                      ORDER BY time DESC
                      LIMIT 5000") or die(mysql_error());
$latlong = array();
while($geo = mysql_fetch_array($geoqry)) {
    if (($geo["0"] != 0) && ($geo["1"] != 0)) {
        $latlong[] = array("latitude" => $geo["0"], "longitude" => $geo["1"]);
    }
}

// Create array of Latitude/Longitude strings in Google Maps JavaScript format
$mapdata = array();
foreach($latlong as $d) {
    $mapdata[] = "new google.maps.LatLng(" . $d['latitude'] . ", " . $d['longitude'] . ")";
}
$imapdata = implode(",\n                    ", $mapdata);

// Centering location for the map is the most recent location
$centerlat = $latlong[0]["latitude"];
$centerlong = $latlong[0]["longitude"];
?>
