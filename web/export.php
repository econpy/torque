<?php
require_once("./db.php");

if (isset($_GET["sid"])) {
    $session_id = $_GET['sid'];
    // Get data for session
    $output = "";
    $sql = mysqli_query("SELECT * FROM $db_table join $db_sessions_table on $db_table.session = $db_sessions_table.session WHERE $db_table.session=".quote_value($session_id)." ORDER BY $db_table.time DESC;") or die(mysqli_error());

    if ($_GET["filetype"] == "csv") {
        $columns_total = mysqli_num_fields($sql);

        // Get The Field Name
        for ($i = 0; $i < $columns_total; $i++) {
            $heading = mysqli_field_name($sql, $i);
            $output .= '"'.$heading.'",';
        }
        $output .="\n";

        // Get Records from the table
        while ($row = mysqli_fetch_array($sql)) {
            for ($i = 0; $i < $columns_total; $i++) {
                $output .='"'.$row["$i"].'",';
            }
            $output .="\n";
        }

        mysqli_free_result($sql);

        // Download the file
        $csvfilename = "torque_session_".$session_id.".csv";
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$csvfilename);

        echo $output;
        exit;
    }
    else if ($_GET["filetype"] == "json") {
        $rows = array();
        while($r = mysqli_fetch_assoc($sql)) {
            $rows[] = $r;
        }
        $jsonrows = json_encode($rows);

        mysqli_free_result($sql);

        // Download the file
        $jsonfilename = "torque_session_".$session_id.".json";
        header('Content-type: application/json');
        header('Content-Disposition: attachment; filename='.$jsonfilename);

        echo $jsonrows;
    }
}

mysqli_close($con);

?>
