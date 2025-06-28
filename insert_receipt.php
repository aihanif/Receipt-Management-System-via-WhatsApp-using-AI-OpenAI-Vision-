<?php


header("Content-Type: application/json");

// Connect to Database
require_once 'connection_work.php';


$input = file_get_contents("php://input");


if (!$conn) {
	throw new Exception("Connection to Database not exist.");
}



// Retrieve JSON from POST
$data = json_decode(file_get_contents("php://input"), true);

// Check all required fields
$required = ['store_name', 'receipt_date', 'address', 'total_price', 'tax', 'payment_type', 'items'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(["status" => "error", "message" => "Missing field: $field"]);
        exit;
    }
}

// Escape & assign
$store_name   = mysqli_real_escape_string($conn, $data['store_name']);
$receipt_date = mysqli_real_escape_string($conn, $data['receipt_date']);
$address      = mysqli_real_escape_string($conn, $data['address']);
$total_price  = floatval($data['total_price']);
$tax          = floatval($data['tax']);
$payment_type = mysqli_real_escape_string($conn, $data['payment_type']);

// Insert into TBL_Receipt_2
$sql = "INSERT INTO TBL_Receipt_2 (store_name, receipt_date, address, total_price, tax, payment_type, created_at)
        VALUES ('$store_name', '$receipt_date', '$address', $total_price, $tax, '$payment_type', NOW())";

if (mysqli_query($conn, $sql)) {
    $receipt_id = mysqli_insert_id($conn);

    // Insert item details into TBL_Item_2
    foreach ($data['items'] as $item) {
        $item_name = mysqli_real_escape_string($conn, $item['item_name']);
        $quantity  = intval($item['quantity']);
        $price     = floatval($item['price']);

        $sql_item = "INSERT INTO TBL_Item_2 (receipt_id, item_name, quantity, price)
                     VALUES ($receipt_id, '$item_name', $quantity, $price)";
        mysqli_query($conn, $sql_item);
    }

    echo json_encode(["status" => "success", "receipt_id" => $receipt_id]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
}

mysqli_close($conn);
?>