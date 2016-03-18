<?php

require_once ("./creds.php");
require_once ("./auth_user.php");

// Connect to Database
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());

// Create array of column name/comments for chart data selector form
// 2015.08.21 - edit by surfrock66 - Rather than pull from the column comments,
//   oull from a new database created which manages variables. Include
//   a column flagging whether a variable is populated or not.
$keyqry = mysql_query("SELECT id,description,units,type,min,max,populated FROM ".$db_name.".".$db_keys_table." ORDER BY description") or die(mysql_error());
$i = 0;
while ($x = mysql_fetch_array($keyqry)) {
	if ((substr($x[0], 0, 1) == "k") ) {
		$keydata[$i] = array("id"=>$x[0], "description"=>$x[1], "units"=>$x[2], "type"=>$x[3], "min"=>$x[4], "max"=>$x[5], "populated"=>$x[6]);
		$i = $i + 1;
	}
}
mysql_free_result($keyqry);
mysql_close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open Torque Viewer</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Joe Gullo (surfrock66)">
    <link rel="stylesheet" href="static/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
    <link rel="stylesheet" href="static/css/torque.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>
      $(function(){
        var message_status = $("#status");

        $("td[contenteditable=true]").blur(function(){
          var field_pid = $(this).attr("id");
          var value = $(this).text();
          $.post('pid_commit.php' , field_pid + "=" + value, function(data) {
            if(data != '') {
              message_status.show();
              message_status.text(data);
              setTimeout(function(){message_status.hide()},3000);
            }
          });
        });
	
        $("input[contenteditable=true]").blur(function(){
          var field_pid = $(this).attr("id");
          var value = $(this).is(":checked");
          $.post('pid_commit.php' , field_pid + "=" + value, function(data) {
            if(data != '') {
              message_status.show();
              message_status.text(data);
              setTimeout(function(){message_status.hide()},3000);
            }
          });
        });
	
        $("select[contenteditable=true]").blur(function(){
          var field_pid = $(this).attr("id");
          var value = $(this).val();
          $.post('pid_commit.php' , field_pid + "=" + value, function(data) {
            if(data != '') {
              message_status.show();
              message_status.text(data);
              setTimeout(function(){message_status.hide()},3000);
            }
          });
        });
      });
    </script>
  </head>
  <body>
    <div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="session.php">Open Torque Viewer</a>
        </div>
      </div>
    </div>
    <table class="table" style="width:98%;margin:0px auto;margin-top:50px;">
      <thead>
        <th>ID</th>
        <th>Description</th>
        <th>Units</th>
        <th>Variable Type</th>
        <th>Min Value</th>
        <th>Max Value</th>
        <th>In Use?</th>
      </thead>
      <tbody>
<?php $i = 1; ?>
<?php foreach ($keydata as $keycol) { ?>
        <tr<?php if ($i & 1) echo " class=\"odd\"";?>>
          <td id="id:<?php echo $keycol['id']; ?>"><?php echo $keycol['id']; ?></td>
          <td id="description:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['description']; ?></td>
          <td id="units:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['units']; ?></td>
          <td>
            <select  id="type:<?php echo $keycol['id']; ?>" contenteditable="true">
              <option value="boolean"<?php if ($keycol['type'] == "boolean") echo ' selected'; ?>>boolean</option>
              <option value="float"<?php if ($keycol['type'] == "float") echo ' selected'; ?>>float</option>
              <option value="varchar(255)"<?php if ($keycol['type'] == "varchar(255)") echo ' selected'; ?>>varchar(255)</option>
            </selecT>
          </td>
          <td id="min:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['min']; ?></td>
          <td id="max:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['max']; ?></td>
          <td><input type="checkbox" id="populated:<?php echo $keycol['id']; ?>" contenteditable="true"<?php if ( $keycol['populated'] ) echo " CHECKED"; ?>/></td>
        </tr>
<?php   $i = $i + 1; ?>
<?php } ?>
      </tbody>
    </table>
    <div id="status" style="padding:10px; background:#88C4FF; color:#000; font-weight:bold; font-size:12px; margin-bottom:10px; display:none; width:90%;"></div>
  </body>
</html>

