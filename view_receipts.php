<?php

// Check connection

// Connect to Database
require_once 'connection_work.php';


if (!$conn) {
	throw new Exception("Connection to Database not exist.");
}

// Get all receipts
$sql = "SELECT * FROM TBL_Receipt_2 ORDER BY receipt_date DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
  <title>Receipt Viewer</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .btn-sm {
      padding: 4px 8px;
      font-size: 0.8rem;
    }
    .action-buttons {
      display: flex;
      gap: 6px;
      justify-content: Right;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">All Receipts</h2>

  <div class="accordion" id="receiptAccordion">
    <?php
    $i = 0;
    while($row = $result->fetch_assoc()):
        $receipt_id = $row['ID'];
        $items = $conn->query("SELECT * FROM TBL_Item_2 WHERE receipt_id = $receipt_id");
    ?>
    <div class="accordion-item mb-2">
      <h2 class="accordion-header" id="heading<?= $i ?>">
        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>">
          <?= htmlspecialchars($row['store_name']) ?> - <?= $row['receipt_date'] ?>
        </button>
      </h2>
      <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#receiptAccordion">
        <div class="accordion-body">
          <p><strong>Address:</strong> <?= $row['address'] ?></p>
          <p><strong>Payment:</strong> <?= $row['payment_type'] ?></p>
          <p><strong>Total:</strong> RM<?= number_format($row['total_price'], 2) ?> &nbsp; | &nbsp; Tax: RM<?= number_format($row['tax'], 2) ?></p>
          <hr>
          <h6>Items:</h6>
          <table class="table table-sm table-bordered">
            <thead>
              <tr><th>Item</th><th>Qty</th><th>Price (RM)</th></tr>
            </thead>
            <tbody>
              <?php while($item = $items->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
          <div class="action-buttons mt-3">
            <a href="Edit_Receipt?id=<?= $row['ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="Delete_Receipt?id=<?= $row['ID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this receipt?')">Delete</a>
          </div>
        </div>
      </div>
    </div>
    <?php $i++; endwhile; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>