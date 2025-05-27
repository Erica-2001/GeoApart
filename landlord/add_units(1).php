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
    $unit_features = mysqli_real_escape_string($conn, $_POST['unit_features']);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Units - Manage Apartment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; padding: 20px; text-align: center; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); max-width: 800px; margin: auto; }
        h2 { color: #007bff; margin-bottom: 20px; }
        .form-container { margin-bottom: 20px; padding: 20px; background: #e9ecef; border-radius: 10px; }
        input, textarea, select { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        .submit-btn { background: #007bff; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; width: 100%; }
        .submit-btn:hover { background: #0056b3; }
        .unit-list { display: flex; flex-direction: column; gap: 15px; }
        .unit-card { display: flex; justify-content: space-between; align-items: center; background: #f4f4f4; padding: 15px; border-radius: 10px; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); transition: 0.3s; }
        .unit-card:hover { background: #e0e0e0; }
        .unit-info { text-align: left; flex-grow: 1; }
        .unit-actions { display: flex; gap: 10px; }
        .btn { padding: 8px 10px; border-radius: 5px; font-size: 14px; text-align: center; text-decoration: none; display: block; color: white; border: none; }
        .btn-edit { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; }
        .unit-gallery img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Units for Apartment</h2>
        <a href="landlord_manage_apartments.php" class="btn btn-back" style="background: #6c757d; color: white; padding: 10px; border-radius: 5px; text-decoration: none;">← Back to Apartments</a>

        <!-- ADD UNIT FORM -->
        <div class="form-container">
            <h3>Add New Unit</h3>
            <form action="add_units.php?id=<?= $apartment_id; ?>" method="POST" enctype="multipart/form-data">
                <input type="text" name="unit_number" placeholder="Unit Number" required>
                <select name="unit_status" required>
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                </select>
                <input type="number" name="unit_price" placeholder="Price per Month" required>
                <textarea name="unit_features" placeholder="Unit Features" required></textarea>
                <input type="file" name="unit_images[]" accept="image/*" multiple required>
                <button type="submit" name="add_unit" class="submit-btn">Add Unit</button>
            </form>
        </div>

        <!-- UNIT LIST -->
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
                        <a href="edit_unit.php?unit_id=<?= $unit['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this unit?');">
                            <input type="hidden" name="unit_id" value="<?= $unit['id']; ?>">
                            <button type="submit" name="delete_unit" class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
