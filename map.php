<?php
require("./mapdata.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <!--<meta http-equiv="refresh" content="30">-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <title>Torque Google Map</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300" rel="stylesheet">
    <style>
      *{
        font-family: 'Open Sans', sans-serif;
        font-weight: 300;
      }
      html, body {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
      #map-canvas {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
    </style>
    <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=weather"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-map/3.0-rc1/jquery.ui.map.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-map/3.0-rc1/jquery.ui.map.extensions.js"></script>
    <script type="text/javascript">
      google.maps.visualRefresh = true;
      var geocoder;
      var map;
      var infowindow = new google.maps.InfoWindow();
      var marker;
      var xlat = <?php echo $centerlat; ?>;
      var xlng = <?php echo $centerlong; ?>;

      function initialize() {
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(xlat, xlng)
        var mapOptions = {
          zoom: 9,
          center: latlng,
          mapTypeId: 'roadmap',
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
        }

        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

//// Uncomment this section to add a weather layer to the map.
//        var weatherLayer = new google.maps.weather.WeatherLayer({
//          temperatureUnits: google.maps.weather.TemperatureUnit.FAHRENHEIT
//        });
//        weatherLayer.setMap(map);

//// Uncomment this section to add a clouds layer to the map.
//        var cloudLayer = new google.maps.weather.CloudLayer();
//        cloudLayer.setMap(map);

        var path = [<?php echo $imapdata; ?>];
        var line = new google.maps.Polyline({
          path: path,
          strokeColor: '#800000',
          strokeOpacity: 0.75,
          strokeWeight: 4
        });
        line.setMap(map);
      }

      function codeLatLng(zoomvar) {
        var lat = parseFloat(xlat);
        var lng = parseFloat(xlng);
        var latlng = new google.maps.LatLng(lat, lng);
        geocoder.geocode({'latLng': latlng}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
              map.setZoom(zoomvar);
              marker = new google.maps.Marker({position: latlng, map: map});
              if(results[1].formatted_address == 'Odessa, KS, USA') { // The default location if there is no data.
                infowindow.setContent('No data is ready yet.');
              }
              else {
                infowindow.setContent(results[1].formatted_address);
              }
              infowindow.open(map, marker);
            }
            else {
              alert('No results found');
            }
          }
          else {
            alert('Geocoder failed due to: ' + status);
          }
        });
      }
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    <script type="text/javascript">
      // Display the most recent latitute/longitude point 4 seconds
      // after the page loads and change zoom level to 11.
      $(function() {setTimeout(function() {codeLatLng(11);}, 4000);});
    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
  </body>
</html>
