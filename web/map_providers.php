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
        const selLyrUrl = { //list of providers for the base layer
          'OSM':'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
          'ESRI':'https://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
          'ESRI.DARK':'https://services.arcgisonline.com/ArcGIS/rest/services/canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}',
          'ESRI.GRAY':'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}',
          'ESRI.SATE':'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
          'ESRI.TOPO':'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
          'ESRI.NATGEO':'https://services.arcgisonline.com/ArcGIS/rest/services/NatGeo_World_Map/MapServer/tile/{z}/{y}/{x}',
          'STAMEN':'https://stamen-tiles.a.ssl.fastly.net/toner/{z}/{x}/{y}.png',
          'STAMEN.TERRAIN':'https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.png',
          'STAMEN.WATERCOLOR':'https://stamen-tiles.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg'
        };
        const updBase = () => { //updates the base layer based on selection
          const selLayer = $('#BaseLayerOpt').find(":selected").val()!==undefined?$('#BaseLayerOpt').find(":selected").val():'OSM';
          tileLayer.setUrl(selLyrUrl[selLayer]||selLyrUrl['OSM']);
        }
        const baseLst = [['Open Street Map','OSM'], //base layer option list
          ['Esri Streets','ESRI'],['Esri Dark Base','ESRI.DARK'],['Esri Gray Base','ESRI.GRAY'],['Esri Satellite','ESRI.SATE'],['Esri Topo','ESRI.TOPO'],['Esri NatGeo','ESRI.NATGEO'],
          ['Stamen','STAMEN'],['Stamen Terain','STAMEN.TERRAIN'],['Stamen Watercolor','STAMEN.WATERCOLOR']];
        $('#map-container')
          .prepend($('<select>',{id:'BaseLayerOpt'}).css({position:'relative','z-index':300,left:'80px'}))//creates a new select element with the options for the base layers
          .prepend($('<div>').css('position','absolute').append($('<div>',{id:'ttip'}).css({position:'relative','z-index':100,'background-color':'white','border-radius':'10px',opacity:0.9,width:'100px'})));//creates the tooltip element
        $.each(baseLst,(i,el)=>$('#BaseLayerOpt').append($('<option>',{value:el[1],text:el[0]})));
        $('#map-container>select').val('ESRI.SATE');
        $('#map-container>select').off('change');
        $('#map-container>select').on('change',updBase);
        //defines the default base layer
        const tileLayer = new ol.source.XYZ({url:selLyrUrl['ESRI.SATE']});
        const baseLayer = new ol.layer.Tile({source:tileLayer});
        const pathAll = [<?php echo $imapdata; ?>];
        const spdAll = [<?php echo $ispddata; ?>]; //this would be a new variable containing speed data for each segment
        let path = pathAll; //by default full range
        let spd = spdAll;
        const spdUnit = '<?php echo !$use_miles?'km/h':'mph' ?>'; //just set the Unit for the tooltip
        const sFeat = f=>new ol.source.Vector({features:[f]});
        let source = sFeat(new ol.Feature({geometry:new ol.geom.LineString(path),name:'trk'})); //build the path layer vector source
        const style = (f,r) => { //function that builds the styles array to color every line segment based on speed
          const [width,geom,max] = [4,f.getGeometry(),Math.max.apply(null,spd.filter(v=>v>0))];
          let [i,stl] = [0,[]];
          geom.forEachSegment((s,e)=>stl.push(new ol.style.Style({geometry:new ol.geom.LineString([s, e]),stroke:new ol.style.Stroke({color:"hsl("+(100*(1-spd[i]/max))+",100%,50%)",width})}))&&i++&&null);
          return stl;
        }
        //functions to create stylized circle for start and end, also marker when hovering chart
        const fCircleF = (c,r)=>new ol.Feature(new ol.geom.Circle(c,r));
        const lPnt = (s,c)=>new ol.layer.Vector({source:s,style:{'stroke-width':3,'stroke-color':c,'fill-color':c.concat([.5])}});
        ///this is the marker features source while hovering the chart
        const markerSource = new ol.source.Vector({features:[]});
        markerUpd = itm => {//this functions updates the marker while hovering the chart and clears it when not hovering
          markerSource.clear();
          itm&&itm.dataIndex>0&&markerSource.addFeature(fCircleF(path[itm.dataIndex],1/1e3)); //big circle
          itm&&itm.dataIndex>0&&markerSource.addFeature(fCircleF(path[itm.dataIndex],1/2e5)); //small circle
          markerSource.changed();
        }
        //creates the marker layer
        const marker = new ol.layer.Vector({source:markerSource,style:{'stroke-width':2,'stroke-color':[190,0,190],'fill-color':[190,0,190,.1]}});
        //setups the layers for osm, the path, start and end circles
        let pnt = [sFeat(fCircleF(path[0],1/3e3)),sFeat(fCircleF(path[path.length-1],1/3e3))];
        const layers = [baseLayer,lPnt(pnt[0],[0,255,0]),lPnt(pnt[1],[0,0,0]),new ol.layer.Vector({source,style}),marker];
        mapUpdRange = (a,b) => {//new function to update the map sources according to the trim slider
          path = pathAll.slice(a,b);
          spd = spdAll.slice(a,b);
          source.clear();pnt[0].clear();pnt[1].clear();
          source.addFeature(new ol.Feature({geometry:new ol.geom.LineString(path),name:'trk'}));
          pnt[0].addFeature(fCircleF(path[0],1/3e3));
          pnt[1].addFeature(fCircleF(path[path.length-1],1/3e3));
          source.changed();pnt[0].changed();pnt[1].changed();
          map.getView().fit(source.getExtent().map((v,i)=>v+(i>1?1:-1)/1e3),map.getSize());
        };
        //creates the map
        ol.proj.useGeographic();
        let map = new ol.Map({layers,target:'map-container'});
			  map.addInteraction(new ol.interaction.DragRotateAndZoom());map.addControl(new ol.control.FullScreen());map.addControl(new ol.control.Rotate());
        //center then map view on our trip plus a little margin on the outside
        map.getView().fit(source.getExtent().map((v,i)=>v+(i>1?1:-1)/1e3),map.getSize());
        //function to get the index of first line segment that intersects with the point the mouse is over
        const segIdx = (g,c)=>{for(let i=1;i<g.length;i++) if (new ol.geom.LineString([g[i-1],g[i]]).intersectsCoordinate(c)) return i;}
        //this whole section just constructs the speed tooltip, could be enhanced with all the variables in the plot? but probably bigger impact on performance depending on how many are selected
        const ttip = $("#ttip");
        const sData=evt=>{
          const pxl = map.getEventPixel(evt.originalEvent);
          const feature = map.forEachFeatureAtPixel(pxl,e=>e.getProperties().name=='trk'&&e);
          let msg = feature&&spd[segIdx(feature.getGeometry().getCoordinates(),feature.getGeometry().getClosestPoint(map.getCoordinateFromPixel(pxl)))];
          if (feature&&msg>0) {
            msg = 'Speed: '+msg+' '+spdUnit;
            ttip.css({top:pxl[1]+'px',left:pxl[0]+'px'}).html(msg)
          } else{
            ttip.html('');
          }
        }
        //this is the actual listener on the map to create our tooltip
        map.on('pointermove',evt=>evt.dragging?ttip.html(''):sData(evt));
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
