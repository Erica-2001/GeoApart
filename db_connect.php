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



<script>
    function togglePassword(fieldId, icon) {
        let passwordField = document.getElementById(fieldId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>


<script src="js/pull_to_refresh.js"></script>
