    <?php if ($mapProvider === 'google') { ?>
    <!-- Initialize the google maps javascript code -->
    <script language="javascript" type="text/javascript" src="https://maps.googleapis.com/maps/api/js<?php echo "?key=$gmapsApiKey&callback=initMap";  ?>"  async></script>
    <script language="javascript" type="text/javascript">
     var map;

      function initMap() {
        var map = new google.maps.Map(document.getElementById("map-canvas"), {
        zoom: 3,
        center: { lat: 0, lng: -180 },
        mapTypeId: '<?php echo $mapStyleSelect; ?>',
        });

        // The potentially large array of LatLng objects for the roadmap
        var path = [<?php echo $imapdata; ?>];
        var pathL = path.length;
        var endCrd = path[0];
        var startCrd = path[pathL-1];

        // Create a boundary using the path to automatically configure
        // the default centering location and zoom.
        var bounds = new google.maps.LatLngBounds();
        for (i = 0; i < path.length; i++) {
          bounds.extend(path[i]);
        }
        map.fitBounds(bounds);
        
        //Draw green and black circles for start and end points
        var startcir = new google.maps.Marker({position: startCrd,icon: {path: google.maps.SymbolPath.CIRCLE,fillOpacity: 0.25,fillColor: '#009900',strokeOpacity: 0.8,strokeColor: '#009900',strokeWeight: 2,scale: 6}});
        var endcir = new google.maps.Marker({position: endCrd,icon: {path: google.maps.SymbolPath.CIRCLE,fillOpacity: 0.25,fillColor: '#000000',strokeOpacity: 0.8,strokeColor: '#000000',strokeWeight: 2,scale: 6}});
        startcir.setMap(map);
        endcir.setMap(map);
        google.maps.event.addDomListener(window, 'load', initMap);

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

<?php } ?>
        var line = new google.maps.Polyline({
          path: path,
          strokeColor: '#800000',
          strokeOpacity: 0.75,
          strokeWeight: 4
        });
        line.setMap(map);
      };
    </script>
<?php } //end IF Google Maps ?>

    <?php if ($mapProvider !== 'google') { ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <?php } ?>
    <?php if ($mapProvider === 'stamen') { ?>
    <script type="text/javascript" src="https://stamen-maps.a.ssl.fastly.net/js/tile.stamen.js?v1.3.0"></script>
    <?php } ?>
    <?php if ($mapProvider === 'esri') { ?>
    <script src="https://unpkg.com/esri-leaflet@3.0.2/dist/esri-leaflet.js" integrity="sha512-myckXhaJsP7Q7MZva03Tfme/MSF5a6HC2xryjAM4FxPLHGqlh5VALCbywHnzs2uPoF/4G/QVXyYDDSkp5nPfig==" crossorigin=""></script>
    <?php } ?>
    <?php if ($mapProvider !== 'google') { ?>
    <script>
    <?php } ?>
    <?php if ($mapProvider === 'stamen') { ?>
    var layer = new L.StamenTileLayer("<?php echo $mapStyleSelect; ?>");
    <?php } ?>
    <?php if ($mapProvider === 'esri') { ?>
    var layer = new L.esri.basemapLayer('<?php echo $mapStyleSelect; ?>');
    <?php } ?>
    <?php if ($mapProvider === 'openstreetmap') { ?>
    var layer = new L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'});
    <?php } ?>
    <?php if ($mapProvider === 'mapbox') { ?>
    var layer = new L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: '<?php echo $mapStyleSelect; ?>',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: '<?php echo $mapboxApiKey; ?>'});
    <?php } ?>
    <?php if ($mapProvider === 'tomtom') { ?>
    var layer = new L.tileLayer('https://api.tomtom.com/map/1/tile/basic/{style}/{z}/{x}/{y}.png?key={apikey}', {
    attribution:'<a href="https://tomtom.com" target="_blank">&copy;  1992 - ' + new Date().getFullYear() + ' TomTom.</a> ',
    style: '<?php echo $mapStyleSelect; ?>',
    apikey: '<?php echo $tomtomApiKey; ?>'});
    <?php } ?>
    <?php if ($mapProvider === 'thunderforest') { ?>
    var layer = new L.tileLayer('https://tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey={apikey}', {
    attribution:'&copy; <a href="http://www.thunderforest.com/">Thunderforest</a>, &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap contributors</a>',
    apikey: '<?php echo $thunderforestApiKey; ?>'});
    <?php } ?>
    <?php if ($mapProvider === 'here') { ?>
    var layer = new L.tileLayer('https://{s}.base.maps.ls.hereapi.com/maptile/2.1/maptile/newest/{type}/{z}/{x}/{y}/{size}/png8?apiKey={apikey}&lg=eng',{
    attribution: 'Map &copy; 1987-' + new Date().getFullYear() + ' <a href="http://developer.here.com">HERE</a>',
    subdomains: '1234',
    apikey: '<?php echo $hereApiKey; ?>',
    type: '<?php echo $mapStyleSelect; ?>',
    maxZoom: 20,
    size: '256'});
    <?php } ?>
    <?php if ($mapProvider === 'maptiler') { ?>
    var layer = new L.tileLayer('https://api.maptiler.com/maps/{style}/{z}/{x}/{y}{r}.png?key={apikey}',{
    attribution:'&copy; <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap contributors</a>',
    style: '<?php echo $mapStyleSelect; ?>',
    apikey: '<?php echo $maptilerApiKey; ?>',
    tileSize: 512,
    zoomOffset: -1,
    maxZoom: 21});
    <?php } ?>
    <?php if ($mapProvider !== 'google') { ?>
    var path = [<?php echo $imapdata; ?>];   
    var map = new L.Map("map-canvas", {
    center: new L.LatLng(37.7, -122.4),
    zoom: 6});
     map.addLayer(layer);

    // start and end point marker
    var pathL = path.length;
    var endCrd = path[0];
    var startCrd = path[pathL-1];
    L.circleMarker(startCrd, {color:'green',title:'Start',alt:'Start Point',radius:6,weight:1}).addTo(map);
    L.circleMarker(endCrd, {color:'black',title:'End',alt:'End Point',radius:6,weight:1}).addTo(map);
    // travel line
    var polyline = L.polyline(path, {color: 'red'}).addTo(map);
    // zoom the map to the polyline
    map.fitBounds(polyline.getBounds(), {maxZoom: 15});
     </script>
    <?php } ?>
