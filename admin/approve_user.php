<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../db_connect.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle Approval
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['approve_user'])) {
    $user_id = intval($_POST['user_id']);

    $get_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($get_user);

    if ($user) {
        $update = mysqli_query($conn, "UPDATE users SET status='Approved' WHERE id='$user_id'");

        if ($update) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'helpcenter.geoapart@gmail.com'; 
                $mail->Password = 'geoapk24-007'; 
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('helpcenter.geoapart@gmail.com', 'GeoApart');
                $mail->addAddress($user['email'], $user['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Your GeoApart Account is Approved!';
                $mail->Body = "
                    <h2>Hi " . htmlspecialchars($user['name']) . ",</h2>
                    <p>Congratulations! Your <strong>GeoApart</strong> account has been <b>approved</b>.</p>
                    <p>You can now log in and enjoy our services.</p>
                    <br><p>Thank you!</p>
                    <p><strong>GeoApart Team</strong></p>
                ";

                $mail->send();
                echo "<script>alert('User approved and email sent successfully!'); window.location.href='admin_manage_users.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('User approved but failed to send email: {$mail->ErrorInfo}'); window.location.href='admin_manage_users.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Failed to update user status.'); window.location.href='admin_manage_users.php';</script>";
            exit();
        }
    }
}

// Fetch Pending Users
$query = "SELECT * FROM users WHERE status = 'Pending' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users | Admin | GeoApart</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; font-family: 'Poppins', sans-serif; padding: 20px; }
        .container-manage { max-width: 1100px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1); }
        .header-title { text-align: center; font-weight: 700; color: #007bff; margin-bottom: 30px; }
        .user-card { background: #ffffff; padding: 20px; border-radius: 15px; box-shadow: 0px 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; transition: 0.3s; }
        .user-card:hover { transform: translateY(-3px); box-shadow: 0px 6px 15px rgba(0,0,0,0.15); }
        .user-info p { margin-bottom: 6px; font-size: 15px; color: #555; }
        .proof-image { margin-top: 10px; text-align: center; }
        .proof-image img { max-width: 300px; max-height: 300px; border-radius: 10px; object-fit: cover; cursor: pointer; transition: 0.3s; }
        .proof-image img:hover { opacity: 0.8; }
        .btn-approve { margin-top: 15px; padding: 8px 20px; font-size: 14px; border-radius: 30px; }
        .modal { display: none; position: fixed; z-index: 9999; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); }
        .modal-content { margin: auto; display: block; width: 90%; max-width: 600px; border-radius: 10px; }
        .close { position: absolute; top: 70px; right: 40px; color: #fff; font-size: 40px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="container-manage">

    <div class="mb-3">
        <a href="admin_dashboard.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h2 class="header-title">🛡️ Approve Users</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($user = mysqli_fetch_assoc($result)): ?>
            <div class="user-card">
                <div class="user-info">
                    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
                    <p><strong>User Type:</strong> <?= htmlspecialchars($user['user_type']) ?></p>
                    <p><strong>Registered At:</strong> <?= date('M d, Y H:i A', strtotime($user['created_at'])) ?></p>
                </div>

                <?php if (!empty($user['proof_image'])): ?>
                    <div class="proof-image">
                        <p><strong>Uploaded Proof:</strong></p>
                        <img src="../<?= htmlspecialchars($user['proof_image']) ?>" alt="Proof Image" onclick="openModal('../<?= htmlspecialchars($user['proof_image']) ?>')">
                    </div>
                <?php else: ?>
                    <p class="text-danger">No proof uploaded.</p>
                <?php endif; ?>

                <div class="text-center">
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit" name="approve_user" class="btn btn-success btn-approve">
                            <i class="fas fa-check-circle"></i> Approve
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center text-muted">No pending users for approval.</p>
    <?php endif; ?>

</div>

<!-- Modal -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

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

</body>
</html>
