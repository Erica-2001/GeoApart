<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

include '../db_connect.php';

// Get logged-in landlord ID
$landlord_id = $_SESSION['user_id'];

// Function to upload multiple images securely
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

// Add Apartment (Landlord ID is automatically set)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_apartment'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $location_link = mysqli_real_escape_string($conn, $_POST['location_link'] ?? '');
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $features = mysqli_real_escape_string($conn, $_POST['features']);
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

// Delete Apartment (Only allows landlord to delete their own)
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

// Fetch apartments owned by logged-in landlord
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: auto;
        }
        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container {
            margin-bottom: 20px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 10px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .apartment-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .apartment-card {
            display: flex;
            align-items: center;
            background: #f4f4f4;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }
        .apartment-card:hover {
            background: #e0e0e0;
        }
        .apartment-image img {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            background: white;
            padding: 5px;
            margin-right: 15px;
        }
        .apartment-info {
            flex-grow: 1;
            text-align: left;
        }
        .apartment-info h3 {
            font-size: 18px;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .apartment-actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-left: auto;
        }
        .btn {
            padding: 8px 10px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            color: white;
            border: none;
        }
        .btn-edit {
            background: #ffc107;
            color: black;
        }
        .btn-delete {
            background: #dc3545;
        }
		.btn-add-unit { background: #28a745; 
		}
    </style>
</head>
<body>
   <div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="landlord_dashboard.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>
        <h2>Manage Apartments</h2>
		

        <!-- ADD APARTMENT FORM -->
        <div class="form-container">
            <h3>Add New Apartment</h3>
            <form action="landlord_manage_apartments.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Apartment Name" required>
                <input type="text" name="location" placeholder="Location" required>
                <input type="url" name="location_link" placeholder="Google Maps Location Link (optional)">
                <input type="text" name="price" placeholder="Price" required>
                <select name="apartment_type" required>
                    <option value="">Select Apartment Type</option>
                    <option value="Studio">Studio</option>
                    <option value="Loft">Loft</option>
                    <option value="Duplex">Duplex</option>
                    <option value="Micro">Micro</option>
                </select>
                <textarea name="features" placeholder="Features" required></textarea>
                <input type="file" name="images[]" accept="image/*" multiple required>
                <button type="submit" name="add_apartment" class="submit-btn">Add Apartment</button>
            </form>
        </div>

	
	
 <!-- APARTMENT LIST -->
<div class="apartment-list">
    <?php while ($apartment = mysqli_fetch_assoc($result)): ?>
        <div class="apartment-card">
            <div class="apartment-info">
                <h3><?= htmlspecialchars($apartment['name']); ?></h3>
				<strong>
                <p class="apartment-price">₱<?= number_format($apartment['price'], 2); ?> / month</p>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($apartment['location']); ?></p>
				
                <p><i class="fas fa-building"></i> Type: <?= htmlspecialchars($apartment['apartment_type']); ?></p>
				
                <p><i class="fas fa-user"></i> Landlord: <?= htmlspecialchars($apartment['landlord_name']); ?></p>
				
                <p><i class="fas fa-envelope"></i> Email: <?= htmlspecialchars($apartment['landlord_email'] ?? 'N/A'); ?></p>
				
                <p><i class="fas fa-phone"></i> Contact: <?= htmlspecialchars($apartment['landlord_mobile'] ?? 'N/A'); ?></p>
				
				</strong>
            </div>
            <div class="apartment-actions">
                <a href="landlord_edit_apartments.php?id=<?= urlencode($apartment['id']); ?>" class="btn btn-edit">Edit</a>
                <a href="landlord_manage_apartments.php?delete=<?= urlencode($apartment['id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this apartment?');">Delete</a>
                <a href="add_units.php?id=<?= urlencode($apartment['id']); ?>" class="btn btn-add-unit">Add Unit</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>