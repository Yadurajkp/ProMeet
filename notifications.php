<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Get username from query parameter
$username = isset($_GET['username']) ? $_GET['username'] : '';
if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Username missing"]);
    exit;
}

// Current date and tomorrow's date
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Array to hold all notifications
$notifications = [];

// --- 1. Fetch all notifications for user's meeting requests ---
$sqlRequests = "SELECT id, title, status, created_at 
                FROM meeting_requests
                WHERE username = ?
                ORDER BY created_at DESC";
$stmt = $conn->prepare($sqlRequests);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $statusText = $row['status']; // pending, approved, rejected
    $notifications[] = [
        "id" => $row['id'],
        "title" => ucfirst($statusText) . " Request",
        "message" => "Your request for '{$row['title']}' is {$statusText}.",
        "meeting_time" => $row['created_at'],
        "type" => "request"
    ];
}

// --- 2. Fetch meetings scheduled for tomorrow where user is host ---
$sqlMeetings = "SELECT id, title, meeting_time
                FROM meetup_meetings
                WHERE hostname = ? AND DATE(meeting_time) = ?";
$stmt2 = $conn->prepare($sqlMeetings);
$stmt2->bind_param("ss", $username, $tomorrow);
$stmt2->execute();
$result2 = $stmt2->get_result();

while ($row = $result2->fetch_assoc()) {
    $notifications[] = [
        "id" => $row['id'],
        "title" => "Meeting Reminder",
        "message" => "You have a meeting '{$row['title']}' scheduled for tomorrow at " . date('H:i', strtotime($row['meeting_time'])) . ".",
        "meeting_time" => $row['meeting_time'],
        "type" => "reminder"
    ];
}

// Return notifications as JSON
echo json_encode([
    "status" => "success",
    "notifications" => $notifications
]);

$stmt->close();
$stmt2->close();
$conn->close();
?>
