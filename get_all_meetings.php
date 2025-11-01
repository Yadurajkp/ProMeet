<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Get the logged-in username
$username = $_GET['username'] ?? null;
$statusFilter = $_GET['status'] ?? null; // optional: pending, approved, rejected

if (!$username) {
    echo json_encode(["success" => false, "message" => "Missing username"]);
    exit;
}

// Base query: select requests where the user is either the requester or host
$queryStr = "
    SELECT mr.id, mr.title, mr.username AS requester, mm.hostname, mm.meeting_time, mr.status
    FROM meeting_requests mr
    INNER JOIN meetup_meetings mm ON mr.title = mm.title
    WHERE mr.username = ? OR mm.hostname = ?
";

// Add status filter if provided
if ($statusFilter) {
    $queryStr .= " AND mr.status = ?";
}

// Prepare statement
$query = $conn->prepare($queryStr);

if (!$query) {
    echo json_encode(["success" => false, "message" => "Query preparation failed", "error" => $conn->error]);
    exit;
}

// Bind parameters
if ($statusFilter) {
    $query->bind_param("sss", $username, $username, $statusFilter);
} else {
    $query->bind_param("ss", $username, $username);
}

$query->execute();
$result = $query->get_result();

$meetings = [];
while ($row = $result->fetch_assoc()) {
    $meetings[] = [
        "id" => $row['id'],
        "title" => $row['title'],
        "requester" => $row['requester'],
        "hostname" => $row['hostname'],
        "meeting_time" => $row['meeting_time'],
        "status" => $row['status']
    ];
}

// Debug: log number of meetings returned
error_log("Meetings fetched for $username: " . count($meetings));

echo json_encode([
    "success" => true,
    "meetings" => $meetings
]);

$query->close();
$conn->close();
?>
