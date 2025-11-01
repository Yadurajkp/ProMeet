<?php
include 'includes/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$user_name = isset($input['name']) ? $input['name'] : null;
$title = isset($input['title']) ? $input['title'] : null;

if (!$user_name || !$title) {
    echo json_encode(['success' => false, 'message' => 'Name and title required']);
    exit;
}

// Get meeting by title
$meeting_query = $conn->prepare("SELECT * FROM meetup_meetings WHERE title = ? LIMIT 1");
$meeting_query->bind_param("s", $title);
$meeting_query->execute();
$meeting_result = $meeting_query->get_result();
$meeting = $meeting_result->fetch_assoc();

if ($meeting) {
    $approval_type = $meeting['meeting_approval'];
    $host = $meeting['hostname'];

    if ($approval_type == 'manual') {
        // Store request for approval
        $stmt = $conn->prepare("INSERT INTO meeting_requests (username, title, host, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("sss", $user_name, $title, $host);
        $stmt->execute();

        // Optional: send notification
        $notification_text = "$user_name has requested to join your meeting: $title";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (receiver, message, created_at, type) VALUES (?, ?, NOW(), 'meeting_request')");
        $notif_stmt->bind_param("ss", $host, $notification_text);
        $notif_stmt->execute();

        echo json_encode(['success' => true, 'message' => "Request sent for approval"]);
    } else {
        // Automatic approval
        $stmt = $conn->prepare("INSERT INTO meeting_participants (name, title, joined_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $user_name, $title);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => "Joined automatically"]);
    }

} else {
    echo json_encode(['success' => false, 'message' => "Meeting not found"]);
}
?>
