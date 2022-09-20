function onSubmitIt() {
    var fields = $("li.search-choice").serializeArray();
    if (fields.length <= 1)
    {
        return false;
    }
    else
    {
        $('#formplotdata').submit();
    }
}

//$(document).ready(()=>chartTooltip); //changed this to be able to create attach the listener when plot is created via js code
chartTooltip = () => {
    var previousPoint = null;
    $("#placeholder").bind("plothover", function (event, pos, item) {
        var a_p = "";
        var d = new Date(parseInt(pos.x.toFixed(0)));
        var curr_hour = d.getHours();
        if (curr_hour < 12) {
           a_p = "AM";
           }
        else {
           a_p = "PM";
           }
        if (curr_hour == 0) {
           curr_hour = 12;
           }
        if (curr_hour > 12) {
           curr_hour = curr_hour - 12;
           }
        var curr_min = d.getMinutes() + "";
        if (curr_min.length == 1) {
           curr_min = "0" + curr_min;
           }
        var curr_sec = d.getSeconds() + "";
        if (curr_sec.length == 1) {
            curr_sec = "0" + curr_sec;
        }
        var formattedTime = curr_hour + ":" + curr_min + ":" + curr_sec + " " + a_p;
        $(".x").text(formattedTime);
        $("#y1").text(pos.y.toFixed(2));
        $("#y2").text(pos.y1.toFixed(2));
        
        if (typeof window.markerUpd==='function') markerUpd(item);

        if ($("#enableTooltip:checked").length > 0) {
            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    $("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);

                    showTooltip(item.pageX, item.pageY,
                                item.series.label + " of " + x + " = " + y);
                }
            }
            else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        }
    });
};

$(document).ready(function(){
  // Activate Chosen on the selection drop down
  $("select#seshidtag").chosen({width: "100%"});
  $("select#selprofile").chosen({width: "100%", disable_search: true, allow_single_deselect: true});
  $("select#selyearmonth").chosen({width: "100%", disable_search: true, allow_single_deselect: true});
  $("select#plot_data").chosen({width: "100%"});
  // Center the selected element
  $("div#seshidtag_chosen a.chosen-single span").attr('align', 'center');
  $("div#selprofile_chosen a.chosen-single span").attr('align', 'center');
  $("div#selyearmonth_chosen a.chosen-single span").attr('align', 'center');
  $("select#plot_data").chosen({no_results_text: "Oops, nothing found!"});
  $("select#plot_data").chosen({placeholder_text_multiple: "Choose OBD2 data.."});
  // When the selection drop down is open, force all elements to align left with padding
  $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
  $('select#selprofile').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#selprofile').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#selyearmonth').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#selyearmonth').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#plot_data').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#plot_data').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
});

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
});

$(document).ready(function(){
  $(".line").peity("line")
});

//start of chart plotting js code
let plot = null; //definition of plot variable in script but outside doPlot function to be able to reuse as a controller when updating base data
function doPlot(position) {
    //asigned the plot to a new variable and new function to update the plot in realtime when using the slider
    chartUpdRange = (a,b) => {
        let dataSet = [];
        flotData.forEach(i=>dataSet.push({label:i.label,data:i.data.slice(a,b)}));
        plot.setData(dataSet);
        plot.draw();
    }
    plot = $.plot("#placeholder", flotData, {
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
    });
    chartTooltip();
}

