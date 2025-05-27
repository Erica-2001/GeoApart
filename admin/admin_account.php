<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Add New Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin added successfully!'); window.location.href='admin_account.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to add admin.');</script>";
    }
    $stmt->close();
}

// Delete Admin
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != 1) { // Prevent deleting the main admin (ID 1)
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Admin deleted successfully!'); window.location.href='admin_account.php';</script>";
        } else {
            echo "<script>alert('Error: Unable to delete admin.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('You cannot delete the main admin!');</script>";
    }
}

// Update Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_admin"])) {
    $id = intval($_POST["id"]);
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE admin_users SET username=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $username, $email, $hashed, $id);
    } else {
        $query = "UPDATE admin_users SET username=?, email=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $username, $email, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Admin updated successfully!'); window.location.href='admin_account.php';</script>";
    } else {
        echo "<script>alert('Error updating admin.');</script>";
    }

    $stmt->close();
}

// Fetch Admin Users
$result = mysqli_query($conn, "SELECT * FROM admin_users");

if (!$result) {
    die("Database Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Admins</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script>
</head>
<body style="background-color: #f8f9fa;">

<div class="container py-4">
  <div class="mb-3">
    <a href="admin_dashboard.php" class="btn btn-light">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>

  <h2 class="text-center text-primary mb-4">Manage Admins</h2>

  <div class="d-grid gap-2 mb-4">
    <!--<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">
      <i class="fas fa-user-plus"></i> Add Admin 
    </button> -->
  </div>

  <div class="row">
    <?php while ($admin = mysqli_fetch_assoc($result)): ?>
      <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">
              <i class="fas fa-user-circle"></i> <?= htmlspecialchars($admin['username']) ?>
            </h5>
            <p class="card-text mb-1"><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
            <p class="card-text"><small class="text-muted">Created At: <?= htmlspecialchars($admin['created_at']) ?></small></p>
            <div class="d-flex justify-content-between">
              <button class="btn btn-warning btn-sm edit-admin-btn"
                data-bs-toggle="modal"
                data-bs-target="#editAdminModal"
                data-id="<?= $admin['id'] ?>"
                data-username="<?= htmlspecialchars($admin['username']) ?>"
                data-email="<?= htmlspecialchars($admin['email']) ?>">
                <i class="fas fa-edit"></i> Edit
              </button>
              <?php if ($admin['id'] != 1): ?>
              <a href="admin_account.php?delete=<?= $admin['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this admin?');">
                <i class="fas fa-trash"></i> Delete
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- ADD ADMIN MODAL -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add New Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="admin_account.php" method="POST">
          <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
          <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
          <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
          <button type="submit" name="add_admin" class="btn btn-success w-100">Add Admin</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- EDIT ADMIN MODAL -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Edit Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="admin_account.php" method="POST">
          <input type="hidden" name="id" id="edit-admin-id">
          <input type="text" name="username" id="edit-admin-username" class="form-control mb-2" required>
          <input type="email" name="email" id="edit-admin-email" class="form-control mb-2" required>
          <input type="password" name="password" id="edit-admin-password" class="form-control mb-2" placeholder="New Password (leave blank to keep current)">
          <button type="submit" name="edit_admin" class="btn btn-warning w-100">Update Admin</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".edit-admin-btn").forEach(button => {
    button.addEventListener("click", function() {
      document.getElementById("edit-admin-id").value = this.getAttribute("data-id");
      document.getElementById("edit-admin-username").value = this.getAttribute("data-username");
      document.getElementById("edit-admin-email").value = this.getAttribute("data-email");
      document.getElementById("edit-admin-password").value = "";
    });
  });
});
</script>

</body>
</html>
