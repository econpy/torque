<?php
    session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME']));
    session_start();
    $_SESSION['time'] = $_GET['time'];
?>
