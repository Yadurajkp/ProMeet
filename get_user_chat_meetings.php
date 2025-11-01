<?php
include 'includes/config.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents('php://input'), true);
$user = $data['username'] ?? '';

if (empty($user)) {
    echo json_encode(["status" => "error", "message" => "Missing username"]);
    exit;
}

// ✅ Updated to use your actual table names:
// meetup_meetings → stores meeting info
// meeting_requests → stores enrollment or approval info
$query = "
    SELECT DISTINCT m.id, m.title, m.meeting_time, m.hostname
    FROM meetup_meetings m
    LEFT JOIN meeting_requests r ON m.title = r.title
    WHERE m.hostname = ? 
       OR (r.username = ? AND r.status = 'approved')
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $user, $user);
$stmt->execute();
$result = $stmt->get_result();

$meetings = [];
while ($row = $result->fetch_assoc()) {
    $meetings[] = $row;
}

if (count($meetings) > 0) {
    echo json_encode(["status" => "success", "data" => $meetings]);
} else {
    echo json_encode(["status" => "empty", "data" => []]);
}
?>
