<?php
$host = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "promeet";
$port = 3307;

$conn = new mysqli($host, $db_user, $db_pass, $db_name,$port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>