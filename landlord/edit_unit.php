<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

include '../db_connect.php';

if (!isset($_GET['unit_id']) || empty($_GET['unit_id'])) {
    echo "<script>alert('Invalid Unit!'); window.location.href='landlord_manage_apartments.php';</script>";
    exit();
}

$unit_id = intval($_GET['unit_id']);
$landlord_id = $_SESSION['user_id'];

$query = "SELECT u.*, a.name AS apartment_name, a.id AS apartment_id 
          FROM apartment_units u
          JOIN apartments a ON u.apartment_id = a.id
          WHERE u.id = ? AND a.landlord_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $unit_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
$unit = $result->fetch_assoc();

if (!$unit) {
    echo "<script>alert('Unauthorized Access!'); window.location.href='landlord_manage_apartments.php';</script>";
    exit();
}

$image_query = "SELECT image_path FROM unit_images WHERE unit_id = ?";
$stmt = $conn->prepare($image_query);
$stmt->bind_param("i", $unit_id);
$stmt->execute();
$image_result = $stmt->get_result();
$images = [];
while ($img = $image_result->fetch_assoc()) {
    $images[] = $img['image_path'];
}

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
                $stmt = $conn->prepare("INSERT INTO unit_images (unit_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $unit_id, $imagePath);
                $stmt->execute();
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_unit'])) {
    $unit_number = trim($_POST['unit_number']);
    $unit_status = trim($_POST['unit_status']);
    $unit_price = floatval($_POST['unit_price']);
    $unit_features = trim($_POST['unit_features']);

    $update_query = "UPDATE apartment_units SET unit_number = ?, unit_status = ?, unit_price = ?, unit_features = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssdsi", $unit_number, $unit_status, $unit_price, $unit_features, $unit_id);

    if ($stmt->execute()) {
        if (!empty($_FILES["unit_images"]["name"][0])) {
            uploadUnitImages($unit_id, $_FILES["unit_images"]);
        }

        echo "<script>alert('Unit updated successfully!'); window.location.href='add_units.php?id={$unit['apartment_id']}';</script>";
    } else {
        echo "<script>alert('Error: Unable to update unit.');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_image'])) {
    $image_path = $_POST['image_path'];
    $delete_image_query = "DELETE FROM unit_images WHERE unit_id = ? AND image_path = ?";
    $stmt = $conn->prepare($delete_image_query);
    $stmt->bind_param("is", $unit_id, $image_path);

    if ($stmt->execute()) {
        if (file_exists("../" . $image_path)) {
            unlink("../" . $image_path);
        }
        echo "<script>alert('Image deleted successfully!'); window.location.href='edit_unit.php?unit_id=$unit_id';</script>";
    } else {
        echo "<script>alert('Error: Unable to delete image.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Unit - <?= htmlspecialchars($unit['apartment_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; padding: 20px; }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { color: #007bff; margin-bottom: 20px; text-align: center; }
        input, textarea, select {
            width: 100%; padding: 10px; margin: 10px 0;
            border: 1px solid #ccc; border-radius: 5px;
        }
        .submit-btn {
            background: #007bff; color: white;
            padding: 10px; border: none;
            border-radius: 5px; cursor: pointer;
            width: 100%; transition: 0.3s;
        }
        .submit-btn:hover { background: #0056b3; }
        .unit-gallery { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 20px; }
        .unit-gallery img {
            width: 100px; height: 100px;
            object-fit: cover; border-radius: 8px;
        }
        .delete-img-btn {
            background: #dc3545; color: white;
            border: none; padding: 5px;
            border-radius: 5px; font-size: 12px;
            margin-top: 5px; cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Unit for <?= htmlspecialchars($unit['apartment_name']); ?></h2>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="unit_number" value="<?= htmlspecialchars($unit['unit_number']); ?>" required>
            <select name="unit_status" required>
                <option value="Available" <?= $unit['unit_status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                <option value="Occupied" <?= $unit['unit_status'] == 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
            </select>
            <input type="number" name="unit_price" value="<?= $unit['unit_price']; ?>" required>
            <textarea name="unit_features" required><?= htmlspecialchars(stripslashes($unit['unit_features'])); ?></textarea>
            <input type="file" name="unit_images[]" accept="image/*" multiple>
            <button type="submit" name="update_unit" class="submit-btn">Update Unit</button>
        </form>

        <h3>Current Images</h3>
        <div class="unit-gallery">
            <?php foreach ($images as $img): ?>
                <div>
                    <img src="../<?= htmlspecialchars($img); ?>" alt="Unit Image">
                    <form action="" method="POST">
                        <input type="hidden" name="image_path" value="<?= htmlspecialchars($img); ?>">
                        <button type="submit" name="delete_image" class="delete-img-btn">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
