<?php
//echo "<!-- Begin session.php at ".date("H:i:s", microtime(true))." -->\r\n";
$loadstart = date("g:i:s A", microtime(true));
$loadmicrostart = explode(' ', microtime());
$loadmicrostart = $loadmicrostart[1] + $loadmicrostart[0];
ini_set('memory_limit', '-1');
require_once("./db.php");
require_once("./auth_user.php");
require_once("./del_session.php");
//require_once("./merge_sessions.php");
require_once("./get_sessions.php");
require_once("./get_columns.php");

//Temp catch for missing creds variables for pull#43
if ($mapProvider == null){
$mapProvider = 'esri';
$mapStyleSelect = 'Streets';
echo '<hr><b>Update variables in creds.php, see readme.md https://github.com/surfrock66/torque#map-providers</b>';
}

// Define and capture variables session time trim.
$timesql = "";
$mintimev = "";
$maxtimev = "";
$timestartval = "-1";
$timeendval = "-1";
if (isset($_GET["tsval"])) {
  $timestartval = $_GET['tsval'];
}
if (isset($_GET["teval"])) {
  $timeendval = $_GET['teval'];
}
 if ($timestartval > 0 && $timeendval > 0) {
$timesql = "and time>=$timestartval and time<=$timeendval";
}

require_once("./plot.php");

$_SESSION['recent_session_id'] = strval(max($sids));
// Check if there is time set in the session; if not, set it
if ( isset($_SESSION['time'] ) ) {
        $timezone = $_SESSION['time'];
} else {
  date_default_timezone_set(date_default_timezone_get());
  $timezone = "GMT ".date('Z')/3600;
}

// Capture the session ID if one has been chosen already
if (isset($_GET["id"])) {
  $session_id = preg_replace('/\D/', '', $_GET['id']);
}

// Call exit function
if (isset($_GET['logout'])) {
    logout_user();
}

// Define and capture variables for maintaining the year and month filters between sessions.
$filteryearmonth = "";
if (isset($_GET["yearmonth"])) {
  $filteryearmonth = $_GET['yearmonth'];
}

// Define some variables to be used in variable management later, specifically when choosing default vars to plot
$i=1;
$var1 = "";
while ( isset($_POST["s$i"]) || isset($_GET["s$i"]) ) {
  ${'var' . $i} = "";
  if (isset($_POST["s$i"])) {
    ${'var' . $i} = $_POST["s$i"];
  }
  elseif (isset($_GET["s$i"])) {
    ${'var' . $i} = $_GET["s$i"];
  }
  $i = $i + 1;
}

