<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}
include '../db_connect.php';

$landlord_id = $_SESSION['user_id'];

function uploadImages($files) {
    $uploadedImages = [];
    $targetDir = "../uploads/";
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    foreach ($files['name'] as $key => $fileName) {
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($fileType, $allowedTypes)) {
            $uniqueName = time() . "_" . basename($fileName);
            $targetFilePath = $targetDir . $uniqueName;
            if (move_uploaded_file($files["tmp_name"][$key], $targetFilePath)) {
                $uploadedImages[] = "uploads/" . $uniqueName;
            }
        }
    }
    return $uploadedImages;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_apartment'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $location_link = mysqli_real_escape_string($conn, $_POST['location_link'] ?? '');
    $price = mysqli_real_escape_string($conn, $_POST['price']);
  $features = isset($_POST['features']) ? implode(', ', $_POST['features']) : '';

    
    $apartment_type = mysqli_real_escape_string($conn, $_POST['apartment_type']);

    $query = "INSERT INTO apartments (name, location, location_link, price, features, apartment_type, landlord_id) 
              VALUES ('$name', '$location', '$location_link', '$price', '$features', '$apartment_type', '$landlord_id')";

    if (mysqli_query($conn, $query)) {
        $apartment_id = mysqli_insert_id($conn);
        if (!empty($_FILES["images"]["name"][0])) {
            $uploadedImages = uploadImages($_FILES["images"]);
            foreach ($uploadedImages as $imagePath) {
                mysqli_query($conn, "INSERT INTO apartment_images (apartment_id, image_path) VALUES ('$apartment_id', '$imagePath')");
            }
        }
        echo "<script>alert('Apartment added successfully!'); window.location.href='landlord_manage_apartments.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to add apartment.');</script>";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM apartments WHERE id=? AND landlord_id=?");
    $stmt->bind_param("ii", $id, $landlord_id);
    if ($stmt->execute()) {
        echo "<script>alert('Apartment deleted successfully!'); window.location.href='landlord_manage_apartments.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to delete apartment.');</script>";
    }
    $stmt->close();
}

$result = mysqli_query($conn, "
    SELECT a.*, u.name AS landlord_name, u.email AS landlord_email, u.mobile AS landlord_mobile 
    FROM apartments a 
    JOIN users u ON a.landlord_id = u.id 
    WHERE a.landlord_id = '$landlord_id'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Apartments</title>
  <!-- Select2 CSS -->
 <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8f9fa;
      padding: 20px;
    }

    .form-container, .apartment-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      padding: 20px;
    }

    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 25px;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .submit-btn {
      background: #007bff;
      color: #fff;
      padding: 10px 20px;
      border: none;
      margin-top: 15px;
      border-radius: 6px;
      cursor: pointer;
    }

    .submit-btn:hover {
      background: #0056b3;
    }

    .apartment-card h3 {
      font-size: 18px;
      margin-bottom: 6px;
      color: #007bff;
    }

    .apartment-card p {
      font-size: 14px;
      margin: 4px 0;
    }

    .features-list {
      list-style: disc;
      margin: 8px 0 0 20px;
      font-size: 13px;
      color: #333;
    }

    .actions {
      display: flex;
      gap: 10px;
      margin-top: 12px;
    }

    .btn {
      padding: 6px 12px;
      font-size: 13px;
      border-radius: 6px;
      cursor: pointer;
      border: none;
      color: white;
      text-decoration: none !important;
    }

    .btn-edit { background-color: #ffc107; color: black; }
    .btn-delete { background-color: #dc3545; }
    .btn-add-unit { background-color: #28a745; }

    .back a {
      text-decoration: none !important;
      font-size: 16px;
      color: #007bff;
      display: inline-block;
      margin-bottom: 15px;
    }

    .back a:hover {
      text-decoration: none !important;
    }
  </style>
</head>
<body>

<div class="back">
  <a href="landlord_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<h2>Manage Apartments</h2>


  <h3>Add New Apartment</h3>
  <form action="" method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Apartment Name" required>
    <input type="text" name="location" placeholder="Location" required>
    <input type="url" name="location_link" placeholder="Google Maps Link (optional)">
    <input type="text" name="price" placeholder="Monthly Price (₱)" required>
    <input type="file" name="images[]" accept="image/*" multiple required>
    <select name="apartment_type" required>
      <option value="">Select Apartment Type</option>
      <option value="Studio">Studio</option>
      <option value="Loft">Loft</option>
      <option value="Duplex">Duplex</option>
      <option value="Micro">Micro</option>
    </select>
    <br><br>
    <label style="margin-top:15px; font-weight: 600;">Amenities:</label>
<select id="amenities-select" name="features[]" multiple="multiple" required>
  <option value="Bed Frame">Bed Frame</option>
  <option value="Pantry">Pantry</option>
  <option value="Bathroom">Bathroom</option>
  <option value="Refrigerator">Refrigerator</option>
  <option value="Aircon">Aircon</option>
  <option value="Own Sub-Meter">Own Sub-Meter</option>
  <option value="WiFi">WiFi</option>
  <option value="Parking Slot">Parking Slot</option>
  <option value="Visitors Allowed">Visitors Allowed</option>
  <option value="Pet Friendly">Pet Friendly</option>
</select>

    <button type="submit" name="add_apartment" class="submit-btn">Add Apartment</button>
  </form>
</div>

<?php while ($apartment = mysqli_fetch_assoc($result)): ?>
  <div class="apartment-card">
    <h3><?= htmlspecialchars($apartment['name']); ?> (<?= htmlspecialchars($apartment['apartment_type']); ?>)</h3>
    <p><strong>Location:</strong> <?= htmlspecialchars($apartment['location']); ?></p>
    <p><strong>Monthly Price:</strong> ₱<?= number_format($apartment['price'], 2); ?></p>
    <p><strong>Landlord:</strong> <?= htmlspecialchars($apartment['landlord_name']); ?> | <?= htmlspecialchars($apartment['landlord_email']); ?> | <?= htmlspecialchars($apartment['landlord_mobile']); ?></p>
    <p><strong>Features:</strong></p>
    <ul class="features-list">
      <?php foreach (explode(',', $apartment['features']) as $feature): ?>
        <li><?= htmlspecialchars(trim($feature)); ?></li>
      <?php endforeach; ?>
    </ul>

    <div class="actions">
      <a href="landlord_edit_apartments.php?id=<?= $apartment['id'] ?>" class="btn btn-edit"><i class="fa fa-pen"></i> Edit</a>
      <a href="?delete=<?= $apartment['id'] ?>" onclick="return confirm('Delete this apartment?')" class="btn btn-delete"><i class="fa fa-trash"></i> Delete</a>
      <a href="add_units.php?id=<?= $apartment['id'] ?>" class="btn btn-add-unit"><i class="fa fa-plus"></i> Add Unit</a>
    </div>
  </div>
<?php endwhile; ?>


<!-- jQuery (required) + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#amenities-select').select2({
      placeholder: "Select amenities...",
      tags: true,
      width: '100%'
    });
  });
</script>

</body>
</html>
