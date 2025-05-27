<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';

$filter = $_GET['type'] ?? 'Landlord'; // Default is landlord

// Handle Approval
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['approve_user'])) {
    $user_id = intval($_POST['user_id']);

    $get_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($get_user);

    if ($user) {
        $update = mysqli_query($conn, "UPDATE users SET status='Approved' WHERE id='$user_id'");

        if ($update) {
            $to = $user['email'];
            $subject = 'Your GeoApart Account is Approved!';
            $message = "
                <html>
                <head>
                    <title>Account Approved</title>
                </head>
                <body>
                    <h2>Hi " . htmlspecialchars($user['name']) . ",</h2>
                    <p>We are pleased to inform you that your <strong>GeoApart</strong> account has been <b>Approved</b>.</p>
                    <p>You can now log in and enjoy our services.</p>
                    <br>
                    <p>Thank you!</p>
                    <p><strong>GeoApart Team</strong></p>
                </body>
                </html>
            ";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: GeoApart <noreply@geoapart.online>" . "\r\n";

            if (mail($to, $subject, $message, $headers)) {
                echo "<script>alert('User approved and email sent successfully!'); window.location.href='admin_manage_users.php';</script>";
            } else {
                echo "<script>alert('User approved but failed to send email.'); window.location.href='admin_manage_users.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Failed to update user status.'); window.location.href='admin_manage_users.php';</script>";
            exit();
        }
    }
}

// Handle Decline
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['decline_user'])) {
    $user_id = intval($_POST['user_id']);
    $reason = trim($_POST['decline_reason'] ?? '');

    $get_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($get_user);

    if ($user) {
        mysqli_query($conn, "UPDATE users SET status='Declined' WHERE id='$user_id'");
        $to = $user['email'];
        $subject = 'GeoApart Registration Declined';
        $message = "<html><body>
            <h2>Hi " . htmlspecialchars($user['name']) . ",</h2>
            <p>Unfortunately, your <strong>GeoApart</strong> registration has been <b>Declined</b>.</p>
            <p><strong>Reason:</strong> " . nl2br(htmlspecialchars($reason)) . "</p>
            <br><p>Thank you for your interest.<br><strong>GeoApart Team</strong></p></body></html>";
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: GeoApart <noreply@geoapart.online>\r\n";
        mail($to, $subject, $message, $headers);
        echo "<script>alert('User declined. Email sent.'); window.location.href='admin_manage_users.php?type=$filter';</script>";
        exit();
    }
}

// Fetch Filtered Users
$query = "SELECT * FROM users WHERE status = 'Pending' AND user_type = '$filter' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Users</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <style>
    body { background: #f4f4f4; font-family: 'Poppins', sans-serif; padding: 20px; }
    .filter-buttons {
  text-align: center;
  margin-bottom: 20px;
}

.filter-buttons a {
  margin: 4px;
  padding: 6px 14px;
  font-size: 13px;
  border: 1px solid #000;
  border-radius: 16px;
  text-decoration: none;
  font-weight: 600;
  color: black;
  background: white;
  display: inline-block;
}

.filter-buttons a.active {
  background: black;
  color: white;
}

    .user-card {
      background: white;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .user-card p { font-size: 14px; margin: 5px 0; }
    .user-card img {
      max-width: 100%; height: auto;
      display: block; margin: 10px auto;
      border-radius: 10px; max-height: 220px;
    }

    .btn-approve {
      background: #28a745;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: bold;
    }
    .btn-decline {
      background: #dc3545;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: bold;
    }
    
  </style>
</head>
<body>

<a href="admin_dashboard.php" class="btn btn-outline-primary rounded-pill mb-3"><i class="fas fa-arrow-left"></i> Back</a>
<h3 class="text-center fw-bold mb-3">🛡️ USER REGISTRATION</h3>

<div class="filter-buttons">
  <a href="?type=Landlord" class="<?= $filter === 'Landlord' ? 'active' : '' ?>">LANDLORD REQUESTS</a>
  <a href="?type=Tenant" class="<?= $filter === 'Tenant' ? 'active' : '' ?>">TENANT REQUESTS</a>
</div>

<?php if (mysqli_num_rows($result) > 0): ?>
  <?php while ($user = mysqli_fetch_assoc($result)): ?>
    <div class="user-card">
      <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
      <p><strong>User Type:</strong> <?= htmlspecialchars($user['user_type']) ?></p>
      <p><strong>Registered At:</strong> <?= date('F d, Y', strtotime($user['created_at'])) ?></p>
      <p><strong>Uploaded Proof:</strong></p>
      <img src="../<?= htmlspecialchars($user['proof_image']) ?>" alt="Proof">

      <div class="text-center mt-3">
        <form method="POST">
          <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
          <button type="submit" name="approve_user" class="btn btn-approve me-2">Approve</button>
          <!-- Trigger Modal -->
<button type="button" class="btn btn-decline" onclick="openDeclineModal(<?= $user['id'] ?>)">Decline</button>


        </form>
      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p class="text-center text-muted">No pending <?= strtolower($filter) ?> requests found.</p>
<?php endif; ?>


</div>

<!-- Modal -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="declineModalLabel">Reason for Decline</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="decline_user_id">
          <textarea name="decline_reason" class="form-control" rows="3" placeholder="Enter reason..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" name="decline_user" class="btn btn-danger">Submit Decline</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
function openDeclineModal(userId) {
  document.getElementById('decline_user_id').value = userId;
  const modal = new bootstrap.Modal(document.getElementById('declineModal'));
  modal.show();
}
</script>


<script>
function openModal(imageSrc) {
    document.getElementById('imageModal').style.display = "block";
    document.getElementById('modalImage').src = imageSrc;
}

function closeModal() {
    document.getElementById('imageModal').style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
