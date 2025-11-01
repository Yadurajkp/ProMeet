<?php
include 'includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
    exit;
}

$name = trim($_POST['name'] ?? '');
if (empty($name)) {
    echo json_encode(["status" => "error", "message" => "Missing user name"]);
    exit;
}

$response = ["status" => "success", "meetings" => []];

// 🔹 Hosted Meetings
$hosted_sql = "SELECT title, meeting_time, participants_limit 
               FROM meetup_meetings 
               WHERE hostname = ?";
$hosted_stmt = $conn->prepare($hosted_sql);
$hosted_stmt->bind_param("s", $name);
$hosted_stmt->execute();
$hosted_result = $hosted_stmt->get_result();

while ($row = $hosted_result->fetch_assoc()) {
    $row['type'] = 'hosted';
    $response["meetings"][] = $row;
}

// 🔹 Enrolled Meetings
$enrolled_sql = "SELECT title FROM meeting_participants WHERE name = ?";
$enrolled_stmt = $conn->prepare($enrolled_sql);
$enrolled_stmt->bind_param("s", $name);
$enrolled_stmt->execute();
$enrolled_result = $enrolled_stmt->get_result();

while ($enrolled = $enrolled_result->fetch_assoc()) {
    $title = $enrolled['title'];

    $meeting_sql = "SELECT title, meeting_time, participants_limit 
                    FROM meetup_meetings 
                    WHERE title = ?";
    $meeting_stmt = $conn->prepare($meeting_sql);
    $meeting_stmt->bind_param("s", $title);
    $meeting_stmt->execute();
    $meeting_result = $meeting_stmt->get_result();

    if ($row = $meeting_result->fetch_assoc()) {
        $row['type'] = 'enrolled';
        $response["meetings"][] = $row;
    }
}

echo json_encode($response);
?>
