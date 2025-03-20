<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = 'root';
$db_name = 'learnix_db';

// Site-wide settings
$site_name = 'Learnix';
$base_url = 'http://localhost/learnix'; // Change this to your project's base URL

// Database connection
$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
