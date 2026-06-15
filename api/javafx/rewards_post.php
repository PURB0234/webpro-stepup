<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan. Gunakan POST."
    ]);
    exit();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$name_reward = isset($_POST['name_reward']) ? mysqli_real_escape_string($conn, $_POST['name_reward']) : '';
$poin = isset($_POST['poin']) ? intval($_POST['poin']) : 0;
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
$stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;

if (empty($name_reward) || $poin <= 0 || empty($description)) {
    echo json_encode([
        "success" => false,
        "message" => "Field wajib: name_reward, poin (> 0), description"
    ]);
    exit();
}

$query = "INSERT INTO rewards (name_reward, description, poin, gambar, stok) 
          VALUES ('$name_reward', '$description', $poin, '', $stok)";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "success" => true,
        "message" => "Reward berhasil ditambahkan",
        "data" => [
            "id_reward" => mysqli_insert_id($conn),
            "name_reward" => $name_reward,
            "description" => $description,
            "poin" => $poin,
            "stok" => $stok
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menambahkan reward: " . mysqli_error($conn)
    ]);
}
?>
