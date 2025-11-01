<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$host = isset($input['host']) ? trim($input['host']) : '';
$title = isset($input['title']) ? trim($input['title']) : '';

// Validate
if (empty($host) || empty($title)) {
    echo json_encode([
        "success" => false,
        "message" => "Required parameters 'host' and 'title' are missing",
        "received" => $input
    ]);
    exit;
}

// Debug log (server-side)
error_log("get_approval_requests.php - Received host: $host, title: $title");

// Query pending requests for this host+title
$stmt = $conn->prepare("SELECT id, username, title, host, status, created_at FROM meeting_requests WHERE host = ? AND title = ? ORDER BY created_at DESC");
$stmt->bind_param("ss", $host, $title);
$stmt->execute();

// Use get_result if supported
$requests = [];
if (method_exists($stmt, 'get_result')) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $requests[] = $row;
    }
} else {
    // fallback bind_result
    $stmt->bind_result($id, $username, $title_db, $host_db, $status, $created_at);
    while ($stmt->fetch()) {
        $requests[] = [
            'id' => $id,
            'username' => $username,
            'title' => $title_db,
            'host' => $host_db,
            'status' => $status,
            'created_at' => $created_at
        ];
    }
}
$stmt->close();

// If no requests found, optionally include all requests for debugging
if (empty($requests)) {
    // not a fatal error — return empty list and a friendly message
    echo json_encode([
        "success" => true,
        "requests" => [],
        "message" => "No requests found for this host/title"
    ]);
    $conn->close();
    exit;
}

// Return found requests
echo json_encode([
    "success" => true,
    "requests" => $requests
]);

$conn->close();
?>