// From the output of the get_sessions.php file, populate the page with info from
//  the current session. Using successful existence of a session as a trigger, 
//  populate some other variables as well.
if (isset($sids[0])) {
  if (!isset($session_id)) {
    $session_id = $sids[0];
  }
  //For the merge function, we need to find out, what would be the next session
  $idx = array_search( $session_id, $sids);
  $session_id_next = "";
  if($idx>0) {
    $session_id_next = $sids[$idx-1];
  }
  $tableYear = date( "Y", $session_id/1000 );
  $tableMonth = date( "m", $session_id/1000 );
  $db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";
  // Get GPS data for the currently selectedsession
  $sessionqry = mysqli_query($con, "SELECT kff1006, kff1005, kd FROM $db_table_full
              WHERE session=$session_id $timesql
              ORDER BY time DESC") or die(mysqli_error($con));
  $geolocs = array();
  while($geo = mysqli_fetch_array($sessionqry)) {
    if (($geo["0"] != 0) && ($geo["1"] != 0)) {
      $geolocs[] = array("lat" => $geo["0"], "lon" => $geo["1"], "spd" => $geo["2"]);
    }
  }

  // Get array of time for session and start and end variables 
  $sessionTime = mysqli_query($con, "SELECT time FROM $db_table_full
              WHERE session=$session_id
              ORDER BY time DESC") or die(mysqli_error($con));
  $timearray = array();

  while ($row = mysqli_fetch_row($sessionTime)) {
     $timearray[$i] = $row[0];
     $i = $i + 1;
}
  $itime = implode(",\n", $timearray);
  $maxtimev = array_values($timearray)[0];
  $mintimev = array_values($timearray)[(count($timearray)-1)];

  // Create array of Latitude/Longitude strings in JavaScript format according to the map provider
  $mapdata = array();
  if ($mapProvider === 'google') {
    foreach($geolocs as $d) {
      $mapdata[] = "new google.maps.LatLng(".$d['lat'].", ".$d['lon'].")";
    }
  } elseif ($mapProvider === 'openlayers') { 
    $spddata = array(); //new array to contain speed
    foreach($geolocs as $d) {
      $mapdata[] = "[".$d['lon'].", ".$d['lat']."]"; //openlayers uses longitude before latitude for points
      $spddata[] = $d['spd'];
    }
    $ispddata = implode(",\n          ", $spddata);
  } else { 
   $mapdata = array();
    foreach($geolocs as $d) {
      $mapdata[] = "[".$d['lat'].", ".$d['lon']."]";
    }
  }
  $imapdata = implode(",\n          ", $mapdata);

  // Don't need to set zoom manually
  $setZoomManually = 0;

  // Query the list of years and months where sessions have been logged, to be used later
  $yearmonthquery = mysqli_query($con, "SELECT DISTINCT CONCAT(YEAR(FROM_UNIXTIME(session/1000)), '_', DATE_FORMAT(FROM_UNIXTIME(session/1000),'%m')) as Suffix, 
		CONCAT(MONTHNAME(FROM_UNIXTIME(session/1000)), ' ', YEAR(FROM_UNIXTIME(session/1000))) as Description 
		FROM $db_sessions_table ORDER BY Suffix DESC") or die(mysqli_error($con));
  $yearmonthsuffixarray = array();
  $yearmonthdescarray = array();
  $i = 0;
  while($row = mysqli_fetch_assoc($yearmonthquery)) {
    $yearmonthsuffixarray[$i] = $row['Suffix'];
    $yearmonthdescarray[$i] = $row['Description'];
    $i = $i + 1;
  }

  // Query the list of profiles where sessions have been logged, to be used later
  $profilequery = mysqli_query($con, "SELECT distinct profileName FROM $db_sessions_table ORDER BY profileName asc") or die(mysqli_error($con));
  $profilearray = array();
  $i = 0;
  while($row = mysqli_fetch_assoc($profilequery)) {
    $profilearray[$i] = $row['profileName'];
    $i = $i + 1;
  }

  //Close the MySQL connection, which is why we can't query years later
  mysqli_free_result($sessionqry);
  mysqli_close($con);
} elseif ($mapProvider === 'google') {
  //Default map in case there's no sessions to query.  Very unlikely this will get used.
  $imapdata = "new google.maps.LatLng(37.235, -115.8111)";
  $setZoomManually = 1;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open Torque Viewer</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Matt Nicklay">
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
<?php if ($setZoomManually === 0) { ?>
    <!-- Flot Local Javascript files -->
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.axislabels.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.hiddengraphs.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.multihighlight-delta.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.time.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.tooltip.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.updater.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.resize.min.js"></script>
    <!-- Configure Jquery Flot graph and plot code -->
    <script language="javascript" type="text/javascript">
      $(document).ready(function(){
<?php   $i=1; ?>
<?php   while ( isset(${'var' . $i }) && !empty(${'var' . $i }) ) { ?>
        var <?php echo "s$i"; ?> = [<?php foreach(${"d".$i} as $b) {echo "[".$b[0].", ".((is_numeric($b[1]))?$b[1]:0)."],";} ?>];
<?php     $i = $i + 1; ?>
<?php   } ?>

        var flotData = [
<?php   $i=1; ?>
<?php   while ( isset(${'var' . $i }) && !empty(${'var' . $i }) ) { ?>
            { data: <?php echo "s$i"; ?>, label: <?php echo "${'v'.$i.'_label'}"; ?> }<?php if ( isset(${'var'.($i+1)}) ) echo ","; ?>
<?php     $i = $i + 1; ?>
<?php   } ?>
        ];
        function doPlot(position) {
          $.plot("#placeholder", flotData, {
            xaxes: [ {
              mode: "time",
              timezone: "browser",
              axisLabel: "Time",
              timeformat: "%I:%M%p",
              twelveHourClock: true
            } ],
            yaxes: [ { axisLabel: "" }, {
              alignTicksWithAxis: position == "right" ? 1 : null,
              position: position,
              axisLabel: ""
            } ],
            legend: {
              position: "nw",
              hideable: true,
              backgroundOpacity: 0.1,
              margin: 0
            },
            selection: { mode: "x" },
            grid: {
              hoverable: true,
              clickable: false
            },
            multihighlightdelta: { mode: 'x' },
            tooltip: false,
            tooltipOpts: {
              //content: "%s at %x: %y",
              content: "%x",
              xDateFormat: "%m/%d/%Y %I:%M:%S%p",
              twelveHourClock: true,
              onHover: function(flotItem, $tooltipEl) {
                console.log(flotItem, $tooltipEl);
              }
            }
          }
        )}
<?php   if ( $var1 <> "" ) { ?>
        doPlot("right");
<?php   } ?>
        $("button").click(function () {
          doPlot($(this).text());
        });
      });
    </script>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
<?php } else { ?>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
<?php } ?>
  </head>
  <body>
    <div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
<?php    if ( empty($_SESSION['torque_user']) ) { ?>
          <a class="navbar-brand" href="session.php">Open Torque Viewer</a>
<?php    } else { ?>
          <a class="navbar-brand" href="session.php">Open Torque Viewer</a><span class="navbar-brand" style="margin-left:25px;">{ <?php echo $_SESSION['torque_user'] ?><a><img width="20" heigth="20" style="margin-left:10px;margin-top:-2px;" src="./static/logout.png" onClick="location.href='session.php?logout=true'" /></a> }</span>
<?php    } ?>
        </div>
      </div>
    </div>
    <div id="map-container" class="col-md-7 col-xs-12">
      <div id="map-canvas"></div>
    </div>
<?php require("./map_providers.php"); ?>
    <div id="right-container" class="col-md-5 col-xs-12">
      <div id="right-cell">
        <h4>Select Session</h4>
        <div class="row center-block" style="padding-bottom:4px;">
          <!-- Filter the session list by year and month -->
          <h5>Filter Sessions (Default date filter is current year/month)</h5>
          <form method="post" class="form-horizontal" role="form" action="url.php?id=<?php echo $session_id; ?>">
            <table width="100%">
              <tr>
                <!-- Profile Filter -->
                <td width="34%">
                  <select id="selprofile" name="selprofile" class="form-control chosen-select" data-placeholder="Select Profile">
                    <option value=""></option>
                    <option value="ALL"<?php if ($filterprofile == "ALL") echo ' selected'; ?>>Any Profile</option>
<?php $i = 0; ?>
<?php while(isset($profilearray[$i])) { ?>
                    <option value="<?php echo $profilearray[$i]; ?>"<?php if ($filterprofile == $profilearray[$i]) echo ' selected'; ?>><?php echo $profilearray[$i]; ?></option>
<?php   $i = $i + 1; ?>
<?php } ?>
                  </select>
                </td>
                <td width="2%"></td>

                <!-- Year Month Filter -->
                <td width="34%">
                  <select id="selyearmonth" name="selyearmonth" class="form-control chosen-select" data-placeholder="Select Year/Month">
                    <option value=""></option>
<?php $i = 0; ?>
<?php while(isset($yearmonthsuffixarray[$i])) { ?>
                    <option value="<?php echo $yearmonthsuffixarray[$i]; ?>"<?php if ($filteryearmonth == $yearmonthsuffixarray[$i]) { echo ' selected'; } else if ($i == 0) { echo 'selected'; } ?>><?php echo $yearmonthdescarray[$i]; ?></option>
<?php   $i = $i + 1; ?>
<?php } ?>
                  </select>
                </td>

                <td width="13%">
                  <div align="center" style="padding-top:2px;"><input class="btn btn-info btn-sm" type="submit" id="formfilterdates" name="filterdates" value="Filter Sessions"></div>
                </td>
              </tr>
            </table>
            <noscript><input type="submit" id="datefilter" name="datefilter" class="input-sm"></noscript>
          </form><br />
          <!-- Session Select Drop-Down List -->
          <form method="post" class="form-horizontal" role="form" action="url.php">
            <select id="seshidtag" name="seshidtag" class="form-control chosen-select" onchange="this.form.submit()" data-placeholder="Select Session..." style="width:100%;">
              <option value=""></option>
<?php foreach ($seshdates as $dateid => $datestr) { ?>
              <option value="<?php echo $dateid; ?>"<?php if ($dateid == $session_id) echo ' selected'; ?>><?php echo $datestr; echo $seshprofile[$dateid]; if ($show_session_length) {echo $seshsizes[$dateid];} ?><?php if ($dateid == $session_id) echo ' (Current Session)'; ?></option>
<?php } ?>
            </select>
<?php   if ( $filteryearmonth <> "" ) { ?>
            <input type="hidden" name="selyearmonth" id="selyearmonth" value="<?php echo $filteryearmonth; ?>" />
<?php   } ?>
            <noscript><input type="submit" id="seshidtag" name="seshidtag" class="input-sm"></noscript>
          </form>
<?php if(isset($session_id) && !empty($session_id)){ ?>
          <div class="btn-group btn-group-justified">
            <table style="width:100%">
              <tr>
                <td>
                  <form method="post" class="form-horizontal" role="form" action="merge_sessions.php?mergesession=<?php echo $session_id; ?>" id="formmerge">
                    <div align="center" style="padding-top:6px;"><input class="btn btn-info btn-sm" type="submit" id="formmerge" name="merge" value="Merge..." title="Merge this session (<?php echo $seshdates[$session_id]; ?>) with the other sessions." /></div>
                  </form>
                </td>
                <td>
                  <form method="post" class="form-horizontal" role="form" action="session.php?deletesession=<?php echo $session_id; ?>" id="formdelete">
                    <div align="center" style="padding-top:6px;"><input class="btn btn-info btn-sm" type="submit" id="formdelete" name="delete" value="Delete" title="Delete this session (<?php echo $seshdates[$session_id]; ?>)." /></div>
                  </form>
                </td>
                <script type="text/javascript">
                  $('#formdelete').submit(function() {
                    var c = confirm("Click OK to delete session (<?php echo $seshdates[$session_id]; ?>).");
                    return c; //you can just return c because it will be true or false
                  });
                </script>
              </tr>
            </table>
          </div>
<?php } ?>
        </div>

<!-- slider -->
  <script>
   const jsTimeMap = [<?php echo $itime; ?>].reverse(); //Session time array, reversed for silder
   var minTimeStart = [<?php echo $mintimev; ?>];
   var maxTimeEnd = [<?php echo $maxtimev; ?>];
   var TimeStartv = [<?php echo $timestartval; ?>]; 
   var TimeEndv = [<?php echo $timeendval; ?>];

  function timelookup(t) { //retrun array index, used for slider steps/value, RIP IE, no polyfill 
    var fx = (e) => e == t;
    var out = jsTimeMap.findIndex(fx);
    return out;
  }
   
  var TimeStartv = timelookup(TimeStartv); 
  var TimeEndv = timelookup(TimeEndv);

  if (TimeStartv  == -1 || TimeEndv == -1) {
    var TimeStartv = timelookup(minTimeStart);
    var TimeEndv = timelookup(maxTimeEnd);
  }

  function ctime(t) {//covert the epoch time to local readable 
   var date = new Date(t);
   return  date.toLocaleTimeString();
  }

  var sv = $(function() {//jquery range slider
    $( "#slider-range11" ).slider({
      range: true,
      min: 0 ,
      max:  jsTimeMap.length -1,
      values: [ TimeStartv, TimeEndv ],
      slide: function( event, ui ) {
    $( "#slider-time" ).val( ctime(jsTimeMap[ui.values[ 0 ]]) + " - " + ctime(jsTimeMap[ui.values[ 1 ]]));
      }});
    $( "#slider-time" ).val( ctime(jsTimeMap[$( "#slider-range11" ).slider( "values", 0 )]) +  " - " + ctime(jsTimeMap[$( "#slider-range11" ).slider( "values", 1 )])); 
    $( "#slider-range11" ).on( "slidechange", function( event, ui ){$('#slider-time').attr("sv0", jsTimeMap[$('#slider-range11').slider("values", 0)])});
    $( "#slider-range11" ).on( "slidechange", function( event, ui ){$('#slider-time').attr("sv1", jsTimeMap[$('#slider-range11').slider("values", 1)])});
  } );

  function settimev(){//set post array for slider
    var sv0 =  document.getElementById("slider-time").getAttribute("sv0");
    var sv1 =  document.getElementById("slider-time").getAttribute("sv1");
    var sv3 = [<?php echo $timestartval; ?>];

    if (sv0 <= 0 && sv1 <= 0){
    var sv0 = [<?php echo $timestartval; ?>];
    var sv1 = [<?php echo $timeendval; ?>];
    }
    if (sv0 == -1 && sv1 == -1){
    var sv0 = minTimeStart;
    var sv1 = maxTimeEnd;
    }
    var svarr = [sv0,sv1];
    document.getElementById("formplotdata").svdata.value = svarr;
  }
</script>
<span class="h4">Trim Session</span>
<input type="text" id="slider-time" readonly style="width:300px; border:0; color:#f6931f; font-weight:bold;" sv0="-1" sv1="-1">
<div id="slider-range11"></div>

<!-- Variable Select Block -->
<?php if ($setZoomManually === 0) { ?>
        <h4>Select Variables to Compare</h4>
          <div class="row center-block" style="padding-top:3px;">
            <form method="post" role="form" action="url.php?makechart=y&seshid=<?php echo $session_id; ?>" id="formplotdata" onsubmit="settimev()">
             <input type="hidden" name="svdata" id="svdata" value="" /> 
              <select data-placeholder="Choose OBD2 data..." multiple class="chosen-select" size="<?php echo $numcols; ?>" style="width:100%;" id="plot_data" onsubmit="onSubmitIt" name="plotdata[]">
                <option value=""></option>
<?php   foreach ($coldata as $xcol) { ?>
                <option value="<?php echo $xcol['colname']; ?>" <?php $i = 1; while ( isset(${'var' . $i}) ) { if ( (${'var' . $i} == $xcol['colname'] ) OR ( $xcol['colfavorite'] == 1 ) ) { echo " selected"; } $i = $i + 1; } ?>><?php echo $xcol['colcomment']; ?></option>
<?php   } ?>
            </select>
<?php   if ( $filteryearmonth <> "" ) { ?>
            <input type="hidden" name="selyearmonth" id="selyearmonth" value="<?php echo $filteryearmonth; ?>" />
<?php   } ?>
            <div align="center" style="padding-top:6px;"><input class="btn btn-info btn-sm" type="submit" id="formplotdata" name="plotdata[]" value="Plot!"></div>
          </form>
        </div>
<?php } else { ?>

<!-- Plot Block -->
        <h4>Plot</h4>
        <div align="center" style="padding-top:10px;">
          <h5><span class="label label-warning">Select a session first!</span></h5>
        </div><br />
<?php } ?>

<!-- Chart Block -->
        <h4>Chart</h4>
        <div class="row center-block" style="padding-bottom:5px;">
<?php if ($setZoomManually === 0) { ?>
          <!-- 2015.07.22 - edit by surfrock66 - Don't display anything if no variables are set (default) -->
<?php   if ( $var1 == "" ) { ?>
          <div align="center" style="padding-top:10px;">
            <h5><span class="label label-warning">No Variables Selected to Plot!</span></h5>
          </div>
<?php   } else { ?>
          <div class="demo-container">
            <div id="placeholder" class="demo-placeholder" style="height:300px;"></div>
          </div>
<?php   } ?>
<?php } else { ?>
          <div align="center" style="padding-top:10px;">
            <h5><span class="label label-warning">Select a session first!</span></h5>
          </div>
<?php } ?>
        </div><br />

<!-- Data Summary Block -->
        <h4>Data Summary</h4>
        <div class="row center-block">
<?php if ($setZoomManually === 0) { ?>
          <!-- 2015.07.22 - edit by surfrock66 - Don't display anything if no variables are set (default) -->
<?php   if ( $var1 <> "" ) { ?>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Min/Max</th>
                  <th>25th Pcnt</th>
                  <th>75th Pcnt</th>
                  <th>Mean</th>
                  <th>Sparkline</th>
                </tr>
              </thead>
              <!-- 2015.08.05 - Edit by surfrock66 - Code to plot unlimited variables -->
              <tbody>
<?php     $i=1; ?>
<?php     while ( isset(${'var' . $i }) ) { ?>
                <tr>
                  <td><strong><?php echo substr(${'v' . $i . '_label'}, 1, -1); ?></strong></td>
                  <td><?php echo ${'min' . $i}.'/'.${'max' . $i}; ?></td>
                  <td><?php echo ${'pcnt25data' . $i}; ?></td>
                  <td><?php echo ${'pcnt75data' . $i}; ?></td>
                  <td><?php echo ${'avg' . $i}; ?></td>
                  <td><span class="line"><?php echo ${'sparkdata' . $i}; ?></span></td>
                </tr>
<?php       $i = $i + 1; ?>
<?php     } ?>
              </tbody>
            </table>
          </div>
<?php   } else { ?>
          <div align="center" style="padding-top:10px;">
            <h5><span class="label label-warning">No Variables Selected to Plot!</span></h5>
          </div>
<?php   } ?>
<?php } else { ?>
          <div align="center" style="padding-top:5px;">
            <h5><span class="label label-warning">Select a session first!</span></h5>
          </div>
<?php } ?>
        </div><br />

<!-- Export Data Block -->
        <h4>Export Data</h4>
        <div class="row center-block" style="padding-bottom:18px;">
<?php if ($setZoomManually === 0) { ?>
          <div class="btn-group btn-group-justified">
            <a class="btn btn-default" role="button" href="<?php echo './export.php?sid='.$session_id.'&filetype=csv'; ?>">CSV</a>
            <a class="btn btn-default" role="button" href="<?php echo './export.php?sid='.$session_id.'&filetype=json'; ?>">JSON</a>
          </div>
<?php } else { ?>
          <div align="center" style="padding-top:10px;">
            <h5><span class="label label-warning">Select a session first!</span></h5>
          </div>
<?php } ?>
        </div>
        <div class="row center-block" style="padding-bottom:18px;text-align:center;">
          <a href="./pid_edit.php" title="Edit PIDs">Edit PIDs</a><br />
          <a href="https://github.com/surfrock66/torque" title="View Source On Github">View Source On Github</a>
          <p style="font-size:10px;margin-top:20px;" >
            Render Start: <?php echo $loadstart; ?>; Render End: <?php $loadend = date("h:i:s A", microtime(true)); echo $loadend; ?><br />
            Load Time: <?php $loadmicroend = explode(' ', microtime()); $loadmicroend = $loadmicroend[1] + $loadmicroend[0]; echo $loadmicroend-$loadmicrostart; ?> seconds<br />
            Session ID: <?php echo $session_id; ?>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>
<?php //echo "<!-- End session.php at ".date("H:i:s", microtime(true))." -->\r\n"; ?>
