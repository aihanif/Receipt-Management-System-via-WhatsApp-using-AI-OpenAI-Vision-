<?php

// edit_receipt.php

// Connect to Database
require_once 'connection_work.php';


if (!$conn) {
	throw new Exception("Connection to Database not exist.");
}

// Get receipt by ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = $conn->real_escape_string($_POST['store_name']);
    $receipt_date = $conn->real_escape_string($_POST['receipt_date']);
    $address = $conn->real_escape_string($_POST['address']);
    $total_price = floatval($_POST['total_price']);
    $tax = floatval($_POST['tax']);
    $payment_type = $conn->real_escape_string($_POST['payment_type']);

    $sql = "UPDATE TBL_Receipt_2 SET 
              store_name='$store_name',
              receipt_date='$receipt_date',
              address='$address',
              total_price=$total_price,
              tax=$tax,
              payment_type='$payment_type'
            WHERE ID=$id";

    if ($conn->query($sql)) {
        header("Location: View_Receipt");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$result = $conn->query("SELECT * FROM TBL_Receipt_2 WHERE ID = $id");
$receipt = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Receipt</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3>Edit Receipt</h3>
  <form method="POST">
    <div class="mb-3">
      <label>Store Name</label>
      <input type="text" name="store_name" class="form-control" value="<?= htmlspecialchars($receipt['store_name']) ?>">
    </div>
    <div class="mb-3">
      <label>Receipt Date</label>
      <input type="date" name="receipt_date" class="form-control" value="<?= $receipt['receipt_date'] ?>">
    </div>
    <div class="mb-3">
      <label>Address</label>
      <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($receipt['address']) ?>">
    </div>
    <div class="mb-3">
      <label>Total Price (RM)</label>
      <input type="number" step="0.01" name="total_price" class="form-control" value="<?= $receipt['total_price'] ?>">
    </div>
    <div class="mb-3">
      <label>Tax (RM)</label>
      <input type="number" step="0.01" name="tax" class="form-control" value="<?= $receipt['tax'] ?>">
    </div>
    <div class="mb-3">
      <label>Payment Type</label>
      <input type="text" name="payment_type" class="form-control" value="<?= htmlspecialchars($receipt['payment_type']) ?>">
    </div>
    <button class="btn btn-primary">Update Receipt</button>
    <a href="view_receipts.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
