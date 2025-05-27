<?php
include("../db_connect.php");

// Ensure the request contains an apartment_id
if (isset($_GET['apartment_id']) && !empty($_GET['apartment_id'])) {
    $apartment_id = intval($_GET['apartment_id']); // Sanitize input

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT price FROM apartments WHERE id = ?");
    $stmt->bind_param("i", $apartment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and return price as JSON
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["price" => $row["price"]]);
    } else {
        echo json_encode(["price" => "0"]); // Default to 0 if not found
    }

    $stmt->close();
} else {
    echo json_encode(["price" => "0"]); // Return 0 if no ID is provided
}
?>
