<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include("db_connect.php");

$user_id = $_SESSION["user_id"];

// Auto-update status if proof of payment exists
mysqli_query($conn, "UPDATE payments SET payment_status = 'Reviewing' WHERE receiver_id = $user_id AND payment_proof IS NOT NULL AND payment_status = 'Pending'");

$query = "SELECT p.*, 
                 s.name AS sender_name, 
                 s.user_type AS sender_type, 
                 a.name AS apartment_name,
                 l.name AS landlord_name
          FROM payments p
          JOIN users s ON p.sender_id = s.id
          JOIN apartments a ON p.apartment_id = a.id
          LEFT JOIN tenant_rentals tr ON tr.tenant_id = p.receiver_id AND tr.apartment_id = p.apartment_id
          LEFT JOIN users l ON tr.landlord_id = l.id
          WHERE p.receiver_id = ?
          ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bills = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Bills | GeoApart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body {
      background: #f4f4f4;
      padding: 20px;
      font-family: 'Poppins', sans-serif;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
      font-size: 24px;
    }

    /* Table layout for desktop */
    .table-wrapper {
      display: block;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
      min-width: 700px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 12px 10px;
      text-align: center;
    }

    th {
      background: #007bff;
      color: white;
    }

    .status-pending {
      color: #e67e22;
      font-weight: bold;
    }

    .status-reviewing {
      color: #17a2b8;
      font-weight: bold;
    }

    .status-paid {
      color: #28a745;
      font-weight: bold;
    }

    .btn-pay {
      background: #28a745;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      color: white;
      font-size: 14px;
      transition: background 0.3s ease;
    }

    .btn-pay:hover {
      background: #218838;
    }

    /* Card layout for mobile */
    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      .table-wrapper {
        padding: 0;
      }

      table {
        border: none;
      }

      tbody tr {
        width: 45%;
        margin-bottom: 15px;
        background: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        padding: 10px;
      }

      tbody td {
        border: none;
        text-align: left;
        padding: 8px 10px;
        position: relative;
        font-size: 14px;
      }

      tbody td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #007bff;
        display: block;
        margin-bottom: 4px;
      }

      .btn-pay {
        width: 40%;
        display: inline-block;
        text-align: center;
        margin-top: 10px;
      }
    }
  </style>
</head>
<body>

<div class="back" style="text-align: left; margin-bottom: 10px;">
    <a href="tenant_dashboard.php" style="text-decoration: none; font-size: 18px; color: #007bff;">
        <i class="fa-solid fa-arrow-left"></i> 
    </a>
</div>
  <h2>Your Bills</h2>

  <?php if ($bills->num_rows == 0): ?>
    <p style="text-align: center;">No bills found.</p>
  <?php else: ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Sender</th>
            <th>Apartment</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Issued</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($bill = $bills->fetch_assoc()): ?>
          <tr>
            <td data-label="PID">#<?= htmlspecialchars($bill['id']) ?></td>
            <td data-label="Sender">
              <?= $bill['sender_type'] === 'Admin' ? 'Administrator' : 'Landlord: ' . htmlspecialchars($bill['sender_name']) ?>
            </td>
            <td data-label="Apartment"><?= htmlspecialchars($bill['apartment_name']) ?></td>
            <td data-label="Amount"><strong>₱<?= number_format($bill['total_amount'], 2) ?></strong></td>
            <td data-label="Status">
              <?php if ($bill['payment_status'] == 'Pending'): ?>
                <span class="status-pending">🕐 Pending</span>
              <?php elseif ($bill['payment_status'] == 'Reviewing'): ?>
                <span class="status-reviewing">⏳ Unpaid</span>
              <?php else: ?>
                <span class="status-paid">✔ Paid</span>
              <?php endif; ?>
            </td>
            <td data-label="Issued"><?= date("F d, Y", strtotime($bill['payment_date'])) ?></td>
            <td data-label="Actions">
              <a href="pay_bill.php?bill_id=<?= $bill['id'] ?>" class="btn-pay">
                <?php if ($bill['payment_status'] == 'Pending'): ?>
                  Send Proof of Payment
                <?php elseif ($bill['payment_status'] == 'Reviewing'): ?>
                  View Status
                <?php else: ?>
                  View Receipt
                <?php endif; ?>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
