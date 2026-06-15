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

// Pastikan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan"
    ]);
    exit;
}

$reward_id = isset($_POST['reward_id']) ? intval($_POST['reward_id']) : 0;

if ($reward_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID reward tidak valid"
    ]);
    exit;
}

// Ambil data reward
$rewardQuery = mysqli_query($conn, "SELECT * FROM rewards WHERE id_reward = $reward_id");
$reward = mysqli_fetch_assoc($rewardQuery);

if (!$reward) {
    echo json_encode([
        "success" => false,
        "message" => "Reward tidak ditemukan"
    ]);
    exit;
}

$poin_dibutuhkan = (int) $reward['poin'];
$stok = (int) $reward['stok'];

// Cek stok
if ($stok <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Stok reward sudah habis"
    ]);
    exit;
}

// Ambil poin user
$userQuery = mysqli_query($conn, "SELECT poin FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "User tidak ditemukan"
    ]);
    exit;
}

$poin_user = (int) $user['poin'];

// Cek apakah poin cukup
if ($poin_user < $poin_dibutuhkan) {
    echo json_encode([
        "success" => false,
        "message" => "Poin Anda tidak cukup. Dibutuhkan " . $poin_dibutuhkan . " poin, Anda memiliki " . $poin_user . " poin."
    ]);
    exit;
}

// ===== PROSES REDEEM =====

// 1. Kurangi poin user
$poin_baru = $poin_user - $poin_dibutuhkan;
$updatePoin = mysqli_query($conn, "UPDATE users SET poin = $poin_baru WHERE id = $user_id");

if (!$updatePoin) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengurangi poin: " . mysqli_error($conn)
    ]);
    exit;
}

// 2. Kurangi stok reward
$stok_baru = $stok - 1;
$updateStok = mysqli_query($conn, "UPDATE rewards SET stok = $stok_baru WHERE id_reward = $reward_id");

if (!$updateStok) {
    // Rollback poin user jika gagal
    mysqli_query($conn, "UPDATE users SET poin = $poin_user WHERE id = $user_id");
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengurangi stok: " . mysqli_error($conn)
    ]);
    exit;
}

// 3. Simpan history redemption
$nama_reward = mysqli_real_escape_string($conn, $reward['name_reward']);
$insertHistory = mysqli_query($conn,
    "INSERT INTO redemptions (user_id, reward_id, poin_digunakan, nama_reward)
     VALUES ($user_id, $reward_id, $poin_dibutuhkan, '$nama_reward')"
);

if (!$insertHistory) {
    // Rollback poin dan stok
    mysqli_query($conn, "UPDATE users SET poin = $poin_user WHERE id = $user_id");
    mysqli_query($conn, "UPDATE rewards SET stok = $stok WHERE id_reward = $reward_id");
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan history: " . mysqli_error($conn)
    ]);
    exit;
}

// Update session poin
$_SESSION['poin'] = $poin_baru;

echo json_encode([
    "success" => true,
    "message" => "Berhasil redeem \"" . $reward['name_reward'] . "\"!",
    "data" => [
        "poin_digunakan" => $poin_dibutuhkan,
        "sisa_poin" => $poin_baru,
        "sisa_stok" => $stok_baru
    ]
]);
?>
