<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isAdmin = isset($_SESSION['admin_id']);
$approverId = $isAdmin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$approverName = $isAdmin ? 'Admin' : 'Landlord';
$isLandlord = !$isAdmin;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unit_id'], $_POST['action'])) {
    $unit_id = intval($_POST['unit_id']);
    $action = $_POST['action'];
    $note = isset($_POST['note']) ? trim($_POST['note']) : null;

    $status = ($action === 'approve') ? 'Occupied' : 'Available';

    $stmt = $conn->prepare("UPDATE apartment_units SET unit_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $unit_id);
    $stmt->execute();

    $q = $conn->prepare("SELECT tenant_id FROM tenant_rentals WHERE unit_id = ? ORDER BY id DESC LIMIT 1");
    $q->bind_param("i", $unit_id);
    $q->execute();
    $res = $q->get_result();
    if ($res->num_rows > 0) {
        $tenant = $res->fetch_assoc();
        $tenant_id = $tenant['tenant_id'];

        $message = ($action === 'approve') 
            ? "🎉 Your rental request was approved by $approverName. Welcome to your new unit!"
            : "❌ Your rental request was rejected by $approverName. Reason: " . ($note ?: 'No reason provided.');

        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $tenant_id, $message);
        $notif->execute();
    }

    header("Location: admin_unit_approvals.php");
    exit();
}

$query = "
    SELECT u.id, u.unit_number, u.unit_status, u.unit_price, a.name AS apartment_name, 
           l.name AS landlord_name, t.name AS tenant_name, t.email, t.mobile
    FROM apartment_units u
    JOIN apartments a ON u.apartment_id = a.id
    JOIN users l ON a.landlord_id = l.id
    JOIN tenant_rentals tr ON tr.unit_id = u.id
    JOIN users t ON tr.tenant_id = t.id
    WHERE u.unit_status = 'Pending'
    ORDER BY u.id DESC
";

$result = $conn->query($query);
if (!$result) {
    die("Query Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unit Approval</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f9fc;
            padding: 30px;
        }
        .table-container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        .Pending { background: #ffc107; color: #000; }
        .Occupied { background: #28a745; color: white; }
        .Available { background: #6c757d; color: white; }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }
        .approve { background: #28a745; color: white; }
        .reject { background: #dc3545; color: white; }
        .approve:hover { background: #218838; }
        .reject:hover { background: #c82333; }
        textarea {
            resize: none;
            padding: 6px;
            width: 100%;
            max-width: 200px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 13px;
        }
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.7); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 8px; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: black; }

        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { display: none; }
            td {
                position: relative;
                padding-left: 50%;
                text-align: left;
            }
            td::before {
                content: attr(data-label);
                position: absolute;
                top: 10px;
                left: 15px;
                font-weight: bold;
                color: #555;
            }
        }
    </style>
</head>
<body>

<div class="table-container">
    <h2>🏠 Unit Approvals - <?= $isAdmin ? 'Admin' : 'Landlord' ?></h2>

    <?php if ($result->num_rows === 0): ?>
        <p style="text-align:center;">✅ No pending unit approvals.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Unit #</th>
                    <th>Apartment</th>
                    <th>Landlord</th>
                    <th>Tenant</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['unit_number']) ?></td>
                        <td><?= htmlspecialchars($row['apartment_name']) ?></td>
                        <td><?= htmlspecialchars($row['landlord_name']) ?></td>
                        <td><a href="#" onclick='showTenantModal(<?= json_encode($row) ?>)'><?= htmlspecialchars($row['tenant_name']) ?></a></td>
                        <td><span class="badge <?= $row['unit_status'] ?>"><?= $row['unit_status'] ?></span></td>
                        <td>₱<?= number_format($row['unit_price'], 2) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="unit_id" value="<?= $row['id'] ?>">
                                <button name="action" value="approve" class="btn approve">Approve</button> 
                                <button name="action" value="reject" class="btn reject">Reject</button>
                                <textarea name="note" placeholder="Reason (for rejection only)" rows="2"></textarea>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="tenantModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="document.getElementById('tenantModal').style.display='none'">&times;</span>
        <div id="tenantDetails"></div>
    </div>
</div>

<script>
function showTenantModal(tenant) {
    document.getElementById("tenantDetails").innerHTML = `
        <h3>👤 Tenant Information</h3>
        <p><strong>Name:</strong> ${tenant.tenant_name}</p>
        <p><strong>Email:</strong> ${tenant.email}</p>
        <p><strong>Phone:</strong> ${tenant.mobile}</p>
    `;
    document.getElementById("tenantModal").style.display = "block";
}
</script>
</body>
</html>
