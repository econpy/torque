<?php
ini_set('memory_limit', '-1');
require 'creds.php';
require 'get_sessions.php';
require 'get_columns.php';
require 'plot.php';

$_SESSION['recent_session_id'] = strval(max($sids));

// Connect to Database
$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

if (isset($_POST["id"])) {
    $session_id = preg_replace('/\D/', '', $_POST['id']);

    // Get GPS data for session
    $sessionqry = mysql_query("SELECT kff1006, kff1005
                          FROM raw_logs
                          WHERE session=$session_id
                          ORDER BY time DESC", $con) or die(mysql_error());

    $geolocs = array();
    while($geo = mysql_fetch_array($sessionqry)) {
        if (($geo['0'] != 0) && ($geo['1'] != 0)) {
            $geolocs[] = array('lat' => $geo['0'], 'lon' => $geo['1']);
        }
    }

    // Create array of Latitude/Longitude strings in Google Maps JavaScript format
    $mapdata = array();
    foreach($geolocs as $d) {
        $mapdata[] = 'new google.maps.LatLng('.$d['lat'].', '.$d['lon'].')';
    }
    $imapdata = implode(",\n                    ", $mapdata);

    // Don't need to set zoom manually
    $setZoomManually = 0;

    mysql_free_result($sessionqry);
    mysql_close($con);
}

elseif (isset($_GET['id'])) {
    $session_id = preg_replace('/\D/', '', $_GET['id']);

    // Get data for session
    $sessionqry = mysql_query("SELECT kff1006, kff1005
                          FROM raw_logs
                          WHERE session=$session_id
                          ORDER BY time DESC", $con) or die(mysql_error());

    $geolocs = array();
    while($geo = mysql_fetch_array($sessionqry)) {
        if (($geo['0'] != 0) && ($geo['1'] != 0)) {
            $geolocs[] = array('lat' => $geo['0'], 'lon' => $geo['1']);
        }
    }

    // Create array of Latitude/Longitude strings in Google Maps JavaScript format
    $mapdata = array();
    foreach($geolocs as $d) {
        $mapdata[] = 'new google.maps.LatLng('.$d['lat'].', '.$d['lon'].')';
    }
    $imapdata = implode(",\n                    ", $mapdata);

    // Don't need to set zoom manually
    $setZoomManually = 0;

    mysql_free_result($sessionqry);
    mysql_close($con);
}

else {
    // Define these so we don't get an error on empty page loads. Instead it
    // will load a map of Area 51.
    $session_id = '';
    $imapdata = 'new google.maps.LatLng(37.235, -115.8111)';
    $setZoomManually = 1;

}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Open Torque Viewer</title>
        <meta name="description" content="Open Torque Viewer">
        <meta name="author" content="Matt Nicklay">
        <!--<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">-->
        <link rel="stylesheet" href="static/css/bootstrap.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
        <link rel="stylesheet" href="static/css/torque.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
            <script language="javascript" type="text/javascript">
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
            </script>
        <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>

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
                  '<br>'+
                  "you haven't selected a session. "+
                  '<br><br>'+
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

        <!-- Flot Javascript files -->
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.axislabels.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.hiddengraphs.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.multihighlight-delta.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.selection.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.time.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.tooltip.min.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.updater.js"></script>
        <script language="javascript" type="text/javascript" src="static/js/jquery.flot.resize.min.js"></script>

        <script language="javascript" type="text/javascript">
        $(document).ready(function(){

            var s1 = [<?php foreach($d1 as $b) {echo "[".$b[0].", ".$b[1]."],";} ?>];
            var s2 = [<?php foreach($d2 as $d) {echo "[".$d[0].", ".$d[1]."],";} ?>];

            var flotData = [
                            { data: s1, label: <?php echo $v1_label; ?> },
                            { data: s2, label: <?php echo $v2_label; ?>, yaxis: 2 }
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
                    yaxes: [ { axisLabel: <?php echo $v1_label; ?> }, {
                        alignTicksWithAxis: position == "right" ? 1 : null,
                        position: position,
                        axisLabel: <?php echo $v2_label; ?>
                    } ],
                    legend: {
                        position: "nw",
                        hideable: true,
                        backgroundOpacity: 0.1,
                        margin: 0
                    },
                    //selection: { mode: "xy" },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        autoHighlight: true
                    },
                    //multihighlightdelta: { mode: 'x' },
                    tooltip: false,
                    tooltipOpts: {
                        content: "%s at %x: %y",
                        xDateFormat: "%I:%M:%S%p",
                        twelveHourClock: true,
                        onHover: function(flotItem, $tooltipEl) {
                            // console.log(flotItem, $tooltipEl);
                        }
                    }
                }
            )}

            doPlot("right");

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
          <nav class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="session.php" style="padding-left:45px;">Open Torque Viewer</a>
                </div>
                <div class="collapse navbar-collapse" id="navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                      <li style="padding-right:65px;line-height:40px;"><button class="button" onclick="toggleCharts()">Toggle Map</button></li>
                    </ul>
                </div>
              </div>
            </nav>
            <div id="map-container" class="col-md-7 col-xs-12">
                <div id="map-canvas"></div>
            </div>
            <div id="right-container" class="col-md-5 col-xs-12">
                <div id="right-cell">

                    <h4>Select Session</h4>
                    <div class="row center-block" style="padding-bottom:4px;">
                        <form method="post" class="form-horizontal" role="form" action="url.php">
                          <select id="seshidtag" name="seshidtag" class="form-control chosen-select" onchange="this.form.submit()" data-placeholder="Select Session..." style="width:100%;">
                            <option value=""></option>
                            <?php
                            foreach ($seshdates as $dateid => $datestr) { ?>
                              <option value="<?php echo $dateid; ?>"<?php if ($dateid == $session_id) echo ' selected'; ?>><?php echo $datestr; ?></option>
                            <?php } ?>
                          </select>
                          <noscript><input type="submit" id="seshidtag" name="seshidtag" class="input-sm"></noscript>
                        </form>
                    </div>

                    <br>

                    <?php if ($setZoomManually === 0) { ?>
                    <h4>Select 2 Variables to Plot</h4>
                    <div class="row center-block" style="padding-top:3px;">
                        <form method="post" role="form" action="url.php?makechart=y&seshid=<?php echo $session_id; ?>" id="formplotdata">
                            <select data-placeholder="Choose OBD2 data..." multiple class="chosen-select" size="<?php echo $numcols; ?>" style="width:100%;" id="plot_data" onsubmit="onSubmitIt" name="plotdata[]">
                                <option value=""></option>
                                <?php foreach ($coldata as $xcol) { ?>
                                  <option value="<?php echo $xcol['colname']; ?>"><?php echo $xcol['colcomment']; ?></option>
                                <?php } ?>
                            </select>
                            <div align="center" style="padding-top:6px;"><input class="btn btn-info btn-sm" type="submit" id="formplotdata" name="plotdata[]" value="Plot!"></div>
                        </form>
                    </div>
                    <?php } else { ?>
                    <h4>Plot</h4>
                      <div align="center" style="padding-top:10px;">
                          <h5><span class="label label-warning">Select a session first!</span></h5>
                      </div>
                    <?php } ?>
                    <br>

                    <h4>Chart</h4>
                    <div class="row center-block" style="padding-bottom:5px;">

                    <?php if ($setZoomManually === 0) { ?>

                        <div class="demo-container">
                            <div id="placeholder" class="demo-placeholder" style="height:300px;"></div>
                        </div>

                    <?php } else { ?>
                        <div align="center" style="padding-top:10px;">
                            <h5><span class="label label-warning">Select a session first!</span></h5>
                        </div>
                    <?php } ?>

                    </div>

                    <br>

                    <h4>Data Summary</h4>
                    <div class="row center-block">

                    <?php if ($setZoomManually === 0) { ?>

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
                                <tbody>
                                    <tr>
                                        <td><strong><?php echo substr($v1_label, 1, -1); ?></strong></td>
                                        <td><?php echo $min1.'/'.$max1; ?></td>
                                        <td><?php echo $pcnt25data1; ?></td>
                                        <td><?php echo $pcnt75data1; ?></td>
                                        <td><?php echo $avg1; ?></td>
                                        <td><span class="line"><?php echo $sparkdata1; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo substr($v2_label, 1, -1); ?></strong></td>
                                        <td><?php echo $min2.'/'.$max2; ?></td>
                                        <td><?php echo $pcnt25data2; ?></td>
                                        <td><?php echo $pcnt75data2; ?></td>
                                        <td><?php echo $avg2; ?></td>
                                        <td><span class="line"><?php echo $sparkdata2; ?></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                    <?php } else { ?>

                        <div align="center" style="padding-top:5px;">
                            <h5><span class="label label-warning">Select a session first!</span></h5>
                        </div>

                    <?php } ?>

                    </div>

                    <br>

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

                </div>
            </div>

        </body>
    </html>
