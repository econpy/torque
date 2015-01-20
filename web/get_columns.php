<?php
require_once("./creds.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db("INFORMATION_SCHEMA") or die(mysql_error());

// Create array of column name/comments for chart data selector form
$colqry = mysql_query("SELECT COLUMN_NAME,COLUMN_COMMENT,DATA_TYPE
                           FROM COLUMNS WHERE TABLE_SCHEMA='".$db_name."'
                           AND TABLE_NAME='".$db_table."'") or die(mysql_error());

// Select the column name and comment for data that can be plotted.
while ($x = mysql_fetch_array($colqry)) {
    if ((substr($x[0], 0, 1) == "k") && ($x[2] == "float")) {
        $coldata[] = array("colname"=>$x[0], "colcomment"=>$x[1]);
    }
}

$numcols = strval(count($coldata)+1);

mysql_free_result($colqry);


//TODO: Do this once in a dedicated file
if (isset($_POST["id"])) {
    $session_id = preg_replace('/\D/', '', $_POST['id']);
}
elseif (isset($_GET["id"])) {
    $session_id = preg_replace('/\D/', '', $_GET['id']);
}


// If we have a certain session, check which colums contain no information at all
$coldataempty = array();
if (isset($session_id)) {
    mysql_select_db($db_name) or die(mysql_error());

    //Count distinct values for each known column
    //TODO: Unroll loop into single query
    foreach ($coldata as $col)
    {
        $colname = $col["colname"];

        // Count number of different values for this specific field
        $colqry = mysql_query("SELECT count(DISTINCT $colname)<2 as $colname
                               FROM $db_table
                               WHERE session=$session_id") or die(mysql_error());
        $colresult = mysql_fetch_assoc($colqry);
        $coldataempty[$colname] = $colresult[$colname];
    }

    //print_r($coldataempty);
}

mysql_close();

?>
