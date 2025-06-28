<?php


// delete_receipt.php

// Connect to Database
require_once 'connection_work.php';


if (!$conn) {
	throw new Exception("Connection to Database not exist.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid ID.");
}

// Delete items first (FK constraint)
$conn->query("DELETE FROM TBL_Item_2 WHERE receipt_id = $id");

// Delete the receipt
if ($conn->query("DELETE FROM TBL_Receipt_2 WHERE ID = $id")) {
    header("Location: View_Receipt");
    exit;
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
