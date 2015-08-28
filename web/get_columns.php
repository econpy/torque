<?php
require_once("./creds.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());

// Create array of column name/comments for chart data selector form
// 2015.08.21 - edit by surfrock66 - Rather than pull from the column comments,
//   oull from a new database created which manages variables. Include
//   a column flagging whether a variable is populated or not.
$colqry = mysql_query("SELECT id,description,type FROM ".$db_name.".keys WHERE populated = 1 ORDER BY description") or die(mysql_error());
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

$coldataempty = array();
mysql_close();

?>
