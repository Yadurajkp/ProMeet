<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get name and title
$name = isset($input['name']) ? trim($input['name']) : null;
$title = isset($input['title']) ? trim($input['title']) : null;

if (!$name || !$title) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing name or title",
        "received" => $input
    ]);
    exit;
}

// Find meeting and host info
$stmt = $conn->prepare("SELECT id, hostname, meeting_approval FROM meetup_meetings WHERE title = ? LIMIT 1");
$stmt->bind_param("s", $title);
$stmt->execute();
$result = $stmt->get_result();
$meeting = $result->fetch_assoc();
$stmt->close();

if (!$meeting) {
    echo json_encode([
        "status" => "error",
        "message" => "Meeting not found"
    ]);
    exit;
}

$host = $meeting['hostname'];
$approval_type = $meeting['meeting_approval'] ?? 'manual';

// Prevent duplicate requests (pending/approved)
$check = $conn->prepare("SELECT id, status FROM meeting_requests WHERE username = ? AND title = ? LIMIT 1");
$check->bind_param("ss", $name, $title);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->bind_result($existing_id, $existing_status);
    $check->fetch();
    echo json_encode([
        "status" => "error",
        "message" => "You have already requested to join this meeting (status: $existing_status)."
    ]);
    $check->close();
    exit;
}
$check->close();

if ($approval_type === 'manual') {
    // Insert pending request (store host for easier queries)
    $ins = $conn->prepare("INSERT INTO meeting_requests (username, title, host, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $ins->bind_param("sss", $name, $title, $host);
    if ($ins->execute()) {
        // Optional notification
        $notification_text = "$name has requested to join your meeting: $title";
        $notif = $conn->prepare("INSERT INTO notifications (receiver, message, created_at, type) VALUES (?, ?, NOW(), 'meeting_request')");
        $notif->bind_param("ss", $host, $notification_text);
        $notif->execute();
        $notif->close();

        echo json_encode([
            "status" => "success",
            "message" => "Request sent successfully and awaiting approval"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to send request",
            "error" => $ins->error
        ]);
    }
    $ins->close();
} else {
    // Auto approval: directly add to participants
    $add = $conn->prepare("INSERT INTO meeting_participants (name, title, joined_at, status) VALUES (?, ?, NOW(), 'approved')");
    $add->bind_param("ss", $name, $title);
    if ($add->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Joined automatically"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to join automatically",
            "error" => $add->error
        ]);
    }
    $add->close();
}

$conn->close();
?>