updCharts = ()=>{
    if ($('#plot_data').chosen().val()==null) {
        if ($('#placeholder')[0]!=undefined) {//clean our plot if it exists
            flotData = [];
            plot.shutdown();
            const noChart = $('<div>',{align:'center',style:'padding-top:10px'}).append($('<h5>').append($('<span>',{class:'label label-warning'}).html('No Variables Selected to Plot!')));
            $('#Chart-Container').empty();
            $('#Chart-Container').append(noChart);
            $('#Summary-Container').empty();
            $('#Summary-Container').append(noChart);
        }
    } else {
        let varPrm = 'plot.php?id='+$('#seshidtag').chosen().val();
        $('#plot_data').chosen().val().forEach((v,i)=>varPrm+='&s'+(i+1)+'='+v);
        $.get(varPrm,d=>{
            flotData = [];
            const gData = JSON.parse(d);
            gData.forEach(v=>flotData.push({label:v[1],data:v[2].map(a=>[parseInt(a[0]),a[1]])}));
            if ($('#placeholder')[0]==undefined) { //this would only be true the first time we load the chart
                $('#Chart-Container').empty();
                $('#Chart-Container').append($('<div>',{class:'demo-container'}).append($('<div>',{id:'placeholder',class:'demo-placeholder',style:'height:300px'})));
                doPlot("right");
            }
            //always update the chart trimmed range when plotting new data
            const [a,b] = [jsTimeMap.length-$('#slider-range11').slider("values",1)-1,jsTimeMap.length-$('#slider-range11').slider("values",0)-1];
            chartUpdRange(a,b);
            //this updates the whole summary table
            $('#Summary-Container').empty();
            $('#Summary-Container').append($('<div>',{class:'table-responsive'}).append($('<table>',{class:'table'}).append($('<thead>').append($('<tr>'))).append('<tbody>')));
            ['Name','Min/Max','25th Pcnt','75th Pcnt','Mean','Sparkline'].forEach(v=>$('#Summary-Container>div>table>thead>tr').append($('<th>').html(v)));
            const trData = v=>{
                const tr=$('<tr>');
                //and at this point I realized maybe I should have made the json output an object instead of an array but whatever //TODO: make it an object
                [v[1],v[5]+'/'+v[4],v[7],v[8],v[6],v[3]].forEach((v,i)=>tr.append($('<td>').html(i<5?v:'').append(i<5?'':$('<span>',{class:'line'}).html(v))));
                return tr;
            }
            gData.forEach(v=>$('#Summary-Container>div>table>tbody').append(trData(v)));
            $(".line").peity("line")
        });
    }
}
//End of chart plotting js code

