<?php
include 'includes/config.php';
header("Content-Type: application/json");
$title = $_GET['title'] ?? '';

if (!$title) {
    echo json_encode(['status'=>'error','message'=>'Missing title']);
    exit;
}

$stmt = $conn->prepare("SELECT user_email, message, timestamp FROM chat_messages WHERE meeting_title=? ORDER BY timestamp ASC");
$stmt->bind_param("s", $title);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while($row = $result->fetch_assoc()){
    $messages[] = $row;
}

echo json_encode(['status'=>'success','messages'=>$messages]);

$stmt->close();
$conn->close();
?>
