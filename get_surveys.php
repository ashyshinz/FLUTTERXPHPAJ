<?php
include 'db_connect.php';

$sql = "SELECT * FROM surveys ORDER BY date_created DESC";
$result = $conn->query($sql);

$surveys = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $surveys[] = $row;
    }
}

echo json_encode($surveys);
$conn->close();
?>