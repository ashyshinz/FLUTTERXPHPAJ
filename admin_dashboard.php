<?php
include 'db_connect.php';
$host = "localhost";
$user = "root"; // replace with your DB username
$pass = "";     // replace with your DB password
$db   = "alumni_db";

$conn = new mysqli($host, $user, $pass, $db, 3306);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// --- 1. Total Alumni ---
$totalAlumniQuery = "SELECT COUNT(*) AS total_alumni FROM alumni";
$totalAlumniRes = $conn->query($totalAlumniQuery);
$totalAlumni = $totalAlumniRes->fetch_assoc()['total_alumni'];

// --- 2. Employment Rate ---
$employmentRateQuery = "SELECT ROUND(SUM(CASE WHEN employment='Employed' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS employment_rate FROM alumni";
$employmentRateRes = $conn->query($employmentRateQuery);
$employmentRate = $employmentRateRes->fetch_assoc()['employment_rate'];

// --- 3. Verified Alumni ---
$verifiedQuery = "SELECT COUNT(*) AS verified_alumni FROM alumni WHERE verification='Verified'";
$verifiedRes = $conn->query($verifiedQuery);
$verifiedAlumni = $verifiedRes->fetch_assoc()['verified_alumni'];

// --- 4. Pending Verification ---
$pendingQuery = "SELECT COUNT(*) AS pending_verification FROM alumni WHERE verification='Pending'";
$pendingRes = $conn->query($pendingQuery);
$pendingAlumni = $pendingRes->fetch_assoc()['pending_verification'];

// --- 5. Recent Submissions (Last 5) ---
$recentSubmissionsQuery = "
SELECT s.id, a.name, a.course, a.batch, s.status, s.submitted_at
FROM submissions s
JOIN alumni a ON s.alumni_id = a.id
ORDER BY s.submitted_at DESC
LIMIT 5
";
$recentSubmissionsRes = $conn->query($recentSubmissionsQuery);

$recentSubmissions = [];
while ($row = $recentSubmissionsRes->fetch_assoc()) {
    $recentSubmissions[] = $row;
}

// --- 6. Batch Distribution ---
$batchDistributionQuery = "
SELECT batch, 
       COUNT(*) AS total_alumni,
       SUM(CASE WHEN employment='Employed' THEN 1 ELSE 0 END) AS employed,
       SUM(CASE WHEN verification='Verified' THEN 1 ELSE 0 END) AS verified
FROM alumni
GROUP BY batch
ORDER BY batch ASC
";
$batchDistributionRes = $conn->query($batchDistributionQuery);
$batchDistribution = [];
while ($row = $batchDistributionRes->fetch_assoc()) {
    $batchDistribution[] = $row;
}

// --- 7. Optional: Recent Admin Actions (Last 5) ---
$recentAdminQuery = "
SELECT *
FROM admin_actions
ORDER BY action_date DESC
LIMIT 5
";
$recentAdminRes = $conn->query($recentAdminQuery);
$recentAdmin = [];
while ($row = $recentAdminRes->fetch_assoc()) {
    $recentAdmin[] = $row;
}

// --- Combine all data ---
$data = [
    "total_alumni" => (int)$totalAlumni,
    "employment_rate" => (float)$employmentRate,
    "verified_alumni" => (int)$verifiedAlumni,
    "pending_verification" => (int)$pendingAlumni,
    "recent_submissions" => $recentSubmissions,
    "batch_distribution" => $batchDistribution,
    "recent_admin_actions" => $recentAdmin
];

// Output JSON
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);

$conn->close();
?>