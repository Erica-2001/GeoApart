<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<script>alert('No apartment selected.'); window.location.href='landlord_manage_apartments.php';</script>";
    exit();
}

$apartment_id = intval($_GET['id']);

// Fetch apartment details
$stmt = $conn->prepare("SELECT * FROM apartments WHERE id = ? AND landlord_id = ?");
$stmt->bind_param("ii", $apartment_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
$apartment = $result->fetch_assoc();

if (!$apartment) {
    echo "<script>alert('Apartment not found or access denied.'); window.location.href='landlord_manage_apartments.php';</script>";
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_apartment'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $price = floatval($_POST['price']);
    $features = $_POST['features']; // Fixed: No escaping needed
    $type = $_POST['apartment_type'];

    $update = $conn->prepare("UPDATE apartments SET name=?, location=?, price=?, features=?, apartment_type=? WHERE id=? AND landlord_id=?");
    $update->bind_param("ssdssii", $name, $location, $price, $features, $type, $apartment_id, $landlord_id);

    if ($update->execute()) {
        echo "<script>alert('Apartment updated successfully!'); window.location.href='landlord_manage_apartments.php';</script>";
    } else {
        echo "<script>alert('Error updating apartment.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Apartment</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f4f4;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    textarea {
      height: 150px;
      resize: vertical;
    }
    button {
      width: 100%;
      background: #007bff;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background: #0056b3;
    }
    .back-link {
      margin-bottom: 15px;
      display: inline-block;
      color: #007bff;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <a href="landlord_manage_apartments.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Manage Apartments</a>

  <div class="container">
    <h2>Edit Apartment</h2>
    <form method="POST">
      <input type="text" name="name" placeholder="Apartment Name" value="<?= htmlspecialchars($apartment['name']) ?>" required>
      <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($apartment['location']) ?>" required>
      <input type="number" name="price" step="0.01" placeholder="Price" value="<?= $apartment['price'] ?>" required>
      <textarea name="features" placeholder="Features" required><?= htmlspecialchars($apartment['features']) ?></textarea>
      <select name="apartment_type" required>
        <option value="Studio" <?= $apartment['apartment_type'] == 'Studio' ? 'selected' : '' ?>>Studio</option>
        <option value="Loft" <?= $apartment['apartment_type'] == 'Loft' ? 'selected' : '' ?>>Loft</option>
        <option value="Duplex" <?= $apartment['apartment_type'] == 'Duplex' ? 'selected' : '' ?>>Duplex</option>
        <option value="Micro" <?= $apartment['apartment_type'] == 'Micro' ? 'selected' : '' ?>>Micro</option>
      </select>
      <button type="submit" name="update_apartment">Update Apartment</button>
    </form>
  </div>
</body>
</html>
