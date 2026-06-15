<?php
require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$query = "SELECT * FROM rewards";
$result = mysqli_query($conn, $query);

if (!$result) {
    // Jika diakses langsung sebagai API, return JSON error
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

$rewards = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rewards[] = $row;
}

// Jika diakses langsung sebagai API → return JSON
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => true,
        "message" => "Data rewards berhasil diambil",
        "total" => count($rewards),
        "data" => $rewards
    ]);
    exit;
}
// Jika di-require dari file lain (misal reward_page.php),
// variabel $rewards sudah tersedia untuk dipakai
?>