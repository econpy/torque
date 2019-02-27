<?php

require_once ("./db.php");
require_once ("./auth_user.php");

if(!empty($_POST)) {
  //database settings
  foreach($_POST as $field_name => $val) {
    //clean post values
    $field_id = strip_tags(trim($field_name));
    $val = strip_tags(trim($val));

    //from the fieldname:id we need to get id
    $split_data = explode(':', $field_id);
    $id = $split_data[1];
    $field_name = $split_data[0];
    if (!empty($id) && !empty($field_name)) {
      if ($field_name == 'populated') {
        if ($val == 'true') {
          $val=1;
        } else {
          $val=0;
       }
      } elseif ($field_name == 'favorite') {
        if ($val == 'true') {
          $val=1;
        } else {
          $val=0;
       }
      }
      //update the values
      $query = "UPDATE $db_name.$db_keys_table SET ".quote_name($field_name)." = ".quote_value($val)." WHERE id = ".quote_value($id);
      mysqli_query($con, $query) || die(mysqli_error($con));
      if($field_name == 'type') {
        $table_list = mysqli_query($con, "SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and table_name like '$db_table%' ORDER BY table_name DESC;");
        while( $row = mysqli_fetch_assoc($table_list) ) {
          $db_table_name = $row["table_name"];
          $query = "ALTER TABLE $db_name.$db_table_name MODIFY ".quote_name($id)." ".mysqli_real_escape_string($con, $val)." NOT NULL DEFAULT '0'";
          mysqli_query($con, $query) || die(mysqli_error($con));
        }
      }
      echo "Updated";
    } else {
      echo "Invalid Requests 1";
    }
  }
} else {
  echo "Invalid Requests 2";
}

mysqli_close($con);

?>
