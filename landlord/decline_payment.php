<?php
// Decline Payment Email Handler
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION['user_type'] !== 'Landlord') {
    header("Location: login.php");
    exit();
}
include("../db_connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["decline_payment_id"])) {
    $payment_id = intval($_POST["decline_payment_id"]);

    // Fetch tenant and landlord info
    $query = "
        SELECT 
            receiver.email AS tenant_email, 
            sender.email AS landlord_email, 
            sender.name AS landlord_name, 
            receiver.name AS tenant_name
        FROM payments 
        JOIN users receiver ON payments.receiver_id = receiver.id 
        JOIN users sender ON payments.sender_id = sender.id 
        WHERE payments.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $tenant_email = $data["tenant_email"];
        $tenant_name = $data["tenant_name"];
        $landlord_email = $data["landlord_email"];
        $landlord_name = $data["landlord_name"];

        // Update payment status and clear proof
        $update = $conn->prepare("UPDATE payments SET payment_status = 'Pending', payment_proof = NULL WHERE id = ?");
        $update->bind_param("i", $payment_id);
        if ($update->execute()) {
            // Prepare email
            $subject = "Your Proof of Payment Was Declined";
            $message = "Dear $tenant_name,

"
                     . "We regret to inform you that the proof of payment you submitted has been declined by $landlord_name.
"
                     . "Please re-submit a valid proof of payment as soon as possible.

"
                     . "If you have questions, you may contact your landlord at $landlord_email.

"
                     . "Thank you.";
            $headers = "From: $landlord_email";

            if (mail($tenant_email, $subject, $message, $headers)) {
                echo "<script>alert('Payment declined and tenant notified by email.'); window.location.href='landlord_manage_payments.php';</script>";
            } else {
                echo "<script>alert('Payment declined, but email failed to send.'); window.location.href='landlord_manage_payments.php';</script>";
            }
        } else {
            echo "<script>alert('Failed to decline payment.');</script>";
        }
        $update->close();
    }
    $stmt->close();
}
?>