<?php
include 'includes/config.php';
header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Prefer request_id, fallback to username+title if necessary
$request_id = isset($input['request_id']) ? intval($input['request_id']) : 0;
$status = isset($input['status']) ? trim($input['status']) : '';
// optional: username/title fallback
$username = isset($input['username']) ? trim($input['username']) : '';
$title = isset($input['title']) ? trim($input['title']) : '';

if (empty($status) || ($request_id <= 0 && (empty($username) || empty($title)))) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters. Provide request_id and status, or username+title and status.']);
    exit;
}

// If request_id provided, fetch the request row
if ($request_id > 0) {
    $get = $conn->prepare("SELECT id, username, title, host FROM meeting_requests WHERE id = ? LIMIT 1");
    $get->bind_param("i", $request_id);
    $get->execute();
    $res = $get->get_result();
    $row = $res->fetch_assoc();
    $get->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit;
    }

    $reqId = $row['id'];
    $reqUser = $row['username'];
    $reqTitle = $row['title'];
} else {
    // fallback using username+title
    $q = $conn->prepare("SELECT id, username, title FROM meeting_requests WHERE username = ? AND title = ? LIMIT 1");
    $q->bind_param("ss", $username, $title);
    $q->execute();
    $res = $q->get_result();
    $row = $res->fetch_assoc();
    $q->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Request not found (username+title)']);
        exit;
    }

    $reqId = $row['id'];
    $reqUser = $row['username'];
    $reqTitle = $row['title'];
}

// Update request status
$update = $conn->prepare("UPDATE meeting_requests SET status = ? WHERE id = ?");
$update->bind_param("si", $status, $reqId);
$success = $update->execute();
$update->close();

if (!$success) {
    echo json_encode(['success' => false, 'message' => 'Failed to update request status']);
    exit;
}

// If approved, add to meeting_participants (avoid duplicates)
if ($status === 'approved') {
    // check not already participant
    $check = $conn->prepare("SELECT id FROM meeting_participants WHERE name = ? AND title = ? LIMIT 1");
    $check->bind_param("ss", $reqUser, $reqTitle);
    $check->execute();
    $check->store_result();
    if ($check->num_rows == 0) {
        $ins = $conn->prepare("INSERT INTO meeting_participants (name, title, joined_at, status) VALUES (?, ?, NOW(), 'approved')");
        $ins->bind_param("ss", $reqUser, $reqTitle);
        $ins->execute();
        $ins->close();
    }
    $check->close();
}

echo json_encode(['success' => true, 'message' => 'Request status updated successfully']);
$conn->close();
?>
