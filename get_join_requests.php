<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Get the host name from GET parameter
$host_name = isset($_GET['hostname']) ? mysqli_real_escape_string($conn, $_GET['hostname']) : '';

if (empty($host_name)) {
    echo json_encode([
        "success" => false,
        "requests" => [],
        "message" => "Invalid host name"
    ]);
    exit;
}

// Fetch pending approval requests for meetings hosted by this user
$query = "
    SELECT jp.id, jp.name, jp.title, jp.joined_at, jp.status
    FROM meeting_participants jp
    WHERE jp.title IN (
        SELECT title FROM meetup_meetings WHERE host_name = '$host_name'
    ) AND jp.status = 'pending'
";

$result = mysqli_query($conn, $query);

$requests = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = [
            "id" => (int)$row['id'],
            "name" => $row['name'],
            "title" => $row['title'],
            "joined_at" => $row['joined_at'],
            "status" => $row['status']
        ];
    }

    echo json_encode([
        "success" => true,
        "requests" => $requests
    ]);
} else {
    echo json_encode([
        "success" => false,
        "requests" => [],
        "message" => "Database query failed"
    ]);
}

mysqli_close($conn);
?>
