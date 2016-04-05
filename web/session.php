<?php
//echo "<!-- Begin session.php at ".date("H:i:s", microtime(true))." -->\r\n";
ini_set('memory_limit', '-1');
require_once("./creds.php");
require_once("./auth_user.php");
require_once("./del_session.php");
require_once("./merge_sessions.php");
require_once("./get_sessions.php");
require_once("./get_columns.php");
require_once("./plot.php");

$_SESSION['recent_session_id'] = strval(max($sids));
// Check if there is time set in the session; if not, set it
if ( isset($_SESSION['time'] ) ) {
        $timezone = $_SESSION['time'];
} else {
  date_default_timezone_set(date_default_timezone_get());
  $timezone = "GMT ".date('Z')/3600;
}

// Define the database connections
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

// Capture the session ID if one has been chosen already
if (isset($_GET["id"])) {
  $session_id = preg_replace('/\D/', '', $_GET['id']);
}

// 2015.08.31 - edit by surfrock66 - Define and capture variables for maintaining
//  the year and month filters between sessions.
$filteryear = "";
$filtermonth = "";
if (isset($_GET["year"])) {
  $filteryear = $_GET['year'];
}
if (isset($_GET["month"])) {
  $filtermonth = $_GET['month'];
}

// 2015.07.22 - edit by surfrock66 - Define some variables to be used in 
//  variable management later, specifically when choosing default vars to plot
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
  // Get GPS data for the currently selectedsession
  $sessionqry = mysql_query("SELECT kff1006, kff1005 FROM $db_table
              WHERE session=$session_id
              ORDER BY time DESC", $con) or die(mysql_error());
  $geolocs = array();
  while($geo = mysql_fetch_array($sessionqry)) {
    if (($geo["0"] != 0) && ($geo["1"] != 0)) {
      $geolocs[] = array("lat" => $geo["0"], "lon" => $geo["1"]);
    }
  }

  // Create array of Latitude/Longitude strings in Google Maps JavaScript format
  $mapdata = array();
  foreach($geolocs as $d) {
    $mapdata[] = "new google.maps.LatLng(".$d['lat'].", ".$d['lon'].")";
  }
  $imapdata = implode(",\n          ", $mapdata);

  // Don't need to set zoom manually
  $setZoomManually = 0;

  // Query the list of years where sessions have been logged, to be used later
  $yearquery = mysql_query("SELECT YEAR(FROM_UNIXTIME(session/1000)) as 'year'
              FROM $db_sessions_table WHERE session <> ''
              GROUP BY YEAR(FROM_UNIXTIME(session/1000)) 
              ORDER BY YEAR(FROM_UNIXTIME(session/1000))", $con) or die(mysql_error());
  $yeararray = array();
  $i = 0;
  while($row = mysql_fetch_assoc($yearquery)) {
    $yeararray[$i] = $row['year'];
    $i = $i + 1;
  }

  // Query the list of profiles where sessions have been logged, to be used later
  $profilequery = mysql_query("SELECT distinct profileName FROM $db_sessions_table ORDER BY profileName asc", $con) or die(mysql_error());
  $profilearray = array();
  $i = 0;
  while($row = mysql_fetch_assoc($profilequery)) {
    $profilearray[$i] = $row['profileName'];
    $i = $i + 1;
  }

  //Close the MySQL connection, which is why we can't query years later
  mysql_free_result($sessionqry);
  mysql_close($con);
} else {
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
    <!-- Pull the current timezone -->
<!--    <script language="javascript" type="text/javascript">
      $(document).ready(function() {
        if("<?php echo $timezone; ?>".length==0){
          var visitortime = new Date();
          var visitortimezone = "GMT " + -visitortime.getTimezoneOffset()/60;
          var timezoneurl = $(location).attr('href').split('?')[0].replace('session', 'timezone');
          $.ajax({
            type: "GET",
            url: timezoneurl,
            data: 'time='+ visitortimezone,
            success: function(){
              location.reload();
            }
          });
        }
      });
    </script>-->
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
    <!-- Initialize the google maps javascript code -->
    <script language="javascript" type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
    <script language="javascript" type="text/javascript">
      function initialize() {
        var mapDiv = document.getElementById('map-canvas');
        var map = new google.maps.Map(mapDiv, {
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          mapTypeControl: true,
          mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            poistion: google.maps.ControlPosition.TOP_RIGHT,
            mapTypeIds: [google.maps.MapTypeId.ROADMAP,
            google.maps.MapTypeId.TERRAIN,
            google.maps.MapTypeId.HYBRID,
            google.maps.MapTypeId.SATELLITE]
          },
          navigationControl: true,
          navigationControlOptions: {
            style: google.maps.NavigationControlStyle.ZOOM_PAN
          },
          scaleControl: true,
          disableDoubleClickZoom: false,
          draggable: true,
          streetViewControl: true,
          draggableCursor: 'move'
        });

        // The potentially large array of LatLng objects for the roadmap
        var path = [<?php echo $imapdata; ?>];

        // Create a boundary using the path to automatically configure
        // the default centering location and zoom.
        var bounds = new google.maps.LatLngBounds();
        for (i = 0; i < path.length; i++) {
          bounds.extend(path[i]);
        }
        map.fitBounds(bounds);

        // If required/desired, set zoom manually now that bounds have been set
<?php if ($setZoomManually === 1) { ?>
        zoomChange = google.maps.event.addListenerOnce(map, 'bounds_changed',
          function(event) {
            if (this.getZoom()){
            this.setZoom(16);
            }
          });
        setTimeout(function(){
        google.maps.event.removeListener(zoomChange)
        }, 1000);

        var contentString = '<div>'+
          '<div class="alert alert-info">'+
          '  <p class="lead" align="center">'+
          "  You're seeing this window because "+
          '<br />'+
          "you haven't selected a session. "+
          '<br /><br />'+
          " Select one from the dropdown menu."+
          '  </p>'+
          '</div>'+
          '</div>';

        var infowindow = new google.maps.InfoWindow({
          content: contentString
        });

        var marker = new google.maps.Marker({
          position: <?php echo $imapdata; ?>,
          map: map,
          title: 'Area 51'
        });

        setTimeout(function() {
        infowindow.open(map, marker)
        }, 2000);
<?php } ?>
        var line = new google.maps.Polyline({
          path: path,
          strokeColor: '#800000',
          strokeOpacity: 0.75,
          strokeWeight: 4
        });
        line.setMap(map);
      };
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
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
        var <?php echo "s$i"; ?> = [<?php foreach(${"d".$i} as $b) {echo "[".$b[0].", ".$b[1]."],";} ?>];
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
          <a class="navbar-brand" href="session.php">Open Torque Viewer</a>
        </div>
      </div>
    </div>
    <div id="map-container" class="col-md-7 col-xs-12">
      <div id="map-canvas"></div>
    </div>
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
                <td width="25%">
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
                <!-- Year Filter -->
                <td width="25%">
                  <select id="selyear" name="selyear" class="form-control chosen-select" data-placeholder="Select Year">
                    <option value=""></option>
                    <option value="ALL"<?php if ($filteryear == "ALL") echo ' selected'; ?>>Any Year</option>
<?php $i = 0; ?>
<?php while(isset($yeararray[$i])) { ?>
                    <option value="<?php echo $yeararray[$i]; ?>"<?php if ($filteryear == $yeararray[$i]) echo ' selected'; ?>><?php echo $yeararray[$i]; ?></option>
<?php   $i = $i + 1; ?>
<?php } ?>
                  </select>
                </td>
                <td width="2%"></td>
                <!-- Month Filter -->
                <td width="25%">
                  <select id="selmonth" name="selmonth" class="form-control chosen-select" data-placeholder="Select Month">
                    <option value=""></option>
                    <option value="ALL"<?php if ($filtermonth == "ALL") echo ' selected'; ?>>Any Month</option>
                    <option value="January"<?php if ($filtermonth == "January") echo ' selected'; ?>>January</option>
                    <option value="February"<?php if ($filtermonth == "February") echo ' selected'; ?>>February</option>
                    <option value="March"<?php if ($filtermonth == "March") echo ' selected'; ?>>March</option>
                    <option value="April"<?php if ($filtermonth == "April") echo ' selected'; ?>>April</option>
                    <option value="May"<?php if ($filtermonth == "May") echo ' selected'; ?>>May</option>
                    <option value="June"<?php if ($filtermonth == "June") echo ' selected'; ?>>June</option>
                    <option value="July"<?php if ($filtermonth == "July") echo ' selected'; ?>>July</option>
                    <option value="August"<?php if ($filtermonth == "August") echo ' selected'; ?>>August</option>
                    <option value="September"<?php if ($filtermonth == "September") echo ' selected'; ?>>September</option>
                    <option value="October"<?php if ($filtermonth == "October") echo ' selected'; ?>>October</option>
                    <option value="November"<?php if ($filtermonth == "November") echo ' selected'; ?>>November</option>
                    <option value="December"<?php if ($filtermonth == "December") echo ' selected'; ?>>December</option>
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
<?php   if ( $filteryear <> "" ) { ?>
            <input type="hidden" name="selyear" id="selyear" value="<?php echo $filteryear; ?>" />
<?php   } ?>
<?php   if ( $filtermonth <> "" ) { ?>
            <input type="hidden" name="selmonth" id="selmonth" value="<?php echo $filtermonth; ?>" />
<?php   } ?>
            <noscript><input type="submit" id="seshidtag" name="seshidtag" class="input-sm"></noscript>
          </form>
<?php if(isset($session_id) && !empty($session_id)){ ?>
          <div class="btn-group btn-group-justified">
            <table style="width:100%">
              <tr>
                <td>
                  <form method="post" class="form-horizontal" role="form" action="session.php?mergesession=<?php echo $session_id; ?>&mergesessionwith=<?php echo $session_id_next; ?>" id="formmerge">
                    <div align="center" style="padding-top:6px;"><input class="btn btn-info btn-sm" type="submit" id="formmerge" name="merge" value="Merge" title="Merge this session (<?php echo $seshdates[$session_id]; ?>) with the next session (<?php if ($session_id_next <> "") { echo $seshdates[$session_id_next]; } ?>)." <?php if($session_id_next == ""){  echo 'disabled="disabled"'; } ?> /></div>
                  </form>
                </td>
                <script type="text/javascript">
                  //Adding a confirmation dialog to above forms
                  $('#formmerge').submit(function() {
                  var c = confirm("Click OK to merge sessions (<?php echo $seshdates[$session_id]; ?>) and (<?php if ( $session_id_next <> "") { echo $seshdates[$session_id_next]; } ?>).");
                  return c; //you can just return c because it will be true or false
                  });
                </script>
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
        </div><br />

<!-- Variable Select Block -->
<?php if ($setZoomManually === 0) { ?>
        <h4>Select Variables to Compare</h4>
          <div class="row center-block" style="padding-top:3px;">
            <form method="post" role="form" action="url.php?makechart=y&seshid=<?php echo $session_id; ?>" id="formplotdata">
              <select data-placeholder="Choose OBD2 data..." multiple class="chosen-select" size="<?php echo $numcols; ?>" style="width:100%;" id="plot_data" onsubmit="onSubmitIt" name="plotdata[]">
                <option value=""></option>
<?php   foreach ($coldata as $xcol) { ?>
                <option value="<?php echo $xcol['colname']; ?>" <?php $i = 1; while ( isset(${'var' . $i}) ) { if ( ${'var' . $i} == $xcol['colname'] ) { echo " selected"; } $i = $i + 1; } ?>><?php echo $xcol['colcomment']; ?></option>
<?php   } ?>
            </select>
<?php   if ( $filteryear <> "" ) { ?>
            <input type="hidden" name="selyear" id="selyear" value="<?php echo $filteryear; ?>" />
<?php   } ?>
<?php   if ( $filtermonth <> "" ) { ?>
            <input type="hidden" name="selmonth" id="selmonth" value="<?php echo $filtermonth; ?>" />
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
<?      } ?>
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
          <a href="http://hda.surfrock66.com/torquetest/pid_edit.php" title="Edit PIDs">Edit PIDs</a><br />
          <a href="https://github.com/surfrock66/torque" title="View Source On Github">View Source On Github</a>
        </div>
      </div>
    </div>
  </body>
</html>
<?php //echo "<!-- End session.php at ".date("H:i:s", microtime(true))." -->\r\n"; ?>
