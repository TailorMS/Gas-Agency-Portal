<?php
/*
* AmodIndane - Admin Login
* Database Connection
*/

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Your DB username
define('DB_PASSWORD', ''); // Your DB password
define('DB_NAME', 'amod_indane_db'); // Your DB name

// Attempt to connect to MySQL database
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}

// For procedural style compatibility
$link = $mysqli;
?>
