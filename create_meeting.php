<?php
include 'includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
    exit;
}

// Collect inputs
$title              = trim($_POST['title'] ?? '');
$description        = trim($_POST['description'] ?? '');
$meeting_type       = trim($_POST['meeting_type'] ?? '');
$meeting_link       = trim($_POST['meeting_link'] ?? '');
$meeting_time       = trim($_POST['meeting_time'] ?? '');
$meeting_domain     = trim($_POST['meeting_domain'] ?? '');
$meeting_approval   = trim($_POST['meeting_approval'] ?? 'manual');
$participants_limit = trim($_POST['participants_limit'] ?? '');
$host_name          = trim($_POST['hostname'] ?? ''); // ✅ Added host_name

// Validate required fields
if (empty($title) || empty($description) || empty($meeting_type) ||
    empty($meeting_time) || empty($meeting_domain) || empty($host_name)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Handle optional banner
$banner_path = null;
if (isset($_FILES['meeting_banner']) && $_FILES['meeting_banner']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "uploads/meetings/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $filename = time() . "_" . basename($_FILES['meeting_banner']['name']);
    $target_file = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['meeting_banner']['tmp_name'], $target_file)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload banner"]);
        exit;
    }
    $banner_path = $target_file;
}

// ✅ Insert into database without location
$sql = "INSERT INTO meetup_meetings 
        (title, description, meeting_type, meeting_link, meeting_time, meeting_domain, 
         participants_limit, meeting_approval, hostname, banner)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param(
    "ssssssssss",
    $title,
    $description,
    $meeting_type,
    $meeting_link,
    $meeting_time,
    $meeting_domain,
    $participants_limit,
    $meeting_approval,
    $host_name,     // ✅ bind host_name
    $banner_path
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Meeting created successfully",
        "meeting_id" => $stmt->insert_id,
        "participants_limit" => $participants_limit
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>