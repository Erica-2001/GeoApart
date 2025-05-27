<?php
session_start();
include 'db_connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Tenant') {
    echo "<script>alert('Please login as tenant first.'); window.location.href='login.php';</script>";
    exit();
}

$tenant_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid unit.'); window.location.href='index.php';</script>";
    exit();
}

$unit_id = intval($_GET['id']);

// Fetch unit, apartment, and landlord info
$unit_query = "
    SELECT 
        au.unit_price, au.apartment_id, au.unit_number,
        ap.landlord_id,
        l.name AS landlord_name, l.email AS landlord_email,
        t.name AS tenant_name, t.email AS tenant_email
    FROM apartment_units au
    JOIN apartments ap ON au.apartment_id = ap.id
    JOIN users l ON ap.landlord_id = l.id
    JOIN users t ON t.id = ?
    WHERE au.id = ?
";

$stmt = $conn->prepare($unit_query);
$stmt->bind_param("ii", $tenant_id, $unit_id);
$stmt->execute();
$unit_result = $stmt->get_result();
$unit = $unit_result->fetch_assoc();

if (!$unit) {
    echo "<script>alert('Unit not found.'); window.location.href='index.php';</script>";
    exit();
}

$apartment_id = $unit['apartment_id'];
$landlord_id = $unit['landlord_id'];
$unit_price = $unit['unit_price'];
$unit_number = $unit['unit_number'];
$landlord_name = $unit['landlord_name'];
$landlord_email = $unit['landlord_email'];
$tenant_name = $unit['tenant_name'];
$tenant_email = $unit['tenant_email'];

// Insert rental request
$stmt = $conn->prepare("
    INSERT INTO tenant_rentals (tenant_id, landlord_id, apartment_id, unit_id)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiii", $tenant_id, $landlord_id, $apartment_id, $unit_id);
$stmt->execute();

// Set unit to 'Pending'
$stmt = $conn->prepare("UPDATE apartment_units SET unit_status = 'Pending' WHERE id = ?");
$stmt->bind_param("i", $unit_id);
$stmt->execute();

// Optional: Insert initial pending payment
$stmt = $conn->prepare("
    INSERT INTO payments (apartment_id, unit_id, receiver_id, sender_id, total_amount, payment_status, payment_date, sender_type)
    VALUES (?, ?, ?, ?, ?, 'Pending', NOW(), 'Tenant')
");
$stmt->bind_param("iiiid", $apartment_id, $unit_id, $landlord_id, $tenant_id, $unit_price);
$stmt->execute();

// Send in-app notification to landlord
$notif_msg = "📩 A tenant has requested to rent Unit #$unit_number. Please review and approve the request.";
$stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
$stmt->bind_param("is", $landlord_id, $notif_msg);
$stmt->execute();

// --- Send Email Notifications ---
$tenant_subject = "GeoApart: Rental Request Submitted";
$tenant_message = "
Dear $tenant_name,<br><br>
Thank you for your interest in Unit #$unit_number. Your rental request has been submitted and is currently awaiting the landlord's approval.<br><br>
Please be prepared to settle the required one (1) month deposit and one (1) month advance payment.<br>
You may contact your landlord <strong>$landlord_name</strong> at <a href='mailto:$landlord_email'>$landlord_email</a> for further assistance.<br><br>
Regards,<br>GeoApart Team
";

$landlord_subject = "GeoApart: Tenant Rental Request";
$landlord_message = "
Dear $landlord_name,<br><br>
Tenant <strong>$tenant_name</strong> has requested to rent Unit #$unit_number under your apartment.<br><br>
Please log in to your GeoApart dashboard to review and approve this request after payment is settled.<br><br>
Regards,<br>GeoApart Team
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: GeoApart <noreply@geoapart.com>" . "\r\n";

// Send tenant email
mail($tenant_email, $tenant_subject, $tenant_message, $headers);

// Send landlord email
mail($landlord_email, $landlord_subject, $landlord_message, $headers);

// Redirect
echo "<script>
    alert('Rental request sent successfully! Please check your email for confirmation.');
    window.location.href='available_units.php?apartment_id=$apartment_id';
</script>";
exit();
?>
