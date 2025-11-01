<?php
include 'includes/config.php';
header('Content-Type: application/json');
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed: " . $conn->connect_error
    ]);
    exit;
}

// --- Get JSON input ---
$input = json_decode(file_get_contents("php://input"), true);
$titleInput = isset($input['title']) ? trim($input['title']) : '';

if (empty($titleInput)) {
    echo json_encode([
        "status" => "error",
        "message" => "Please provide a meeting title"
    ]);
    exit;
}

// --- Prepare SQL safely (exact match) ---
$stmt = $conn->prepare("SELECT title, hostName, meeting_time, meeting_domain, location FROM meetup_meetings WHERE title = ?");
$stmt->bind_param("s", $titleInput);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $meetings = [];
    while ($row = $result->fetch_assoc()) {
        $meetings[] = [
            "title" => $row['title'],
            "hostName" => $row['hostName'],
            "meeting_time" => $row['meeting_time'],
            "meeting_domain" => $row['meeting_domain'],
            "location" => $row['location']
        ];
    }
    echo json_encode([
        "status" => "success",
        "data" => $meetings
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No meetings found with title: $titleInput"
    ]);
}

$stmt->close();
$conn->close();
?>
