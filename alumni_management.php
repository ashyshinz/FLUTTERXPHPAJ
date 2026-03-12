<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. Database Credentials
$host = "localhost";
$user = "root";
$pass = ""; // Default for XAMPP is empty
$dbname = "alumni_db";
$port  = 3306;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Create Connection
$conn = new mysqli($host, $user, $pass, $dbname);

// 3. Check Connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// 4. Fetch Alumni Data
$sql = "SELECT * FROM alumni";
$result = $conn->query($sql);

$alumniArr = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        array_push($alumniArr, $row);
    }
    echo json_encode($alumniArr);
} else {
    echo json_encode([]);
}

$conn->close();
?>