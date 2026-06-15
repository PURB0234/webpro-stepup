<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$query = "SELECT * FROM rewards ORDER BY id_reward ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query error: " . mysqli_error($conn)
    ]);
    exit();
}

$rewards = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['id_reward'] = (int) $row['id_reward'];
    $row['poin'] = (int) $row['poin'];
    $rewards[] = $row;
}

echo json_encode([
    "success" => true,
    "total" => count($rewards),
    "data" => $rewards
]);
?>
