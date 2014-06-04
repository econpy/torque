<?php
ini_set('memory_limit', '-1');
require("./creds.php");
require("./get_sessions.php");

session_start();
$_SESSION['recent_session_id'] = strval(max($sids));

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

if (isset($_POST["id"])) {
    $getid = mysql_escape_string($_POST['id']);
    $session_id = intval($getid);

    // Get GPS data for session
    $sessionqry = mysql_query("SELECT kff1006, kff1005
                          FROM $db_table
                          WHERE session=$session_id
                          ORDER BY time DESC;") or die(mysql_error());

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
    $imapdata = implode(",\n                    ", $mapdata);

    // Don't need to set zoom manually
    $setZoomManually = 0;

}

elseif (isset($_GET["id"])) {
    $getid = mysql_escape_string($_GET['id']);
    $session_id = intval($getid);

    // Get data for session
    $sessionqry = mysql_query("SELECT kff1006, kff1005
                          FROM $db_table
                          WHERE session=$session_id
                          ORDER BY time DESC;") or die(mysql_error());

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
    $imapdata = implode(",\n                    ", $mapdata);

    // Don't need to set zoom manually
    $setZoomManually = 0;
}

else {
    // Define these so we don't get an error on empty page loads. Instead it
    // will load a map of Area 51.
    $session_id = "";
    $imapdata = "new google.maps.LatLng(37.235, -115.8111)";
    $setZoomManually = 1;

}

?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Torque Map Viewer</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <style>
      *{
        font-family: 'Lato', sans-serif;
      }
      html, body {
        height: 100%;
        margin: 0px;
        padding: 0px;
        background-color: #428bca;
      }
      #map-canvas {
        height: 80%;
        margin: 0px;
        padding: 0px;
        /*padding-top: -10px;*/
      }
      .row{
          /*margin-top:40px;*/
          padding: 0 10px;
      }
      .clickable{
          cursor: pointer;
      }

      .panel-heading span {
        /*margin-top: -10px;*/
        font-size: 14px;
      }
    </style>
    <script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
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
              '<div class="alert alert-danger">'+
              '  <p class="lead" align="center">'+
              "  You're seeing this window because you have not selected a session. "+
              "  Please pick one from the dropdown menu above."+
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
      }

      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>

  <body>

    <div class="container-fluid" style="margin-top:9px;">
      <div class="row-fluid">

        <div class="col-md-3 col-sm-3" style="margin-top:12px;">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <span class="glyphicon glyphicon-stats" style="padding-right:7px;"></span><a data-toggle="collapse" href="#charts-plots">Charts and Plots</a>
              </h3>
            </div>
            <div id="charts-plots" class="panel-collapse collapse">
              <div class="list-group">
                <?php if ($setZoomManually === 0) { ?>
                  <ul style="padding-top:10px;">
                    <li><a href="<?php echo './plot.php?sid='.$session_id.'&s1=kff1001&s2=kc';?>" target="_blank"><small>Speed (GPS) vs. Engine RPM</small></a></li>
                    <li><a href="<?php echo './plot.php?sid='.$session_id.'&s1=kf&s2=k46';?>" target="_blank"><small>Intake Air Temp vs. Ambient Air Temp</small></a></li>
                    <li><a href="<?php echo './plot.php?sid='.$session_id.'&s1=kff1249&s2=k10';?>" target="_blank"><small>Air Fuel Ratio vs. MAF Rate</small></a></li>
                    <li><a href="<?php echo './plot.php?sid='.$session_id.'&s1=k5&s2=k3c';?>" target="_blank"><small>Coolant Temp vs. Catalyst Temp</small></a></li>
                  </ul>
                <?php } else { ?>
                  <div align="center" style="padding-top:10px;">
                    <h4><span class="label label-warning">Select a session first!</span></h4>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-sm-6" style="padding-right:12px;">
          <h4 align="center" style="color:#FFD700;"><i class="icon-arrow-down"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Select a session:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="icon-arrow-down"></i></h4>
          <div style="width:100%;" align="center">
            <form method="post" class="form-horizontal" role="form" action="url.php">
              <select id="seshidtag" name="seshidtag" class="form-control chosen-select" onchange="this.form.submit()" data-placeholder="Session IDs" style="width:90%;">
                <option value=""></option>
                <?php foreach($seshdates as $dateid => $datestr) { ?>
                  <option value="<?php echo $dateid; ?>"<?php if ($dateid == $session_id) echo ' selected';?>><?php echo $datestr; ?></option>
                <?php } ?>
              </select>
              <noscript><input type="submit" id="seshidtag" name="seshidtag" class="input-sm"></noscript>
            </form>
          </div>
          <br/>
        </div>

        <div class="col-md-3 col-sm-3" style="margin-top:12px;">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <span class="glyphicon glyphicon-th-large" style="padding-right:7px;"></span><a data-toggle="collapse" href="#download-data">Export Data</a>
              </h3>
            </div>
            <div id="download-data" class="panel-collapse collapse">
              <div class="list-group">
                <?php if ($setZoomManually === 0) { ?>
                  <ul style="padding-top:10px;">
                    <li><a href="<?php echo './export.php?sid='.$session_id.'&filetype=csv'; ?>" target="_blank">CSV</a></li>
                    <li><a href="<?php echo './export.php?sid='.$session_id.'&filetype=json'; ?>" target="_blank">JSON</a></li>
                  </ul>
                <?php } else { ?>
                  <div align="center" style="padding-top:10px;">
                    <h4><span class="label label-warning">Select a session first!</span></h4>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Map Object -->
    <div class="row-fluid">
      <div id="map-canvas"></div>
    </div>

    <!-- FOOTER -->
    <div class="row-fluid" style="padding-top:10px;">
      <div align="center">
        <p><a href="https://github.com/econpy/torque#readme" target="_blank"><span style="color:#FFFFFF;">About</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://github.com/econpy/torque" target="_blank"><span style="color:#FFFFFF;">Github</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mattnicklay.com/" target="_blank"><span style="color:#FFFFFF;">Contact</span></a></p>
      </div>
    </div>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.1.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="assets/js/chosen.jquery.min.js"></script>

    <script type="text/javascript">
      $(document).ready(function(){
        // Activate Chosen on the selection drop down
        $("select#seshidtag").chosen();
        // When the selection drop down is open, force all elements to align left
        $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
      });
    </script>

    <script type="text/javascript">
    $(document).on('click', '.panel-heading span.clickable', function(e){
        var $this = $(this);
      if(!$this.hasClass('panel-collapsed')) {
        $this.parents('.panel').find('.panel-body').slideUp();
        $this.addClass('panel-collapsed');
        $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
      } else {
        $this.parents('.panel').find('.panel-body').slideDown();
        $this.removeClass('panel-collapsed');
        $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
      }
    })
    </script>

  </body>
</html>




