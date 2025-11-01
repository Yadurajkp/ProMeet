<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Decode JSON input (if sent as raw JSON)
$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support both JSON and regular POST form data
    $meetingTitle = $input['meeting_title'] ?? ($_POST['meeting_title'] ?? '');
    $userEmail    = $input['user_email'] ?? ($_POST['user_email'] ?? '');
    $message      = $input['message'] ?? ($_POST['message'] ?? '');

    // Validate inputs
    if (empty($meetingTitle) || empty($userEmail) || empty($message)) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);
        exit;
    }

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO chat_messages (meeting_title, user_email, message, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $meetingTitle, $userEmail, $message);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Message sent successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database insert failed: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>
