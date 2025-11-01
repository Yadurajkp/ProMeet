<?php
// suggested_meetings.php
include 'includes/config.php'; 
header("Content-Type: application/json; charset=utf-8");

// Read input JSON (if filters are passed)
$data = json_decode(file_get_contents("php://input"), true);
$limit = isset($data['limit']) ? intval($data['limit']) : 0;
$meeting_type = isset($data['meeting_type']) ? trim($data['meeting_type']) : "";

// Query (exclude meeting_link, meeting_domain, meeting_approval, created_at, id)
$query = "SELECT title, description, meeting_type, meeting_time, participants_limit, banner 
          FROM meetup_meetings";

// Filters
$conditions = [];
if (!empty($meeting_type)) {
    $conditions[] = "meeting_type = '" . mysqli_real_escape_string($conn, $meeting_type) . "'";
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
if ($limit > 0) {
    $query .= " LIMIT " . $limit;
}

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    exit;
}

$meetings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $meetings[] = [
        "title" => $row["title"],
        "description" => $row["description"],
        "meeting_type" => $row["meeting_type"],
        "meeting_time" => $row["meeting_time"],
        "participants_limit" => $row["participants_limit"],
        "banner" => $row["banner"],
        "button_text" => "Join Event"
    ];
}

echo json_encode([
    "status" => "success",
    "count" => count($meetings),
    "data" => $meetings
]);

mysqli_close($conn);
?>
