<?php
include 'includes/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'] ?? '';
$title = $data['title'] ?? '';

if ($name == '' || $title == '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$sql = "DELETE FROM meeting_participants WHERE name = ? AND title = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $title);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'You have left the meeting']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'You are not part of this meeting']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
