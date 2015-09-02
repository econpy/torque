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

$(document).ready(function(){
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

$(document).ready(function(){
  // Activate Chosen on the selection drop down
  $("select#seshidtag").chosen({width: "100%"});
  $("select#selyear").chosen({width: "100%", disable_search: true, allow_single_deselect: true});
  $("select#selmonth").chosen({width: "100%", disable_search: true, allow_single_deselect: true});
  $("select#plot_data").chosen({width: "100%"});
  // Center the selected element
  $("div#seshidtag_chosen a.chosen-single span").attr('align', 'center');
  $("div#selyear_chosen a.chosen-single span").attr('align', 'center');
  $("div#selmonth_chosen a.chosen-single span").attr('align', 'center');
  $("select#plot_data").chosen({no_results_text: "Oops, nothing found!"});
  $("select#plot_data").chosen({placeholder_text_multiple: "Choose OBD2 data.."});
  // When the selection drop down is open, force all elements to align left with padding
  $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
  $('select#selyear').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#selyear').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#selmonth').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
  $('select#selmonth').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
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
