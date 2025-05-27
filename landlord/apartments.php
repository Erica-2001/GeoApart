<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Function to upload image
function uploadImage($file) {
    $targetDir = "../uploads/";
    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return "uploads/" . $fileName;
        }
    }
    return "uploads/default_apartment.jpg";
}

// Add Apartment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_apartment'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $features = mysqli_real_escape_string($conn, $_POST['features']);
    $landlord_id = mysqli_real_escape_string($conn, $_POST['landlord_id']);
    
    $image = uploadImage($_FILES["image"]);

    $query = "INSERT INTO apartments (name, location, price, features, landlord_id, image) 
              VALUES ('$name', '$location', '$price', '$features', '$landlord_id', '$image')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Apartment added successfully!'); window.location.href='admin_manage_apartments.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to add apartment.');</script>";
    }
}

// Delete Apartment
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM apartments WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Apartment deleted successfully!'); window.location.href='admin_manage_apartments.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to delete apartment.');</script>";
    }
    $stmt->close();
}

// Get Apartments List
$result = mysqli_query($conn, "SELECT * FROM apartments");

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
            text-align: center;
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: 0.3s;
            text-decoration: none;
            color: white;
            display: inline-block;
        }
        .btn-add {
            background: #28a745;
        }
        .btn-add:hover {
            background: #218838;
        }
        .btn-edit {
            background: #ffc107;
            color: black;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete {
            background: #dc3545;
        }
        .btn-delete:hover {
            background: #b02a37;
        }
        .form-container {
            margin-top: 20px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 10px;
        }
        input, textarea, select {
            width: 90%;
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
        .apartment-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Manage Apartments</h2>

        <!-- ADD APARTMENT FORM -->
        <div class="form-container">
            <h3>Add New Apartment</h3>
            <form action="admin_manage_apartments.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Apartment Name" required>
                <input type="text" name="location" placeholder="Location" required>
                <input type="number" name="price" placeholder="Price" required>
                <textarea name="features" placeholder="Features" required></textarea>
                <input type="number" name="landlord_id" placeholder="Landlord ID" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" name="add_apartment" class="submit-btn">Add Apartment</button>
            </form>
        </div>

        <!-- APARTMENT LIST -->
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Location</th>
                <th>Price</th>
                <th>Features</th>
                <th>Actions</th>
            </tr>
            <?php while ($apartment = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $apartment['id']; ?></td>
                    <td><img src="../<?php echo $apartment['image']; ?>" class="apartment-img" alt="Apartment"></td>
                    <td><?php echo $apartment['name']; ?></td>
                    <td><?php echo $apartment['location']; ?></td>
                    <td>₱<?php echo number_format($apartment['price'], 2); ?></td>
                    <td><?php echo $apartment['features']; ?></td>
                    <td>
                        <a href="admin_edit_apartment.php?id=<?php echo $apartment['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="admin_manage_apartments.php?delete=<?php echo $apartment['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
