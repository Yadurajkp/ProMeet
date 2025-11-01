<?php
include 'includes/config.php';
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Step 1: Check database connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// Step 2: Run your query
$query = "SELECT title, meeting_type, meeting_domain, meeting_time, meeting_link FROM meetup_meetings";
$result = mysqli_query($conn, $query);

// Step 3: Check if query failed
if (!$result) {
    echo json_encode(["error" => "Query failed: " . mysqli_error($conn)]);
    exit;
}

// Step 4: Fetch data
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Step 5: Return data or message if empty
if (empty($data)) {
    echo json_encode(["message" => "No meetings found"]);
} else {
    echo json_encode(["count" => count($data), "data" => $data]);
}
?>
