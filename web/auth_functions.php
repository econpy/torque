<?php

//Get Username from Browser-Request
function get_user()
{
    if (isset($_POST["user"])) {
        $user = $_POST['user'];
    }
    elseif (isset($_GET["user"])) {
        $user = $_GET['user'];
    }
	else
    {
        $user = "";
    }

    return $user;
}


//Get Password from Browser-Request
function get_pass()
{
    if (isset($_POST["pass"])) {
        $pass = $_POST['pass'];
    }
    elseif (isset($_GET["pass"])) {
        $pass = $_GET['pass'];
    }
	else
    {
        $pass = "";
    }

    return $pass;
}


//Get Torque-ID from Browser-Request
function get_id()
{
    $id = "";

    if (isset($_POST["id"])) {
        if (1 === preg_match('/[\da-f]{32}/i', $_POST['id'], $matches))
        {
            $id = $matches[0];
        }
    }
    elseif (isset($_GET["id"])) {
        if (1 === preg_match('/[\da-f]{32}/i', $_GET['id'], $matches))
        {
            $id = $matches[0];
        }
    }
    
    return $id;
}


//True if User/Pass match those of creds.php
//If both $auth_user and $auth_pass are empty, all passwords are accepted.
function auth_user()
{
    global $auth_user, $auth_pass;
    
    $user = get_user();
    $pass = get_pass();

    //No User/Pass defined: Allow everything
    if ( empty($auth_user) && empty($auth_pass) ) {
        return true;
    }

    if ( ($user == $auth_user) && ($pass == $auth_pass) ) {
        return true;
    }

    return false;
}


//True is Torque-ID matches any of the IDs or HASHes defined in creds.php
//If both IDs and HASHes are empty, all IDs are accepted.
function auth_id()
{
    global $torque_id, $torque_id_hash;
    // Prepare authentification of Torque Instance that uploads data to this server
    // If $torque_id is defined, this will overwrite $torque_id_hash from creds.php

    $session_id = get_id();

    // Parse IDs from "creds.php", if IDs are defined these will overrule HASHES
    $auth_by_hash_possible = false;
    if (isset($torque_id) && !empty($torque_id))
    {
        if (!is_array($torque_id))
            $torque_id = array($torque_id);

        $torque_id_hash = array_map(md5,$torque_id);
        $auth_by_hash_possible = true;
    }
    // Parse HASHES
    elseif (isset($torque_id_hash) && !empty($torque_id_hash))
    {
        if (!is_array($torque_id_hash))
            $torque_id_hash = array($torque_id_hash);
        $auth_by_hash_possible = true;
    }

    // Authenticate torque instance: Check if we know its HASH
    if ($auth_by_hash_possible)
    {
        if (in_array($session_id, $torque_id_hash) )
        {
            return true;
        }
    }
    //No IDs/HASHEs defined: Allow everything
    else
    {
        return true;
    }

    return false;
}


?>