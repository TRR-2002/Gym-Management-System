<?php

define('DB_HOST', 'localhost'); 
define('DB_USER', 'root');     
define('DB_PASS', '');          
define('DB_NAME', 'gym_ms');    

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {

    die("ERROR: Could not connect to the database. Please try again later.");
}
?>