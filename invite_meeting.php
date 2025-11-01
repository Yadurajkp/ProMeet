<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
    exit;
}

$response = array();

try {
    // Fetch required columns including banner and hostname
    $query = "SELECT title, meeting_type, participants_limit, meeting_time, banner, hostname 
              FROM meetup_meetings 
              ORDER BY meeting_time DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }

    $meetings = array();

    // Base URL for images (adjust this as needed)
    $baseUrl = "http://192.168.29.102/ProMeet/";

    while ($row = mysqli_fetch_assoc($result)) {
        $meetings[] = array(
            "title" => $row['title'],
            "meeting_type" => $row['meeting_type'],
            "participants_limit" => $row['participants_limit'],
            "meeting_time" => $row['meeting_time'],
            "hostname" => $row['hostname'],  // ✅ Added hostname
            "banner" => !empty($row['banner']) ? $baseUrl . $row['banner'] : null
        );
    }

    $response["status"] = "success";
    $response["data"] = $meetings;

} catch (Exception $e) {
    $response["status"] = "error";
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
?>
