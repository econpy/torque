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
<?php if ($mapProvider === 'openlayers') { //I added a new map provider to use openlayers to be able to color each segment of our path based on speed?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.1.0/ol.css">
    <script src="https://cdn.jsdelivr.net/npm/ol@v7.1.0/dist/ol.js"></script>
	  <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL,Object.assign"></script>
    <script language="javascript" type="text/javascript">
      const initMap = () => {
        var path = [<?php echo $imapdata; ?>];
        var spd = [<?php echo $ispddata; ?>]; //this would be a new variable containing speed data for each segment
        const source = new ol.source.Vector({features:[new ol.Feature(new ol.geom.LineString(path))]}); //build the path layer vector source
        const style = (f,r) => { //function that builds the styles array to color every line segment based on speed
          const [width,geom,max] = [4,f.getGeometry(),Math.max.apply(null,spd.filter(v=>v>0))];
          let [i,stl] = [0,[]];
          geom.forEachSegment((s,e)=>stl.push(new ol.style.Style({geometry:new ol.geom.LineString([s, e]),stroke:new ol.style.Stroke({color:"hsl("+(100*(1-spd[i]/max))+",100%,50%)",width})}))&&i++&&null);
          return stl;
        }
        //function to create stylized circle for start and end
        const fPnt = (p,c)=>new ol.layer.Vector({source:new ol.source.Vector({features:[new ol.Feature(new ol.geom.Circle(p,1/3e3))]}),style:{'stroke-width':3,'stroke-color':c,'fill-color':c.concat([.5])}})
        //setups the layers for osm, the path, start and end circles
        const layers = [new ol.layer.Tile({source:new ol.source.OSM()}),new ol.layer.Vector({source,style}),fPnt(path,[0,255,0]),fPnt(path,[0,0,0])];
        //creates the map
        ol.proj.useGeographic();
			  map = new ol.Map({layers,target:'map-container'});
			  map.addInteraction(new ol.interaction.DragRotateAndZoom())&&map.addControl(new ol.control.FullScreen())&&map.addControl(new ol.control.Rotate());
        //center then map view on our trip plus a little margin on the outside
        map.getView().fit(source.getExtent().map((v,i)=>v+(i>1?1:-1)/1e3),map.getSize());
      };
      initMap();
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
