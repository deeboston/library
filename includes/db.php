<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "u992749838_pa_boston"; // ✅ must match exactly

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
