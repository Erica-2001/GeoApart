<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tenant_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$tenant_id = intval($_POST['tenant_id']);

// Fetch the active rental for the tenant
$query = "
    SELECT tr.apartment_id, tr.unit_id, a.name AS apartment_name, au.unit_number, au.unit_price
    FROM tenant_rentals tr
    JOIN apartments a ON tr.apartment_id = a.id
    JOIN apartment_units au ON tr.unit_id = au.id
    WHERE tr.tenant_id = ? AND tr.status = 'Active'
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No active rental found']);
    exit();
}

$data = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'apartment_id' => $data['apartment_id'],
    'unit_id' => $data['unit_id'],
    'apartment_name' => $data['apartment_name'],
    'unit_number' => $data['unit_number'],
    'unit_price' => $data['unit_price']
]);
?>
