<?php
require("./creds.php");
require("./get_sessions.php");
require("./parse_functions.php");


// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// Grab the session number
if (isset($_GET["sid"]) and in_array($_GET["sid"], $sids)) {
    $session_id = intval(mysql_escape_string($_GET['sid']));

    // Get the torque key->val mappings
    $js = CSVtoJSON("./data/torque_keys.csv");
    $jsarr = json_decode($js, TRUE);

    // The columns to plot -- if no PIDs are specified I default to engine RPMs and
    // OBD speed as they are most likely being logged anyways.
    if (isset($_GET["s1"])) {
        $v1 = mysql_escape_string($_GET['s1']);
    }
    else {
        $v1 = "kc"; // Engine RPM
    }
    if (isset($_GET["s2"])) {
        $v2 = mysql_escape_string($_GET['s2']);
    }
    else {
        $v2 = "k5";   // Coolant Temp
    }

    // Grab the label for each PID to be used in the plot
    $v1_label = '"'.$jsarr[$v1].'"';
    $v2_label = '"'.$jsarr[$v2].'"';

    // Get data for session
    $sessionqry = mysql_query("SELECT time,$v1,$v2
                          FROM $db_table
                          WHERE session=$session_id
                          ORDER BY time DESC;") or die(mysql_error());

    // Convert data to my liking
    // TODO: Use the userDefault fields to do these conversions dynamically
    while($row = mysql_fetch_assoc($sessionqry)) {
        if (substri_count($jsarr[$v1], "Speed") > 0) {
            $d1[] = array(intval($row['time']),intval($row[$v1])*0.621371);
        }
        elseif (substri_count($jsarr[$v1], "Temp") > 0) {
            $d1[] = array(intval($row['time']),floatval($row[$v1])*9/5+32);
        }
        else {
            $d1[] = array(intval($row['time']),intval($row[$v1]));
        }
        if (substri_count($jsarr[$v2], "Speed") > 0) {
            $d2[] = array(intval($row['time']),intval($row[$v2])*0.621371);
        }
        elseif (substri_count($jsarr[$v2], "Temp") > 0) {
            $d2[] = array(intval($row['time']),floatval($row[$v2])*9/5+32);
        }
        else {
            $d2[] = array(intval($row['time']),intval($row[$v2]));
        }
    }

}

else if (!isset($_GET["sid"])) {
    $session_id = 0;
    $title_str = "Torque Plots - An Error Occured";
    $page_txt = "An error has occured. The session ID you've provided appears to be ok, but an unknown problem has occured.";
}

else if (!in_array($_GET["sid"], $sids)) {
    $session_id = 1;
    $title_str = "Torque Plots - Invalid Session ID";
    $page_txt = "The session number you've provided is not a valid session number in the database.";
}

else {
    $session_id = 2;
    $title_str = "Torque Plots - Session Not Set";
    $page_txt = "ERROR! No session number was provided.";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<?php if ($session_id < 3) { ?>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $title_str; ?></title>
</head>
<body>
  <div align="center" style="padding-top:45px;">
    <h2><?php echo $page_txt; ?></h2>
  </div>
</body>

<?php } else { ?>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo "Torque Plots - Session #".$session_id; ?></title>
    <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="assets/js/excanvas.min.js"></script><![endif]-->
    <link href="assets/css/flotexamples.css" rel="stylesheet" type="text/css">
    <script language="javascript" type="text/javascript" src="assets/js/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.axislabels.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.hiddengraphs.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.multihighlight-delta.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.time.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.tooltip.min.js"></script>
    <script language="javascript" type="text/javascript" src="assets/js/jquery.flot.updater.js"></script>
    <script type="text/javascript">
    $(function() {

        var s1 = [<?php foreach($d1 as $b) {echo "[".$b[0].", ".$b[1]."],";} ?>];
        var s2 = [<?php foreach($d2 as $d) {echo "[".$d[0].", ".$d[1]."],";} ?>];

        var flotData = [
                        { data: s1, label: <?php echo $v1_label; ?> },
                        { data: s2, label: <?php echo $v2_label; ?>, yaxis: 2 }
                       ];

        function doPlot(position) {
            //$.plotAnimator("#placeholder", [
            $.plot("#placeholder", flotData, {
                xaxes: [ {
                    mode: "time",
                    timezone: "browser",
                    axisLabel: "Time",
                    timeformat: "%I:%M%p",
                    twelveHourClock: true
                } ],
                //yaxes: [ { min: 0 , axisLabel: <?php echo '"'.$jsarr[$v1].'"'; ?> }, {
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
                    clickable: true
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

        // Setup the multiplots
        //$.each(flotData, function(index, plot){
            // Link the plots for highlighting them at the same time.
        //    plot.getOptions().multihighlight.linkedPlots = new Array();
        //    $.each(flotData, function(innerIndex, innerPlot){
        //        if (index != innerIndex) {
        //            plot.getOptions().multihighlight.linkedPlots.push(innerPlot);
        //        }
        //    });
        //});

        $("button").click(function () {
            doPlot($(this).text());
        });
    });
    </script>

    <script type="text/javascript">
    $(function () {
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
            $("#y1").text(pos.y1.toFixed(2));
            $("#y2").text(pos.y2.toFixed(2));

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
    });
    </script>
    <script type="text/javascript">
        var reloading;

        function checkReloading() {
            if (window.location.hash=="#autoreload") {
                reloading=setTimeout("window.location.reload();", 5000);
                document.getElementById("reloadCB").checked=true;
            }
        }

        function toggleAutoRefresh(cb) {
            if (cb.checked) {
                window.location.replace("#autoreload");
                reloading=setTimeout("window.location.reload();", 5000);
            } else {
                window.location.replace("#");
                clearTimeout(reloading);
            }
        }

        window.onload=checkReloading;
    </script>
</head>
<body>
    <div id="header">
        <h2>Torque Plots</h2>
    </div>
    <div id="content">
        <input type="checkbox" onclick="toggleAutoRefresh(this);" id="reloadCB"> <code>Auto Refresh (5 seconds)</code>
        <div class="demo-container">
            <div id="placeholder" class="demo-placeholder"></div>
        </div>
    </div>
    <div>
      <p style="padding-left:50px;"><strong>Y1:</strong> <span id="y1">0</span> at <span class="x">0</span>.</p>
      <p style="padding-left:50px;"><strong>Y2:</strong> <span id="y2">0</span> at <span class="x">0</span>.</p>
    </div>
    <div id="footer">
        Copyright &copy; 2014 - Matt Nicklay
    </div>
</body>

<?php } ?>
</html>
