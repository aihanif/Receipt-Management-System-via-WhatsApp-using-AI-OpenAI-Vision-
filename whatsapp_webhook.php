<?php
// whatsapp_webhook.php

require_once 'config.php'; // Contains keys stored securely

// Connect to Database
require_once 'connection_work.php';

if (!$conn) {
	throw new Exception("Connection to Database not exist.");
}


// === DAILY LIMIT CHECK ===
$from = $_POST['From'] ?? '';
$today = date('Y-m-d');


$conn->query("INSERT INTO usage_limits (user_phone, usage_date, usage_count)
            VALUES ('$from', '$today', 1)
            ON DUPLICATE KEY UPDATE usage_count = usage_count + 1");

$res = $conn->query("SELECT usage_count FROM usage_limits WHERE user_phone = '$from' AND usage_date = '$today'");
$row = $res->fetch_assoc();
if ($row['usage_count'] > 5) {
    header("Content-type: text/xml");
    echo "<Response><Message>Anda telah mencapai had maksimum 5 resit untuk hari ini. Sila cuba lagi esok.</Message></Response>";
    exit;
}



// === TWILIO MEDIA INPUT ===
$mediaUrl = $_POST['MediaUrl0'] ?? null;
$from = $_POST['From'] ?? '';
if (!$mediaUrl) die("<Response><Message>No image found.</Message></Response>");

// === DOWNLOAD IMAGE FROM TWILIO ===
$ch = curl_init($mediaUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => TWILIO_SID . ":" . TWILIO_TOKEN,
	CURLOPT_FOLLOWLOCATION => true
]);

$imageData = curl_exec($ch);
curl_close($ch);
if (!$imageData) {
    header("Content-type: text/xml");
    echo "<Response><Message>Gagal muat turun imej dari WhatsApp. Sila cuba semula.</Message></Response>";
    exit;
}


// === VALIDATE IMAGE FORMAT ===
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($imageData);

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime, $allowed)) {
    header("Content-type: text/xml");
    echo "<Response><Message>Format imej tidak disokong. Sila hantar JPEG, PNG, GIF atau WEBP.</Message></Response>";
    exit;
}

$ext = explode('/', $mime)[1]; // jpeg, png, etc
$filePath = "receipt.$ext";
file_put_contents($filePath, $imageData);

if (!file_exists($filePath) || filesize($filePath) < 1000) {
    header("Content-type: text/xml");
    echo "<Response><Message>Imej rosak atau terlalu kecil. Sila hantar semula.</Message></Response>";
    exit;
}


// === CALL GPT-4o ===
$base64Image = base64_encode(file_get_contents($filePath));
$ext = explode('/', $mime)[1]; // jpeg, png, etc
$payload = [
    "model" => "gpt-4o",
    "messages" => [[
        "role" => "user",
        "content" => [
            ["type" => "text", "text" => "Extract receipt info (store name, date, address, payment type, item name, quantity, price, tax). Return JSON only."],
            ["type" => "image_url", "image_url" => ["url" => "data:image/$ext;base64,$base64Image"]]
        ]
    ]],
    "max_tokens" => 1000
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . OPENAI_KEY,
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

// === PARSE GPT RESPONSE ===
try {
    $contentRaw = $data['choices'][0]['message']['content'] ?? '';
    $contentClean = preg_replace('/^```json|```$/m', '', trim($contentRaw));
    $parsed = json_decode($contentClean, true);

    if (!$parsed) throw new Exception("Gagal decode JSON dari GPT.");

    $store = $conn->real_escape_string($parsed['store_name'] ?? '');
    //$date = $conn->real_escape_string($parsed['date'] ?? date('Y-m-d'));
	
	$today = date("Y-m-d h:i:s");
	$rawDate = $parsed['date'] ?? date('Y-m-d');
	$dateObj = DateTime::createFromFormat('d/m/Y', $rawDate);
	$date = $dateObj ? $dateObj->format('Y-m-d') : date('Y-m-d');
	
    $addr = $conn->real_escape_string($parsed['address'] ?? '');
    $total = floatval($parsed['total'] ?? 0);
    $tax = floatval($parsed['tax'] ?? 0);
    $pay = $conn->real_escape_string($parsed['payment_type'] ?? '');

	
    // Insert into TBL_Receipt_2
    $conn->query("INSERT INTO TBL_Receipt_2 (store_name, receipt_date, address, total_price, tax, payment_type,created_at)
                VALUES ('$store', '$date', '$addr', $total, $tax, '$pay', '$today')");
	
	
    $receipt_id = $conn->insert_id;
	

    foreach ($parsed['items'] ?? [] as $item) {
        $name = $conn->real_escape_string($item['name'] ?? '');
        $qty = intval($item['quantity'] ?? 0);
        $price = floatval($item['price'] ?? 0);
        $conn->query("INSERT INTO TBL_Item_2 (receipt_id, item_name, quantity, price)
                    VALUES ($receipt_id, '$name', $qty, $price)");
    }


	
    $viewUrl = "URL_VIEW_RECEIPT_APP";
    $message = "Terima kasih! Resit anda berjaya direkodkan:\n"
             . "Kedai: $store\nTarikh: $date\nJumlah: RM" . number_format($total,2) . "\n"
             . "Bayaran: $pay\nItem: " . count($parsed['items'] ?? []) . "\n"
             . "Lihat: $viewUrl";
	file_put_contents('log.txt', "\nmessage-".$message. "\n", FILE_APPEND);
	
} catch (Exception $e) {
	file_put_contents('log_error.txt', $e);
    $message = "Maaf, terdapat masalah memproses resit. Sila cuba lagi.";
}

// === SEND BACK REPLY ===
header("Content-type: text/xml");
echo "<Response><Message>$message</Message></Response>";
?>