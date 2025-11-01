<?php
include 'includes/config.php';
header("Content-Type: application/json");

// Query to fetch all required fields
$sql = "SELECT title, meeting_time, meeting_link, hostname, meeting_type FROM meetup_meetings";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

if ($result->num_rows > 0) {
    $meetings = [];
    while ($row = $result->fetch_assoc()) {
        $meetings[] = $row;
    }

    echo json_encode([
        "success" => true,
        "MeetingData" => $meetings
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No meetings found"
    ]);
}

$conn->close();
?>
