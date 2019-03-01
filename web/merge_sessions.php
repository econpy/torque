<?php
//echo "<!-- Begin merge_sessions.php at ".date("H:i:s", microtime(true))." -->\r\n";
require_once("./db.php");
require_once("./get_sessions.php");

if (!isset($_SESSION)) { session_start(); }

if (isset($_POST["mergesession"])) {
    $mergesession = preg_replace('/\D/', '', $_POST['mergesession']);
}
elseif (isset($_GET["mergesession"])) {
    $mergesession = preg_replace('/\D/', '', $_GET['mergesession']);
}

$sessionids = array();

// 2016.04.11 - edit by surfrock66 - Define some variables to be used in 
//  variable management later, specifically when choosing sessions to merge
$i=1;
$mergesess1 = "";
foreach ($_GET as $key => $value) {
    if ($key != "mergesession") {
        ${'mergesess' . $i} = $key;
        array_push($sessionids, $key);
        $i = $i + 1;
    } else {
        array_push($sessionids, $value);
    }
}

//if (isset($mergesession) && !empty($mergesession) && isset($mergesessionwith) && !empty($mergesessionwith) ) {
if (isset($mergesession) && !empty($mergesession) && isset($mergesess1) && !empty($mergesess1) ) {
    $qrystr = "SELECT MIN(timestart) as timestart, MAX(timeend) as timeend, MIN(session) as session, SUM(sessionsize) as sessionsize FROM $db_sessions_table WHERE session = ".quote_value($mergesession);
    $i=1;
    while (isset(${'mergesess' . $i}) || !empty(${'mergesess' . $i})) {
        $qrystr = $qrystr . " OR session = '" . ${'mergesess' . $i} . "'";
        $i = $i + 1;
    }
    $mergeqry = mysqli_query($con, $qrystr) or die(mysqli_error($con));
    $mergerow = mysqli_fetch_assoc($mergeqry);
    $newsession = $mergerow['session'];
    $newtimestart = $mergerow['timestart'];
    $newtimeend = $mergerow['timeend'];
    $newsessionsize = $mergerow['sessionsize'];
    mysqli_free_result($mergeqry);

    $tableYear = date( "Y", $mergesession/1000 );
    $tableMonth = date( "m", $mergesession/1000 );
    $db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";

    foreach ($sessionids as $value) {
        if ($value == $newsession) {
            $updatequery = "UPDATE $db_sessions_table SET timestart=$newtimestart, timeend=$newtimeend, sessionsize=$newsessionsize where session=$newsession";
            mysqli_query($con, $updatequery) or die(mysqli_error($con));
        } else {
            $delquery = "DELETE FROM $db_sessions_table WHERE session = '$value'";
            mysqli_query($con, $delquery) or die(mysqli_error($con));
            $updatequery = "UPDATE $db_table_full SET session=$newsession WHERE session=".quote_value($value);
            mysqli_query($con, $updatequery) or die(mysqli_error($con));
        }
    }
    //Show merged session
    $session_id = $mergesession;
    header('Location: session.php?id=' . $mergesession);
} elseif (isset($mergesession) && !empty($mergesession)) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open Torque Viewer</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Joe Gullo (surfrock66)">
    <link rel="stylesheet" href="static/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
    <link rel="stylesheet" href="static/css/torque.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  </head>
  <body>
    <div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="session.php">Open Torque Viewer</a>
        </div>
      </div>
    </div>
    <form style="margin-top:50px;" action="merge_sessions.php" method="get" id="formmerge" >
      <input type="hidden" name="mergesession" value="<?php echo $mergesession; ?>" />
      <div width="100%" align="center"><input class="btn btn-info btn-sm" type="submit" value="Merge Selected Sessions" /></div>
      <table class="table" style="width:98%;margin:0px auto;">
        <thead>
          <th>Merge?</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Session Duration</th>
          <th>Number of Datapoints</th>
          <th>Profile</th>
        </thead>
        <tbody>
<?php
    $sessqry = mysqli_query($con, "SELECT timestart, timeend, session, profileName, sessionsize FROM $db_sessions_table WHERE sessionsize >= $min_session_size ORDER BY session desc") or die(mysqli_error($con));
    $i = 0;
    while ($x = mysqli_fetch_array($sessqry)) {
?>
          <tr>
            <td><input type="checkbox" name="<?php echo $x['session']; ?>" <?php if ($x['session'] == $mergesession) { echo "checked disabled"; } ?>/></td>
            <td id="start:<?php echo $x['session']; ?>"><?php echo date("F d, Y h:ia", substr($x["timestart"], 0, -3)); ?></td>
            <td id="end:<?php echo $x['session']; ?>"><?php echo date("F d, Y h:ia", substr($x["timeend"], 0, -3)); ?></td>
            <td id="length:<?php echo $x['session']; ?>"><?php echo gmdate("H:i:s", ($x["timeend"] - $x["timestart"])/1000); ?></td>
            <td id="size:<?php echo $x['session']; ?>"><?php echo $x["sessionsize"]; ?></td>
            <td id="profile:<?php echo $x['session']; ?>"><?php echo $x["profileName"]; ?></td>
          </tr>
<?php
    }
?>
        </tbody>
      </table>
    </form>
    <script type="text/javascript">
      $('#formmerge').submit(function() {
        var c = confirm("Click OK to merge the selected session(s) with session <?php echo $mergesession; ?>.\nPlease make sure what you're trying to do makes sense, this cannot be easily undone!");
        return c; //you can just return c because it will be true or false
      });
    </script>
    <div id="status" style="padding:10px; background:#88C4FF; color:#000; font-weight:bold; font-size:12px; margin-bottom:10px; display:none; width:90%;"></div>
  </body>
</html>
<?php
    mysqli_free_result($sessqry);
}
mysqli_close($con);
//echo "<!-- End merge_sessions.php at ".date("H:i:s", microtime(true))." -->\r\n";
?>
