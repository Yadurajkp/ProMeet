<?php
include 'includes/config.php';
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . '_' . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $query = "UPDATE meetup_signup SET profile_image=? WHERE email=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $image_name, $email);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Profile image updated"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database update failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No image received"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
