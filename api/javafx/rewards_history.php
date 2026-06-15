<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Auto-create redemptions table if not exists
mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_id INT NOT NULL,
    poin_digunakan INT NOT NULL,
    nama_reward VARCHAR(255) NOT NULL,
    tanggal_redeem TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES rewards(id_reward) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter user_id wajib diisi"
    ]);
    exit();
}

// Ambil history redemption user
$query = mysqli_query($conn,
    "SELECT redemptions.*, rewards.gambar AS gambar_reward
     FROM redemptions
     LEFT JOIN rewards ON redemptions.reward_id = rewards.id_reward
     WHERE redemptions.user_id = $user_id
     ORDER BY redemptions.tanggal_redeem DESC"
);

$history = [];
while ($row = mysqli_fetch_assoc($query)) {
    $row['id'] = (int) $row['id'];
    $row['user_id'] = (int) $row['user_id'];
    $row['reward_id'] = (int) $row['reward_id'];
    $row['poin_digunakan'] = (int) $row['poin_digunakan'];
    $history[] = $row;
}

// Ambil poin user saat ini
$userQuery = mysqli_query($conn, "SELECT poin FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($userQuery);
$poin_user = $user ? (int) $user['poin'] : 0;

echo json_encode([
    "success" => true,
    "poin_user" => $poin_user,
    "total_redeem" => count($history),
    "data" => $history
]);
?>
