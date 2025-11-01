<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Decode JSON input (if sent)
$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Try to get from JSON or fallback to form data
    $meetingTitle = $input['meeting_title'] ?? ($_POST['meeting_title'] ?? '');

    if (empty($meetingTitle)) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing meeting title"
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_email, message, timestamp 
                            FROM chat_messages 
                            WHERE meeting_title = ? 
                            ORDER BY id ASC");
    $stmt->bind_param("s", $meetingTitle);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "messages" => $messages
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
?>
