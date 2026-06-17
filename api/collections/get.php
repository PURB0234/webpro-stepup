<?php
require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Query to get collections along with assigned challenge count
$query = "SELECT c.*, COALESCE(cc.total_challenges, 0) AS total_challenges
          FROM collections c
          LEFT JOIN (
              SELECT collection_id, COUNT(*) AS total_challenges
              FROM collection_challenges
              GROUP BY collection_id
          ) cc ON c.id = cc.collection_id
          ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "Query error: " . mysqli_error($conn)
        ]);
        exit;
    }
    die("Query error: " . mysqli_error($conn));
}

$collections = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = intval($row['id']);
    $row['total_challenges'] = intval($row['total_challenges']);
    $collections[] = $row;
}

// If accessed directly as API -> return JSON
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => true,
        "message" => "Data collections berhasil diambil",
        "total" => count($collections),
        "data" => $collections
    ]);
    exit;
}
// If required from another file, $collections variable is ready to use
?>
