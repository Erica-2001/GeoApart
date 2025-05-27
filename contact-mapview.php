<?php include 'navigation.php'; ?>
<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GeoApart</title>
  <style>
    /* Ensure full-page layout */
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* Map Container */
    .map-container {
      position: relative;
      width: 100%;
      height: 100vh; /* Full viewport height */
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .map-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: 0;
    }

    /* Contact Button - Fixed at the bottom */
    .contact-btn {
      position: fixed;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      max-width: 400px;
      background: #e0e0e0;
      color: black;
      padding: 15px;
      border: 2px solid black;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      text-align: center;
      cursor: pointer;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Button Hover Effect */
    .contact-btn:hover {
      background: #d0d0d0;
    }

  </style>
</head>
<body>

  <!-- MAP CONTAINER -->
  <div class="map-container">
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d589.8123242031362!2d121.05899805245693!3d13.756408301665344!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sph!4v1740379853220!5m2!1sen!2sph"
      allowfullscreen
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
    ></iframe>
  </div>

<!-- Contact Button -->
<button class="contact-btn" onclick="window.location.href='contact.php'">Contact Mr. John Doe</button>


</body>
</html>
