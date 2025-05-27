<?php
$servername = "localhost";
$username = "root"; // Change if using another username
$password = ""; // Add your database password
$database = "geopart_db"; // Change to your database name

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


<script src="js/pull_to_refresh.js"></script>