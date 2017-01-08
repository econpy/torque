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
echo "\nField Name: '$field_name'\nField ID: '$id'\nValue: '$val'\n";
    if(!empty($id) && !empty($field_name) && !empty($val)) {
      if($field_name == 'populated') {
        if($val == 'true'){
          $val=1;
        } else {
          $val=0;
       }
      }
      //update the values
      $query = "UPDATE $db_name.$db_keys_table SET ".quote_name($field_name)." = ".quote_value($val)." WHERE id = ".quote_value($id);
echo "\n$query\n";
      mysqli_query($query) || die(mysqli_error($con));
      if($field_name == 'type') {
      $query = "ALTER TABLE $db_name.$db_table MODIFY ".quote_name($id)." ".mysqli_real_escape_string($val)." NOT NULL DEFAULT '0'";
echo $query;
        mysqli_query($query) || die(mysqli_error($con));
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