//Start Openlayers Map Provider js code
initMapOpenlayers = () => {
    const spdUnit = window.MapData.spdUnit;
    const keys = window.MapData.keys;
    let path = window.MapData.path.map(v=>[v[1],v[0]]); //by default full range, this also changes [Lat,Lon] coordinates format to [Lon,Lat]
    let spd = window.MapData.spd;
    let oMapLst = { //base map list and options that don't need an api key
        'Openstreetmap':{labels:[''],styles:[''],styles:[''],url:'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'},
        'Esri':{
            labels:['World Street Map','Dark Gray','Light Gray','World Topo Map','World Imagery','NatGeo World Map'/*,'USATopo'*/],
            styles:['World_Street_Map','canvas/World_Dark_Gray_Base','Canvas/World_Light_Gray_Base','World_Topo_Map','World_Imagery','NatGeo_World_Map'],
            url:'https://services.arcgisonline.com/ArcGIS/rest/services/{style}/MapServer/tile/{z}/{y}/{x}'},
        'Stamen':{
            labels:['Terrain','Toner','Watercolor'],
            styles:['terrain','toner','watercolor'],
            url:'https://stamen-tiles.a.ssl.fastly.net/{style}/{z}/{x}/{y}.png'},
    };
    //adds the map options if theres an Api Key confugired
    if (keys.mapbox.length>1) oMapLst.Mapbox = {
        labels:['Streets','Outdoors','Light','Dark','Satellite','Satellite Streets','Navigation','Navigation Night'],
        styles:['streets-v11','outdoors-v11','light-v10','dark-v10','satellite-v9','satellite-streets-v11','navigation-day-v1','navigation-night-v1'],
        url:'https://api.mapbox.com/styles/v1/mapbox/{style}/tiles/{z}/{x}/{y}?access_token='+keys.mapbox};
    if (keys.tomtom.length>1) oMapLst.TomTom = {
        labels:['Main','Night'],
        styles:['main','night'],
        url:'https://api.tomtom.com/map/1/tile/basic/{style}/{z}/{x}/{y}.png?key='+keys.tomtom};
    if (keys.thunderforest.length>1) oMapLst.Thunderforest = {
        labels:['Transport','Transport Dark','Spinal','Landscape','Outdoors','Pioneer','Mobile Atlas','Neighbourhood'],
        styles:['transport','transport-dark','spinal-map','landscape','outdoors','pioneer','mobile-atlas','neighbourhood'],
        url:'https://tile.thunderforest.com/{style}/{z}/{x}/{y}.png?apikey='+keys.thunderforest,};
    if (keys.here.length>1) oMapLst.Here = {
        labels:['Normal Day','Normal Day Grey','Reduced Day','Normal Night','Reduced Night'],
        styles:['normal.day','normal.day.grey','reduced.day','normal.night','reduced.night'],
        url:'https://1.base.maps.ls.hereapi.com/maptile/2.1/maptile/newest/{style}/{z}/{x}/{y}/256/png8?apiKey='+keys.here+'&lg=eng'};
    if (keys.here.length>1) oMapLst.HereAerial = {
        labels:['Satellite Day','Hybrid Day'],
        styles:['satellite.day','hybrid.day'],
        url:'https://1.aerial.maps.ls.hereapi.com/maptile/2.1/maptile/newest/{style}/{z}/{x}/{y}/256/png8?apiKey='+keys.here+'&lg=eng'};
    if (keys.maptiler.length>1) oMapLst.Maptiler = {
        labels:['Streets','Basic','Bright','Pastel','Positron','Toner','Topo','Voyager'],
        styles:['streets','basic','bright','pastel','positron','toner','topo','voyager'],
        url:'https://api.maptiler.com/maps/{style}/{z}/{x}/{y}.png?key='+keys.maptiler};
    
    window.selLyrUrl = {}; //list of providers for the base layer
    Object.entries(oMapLst).map(([p,v])=>v.styles.forEach(e=>selLyrUrl[p+'.'+e]=v.url.replace(/\{style\}/,e)));
    window.baseLst = [];//base layer option list
    Object.entries(oMapLst).map(([p,v])=>v.labels.forEach((e,i)=>baseLst.push([p+(e.length>0?'-'+e:''),p+'.'+v.styles[i]])));

    $('#map-canvas')
        .prepend($('<div>').css('position','absolute')
            .prepend($('<select>',{id:'BaseLayerOpt'}).css({position:'relative','z-index':300,left:'80px'}))//creates a new select element with the options for the base layers
            .append($('<div>',{id:'ttip'}).css({position:'relative','z-index':100,'background-color':'white','border-radius':'10px',opacity:0.9,width:'100px'})));//creates the tooltip element
    $.each(baseLst,(i,el)=>$('#BaseLayerOpt').append($('<option>',{value:el[1],text:el[0]})));
    $('#BaseLayerOpt').val('Esri.World_Imagery');
    $('#BaseLayerOpt').off('change');
    const updBase = () => { //updates the base layer based on selection
        const selLayer = $('#BaseLayerOpt').find(":selected").val()!==undefined?$('#BaseLayerOpt').find(":selected").val():'Esri.World_Imagery';
        tileLayer.setUrl(selLyrUrl[selLayer]||selLyrUrl['Esri.World_Imagery']);
    }
    $('#BaseLayerOpt').on('change',updBase);
    //defines the default base layer
    const tileLayer = new ol.source.XYZ({url:selLyrUrl['Esri.World_Imagery']});
    const baseLayer = new ol.layer.Tile({source:tileLayer});
    const sFeat = f=>new ol.source.Vector({features:[f]});
    let pathSrc = sFeat(new ol.Feature({geometry:new ol.geom.LineString(path),name:'trk'})); //build the path layer vector source
    const fStl = (f,r) => { //function that builds the styles array to color every line segment based on speed
        const [width,geom,max] = [4,f.getGeometry(),Math.max.apply(null,spd.filter(v=>v>0))];
        let [i,stl] = [0,[]];
        geom.forEachSegment(
            (s,e)=>stl.push(new ol.style.Style({geometry:new ol.geom.LineString([s, e]),stroke:new ol.style.Stroke({color:"hsl("+(100*(1-spd[i]/max))+",100%,50%)",width})}))&&i++&&null);
        return stl;
    }
    //functions to create stylized circle for start and end, also marker when hovering chart
    const fCircleF = (c,r)=>new ol.Feature(new ol.geom.Circle(c,r));
    const lPnt = (s,c)=>new ol.layer.Vector({source:s,style:{'stroke-width':3,'stroke-color':c,'fill-color':c.concat([.5])}});
    ///this is the marker features source while hovering the chart
    const markerSource = new ol.source.Vector({features:[]});
    markerUpd = itm => {//this functions updates the marker while hovering the chart and clears it when not hovering
        markerSource.clear();
        itm&&itm.dataIndex>0&&markerSource.addFeatures([fCircleF(path[itm.dataIndex],1/1e3),fCircleF(path[itm.dataIndex],1/2e4)]); //circle Markers
    }
    //creates the marker layer
    const marker = new ol.layer.Vector({source:markerSource,style:{'stroke-width':2,'stroke-color':[190,0,190],'fill-color':[190,0,190,.1]}});
    //setups the layers for osm, the path, start and end circles
    let pnt = [sFeat(fCircleF(path[0],1/3e3)),sFeat(fCircleF(path[path.length-1],1/3e3))];
    const layers = [baseLayer,lPnt(pnt[0],[0,255,0]),lPnt(pnt[1],[0,0,0]),new ol.layer.Vector({source:pathSrc,style:fStl}),marker];
    mapUpdRange = (a,b) => {//new function to update the map sources according to the trim slider
        const tempCrd = window.MapData.path.map((v,i)=>[v[0],v[1],window.MapData.spd[i]]).slice(a,b).filter(([a,b,c])=>(a>0||a<0||b>0||b<0));
        path = tempCrd.map(v=>[v[1],v[0]]);
        spd = tempCrd.map(v=>v[2]);
        if (path.length==0) return alert('Time range has no gps data');
        pathSrc.clear();pnt[0].clear();pnt[1].clear();
        pathSrc.addFeature(new ol.Feature({geometry:new ol.geom.LineString(path),name:'trk'}));
        pnt[0].addFeature(fCircleF(path[0],1/3e3));
        pnt[1].addFeature(fCircleF(path[path.length-1],1/3e3));
        map.getView().fit(pathSrc.getExtent().map((v,i)=>v+(i>1?1:-1)/1e3),map.getSize());
    };
    //creates the map
    ol.proj.useGeographic();
    let map = new ol.Map({layers,target:'map-canvas'});
    map.addInteraction(new ol.interaction.DragRotateAndZoom());map.addControl(new ol.control.FullScreen());map.addControl(new ol.control.Rotate());
    //center then map view on our trip plus a little margin on the outside
    map.getView().fit(pathSrc.getExtent().map((v,i)=>v+(i>1?1:-1)/1e3),map.getSize());
    //function to get the index of first line segment that intersects with the point the mouse is over
    const segIdx = (g,c)=>{for(let i=1;i<g.length;i++) if (new ol.geom.LineString([g[i-1],g[i]]).intersectsCoordinate(c)) return i;}
    //this whole section just constructs the speed tooltip, could be enhanced with all the variables in the plot? but probably bigger impact on perfomance depending on how many are selected
    const ttip = $("#ttip");
    const sData=evt=>{
        const pxl = map.getEventPixel(evt.originalEvent);
        const feature = map.forEachFeatureAtPixel(pxl,e=>e.getProperties().name=='trk'&&e);
        let msg = feature&&spd[segIdx(feature.getGeometry().getCoordinates(),feature.getGeometry().getClosestPoint(map.getCoordinateFromPixel(pxl)))];
        if (feature&&msg>0) {
            msg = 'Speed: '+msg+' '+spdUnit;
            ttip.css({top:pxl[1]+'px',left:pxl[0]+'px'}).html(msg);
        } else{
            ttip.html('');
        }
    }
    //this is the actual listener on the map to create our tooltip
    map.on('pointermove',evt=>evt.dragging?ttip.html(''):sData(evt));

    tempMap = a=>{//new function to update map with temp values read from torque CSV
        path = a.map(v=>[v[1],v[0]]); //reorder coords
        spd = a.map(v=>v[2]);
        pathSrc.clear();pnt[0].clear();pnt[1].clear();
        pathSrc.addFeature(new ol.Feature({geometry:new ol.geom.LineString(path),name:'trk'}));
        pnt[0].addFeature(fCircleF(path[0],1/3e3));
        pnt[1].addFeature(fCircleF(path[path.length-1],1/3e3));
        map.getView().fit(pathSrc.getExtent().map((v,i)=>v+(i>1?1:-1)/1e3),map.getSize());
    };
}
//End of Openlayers Map Provider js code

