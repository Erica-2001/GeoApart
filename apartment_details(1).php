<?php
session_start();
include 'db_connect.php';

// Validate apartment ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid Apartment!'); window.location.href='index.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);

// Fetch apartment details
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

// Fetch apartment images
$image_query = "SELECT image_path FROM apartment_images WHERE apartment_id = ?";
$stmt = $conn->prepare($image_query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$image_result = $stmt->get_result();
$images = [];
while ($img = $image_result->fetch_assoc()) {
    $images[] = $img['image_path'];
}

// Ensure at least one image exists
if (empty($images)) {
    $images[] = "uploads/default_apartment.jpg";
}

$googleMapsQuery = urlencode($apartment['location']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($apartment['name']); ?> - Apartment Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; color: #333; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); text-align: left; }
        h2 { color: #007bff; margin-bottom: 10px; text-align: center; }
        .price { font-size: 24px; font-weight: bold; color: black; text-align: center; }
        .location { font-size: 16px; color: #555; text-align: center; margin-bottom: 10px; }
        .landlord-info { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #ddd; margin-top: 10px; }
        .landlord-info i { margin-right: 5px; }
        
        /* Image Slider */
        .slider-container { overflow: hidden; width: 100%; border-radius: 8px; position: relative; }
        .slider-wrapper { display: flex; transition: transform 0.5s ease-in-out; }
        .slider-wrapper img { width: 100%; height: 300px; object-fit: cover; border-radius: 8px; }
        .slider-button { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0, 0, 0, 0.5); color: white; border: none; padding: 10px; cursor: pointer; font-size: 18px; }
        .slider-button.left { left: 10px; }
        .slider-button.right { right: 10px; }

        iframe { width: 100%; height: 350px; border-radius: 8px; margin-top: 10px; border: none; }
        .btn { padding: 12px 18px; border: none; border-radius: 6px; color: white; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; margin-top: 10px; }
        .btn-contact { background: #28a745; }
        .btn-contact:hover { background: #218838; }
        .btn-whatsapp { background: #25D366; }
        .btn-whatsapp:hover { background: #1EBE57; }
        .btn-map { background: #007bff; }
        .btn-map:hover { background: #0056b3; }
        .btn-available { background: #ffc107; color: black; }
        .btn-available:hover { background: #e0a800; }
        .details-section { padding: 15px; background: #f1f1f1; border-radius: 8px; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="container">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

 <div class="back">
        <a href="tenant_dashboard.php"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
	<br>
        <h2><?php echo htmlspecialchars($apartment['name']); ?></h2>
        <p class="price">₱<?php echo number_format($apartment['price'], 2); ?> / month</p>

      

        <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($apartment['location']); ?></p>

        <!-- Image Slider -->
        <div class="slider-container">
            <div class="slider-wrapper">
                <?php foreach ($images as $img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Apartment Image">
                <?php endforeach; ?>
            </div>
            <button class="slider-button left" onclick="prevSlide()">&#10094;</button>
            <button class="slider-button right" onclick="nextSlide()">&#10095;</button>
        </div>

        <!-- Apartment Features -->
        <div class="details-section">
            <h3>Features</h3>
            <p><?php echo nl2br(htmlspecialchars($apartment['features'])); ?></p>
        </div>

        <!-- Landlord Contact Information -->
        <div class="details-section">
            <h3>Landlord Details</h3>
            <div class="landlord-info">
                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($apartment['landlord_name']); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($apartment['landlord_email']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($apartment['landlord_mobile']); ?></p>
                <a href="mailto:<?php echo htmlspecialchars($apartment['landlord_email']); ?>" class="btn btn-contact"><i class="fas fa-envelope"></i> Email Landlord</a>
                <a href="https://wa.me/<?php echo htmlspecialchars($apartment['landlord_mobile']); ?>" class="btn btn-whatsapp"><i class="fab fa-whatsapp"></i> Chat on WhatsApp</a>
            </div>
        </div>
 <!-- Centered Container -->
<div style="text-align: center; margin-top: 20px;">
  <a href="available_units.php?apartment_id=<?php echo $apartment_id; ?>" class="btn btn-available">
    <i class="fas fa-door-open"></i> Check Available Units
  </a>
        <!-- Google Maps Embed -->
        <div class="details-section">
            <h3>Apartment Location</h3>
            <iframe src="https://www.google.com/maps?q=<?php echo $googleMapsQuery; ?>&output=embed"></iframe>
            <a href="https://www.google.com/maps/@13.7632518,121.0627357,164m/data=!3m1!1e3?entry=ttu&g_ep=EgoyMDI1MDMyNC4wIKXMDSoASAFQAw%3D%3D<?php echo $googleMapsQuery; ?>" target="_blank" class="btn btn-map"><i class="fas fa-map"></i> Get Directions</a>
        </div>
    </div>

    <script>
        let slideIndex = 0;
        function showSlide() {
            let slides = document.querySelector('.slider-wrapper');
            slides.style.transform = `translateX(-${slideIndex * 100}%)`;
        }
        function prevSlide() { slideIndex = (slideIndex > 0) ? slideIndex - 1 : slideIndex; showSlide(); }
        function nextSlide() { slideIndex = (slideIndex < <?php echo count($images) - 1; ?>) ? slideIndex + 1 : slideIndex; showSlide(); }
    </script>

</body>
</html>
