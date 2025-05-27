<?php
include 'db_connect.php';

// Validate apartment ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid Apartment!'); window.location.href='index.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);

// Fetch apartment details (added location_link)
$query = "SELECT a.*, u.name AS landlord_name, u.email AS landlord_email, u.mobile AS landlord_mobile 
          FROM apartments a 
          JOIN users u ON a.landlord_id = u.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$result = $stmt->get_result();
$apartment = $result->fetch_assoc();

if (!$apartment) {
    echo "<script>alert('Apartment not found!'); window.location.href='index.php';</script>";
    exit();
}

// Fetch images
$image_query = "SELECT image_path FROM apartment_images WHERE apartment_id = ?";
$stmt = $conn->prepare($image_query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$image_result = $stmt->get_result();
$images = [];
while ($img = $image_result->fetch_assoc()) {
    $images[] = $img['image_path'];
}
if (empty($images)) {
    $images[] = "uploads/default_apartment.jpg";
}

$googleMapsQuery = urlencode($apartment['location']);
$location_link = !empty($apartment['location_link']) ? $apartment['location_link'] : "https://www.google.com/maps/search/?api=1&query={$googleMapsQuery}";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($apartment['name']) ?> - Details</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #f9f9f9; color: #333; padding: 20px; }
    .card { background: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 400px; margin: auto; }
    .slider-wrapper { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; border-radius: 16px; margin-bottom: 15px; }
    .slider-wrapper img { width: 100%; height: 220px; object-fit: cover; scroll-snap-align: center; border-radius: 16px; }
    h2 { font-size: 20px; text-align: center; margin-bottom: 5px; }
    .price { text-align: center; font-size: 16px; color: #444; margin-bottom: 15px; }
    .row { display: flex; justify-content: center; gap: 20px; margin-bottom: 15px; font-size: 14px; }
    .row i { margin-right: 5px; }
    .location { text-align: center; font-size: 14px; background: #eee; padding: 8px; border-radius: 10px; margin-bottom: 15px; }
    .features { margin-bottom: 15px; }
    .features ul { list-style: none; padding-left: 0; }
    .features li { margin-bottom: 8px; }
    .features i { color: #28a745; margin-right: 6px; }
    .landlord { background: #f1f1f1; border-radius: 10px; padding: 10px; font-size: 14px; margin-bottom: 15px; text-align: center; }
    .btn {
      display: block;
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      text-align: center;
      text-decoration: none;
      margin-bottom: 10px;
    }
    .btn-email { background: #28a745; color: white; }
    .btn-whatsapp { background: #25D366; color: white; }
    .btn-units { background: #ffc107; color: black; }
    .btn-map { background: #007bff; color: white; }
    iframe { width: 100%; height: 250px; border: none; border-radius: 12px; margin-top: 10px; }
    .back { margin-bottom: 10px; font-size: 20px; }
    .btn-messenger { background: #006AFF; color: white; }
  </style>
</head>
<body>

  <div class="back">
    <a href="index.php"><i class="fa-solid fa-arrow-left"></i></a>
  </div>

  <div class="slider-wrapper">
    <?php foreach ($images as $img): ?>
      <img src="<?= htmlspecialchars($img) ?>" alt="Apartment">
    <?php endforeach; ?>
  </div>

  <h2><?= htmlspecialchars($apartment['name']) ?></h2>
  <p class="price">₱<?= number_format($apartment['price'], 2) ?> / month</p>

  <div class="row">
    <div><i class="fas fa-door-open"></i> <?= htmlspecialchars($apartment['apartment_type']) ?></div>
  </div>

  <div class="location">
    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($apartment['location']) ?>
  </div>

  <div class="features">
    <h4>Features:</h4>
    <ul>
      <?php foreach (explode("\n", $apartment['features']) as $feature): ?>
        <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars(trim($feature)) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <a href="sms:<?= htmlspecialchars($apartment['landlord_mobile']) ?>?body=Hello%20I%27m%20interested%20in%20your%20apartment" class="btn btn-email">
    <i class="fas fa-sms"></i> Message Landlord
  </a>

  <a href="available_units_public.php?apartment_id=<?= $apartment_id ?>" class="btn btn-units"><i class="fas fa-door-open"></i> Check Available Units</a>

  <iframe src="https://www.google.com/maps?q=<?= $googleMapsQuery ?>&output=embed"></iframe>

  <a href="<?= htmlspecialchars($location_link) ?>" class="btn btn-map" target="_blank">
    <i class="fas fa-map"></i> Get Directions
  </a>

</body>
</html>
