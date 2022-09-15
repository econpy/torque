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
    flotData.every(i=>dataSet.push({label:i.label,data:i.data.slice(a,b)}));
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
            $('#Chart-Container').empty();
            $('#Chart-Container').append($('<div>',{align:'center',style:'padding-top:10px'}).append($('<h5>').append($('<span>',{class:'label label-warning'}).html('No Variables Selected to Plot!'))));
        }
    } else {
        let varPrm = 'plot.php?id='+$('#seshidtag').chosen().val();
        $('#plot_data').chosen().val().every((v,i)=>varPrm+='&s'+(i+1)+'='+v);
        $.get(varPrm,d=>{
            flotData = [];
            JSON.parse(d).every(v=>flotData.push({label:v[1],data:v[2].map(a=>a=[parseInt(a[0]),a[1]])}));
            if ($('#placeholder')[0]==undefined) { //this would only be true the first time we load the chart
                $('#Chart-Container').empty();
                $('#Chart-Container').append($('<div>',{class:'demo-container'}).append($('<div>',{id:'placeholder',class:'demo-placeholder',style:'height:300px'})));
                doPlot("right");
            }
            //always update the chart trimmed range when plotting new data
            const [a,b] = [jsTimeMap.length-$('#slider-range11').slider("values",1)-1,jsTimeMap.length-$('#slider-range11').slider("values",0)-1];
            chartUpdRange(a,b);
        });
    }
}
//End of chart plotting js code

//Start Openlayers Map Provider js code
initMapOpenlayers = (pathAll,spdAll,spdUnit) => {
    let path = pathAll; //by default full range
    let spd = spdAll;
    const baseLst = [['Open Street Map','OSM'], //base layer option list
        ['Esri Streets','ESRI'],['Esri Dark Base','ESRI.DARK'],['Esri Gray Base','ESRI.GRAY'],['Esri Satellite','ESRI.SATE'],['Esri Topo','ESRI.TOPO'],['Esri NatGeo','ESRI.NATGEO'],
        ['Stamen','STAMEN'],['Stamen Terain','STAMEN.TERRAIN'],['Stamen Watercolor','STAMEN.WATERCOLOR']];
    $('#map-container')
        .prepend($('<select>',{id:'BaseLayerOpt'}).css({position:'relative','z-index':300,left:'80px'}))//creates a new select element with the options for the base layers
        .prepend($('<div>').css('position','absolute').append($('<div>',{id:'ttip'}).css({position:'relative','z-index':100,'background-color':'white','border-radius':'10px',opacity:0.9,width:'100px'})));//creates the tooltip element
    $.each(baseLst,(i,el)=>$('#BaseLayerOpt').append($('<option>',{value:el[1],text:el[0]})));
    $('#map-container>select').val('ESRI.SATE');
    $('#map-container>select').off('change');
    const updBase = () => { //updates the base layer based on selection
        const selLayer = $('#BaseLayerOpt').find(":selected").val()!==undefined?$('#BaseLayerOpt').find(":selected").val():'OSM';
        tileLayer.setUrl(selLyrUrl[selLayer]||selLyrUrl['OSM']);
    }
    $('#map-container>select').on('change',updBase);
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
    //defines the default base layer
    const tileLayer = new ol.source.XYZ({url:selLyrUrl['ESRI.SATE']});
    const baseLayer = new ol.layer.Tile({source:tileLayer});
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
        ttip.css({top:pxl[1]+'px',left:pxl[0]+'px'}).html(msg);
        } else{
        ttip.html('');
        }
    }
    //this is the actual listener on the map to create our tooltip
    map.on('pointermove',evt=>evt.dragging?ttip.html(''):sData(evt));
}
//End of Openlayers Map Provider js code