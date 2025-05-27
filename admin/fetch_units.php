<?php
include '../db_connect.php';

// Only accept POST request with apartment_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['apartment_id'])) {
    echo json_encode([]);
    exit();
}

$apartment_id = intval($_POST['apartment_id']);

// Fetch units that are actively rented in the selected apartment
$query = "
    SELECT u.id, u.unit_number, u.unit_price, u.unit_status
    FROM apartment_units u
    JOIN tenant_rentals tr ON u.id = tr.unit_id
    WHERE u.apartment_id = ? AND tr.status = 'Active'
    GROUP BY u.id
    ORDER BY u.unit_number ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$result = $stmt->get_result();

$units = [];

while ($row = $result->fetch_assoc()) {
    $units[] = [
        'id' => $row['id'],
        'unit_number' => $row['unit_number'],
        'unit_price' => $row['unit_price'],
        'unit_status' => $row['unit_status']
    ];
}

echo json_encode($units);
?>
