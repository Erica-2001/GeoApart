<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user['user_type'] !== 'Tenant') {
    echo "<script>alert('Access Denied! Only tenants can access this page.'); window.location.href='login.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Baranda's Apartment - Directions</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f9f9f9;
    }
    .back {
      padding: 15px;
      font-size: 18px;
    }
    .back a {
      color: #007bff;
      text-decoration: none;
    }
    .back a:hover {
      text-decoration: underline;
    }
    h3 {
      text-align: center;
      color: #007bff;
      margin-top: 10px;
    }
    #map {
      width: 100%;
      height: 80vh;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      margin: 20px auto;
    }
  </style>
</head>
<body>

  <div class="back">
    <a href="tenant_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
  </div>

  <h3>📍 Route to Baranda's Apartment</h3>
  <div class="container">
    <div id="map"></div>
  </div>

  <!-- Google Maps API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBRKKe1DSD3al3QJb7yKQQqvdePj0nYs78&callback=initMap" async defer></script>
  <script>
    const destination = { lat: 13.763582, lng: 121.061998 };

    function initMap() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            const origin = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            const map = new google.maps.Map(document.getElementById("map"), {
              zoom: 14,
              center: origin
            });

            const directionsService = new google.maps.DirectionsService();
            const directionsRenderer = new google.maps.DirectionsRenderer({ map });

            // Calculate and render route
            directionsService.route({
              origin: origin,
              destination: destination,
              travelMode: google.maps.TravelMode.DRIVING
            }, (response, status) => {
              if (status === "OK") {
                directionsRenderer.setDirections(response);
              } else {
                alert("Directions request failed: " + status);
              }
            });

            // Origin Marker
            const originMarker = new google.maps.Marker({
              position: origin,
              map: map,
              title: "You are here",
              icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"
              }
            });

            const originInfo = new google.maps.InfoWindow({
              content: "<strong><b>Hi, I'm your guide today! This is where you are!</b></strong>",
            });
            originInfo.open(map, originMarker);

            // Destination Marker
            const destinationMarker = new google.maps.Marker({
              position: destination,
              map: map,
              title: "Baranda's Apartment",
              icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
              }
            });

            const destinationInfo = new google.maps.InfoWindow({
              content: "<strong><b>This is where you go! <br>Baranda's Apartment</b></strong>",
            });
            destinationInfo.open(map, destinationMarker);

          },
          (error) => {
            let msg = "Location access error.";
            if (error.code === 1) msg = "Permission to access location denied.";
            else if (error.code === 2) msg = "Location unavailable.";
            else if (error.code === 3) msg = "Location request timed out.";
            alert(msg);
          },
          { enableHighAccuracy: true, timeout: 10000 }
        );
      } else {
        alert("Geolocation is not supported by this browser.");
      }
    }
  </script>

  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
