<?php
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$status = $data['status'] ?? 'Draft';
$responses = intval($data['responses'] ?? 0);
$date_created = $data['date_created'] ?? date("Y-m-d H:i:s");

// Validate required fields
if (!$title || !$description) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO surveys (title, description, status, responses, date_created) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssds", $title, $description, $status, $responses, $date_created);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error","message"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>