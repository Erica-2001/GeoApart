<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

// Add New User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

    $query = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $password, $user_type);

    if ($stmt->execute()) {
        echo "<script>alert('User added successfully!'); window.location.href='admin_manage_users.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to add user.');</script>";
    }
    $stmt->close();
}

// Delete User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href='admin_manage_users.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to delete user.');</script>";
    }
    $stmt->close();
}

// Get Users List
$query = "SELECT users.*, 
                 (SELECT name FROM apartments WHERE apartments.landlord_id = users.id LIMIT 1) AS apartment, 
                 COALESCE(SUM(payments.total_amount), 0) AS total_amount 
          FROM users 
          LEFT JOIN payments ON users.id = payments.receiver_id 
          GROUP BY users.id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Query Failed: " . mysqli_error($conn));
}

// Prepare users data for JS (for live search)
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script> <!-- Replace your-kit-id if needed -->

    <style>
        .container-manage-users {
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .container-manage-users {
                margin-left: 0;
                padding: 10px;
            }
        }
        .user-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }
        .search-bar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">

<div class="container-manage-users">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="admin_dashboard.php" class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <h2 class="text-center text-primary mb-4">Manage Users</h2>

    <!-- ADD USER BUTTON -->
    <div class="d-grid gap-2 mb-4">
        <button class="btn btn-success" onclick="openModal()">
            <i class="fas fa-user-plus"></i> Add User
        </button>
    </div>

    <!-- SEARCH BAR -->
    <input type="text" id="searchInput" onkeyup="searchUsers()" class="form-control search-bar" placeholder="Search users by name, email or type...">

    <!-- Users Cards -->
    <div id="usersContainer">
        <?php foreach ($users as $user): ?>
            <div class="user-card" data-name="<?php echo strtolower($user['name']); ?>" data-email="<?php echo strtolower($user['email']); ?>" data-type="<?php echo strtolower($user['user_type']); ?>">
                <h5><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['name']); ?></h5>
                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="mb-1"><strong>Account Type:</strong> <?php echo htmlspecialchars($user['user_type']); ?></p>
                <p class="mb-1"><strong>Apartment:</strong> <?php echo $user['apartment'] ? htmlspecialchars($user['apartment']) : 'N/A'; ?></p>
                <p><strong>Payments:</strong> 
                    <?php echo $user['total_amount'] ? '₱' . number_format($user['total_amount'], 2) : 'No Payments'; ?>
                </p>
                <div class="d-flex justify-content-between">
                    <a href="admin_edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="admin_manage_users.php?delete=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ADD USER MODAL -->
<div class="modal" id="addUserModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" onclick="closeModal()"></button>
            </div>
            <div class="modal-body">
                <form action="admin_manage_users.php" method="POST">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                    <select name="user_type" class="form-control mb-2" required>
                        <option value="Tenant">Tenant</option>
                        <option value="Landlord">Landlord</option>
                    </select>
                    <button type="submit" name="add_user" class="btn btn-success w-100">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Functions
function openModal() {
    document.getElementById("addUserModal").style.display = "flex";
}
function closeModal() {
    document.getElementById("addUserModal").style.display = "none";
}

// Live Search Function
function searchUsers() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const cards = document.querySelectorAll("#usersContainer .user-card");

    cards.forEach(card => {
        const name = card.getAttribute("data-name");
        const email = card.getAttribute("data-email");
        const type = card.getAttribute("data-type");

        if (name.includes(input) || email.includes(input) || type.includes(input)) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}
</script>

</body>
</html>
