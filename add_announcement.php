<?php
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$type = $data['type'] ?? '';
$event_date = $data['event_date'] ?? '';
$author = $data['author'] ?? 'Admin';

// Validate required fields
if (!$title || !$description || !$type) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO announcements (title, description, type, event_date, author, date_created, views) VALUES (?, ?, ?, ?, ?, NOW(), 0)");
$stmt->bind_param("sssss", $title, $description, $type, $event_date, $author);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error","message"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>