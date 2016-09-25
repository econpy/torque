<?php

require_once ("./db.php");
require_once ("./auth_user.php");

if(!empty($_POST)) {
  //database settings
  foreach($_POST as $field_name => $val) {
    //clean post values
    $field_id = strip_tags(trim($field_name));
    $val = strip_tags(trim(mysql_real_escape_string($val)));

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
echo "\nUPDATE $db_name.$db_keys_table SET $field_name = '$val' WHERE id = '$id'\n";
      mysql_query("UPDATE $db_name.$db_keys_table SET $field_name = '$val' WHERE id = '$id'") or mysql_error;
      if($field_name == 'type') {
echo "ALTER TABLE $db_name.$db_table MODIFY $id $val NOT NULL DEFAULT '0'";
        mysql_query("ALTER TABLE $db_name.$db_table MODIFY $id $val NOT NULL DEFAULT '0'") or mysql_error;
      }
      echo "Updated";
    } else {
      echo "Invalid Requests 1";
    }
  }
} else {
  echo "Invalid Requests 2";
}

mysql_close($con);

?>
