<?php
include 'db_connect.php';

$sql = "SELECT * FROM announcements ORDER BY date_created DESC";
$result = $conn->query($sql);

$announcements = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

echo json_encode($announcements);
$conn->close();
?>