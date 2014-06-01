<?php
require("./creds.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

if (isset($_GET["sid"])) {
    $session_id = intval(mysql_escape_string($_GET['sid']));
}
else {
    $session_id = 0;
}

// Get data for session
$output = "";
$sql = mysql_query("SELECT * FROM $db_table WHERE session=$session_id ORDER BY time DESC;") or die(mysql_error());
$columns_total = mysql_num_fields($sql);

// Get The Field Name
for ($i = 0; $i < $columns_total; $i++) {
    $heading = mysql_field_name($sql, $i);
    $output .= '"'.$heading.'",';
}
$output .="\n";

// Get Records from the table
while ($row = mysql_fetch_array($sql)) {
    for ($i = 0; $i < $columns_total; $i++) {
        $output .='"'.$row["$i"].'",';
    }
    $output .="\n";
}

// Download the file
$csvfilename = "torque_session_".$session_id.".csv";
header('Content-type: application/csv');
header('Content-Disposition: attachment; filename='.$csvfilename);

echo $output;
exit;

?>
