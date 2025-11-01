<?php
// get_user_hosted_or_enrolled.php
include 'includes/config.php';
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status'=>'error','message'=>'Invalid input']);
    exit;
}

$name = trim($input['name'] ?? '');
$type = trim($input['type'] ?? 'enrolled'); // 'hosted' or 'enrolled'
$date = trim($input['date'] ?? ''); // optional in YYYY-MM-DD

if ($name === '') {
    echo json_encode(['status'=>'error','message'=>'name required']);
    exit;
}

if ($type === 'hosted') {
    // Hostname matches user's name
    if ($date !== '') {
        // filter by date only (match date part of meeting_time)
        $sql = "SELECT id, title, meeting_time, meeting_domain, participants_limit FROM meetup_meetings WHERE hostname = ? AND DATE(meeting_time) = ? ORDER BY meeting_time ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $name, $date);
    } else {
        $sql = "SELECT id, title, meeting_time, meeting_domain, participants_limit FROM meetup_meetings WHERE hostname = ? ORDER BY meeting_time ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $name);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','count'=>count($rows),'data'=>$rows]);
    exit;
}

// else: enrolled
// We need to look into meeting_participants table where name matches, get titles, then fetch details from meetup_meetings
// If date provided, filter meetings by date too
if ($date !== '') {
    // First get distinct titles user joined on that exist on that date
    $sql = "SELECT mm.id, mm.title, mm.meeting_time, mm.meeting_domain, mm.participants_limit
            FROM meeting_participants mp
            JOIN meetup_meetings mm ON mm.title = mp.title
            WHERE mp.name = ? AND DATE(mm.meeting_time) = ?
            ORDER BY mm.meeting_time ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $name, $date);
} else {
    $sql = "SELECT mm.id, mm.title, mm.meeting_time, mm.meeting_domain, mm.participants_limit
            FROM meeting_participants mp
            JOIN meetup_meetings mm ON mm.title = mp.title
            WHERE mp.name = ?
            ORDER BY mm.meeting_time ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $name);
}

$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status'=>'success','count'=>count($rows),'data'=>$rows]);
