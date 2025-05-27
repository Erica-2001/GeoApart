<?php
include 'db_connect.php';

if (isset($_POST['apartment_id'])) {
    $id = intval($_POST['apartment_id']);
    $conn->query("UPDATE apartments SET viewed = 1 WHERE id = $id");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