//Start of Google Map Provider js code
function initMapGoogle() {
    const style = window.MapData.style;
    const manualZoom = window.MapData.manualZoom;
    var map = new google.maps.Map(document.getElementById("map-canvas"), {
        zoom: 3,
        center: { lat: 0, lng: -180 },
        mapTypeId: style,
    });

    // The potentially large array of LatLng objects for the roadmap
    var path = window.MapData.path.map(v=>new google.maps.LatLng(v[0],v[1])); //takes every coordinate array and makes it a coordinate object needed for gmaps
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
    var startcir = new google.maps.Marker({
        position: startCrd,
        icon: {path: google.maps.SymbolPath.CIRCLE,fillOpacity: 0.25,fillColor: '#009900',strokeOpacity: 0.8,strokeColor: '#009900',strokeWeight: 2,scale: 6}
    });
    var endcir = new google.maps.Marker({
        position: endCrd,
        icon: {path: google.maps.SymbolPath.CIRCLE,fillOpacity: 0.25,fillColor: '#000000',strokeOpacity: 0.8,strokeColor: '#000000',strokeWeight: 2,scale: 6}
    });
    startcir.setMap(map);
    endcir.setMap(map);
    //google.maps.event.addDomListener(window, 'load', initMapGoogle);

    // If required/desired, set zoom manually now that bounds have been set
    if (manualZoom === 1) {
        zoomChange = google.maps.event.addListenerOnce(map, 'bounds_changed',
            function(event) {
                if (this.getZoom()){
                    this.setZoom(16);
                }
            }
        );
        setTimeout(function(){
            google.maps.event.removeListener(zoomChange)
        }, 1000);
    }

    var line = new google.maps.Polyline({
      path: path,
      strokeColor: '#800000',
      strokeOpacity: 0.75,
      strokeWeight: 4
    });
    line.setMap(map);

    mapUpdRange = (a,b) => {//new function to update the map sources according to the trim slider
        path = window.MapData.path.slice(a,b).filter(([a,b])=>(a>0||a<0||b>0||b<0)).map(v=>new google.maps.LatLng(v[0],v[1]));
        if (path.length==0) return alert('Time range has no gps data');
        line.setPath(path);
        startcir.setPosition(path[path.length-1]);
        endcir.setPosition(path[0]);
        bounds = new google.maps.LatLngBounds();
        path.forEach(v=>bounds.extend(v));
        map.fitBounds(bounds);
    };

    const markerCir = new google.maps.Marker({
        position:path[0],
        icon: {path: google.maps.SymbolPath.CIRCLE,fillOpacity: 0.1,fillColor: '#bb00bb',strokeOpacity: 0.8,strokeColor: '#bb00bb',strokeWeight: 2,scale: 6}
    });
    const markerPnt = new google.maps.Marker({
        position:path[0],
        icon: {path: google.maps.SymbolPath.CIRCLE,fillOpacity: 0.1,fillColor: '#bb00bb',strokeOpacity: 0.8,strokeColor: '#bb00bb',strokeWeight: 2,scale: 1}
    });
    markerUpd = itm => {//this functions updates the marker while hovering the chart and clears it when not hovering
        itm&&itm.dataIndex>0&&markerCir.setPosition(path[itm.dataIndex]);
        itm&&itm.dataIndex>0&&markerPnt.setPosition(path[itm.dataIndex]);
        markerCir.setMap((itm&&itm.dataIndex>0)?map:null);
        markerPnt.setMap((itm&&itm.dataIndex>0)?map:null);
    }
    tempMap = a=>{//new function to update map with temp values read from torque CSV
        path = a.map(v=>new google.maps.LatLng(v[0],v[1])); //create google coord
        line.setPath(path);
        startcir.setPosition(path[path.length-1]);
        endcir.setPosition(path[0]);
        bounds = new google.maps.LatLngBounds();
        path.forEach(v=>bounds.extend(v));
        map.fitBounds(bounds);
    };
}
//End of Google Map Provider js code

