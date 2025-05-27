<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Get Apartment ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid apartment.'); window.location.href='admin_manage_apartments.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);

// Get apartment name for display
$apartment_name = '';
$name_query = mysqli_query($conn, "SELECT name FROM apartments WHERE id = '$apartment_id'");
if ($name_row = mysqli_fetch_assoc($name_query)) {
    $apartment_name = $name_row['name'];
}

// Add Unit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_unit'])) {
    $unit_number = mysqli_real_escape_string($conn, $_POST['unit_number']);
    $unit_status = mysqli_real_escape_string($conn, $_POST['unit_status']);
    $unit_price = mysqli_real_escape_string($conn, $_POST['unit_price']);
    $unit_features = mysqli_real_escape_string($conn, $_POST['unit_features']);

    $insert = "INSERT INTO apartment_units (apartment_id, unit_number, unit_status, unit_price, unit_features)
               VALUES ('$apartment_id', '$unit_number', '$unit_status', '$unit_price', '$unit_features')";
    if (mysqli_query($conn, $insert)) {
        $unit_id = mysqli_insert_id($conn);

        // Upload images
        if (!empty($_FILES["images"]["name"][0])) {
            $targetDir = "../uploads/units/";
            $allowedTypes = ['jpg','jpeg','png','gif'];
            foreach ($_FILES["images"]["name"] as $key => $filename) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($ext, $allowedTypes)) {
                    $uniqueName = time() . '_' . basename($filename);
                    $filePath = $targetDir . $uniqueName;
                    if (move_uploaded_file($_FILES["images"]["tmp_name"][$key], $filePath)) {
                        $relativePath = "uploads/units/" . $uniqueName;
                        mysqli_query($conn, "INSERT INTO unit_images (unit_id, image_path) VALUES ('$unit_id', '$relativePath')");
                    }
                }
            }
        }

        echo "<script>alert('Unit added successfully!'); window.location.href='add_units.php?id=$apartment_id';</script>";
    } else {
        echo "<script>alert('Failed to add unit.');</script>";
    }
}

// Fetch existing units
$units = mysqli_query($conn, "SELECT * FROM apartment_units WHERE apartment_id = '$apartment_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Units - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; padding: 20px; text-align: center; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); max-width: 800px; margin: auto; }
        h2 { color: #007bff; margin-bottom: 20px; }
        .form-section { margin-bottom: 20px; padding: 20px; background: #e9ecef; border-radius: 10px; }
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
    <h2>Add Unit to Apartment : <?= htmlspecialchars($apartment_name); ?></h2>

    <div class="form-section">
        <h3>Add New Unit</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="unit_number" placeholder="Unit Number" required>
            <select name="unit_status" required>
                <option value="Available">Available</option>
                <option value="Occupied">Occupied</option>
            </select>
            <input type="number" name="unit_price" placeholder="Monthly Price" required>
            <textarea name="unit_features" placeholder="Unit Features" required></textarea>
            <input type="file" name="images[]" accept="image/*" multiple required>
            <button type="submit" name="add_unit" class="submit-btn">Add Unit</button>
        </form>
    </div>

    <div class="unit-list">
        <?php while ($unit = mysqli_fetch_assoc($units)): ?>
            <div class="unit-card">
                <div class="unit-info">
                    <h3>Unit <?= htmlspecialchars($unit['unit_number']); ?></h3>
                    <p><strong>Status:</strong> <?= htmlspecialchars($unit['unit_status']); ?></p>
                    <p><strong>Price:</strong> ₱<?= number_format($unit['unit_price'], 2); ?> / month</p>
                    <p><strong>Features:</strong> <?= nl2br(htmlspecialchars($unit['unit_features'])); ?></p>
                    <div class="unit-gallery">
                        <?php
                        $imgQ = mysqli_query($conn, "SELECT image_path FROM unit_images WHERE unit_id = " . $unit['id']);
                        while ($img = mysqli_fetch_assoc($imgQ)) {
                            echo "<img src='../{$img['image_path']}' alt='Unit Image'>";
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
