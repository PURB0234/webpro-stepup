<?php

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Anda harus login terlebih dahulu"
    ]);
    exit;
}

// Ambil nama baru
$nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, trim($_POST['nama'])) : '';

if (empty($nama)) {
    echo json_encode([
        "success" => false,
        "message" => "Nama tidak boleh kosong"
    ]);
    exit;
}

// Handle foto profil
$foto_update = "";
if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $filename = $_FILES['foto_profile']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode([
            "success" => false,
            "message" => "Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP"
        ]);
        exit;
    }

    // Cek ukuran (max 2MB)
    if ($_FILES['foto_profile']['size'] > 2 * 1024 * 1024) {
        echo json_encode([
            "success" => false,
            "message" => "Ukuran file maksimal 2MB"
        ]);
        exit;
    }

    // Generate nama unik
    $newFilename = time() . '_' . $user_id . '.' . $ext;
    $uploadDir = __DIR__ . "/../../uploads/profiles/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Hapus foto lama
    $oldQuery = mysqli_query($conn, "SELECT foto_profile FROM users WHERE id = $user_id");
    $oldData = mysqli_fetch_assoc($oldQuery);
    if ($oldData && !empty($oldData['foto_profile'])) {
        $oldPath = $uploadDir . $oldData['foto_profile'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // Upload foto baru
    if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $uploadDir . $newFilename)) {
        $foto_escaped = mysqli_real_escape_string($conn, $newFilename);
        $foto_update = ", foto_profile = '$foto_escaped'";
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gagal mengupload foto"
        ]);
        exit;
    }
}

// Update database
$query = mysqli_query($conn,
    "UPDATE users SET nama = '$nama' $foto_update WHERE id = $user_id"
);

if ($query) {
    // Update session
    $_SESSION['nama'] = $nama;

    // Jika foto diupdate, update session juga
    if (!empty($foto_update)) {
        $_SESSION['foto_profile'] = $newFilename;
    }

    echo json_encode([
        "success" => true,
        "message" => "Profil berhasil diperbarui"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui profil: " . mysqli_error($conn)
    ]);
}