//Start of Leaflet Map Providers js code
initMapLeaflet = () => {
    const provider = window.MapData.provider;
    const style = window.MapData.style;
    const keys = window.MapData.keys;
    var path = window.MapData.path;
    var map = new L.Map("map-canvas", {
        center: new L.LatLng(37.7, -122.4),
        zoom: 6});
    let layer = null;
    if (provider === 'stamen') {
        const stamenLayer = ()=>{layer=new L.StamenTileLayer(style);map.addLayer(layer)};
        (L.StamenTileLayer==undefined)?$.getScript('https://stamen-maps.a.ssl.fastly.net/js/tile.stamen.js?v1.3.0',stamenLayer):stamenLayer();
    } else if (provider === 'esri') {
        const esriLayer = ()=>{layer=new L.esri.basemapLayer(style);map.addLayer(layer)};
        (L.esri==undefined)?$.getScript('https://unpkg.com/esri-leaflet@3.0.2/dist/esri-leaflet.js',esriLayer):esriLayer();
    } else if (provider === 'openstreetmap') {
        layer = new L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'});
    } else if (provider === 'mapbox') {
        layer = new L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: style,
            tileSize: 512,
            zoomOffset: -1,
            accessToken: keys.mapbox});
    } else if (provider === 'tomtom') {
        layer = new L.tileLayer('https://api.tomtom.com/map/1/tile/basic/{style}/{z}/{x}/{y}.png?key={apikey}', {
            attribution:'<a href="https://tomtom.com" target="_blank">&copy;  1992 - ' + new Date().getFullYear() + ' TomTom.</a> ',
            style: style,
            apikey: keys.tomtom});
    } else if (provider === 'thunderforest') {
        layer = new L.tileLayer('https://tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey={apikey}', {
            attribution:'&copy; <a href="http://www.thunderforest.com/">Thunderforest</a>, &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap contributors</a>',
            apikey: keyst.thunderforest});
    } else if (provider === 'here') {
        layer = new L.tileLayer('https://{s}.base.maps.ls.hereapi.com/maptile/2.1/maptile/newest/{type}/{z}/{x}/{y}/{size}/png8?apiKey={apikey}&lg=eng',{
            attribution: 'Map &copy; 1987-' + new Date().getFullYear() + ' <a href="http://developer.here.com">HERE</a>',
            subdomains: '1234',
            type: style,
            apikey: keys.here,
            maxZoom: 20,
            size: '256'});
    } else if (provider === 'maptiler') {
        layer = new L.tileLayer('https://api.maptiler.com/maps/{style}/{z}/{x}/{y}{r}.png?key={apikey}',{
            attribution:'&copy; <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap contributors</a>',
            style: style,
            apikey: keys.maptiler,
            tileSize: 512,
            zoomOffset: -1,
            maxZoom: 21});
    }
    (layer!==null)&&map.addLayer(layer);

    // start and end point marker
    var pathL = path.length;
    var endCrd = path[0];
    var startCrd = path[pathL-1];
    const startcir = L.circleMarker(startCrd, {color:'green',title:'Start',alt:'Start Point',radius:6,weight:1}).addTo(map);
    const endcir = L.circleMarker(endCrd, {color:'black',title:'End',alt:'End Point',radius:6,weight:1}).addTo(map);
    // travel line
    var polyline = L.polyline(path, {color: 'red'}).addTo(map);
    // zoom the map to the polyline
    map.fitBounds(polyline.getBounds(), {maxZoom: 15});

    mapUpdRange = (a,b) => {//new function to update the map sources according to the trim slider
        path = window.MapData.path.slice(a,b).filter(([a,b])=>(a>0||a<0||b>0||b<0));
        if (path.length==0) return alert('Time range has no gps data');
        polyline.setLatLngs(path);
        startcir.setLatLng(path[path.length-1]);
        endcir.setLatLng(path[0]);
        map.fitBounds(polyline.getBounds(), {maxZoom: 15});
    };
    const markerCir = L.circleMarker(startCrd, {color:'purple',alt:'Start Point',radius:10,weight:1});
    const markerPnt = L.circleMarker(startCrd, {color:'purple',alt:'End Point',radius:2,weight:1});
    markerUpd = itm => {//this functions updates the marker while hovering the chart and clears it when not hovering
        itm&&itm.dataIndex>0&&markerCir.setLatLng(path[itm.dataIndex]);
        itm&&itm.dataIndex>0&&markerPnt.setLatLng(path[itm.dataIndex]);
        (itm&&itm.dataIndex>0)?markerCir.addTo(map):map.removeLayer(markerCir);
        (itm&&itm.dataIndex>0)?markerPnt.addTo(map):map.removeLayer(markerPnt);
    }

    tempMap = a=>{//new function to update map with temp values read from torque CSV
        path = a.map(v=>[v[0],v[1]]); //discard speed for now
        polyline.setLatLngs(path);
        startcir.setLatLng(path[path.length-1]);
        endcir.setLatLng(path[0]);
        map.fitBounds(polyline.getBounds(), {maxZoom: 15});
    };
}
//End of Leaflet Map Providers js code

