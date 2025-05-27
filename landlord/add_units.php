<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

include '../db_connect.php';

// Get logged-in landlord ID
$landlord_id = $_SESSION['user_id'];

// Get Apartment ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid Apartment!'); window.location.href='landlord_manage_apartments.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);

// Check if the landlord owns the apartment
$check_query = "SELECT * FROM apartments WHERE id = '$apartment_id' AND landlord_id = '$landlord_id'";
$check_result = mysqli_query($conn, $check_query);
if (mysqli_num_rows($check_result) == 0) {
    echo "<script>alert('Unauthorized Access!'); window.location.href='landlord_manage_apartments.php';</script>";
    exit();
}

// Function to upload multiple images securely
function uploadUnitImages($unit_id, $files) {
    global $conn;
    $targetDir = "../uploads/units/";
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    foreach ($files['name'] as $key => $fileName) {
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($fileType, $allowedTypes)) {
            $uniqueName = time() . "_" . basename($fileName);
            $targetFilePath = $targetDir . $uniqueName;

            if (move_uploaded_file($files["tmp_name"][$key], $targetFilePath)) {
                $imagePath = "uploads/units/" . $uniqueName;
                mysqli_query($conn, "INSERT INTO unit_images (unit_id, image_path) VALUES ('$unit_id', '$imagePath')");
            }
        }
    }
}

// Add Unit Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_unit'])) {
    $unit_number = mysqli_real_escape_string($conn, $_POST['unit_number']);
    $unit_status = mysqli_real_escape_string($conn, $_POST['unit_status']);
    $unit_price = mysqli_real_escape_string($conn, $_POST['unit_price']);
    $unit_features = isset($_POST['unit_features']) ? implode(', ', $_POST['unit_features']) : '';


    $insert_query = "INSERT INTO apartment_units (apartment_id, unit_number, unit_status, unit_price, unit_features) 
                     VALUES ('$apartment_id', '$unit_number', '$unit_status', '$unit_price', '$unit_features')";

    if (mysqli_query($conn, $insert_query)) {
        $unit_id = mysqli_insert_id($conn);
        
        // Upload multiple images
        if (!empty($_FILES["unit_images"]["name"][0])) {
            uploadUnitImages($unit_id, $_FILES["unit_images"]);
        }

        echo "<script>alert('Unit added successfully!'); window.location.href='add_units.php?id=$apartment_id';</script>";
    } else {
        echo "<script>alert('Error: Unable to add unit.');</script>";
    }
}

// Fetch Units for the Apartment
$units_query = "SELECT * FROM apartment_units WHERE apartment_id = '$apartment_id' ORDER BY unit_number ASC";
$units_result = mysqli_query($conn, $units_query);
?>

<!-- your PHP logic remains the same (unchanged for brevity) -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Units - GeoApart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body {
      background: #f4f4f4;
      padding: 20px;
    }

    .container {
      background: white;
      max-width: 1100px;
      margin: auto;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .back {
      margin-bottom: 15px;
    }

    .back a {
      text-decoration: none;
      color: #007bff;
      font-size: 16px;
    }

    h2 {
      color: #007bff;
      text-align: center;
      margin-bottom: 30px;
    }

    .form-container {
      background: #e9ecef;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
    }

    .form-container input,
    .form-container select,
    .form-container textarea {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .submit-btn {
      background-color: #007bff;
      color: white;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 6px;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
    }

    .submit-btn:hover {
      background-color: #0056b3;
    }

    .unit-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .unit-card {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .unit-info h3 {
      margin-bottom: 10px;
      color: #007bff;
    }

    .unit-info p {
      font-size: 14px;
      margin: 4px 0;
    }

    .unit-gallery {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .unit-gallery img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .unit-actions {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .unit-actions a,
    .unit-actions button {
      text-decoration: none;
      border: none;
      padding: 10px 14px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-edit {
      background-color: #ffc107;
      color: #000;
    }

    .btn-delete {
      background-color: #dc3545;
      color: #fff;
    }

    @media (max-width: 768px) {
      .unit-gallery img {
        width: 100%;
        max-width: 100px;
      }
    }
  </style>
</head>
<body>

    <div class="back">
      <a href="landlord_manage_apartments.php"><i class="fas fa-arrow-left"></i> Back to Apartments</a>
    </div>

    <h2>Manage Units for Apartment</h2>

    <div class="form-container">
      <h3>Add New Unit</h3>
      <form action="add_units.php?id=<?= $apartment_id; ?>" method="POST" enctype="multipart/form-data">
        <input type="text" name="unit_number" placeholder="Unit Number" required>
        <select name="unit_status" required>
          <option value="Available">Available</option>
          <option value="Occupied">Occupied</option>
        </select>
        <input type="number" name="unit_price" placeholder="Monthly Price" required>
        <br>
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

<input type="file" name="unit_images[]" accept="image/*" multiple required>
        <button type="submit" name="add_unit" class="submit-btn">Add Unit</button>
      </form>
    </div>

    <div class="unit-list">
      <?php while ($unit = $units_result->fetch_assoc()): ?>
        <div class="unit-card">
          <div class="unit-info">
            <h3>Unit <?= htmlspecialchars($unit['unit_number']); ?></h3>
            <p><strong>Status:</strong> <?= htmlspecialchars($unit['unit_status']); ?></p>
            <p><strong>Price:</strong> ₱<?= number_format($unit['unit_price'], 2); ?> / month</p>
            <p><strong>Features:</strong> <?= htmlspecialchars($unit['unit_features']); ?></p>
            <div class="unit-gallery">
              <?php
              $imageQuery = "SELECT image_path FROM unit_images WHERE unit_id = ?";
              $stmt = $conn->prepare($imageQuery);
              $stmt->bind_param("i", $unit['id']);
              $stmt->execute();
              $imageResult = $stmt->get_result();
              while ($image = $imageResult->fetch_assoc()) {
                  echo '<img src="../' . $image['image_path'] . '" alt="Unit Image">';
              }
              ?>
            </div>
          </div>
          <div class="unit-actions">
            <a href="edit_unit.php?unit_id=<?= $unit['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this unit?');">
              <input type="hidden" name="unit_id" value="<?= $unit['id']; ?>">
              <button type="submit" name="delete_unit" class="btn-delete"><i class="fas fa-trash"></i> Delete</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
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
