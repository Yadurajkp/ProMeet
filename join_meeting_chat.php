<?php
include 'includes/config.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'] ?? '';
$user_email = $data['user_email'] ?? '';

if (!$title || !$user_email) {
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
}

// Check if user is already in chat
$stmt = $conn->prepare("SELECT id FROM chat_participants WHERE meeting_title=? AND user_email=?");
$stmt->bind_param("ss", $title, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status'=>'success','message'=>'User already in chat']);
    exit;
}

// Add user to chat participants
$stmt = $conn->prepare("INSERT INTO chat_participants (meeting_title, user_email) VALUES (?, ?)");
$stmt->bind_param("ss", $title, $user_email);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success','message'=>'User added to chat']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to add user']);
}

$stmt->close();
$conn->close();
?>