//slider js code
initSlider = (jsTimeMap,minTimeStart,maxTimeEnd,timestartval,timeendval)=>{
    var minTimeStart = minTimeStart;
    var maxTimeEnd = maxTimeEnd;
    var TimeStartv = timestartval;
    var TimeEndv = timeendval;

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
        //$( "#slider-range11" ).on( "slidechange", function( event, ui ){$('#slider-time').attr("sv0", jsTimeMap[$('#slider-range11').slider("values", 0)])});
        //$( "#slider-range11" ).on( "slidechange", function( event, ui ){$('#slider-time').attr("sv1", jsTimeMap[$('#slider-range11').slider("values", 1)])});
        //merged the 2 listeners in 1 and added functions to visually trim map data and plot in realtime when using the trim session slider
        $( "#slider-range11" ).on( "slidechange", (event,ui)=>{
            $('#slider-time').attr("sv0", jsTimeMap[$('#slider-range11').slider("values", 0)])
            $('#slider-time').attr("sv1", jsTimeMap[$('#slider-range11').slider("values", 1)])
            const [a,b] = [jsTimeMap.length-$('#slider-range11').slider("values",1)-1,jsTimeMap.length-$('#slider-range11').slider("values",0)-1];
            if (typeof mapUpdRange=='function') mapUpdRange(a,b);
            if (typeof chartUpdRange=='function') chartUpdRange(a,b);
        });
    } );

    window.settimev = () => {//set post array for slider
        var sv0 =  document.getElementById("slider-time").getAttribute("sv0");
        var sv1 =  document.getElementById("slider-time").getAttribute("sv1");
        var sv3 = timestartval;

        if (sv0 <= 0 && sv1 <= 0){
            var sv0 = timestartval;
            var sv1 = timeendval;
        }
        if (sv0 == -1 && sv1 == -1){
            var sv0 = minTimeStart;
            var sv1 = maxTimeEnd;
        }
        var svarr = [sv0,sv1];
        document.getElementById("formplotdata").svdata.value = svarr;
    }
}
//End slider js code

