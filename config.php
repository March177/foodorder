<?php
// config.php

$host = 'localhost'; // Database host
$user = 'root';      // Database username
$pass = '';          // Database password
$db   = 'restaurants_db';      // Database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
