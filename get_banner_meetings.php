<?php
// get_banner_meetings.php
include 'includes/config.php';
header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Option 1: Return all meetings (past and future) – remove NOW() filter
$sql = "SELECT id, hostname, title, description, meeting_type, meeting_link, meeting_time, meeting_domain, participants_limit, location, banner 
        FROM meetup_meetings 
        ORDER BY meeting_time ASC 
        LIMIT ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $limit);
$stmt->execute();
$res = $stmt->get_result();

$meetings = [];
while ($row = $res->fetch_assoc()) {
    $meetings[] = $row;
}

// Optional fallback: if no meetings exist, return sample meeting
if (count($meetings) === 0) {
    $meetings[] = [
        'id' => 0,
        'hostname' => 'Admin',
        'title' => 'Sample Meeting',
        'description' => 'This is a sample meeting',
        'meeting_type' => 'online',
        'meeting_link' => '#',
        'meeting_time' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'meeting_domain' => 'General',
        'participants_limit' => 10,
        'location' => 'Online',
        'banner' => 'uploads/meetings/sample_banner.jpg'
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($meetings),
    'data' => $meetings
]);