//CSV Import
initImportCSV = () => {
    const tempSlider = a=>{
        $( "#slider-range11").off( "slidechange"); //this is to avoid 2 listeners
        initSlider(a,[a[0]],[a[a.length-1]],[-1],[-1]);
    }
    const tempChart = a=>{
        const killPlot = ()=> {
            if ($('#placeholder')[0]!=undefined) {//clean our plot if it exists
                flotData = [];
                plot.shutdown();
                const noChart = $('<div>',{align:'center',style:'padding-top:10px'}).append($('<h5>').append($('<span>',{class:'label label-warning'}).html('No Variables Selected to Plot!')));
                $('#Chart-Container').empty();
                $('#Chart-Container').append(noChart);
                $('#Summary-Container').empty();
                $('#Summary-Container').append(noChart);
            }
        }
        tempUpdCharts = ()=>{
            const jsTimeMap = [...a['Device Time']];
            if ($('#plot_data').chosen().val()==null) {
                killPlot();
            } else {
                flotData = [];
                $('#plot_data').chosen().val().forEach(v=>flotData.push({label:v,data:a[v].map((e,i)=>[a['Device Time'][i],e])}));
                if ($('#placeholder')[0]==undefined) {
                    $('#Chart-Container').empty();
                    $('#Chart-Container').append($('<div>',{class:'demo-container'}).append($('<div>',{id:'placeholder',class:'demo-placeholder',style:'height:300px'})));
                    doPlot("right");
                }
                //always update the chart trimmed range when plotting new data
                const [b,c] = [jsTimeMap.length-$('#slider-range11').slider("values",1)-1,jsTimeMap.length-$('#slider-range11').slider("values",0)-1];
                chartUpdRange(b,c);
                $('#Summary-Container').empty();
                $('#Summary-Container').append($('<div>',{class:'table-responsive'}).append($('<table>',{class:'table'}).append($('<thead>').append($('<tr>'))).append('<tbody>')));
                ['Name','Min/Max','25th Pcnt','75th Pcnt','Mean','Sparkline'].forEach(v=>$('#Summary-Container>div>table>thead>tr').append($('<th>').html(v)));
                const quantile = (a,q)=>{
                    const base = Math.floor((a.length-1)*q/100);
                    if (a[base+1]!==undefined) return a[base]+(((a.length-1)*q/100)-base)*(a[base+1]-a[base]);
                    else return a[base];
                };
                const trData = (k,a)=>{
                    const aSorted = [...a].sort((a,b)=>a-b);
                    const tr=$('<tr>');
                    [k,aSorted[0]+'/'+aSorted[a.length-1],quantile(aSorted,25),quantile(aSorted,75),aSorted.reduce((a,b)=>a+b,0)/a.length,[...a].reverse().join(",")]
                        .forEach((v,i)=>{tr.append($('<td>').html(i<5?v:'').append(i<5?'':$('<span>',{class:'line'}).html(v)));});
                    return tr;
                }
                $('#plot_data').chosen().val().forEach(k=>$('#Summary-Container>div>table>tbody').append(trData(k,a[k])));
                $(".line").peity("line");
            }
        }
        killPlot();
        //rebuild options based on csv columns with actual data
        $('#plot_data').empty();
        Object.entries(a).filter(([k,v])=>(!k.match(/(GPS|Device)\sTime/))&&v.some(e=>(e>0||e<0))).forEach(([k,v])=>$('#plot_data').append($('<option>',{value:k,text:k})));
        $('#plot_data').trigger("chosen:updated");
        $('#plot_data').chosen().off('change'); //disable original listener
        $('#plot_data').chosen().change(tempUpdCharts);
    }
    const tempCSV = a=>{
        //build an object with the csv columns and saving data
        const chkZero = v=>(v>0||v<0)?v:0; //this functions just avoids NaN data
        const tempData = {};
        a[0].forEach(//we use the first line of the csv to create our keys
            (v,i)=>(tempData[v]==undefined)&& //check duplicates
                (tempData[v]=a.slice(1,a.length-(a[a.length-1].length<5?1:0)).reverse() //create object with column data, remove first line, and remove last line if it has less than 5 columns
                    .map(e=>v.match(/(GPS|Device)\sTime/)?Date.parse(e[i]):chkZero(parseFloat(e[i]))))); //parse string to time if it match torque time fields or float otherwise
        tempSlider([...tempData['Device Time']].reverse());
        tempChart(tempData);
        //we build a new array with only the coordinates and speed (obd>gps), turn the strings to float and then filter lines with lat or long <> 0
        const spdIdx = Object.entries(tempData).reverse().filter(([a,b])=>a.match(/Speed \(GPS|OBD\)/)&&b.some(e=>e>0));
        let data = tempData['Latitude'].map((v,i)=>[v,tempData['Longitude'][i],spdIdx.length==0?0:tempData[spdIdx[0][0]][i]]);
        window.MapData.path = data.map(v=>[v[0],v[1]]);
        window.MapData.spd = data.map(v=>v[2]);
        //the map generation from csv expects data already filtered, the trim functions need the original data to work correctly in case there are data points with no gps data so we just filter it before generating the map
        data = data.filter(([a,b,c])=>(a>0||a<0||b>0||b<0));
        (data.length>0)?tempMap(data):alert('Map has not updated with the csv data, cause there is no valid gps data');
        $('input#formplotdata').hide(); //hide this button when you load a csv file, cause it actually stops working, trim wouldn't match the session, and plot variables use csv description so they don't match codes either
        alert('CSV file finished loading');
        //still need a php script to import to database but I have to resolve variable description to torque keys before that, maybe show spreadsheet like interface for the user to match?
    }
    const readCSV = t=>{
        //just split text in lines and every line in fields
        const csv = t.split('\n').map(v=>v.split(',').map(e=>e.trim()));
        if (csv.length<5) return alert('This file is probably not a csv or has less than the 5 lines minimun.');
        if (csv[0].length<5) return alert('This file is probably not a csv or has less than the 5 fields minimun.');
        if (csv[0][0]!=='GPS Time') return alert('This file is aparently a csv but it is not from torque.');//first field always has to be GPS Time
        tempCSV(csv);
    }
    const upload = e=>{
        e.stopPropagation();
        e.preventDefault();
        const f = e.dataTransfer.files;
        if (f.length > 0) {
            const rdr = new FileReader();
            rdr.onload = e=>readCSV(e.target.result);
            rdr.readAsText(f[0]);
        };
    }
    window.addEventListener("dragenter", e=>e.preventDefault(), false);
    window.addEventListener("dragover", e=>e.preventDefault(), false);
    window.addEventListener("drop", upload, false);
}
$(document).ready(initImportCSV)
//End CSV Import
