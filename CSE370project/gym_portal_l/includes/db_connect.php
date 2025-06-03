<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';         
$db_name = 'gym_ms';   


$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);


if ($mysqli->connect_error) {

    die("Database Connection Failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

?>