<?php
header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Auto-create poin column in users table if not exists
$checkPoin = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'poin'");
if (mysqli_num_rows($checkPoin) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN poin INT DEFAULT 0");
}

// Cek login
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
    ]);
    exit;
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
