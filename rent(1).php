<?php
session_start();
include 'db_connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first.'); window.location.href='login.php';</script>";
    exit();
}

$tenant_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid unit.'); window.location.href='index.php';</script>";
    exit();
}

$unit_id = intval($_GET['id']);

// Fetch unit details
$unit_query = "
    SELECT apartment_units.unit_price, apartment_units.apartment_id, apartments.landlord_id 
    FROM apartment_units 
    JOIN apartments ON apartment_units.apartment_id = apartments.id 
    WHERE apartment_units.id = ?
";

$stmt = $conn->prepare($unit_query);
if (!$stmt) {
    die("Prepare failed (unit fetch): " . $conn->error);
}
$stmt->bind_param("i", $unit_id);
$stmt->execute();
$unit_result = $stmt->get_result();
$unit = $unit_result->fetch_assoc();

if (!$unit) {
    echo "<script>alert('Unit not found.'); window.location.href='index.php';</script>";
    exit();
}

// Insert into payments table
$insert_payment = "
    INSERT INTO payments (apartment_id, unit_id, receiver_id, sender_id, total_amount, payment_status, payment_date)
    VALUES (?, ?, ?, ?, ?, 'Pending', NOW())
";

$stmt = $conn->prepare($insert_payment);
if (!$stmt) {
    die("Prepare failed (insert payment): " . $conn->error);
}
$stmt->bind_param("iiiid", 
    $unit['apartment_id'], 
    $unit_id, 
    $unit['landlord_id'], 
    $tenant_id, 
    $unit['unit_price']
);
$stmt->execute();

// Insert into tenant_rentals table
$insert_rental = "
    INSERT INTO tenant_rentals (tenant_id, landlord_id, apartment_id, unit_id)
    VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_rental);
if (!$stmt) {
    die("Prepare failed (insert rental): " . $conn->error);
}
$stmt->bind_param("iiii", $tenant_id, $unit['landlord_id'], $unit['apartment_id'], $unit_id);
$stmt->execute();

// Update unit status to Pending (instead of Occupied)
$update_unit = "UPDATE apartment_units SET unit_status = 'Pending' WHERE id = ?";
$stmt = $conn->prepare($update_unit);
if (!$stmt) {
    die("Prepare failed (update unit): " . $conn->error);
}
$stmt->bind_param("i", $unit_id);
$stmt->execute();

// Redirect to confirmation or tenant dashboard
echo "<script>
    alert('Rental request sent! Status: Waiting for approval.');
    window.location.href='available_units.php?apartment_id={$unit['apartment_id']}';
</script>";
exit();
