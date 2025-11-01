<?php
include 'includes/config.php';
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'] ?? '';
$user_email = $data['user_email'] ?? '';
$message = $data['message'] ?? '';

if (!$title || !$user_email || !$message) {
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO chat_messages (meeting_title, user_email, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $user_email, $message);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success','message'=>'Message sent']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to send message']);
}

$stmt->close();
$conn->close();
?>
