<?php


// info connection to MySQL
$host = ""; // check if needed
$username = "";
$password = "";
$database = "";

// connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

// Enable error reporting so we can see the actual error message.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4"); // Set the charset so that the data is more stable
} catch (Exception $e) {
    http_response_code(500);
    error_log("Database connection error: " . $e->getMessage()); // Log error
    echo json_encode(["error" => "Gagal sambung ke database."]);
    exit;
}


?>