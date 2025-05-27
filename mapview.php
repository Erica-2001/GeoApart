<?php include 'navigation.php'; ?>
<?php include 'db_connect.php'; ?>
 <!-- TABS -->
  <br><br><br><br>
  <nav class="tab-nav">
    <button class="tab" id="feed-tab">Feed</button>
    <button class="tab active" id="map-tab">Map View</button>
  </nav>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GeoApart</title>
  <style>
    /* Optional: Make the map responsive */
    .map-container {
      position: relative;
      width: 100%;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      /* Adjust padding-bottom for different aspect ratios (e.g., 75% for 4:3) */
    }
    .map-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 300%;
      border: 0; /* Removes iframe border */
    }
	
  </style>
</head>
<body>

  
<!-- MAP CONTAINER -->
<div id="map" style="width: 180%; height: 700px; border-radius: 23px;"></div>

<script>
    function initMap() {
        var mapOptions = {
            zoom: 18,
            center: { lat: 13.7632758, lng: 121.0634052 }, // Default center point near apartments
            streetViewControl: true,
            mapTypeControl: true,
            fullscreenControl: true,
            zoomControl: true
        };

        var map = new google.maps.Map(document.getElementById('map'), mapOptions);

        // Apartment locations with formatted info
        var locations = [
            {
                title: "Baranda's Apartment",
                content: `<strong>Baranda's Apartment</strong><br>Landlord: Joy Baranda & Eddie Baranda<br>Contact: 09760649022`,
                lat: 13.763582, lng: 121.061998
            },
            {
                title: "LBB's Apartment",
                content: `<strong>LBB's Apartment</strong><br>Landlord: Luis Bagsit Bagui<br>Contact: 09760649022`,
                lat: 13.763319, lng: 121.062330
            },
            {
                title: "Aila’s Apartment 1",
                content: `<strong>Aila’s Apartment 1</strong><br>Landlord: Rosemarie Donayre<br>Contact: 09988584015`,
                lat: 13.763134, lng: 121.062550
            },
            {
                title: "Aila’s Apartment 2",
                content: `<strong>Aila’s Apartment 2</strong><br>Landlord: Rosemarie Donayre<br>Contact: 09988584015`,
                lat: 13.763349, lng: 121.063074
            },
        ];

        // Create one InfoWindow instance
        var infoWindow = new google.maps.InfoWindow();

        // Add clickable markers
        locations.forEach(function(location) {
            var marker = new google.maps.Marker({
                position: { lat: location.lat, lng: location.lng },
                map: map,
                title: location.title
            });

            marker.addListener('click', function () {
                infoWindow.setContent(location.content);
                infoWindow.open(map, marker);
            });
        });
    }
</script>


<!-- Add Google Maps JavaScript API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCtaWUbp10XmiNRZg7upQHgutTCaPmtL3M&callback=initMap"></script>


   

</body>
</html>
<script>
  document.getElementById("map-tab").addEventListener("click", function () {
    window.location.href = "mapview.php";
  });
</script>
<script>
  document.getElementById("feed-tab").addEventListener("click", function () {
    window.location.href = "index.php";
  });
</script>
