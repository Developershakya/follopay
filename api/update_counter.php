<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "earning_app"
);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "DB Connection failed: " . $conn->connect_error
    ]));
}

// Read POST data
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['count'])) {
    die(json_encode([
        "status" => "error",
        "message" => "Count missing"
    ]));
}

$newCount = intval($data['count']);

// Prepare & execute statement
$stmt = $conn->prepare("UPDATE counter SET count_value=? WHERE id=1");
if (!$stmt) {
    die(json_encode([
        "status" => "error",
        "message" => "Prepare failed: " . $conn->error
    ]));
}

$stmt->bind_param("i", $newCount);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "count" => $newCount
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Execute failed: " . $stmt->error
    ]);
}
?>
