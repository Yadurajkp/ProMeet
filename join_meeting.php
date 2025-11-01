<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Read raw JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Sanitize inputs
$name = trim($data['name'] ?? '');
$title = trim($data['title'] ?? '');

if (empty($name) || empty($title)) {
    echo json_encode(["status" => "error", "message" => "Missing name or title"]);
    exit;
}

// Check if already joined
$check = mysqli_query($conn, "SELECT * FROM meeting_participants WHERE name='$name' AND title='$title'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(["status" => "error", "message" => "You already joined this meeting"]);
    exit;
}

// Insert new participant
$insert = mysqli_query($conn, "INSERT INTO meeting_participants (name, title) VALUES ('$name', '$title')");

if ($insert) {
    echo json_encode(["status" => "success", "message" => "Joined successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database insert failed"]);
}

mysqli_close($conn);
?>
