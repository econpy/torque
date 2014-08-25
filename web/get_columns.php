<?php
require 'creds.php';

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db("INFORMATION_SCHEMA", $con) or die(mysql_error());

// Create array of column name/comments for chart data selector form
$colqry = mysql_query("SELECT COLUMN_NAME,COLUMN_COMMENT,DATA_TYPE
                       FROM COLUMNS
                       WHERE TABLE_SCHEMA='".$db_name."'
                       AND TABLE_NAME='".$db_table."'", $con) or die(mysql_error());

// Select the column name and comment for data that can be plotted.
while ($x = mysql_fetch_array($colqry)) {
    if ((substr($x[0], 0, 1) == "k") && ($x[2] == "float")) {
        $coldata[] = array("colname"=>$x[0], "colcomment"=>$x[1]);
    }
}

$numcols = strval(count($coldata)+1);

mysql_free_result($colqry);
mysql_close($con);

?>
