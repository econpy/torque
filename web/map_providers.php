<?php if ($mapProvider === 'google') { ?>
    <!-- Initialize the google maps javascript code -->
    <script language="javascript" type="text/javascript">
      const path = [<?php echo $imapdata; ?>];
      window.gMapData = [path,'<?php echo $mapStyleSelect; ?>',<?php echo $setZoomManually;?>];
      initMap = ()=>((typeof initMapGoogle=='function')&&window.gMapData!==undefined)?initMapGoogle():setTimeout(()=>initMap,10);
    </script>
    <script language="javascript" type="text/javascript" src="https://maps.googleapis.com/maps/api/js<?php echo "?key=$gmapsApiKey&callback=initMap";  ?>"  async></script>
<?php } //end IF Google Maps ?>
<?php if ($mapProvider === 'openlayers') { //I added a new map provider to use openlayers to be able to color each segment of our path based on speed?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.1.0/ol.css">
    <script src="https://cdn.jsdelivr.net/npm/ol@v7.1.0/dist/ol.js"></script>
	  <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL,Object.assign"></script>
    <script language="javascript" type="text/javascript">
      const pathAll = [<?php echo $imapdata; ?>];
      const spdAll = [<?php echo $ispddata; ?>]; //this would be a new variable containing speed data for each segment
      const spdUnit = '<?php echo !$use_miles?'km/h':'mph' ?>'; //just set the Unit for the tooltip
      $(document).ready(()=>initMapOpenlayers(pathAll,spdAll,spdUnit));
    </script>
<?php } //end IF Openlayers ?>
    <?php if ($mapProvider !== 'google' && $mapProvider !== 'openlayers') { ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <?php } ?>
    <?php if ($mapProvider === 'stamen') { ?>
    <script type="text/javascript" src="https://stamen-maps.a.ssl.fastly.net/js/tile.stamen.js?v1.3.0"></script>
    <?php } ?>
    <?php if ($mapProvider === 'esri') { ?>
    <script src="https://unpkg.com/esri-leaflet@3.0.2/dist/esri-leaflet.js" integrity="sha512-myckXhaJsP7Q7MZva03Tfme/MSF5a6HC2xryjAM4FxPLHGqlh5VALCbywHnzs2uPoF/4G/QVXyYDDSkp5nPfig==" crossorigin=""></script>
    <?php } ?>
    <?php if ($mapProvider !== 'google' && $mapProvider !== 'openlayers') { ?>
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
    <?php if ($mapProvider !== 'google' && $mapProvider !== 'openlayers') { ?>
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
