<?php
require_once("./db.php");

if (isset($_GET["sid"])) {
    $session_id = $_GET['sid'];
    // Get data for session
    $output = "";
    $sql = mysql_query("SELECT * FROM $db_table join $db_sessions_table on $db_table.session = $db_sessions_table.session WHERE $db_table.session=".quote_value($session_id)." ORDER BY $db_table.time DESC;") or die(mysql_error());

    if ($_GET["filetype"] == "csv") {
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

        mysql_free_result($sql);

        // Download the file
        $csvfilename = "torque_session_".$session_id.".csv";
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$csvfilename);

        echo $output;
        exit;
    }
    else if ($_GET["filetype"] == "json") {
        $rows = array();
        while($r = mysql_fetch_assoc($sql)) {
            $rows[] = $r;
        }
        $jsonrows = json_encode($rows);

        mysql_free_result($sql);

        // Download the file
        $jsonfilename = "torque_session_".$session_id.".json";
        header('Content-type: application/json');
        header('Content-Disposition: attachment; filename='.$jsonfilename);

        echo $jsonrows;
    }
}

mysql_close($con);

?>
