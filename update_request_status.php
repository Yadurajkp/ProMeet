<?php
include 'includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? null;
$title = $input['title'] ?? null;
$status = $input['status'] ?? null;

if (!$username || !$title || !$status) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$query = $conn->prepare("UPDATE meeting_requests SET status=? WHERE username=? AND title=?");
$query->bind_param("sss", $status, $username, $title);

if ($query->execute()) {
    echo json_encode(["success" => true, "message" => "Status updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Database update failed", "error" => $query->error]);
}

$query->close();
$conn->close();
?>
