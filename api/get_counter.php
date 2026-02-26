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

// Execute query
$result = $conn->query("SELECT count_value FROM counter WHERE id=1");

if (!$result) {
    die(json_encode([
        "status" => "error",
        "message" => "Query failed: " . $conn->error
    ]));
}

$row = $result->fetch_assoc();

echo json_encode([
    "status" => "success",
    "count" => $row['count_value']
]);
?>
