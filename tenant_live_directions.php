<?php

include 'db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid Apartment!'); window.location.href='index.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);
$query = "SELECT name, location_link, landlord_id FROM apartments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$result = $stmt->get_result();
$apartment = $result->fetch_assoc();

if (!$apartment || empty($apartment['location_link'])) {
    echo "<script>alert('Location not found!'); window.location.href='index.php';</script>";
    exit();
}

$apartmentName = htmlspecialchars($apartment['name']);
$landlord_id = $apartment['landlord_id'];

// Fetch landlord contact info
$landlord_query = $conn->prepare("SELECT name, mobile FROM users WHERE id = ?");
$landlord_query->bind_param("i", $landlord_id);
$landlord_query->execute();
$landlord_result = $landlord_query->get_result();
$landlord = $landlord_result->fetch_assoc();

$contactName = $landlord ? $landlord['name'] : "Landlord";
$mobile = $landlord ? $landlord['mobile'] : "#";

// Extract lat/lng from location_link
preg_match('/@?([-.\d]+),([-.\d]+)/', $apartment['location_link'], $matches);
$destLat = $matches[1] ?? null;
$destLng = $matches[2] ?? null;

if (!$destLat || !$destLng) {
    echo "<script>alert('Invalid location link format!'); window.location.href='index.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Live Directions to <?= $apartmentName ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    #map {
      height: 100%;
      width: 100%;
    }

    .contact-btn {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #e0e0e0;
  color: black;
  padding: 15px 25px;
  border: 2px solid black;
  border-radius: 15px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 999;
  text-align: center;
  text-decoration: none; /* ✅ This removes the underline */
}


    .contact-btn:hover {
      background: #d0d0d0;
    }

    .map-container {
      position: relative;
      height: 100%;
    }
  </style>
</head>
<body>

<div class="map-container">
  <div id="map"></div>
  
  
  
  

<a href="tenant_dashboard.php" 
   
   class="contact-btn">Dashboard</a>

<script>
function markAsViewedAndRedirect(apartmentId) {
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;

    if (!isLoggedIn) {
        alert("Please log in first to continue.");
        window.location.href = "login.php";
        return;
    }

    // Mark as viewed using localStorage
    let viewed = JSON.parse(localStorage.getItem("viewed_apartments") || "[]");
    if (!viewed.includes(apartmentId)) {
        viewed.push(apartmentId);
        localStorage.setItem("viewed_apartments", JSON.stringify(viewed));
    }

    // Redirect
    window.location.href = "apartment_details_public_view.php?id=" + apartmentId;
}
</script>







<script>
let map, directionsService, directionsRenderer;

function initMap() {
    const destination = { lat: <?= $destLat ?>, lng: <?= $destLng ?> };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 14,
        center: destination
    });

    const marker = new google.maps.Marker({
        position: destination,
        map: map,
        title: "<?= $apartmentName ?>",
    });

    const infoWindow = new google.maps.InfoWindow({
        content: "<strong><?= $apartmentName ?></strong>"
    });

    marker.addListener("click", () => {
        infoWindow.open(map, marker);
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({ map });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const userLocation = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude
            };

            directionsService.route({
                origin: userLocation,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            }, (result, status) => {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);
                } else {
                    alert("Could not calculate route: " + status);
                }
            });

        }, () => {
            alert("Unable to access your location.");
        });
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCtaWUbp10XmiNRZg7upQHgutTCaPmtL3M&callback=initMap" async defer></script>
</body>
</html>
