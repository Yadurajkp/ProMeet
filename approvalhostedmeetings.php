<?php
include 'includes/config.php';
header('Content-Type: application/json');

$hostname = '';

// Accept POST form fields or raw JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hostname'])) {
        $hostname = trim($_POST['hostname']);
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['hostname'])) {
            $hostname = trim($input['hostname']);
        }
    }
}

if (empty($hostname)) {
    echo json_encode([
        'success' => false,
        'message' => 'Hostname is required'
    ]);
    exit;
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch hosted meetings for this hostname
$sql = "SELECT id, title, meeting_time, participants_limit, meeting_approval FROM meetup_meetings WHERE hostname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hostname);
$stmt->execute();
$result = $stmt->get_result();

$meetings = [];
while ($row = $result->fetch_assoc()) {
    $meetings[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'meetings' => $meetings
]);
?>
