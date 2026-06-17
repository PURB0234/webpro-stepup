<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../services/koneksi.php";
require_once __DIR__ . "/../../services/badge_helper.php";
/** @var mysqli $conn */

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal"
    ]);
    exit();
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Execute auto-award checker if user is logged in
if ($user_id > 0) {
    checkAndAwardBadges($conn, $user_id);
}

// Fetch all badges
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$statusCondition = $isAdmin ? "" : "WHERE b.status = 'active'";

$query = "SELECT b.*, 
                 c.title AS related_challenge_title, 
                 col.name AS related_collection_name,
                 ub.earned_at
          FROM badges b
          LEFT JOIN challenges c ON b.related_challenge_id = c.id
          LEFT JOIN collections col ON b.related_collection_id = col.id
          LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = $user_id
          $statusCondition
          ORDER BY b.created_at DESC";

$result = mysqli_query($conn, $query);
if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengambil data badge: " . mysqli_error($conn)
    ]);
    exit();
}

// Prepare helper statistics if user is logged in
$totalCompletedChallenges = 0;
$totalCompletedCollections = 0;
$totalSteps = 0;
$streakDays = 0;

if ($user_id > 0) {
    $chalCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM challenge_participants WHERE user_id = $user_id AND completion_status = 'completed'");
    $totalCompletedChallenges = mysqli_fetch_assoc($chalCountQuery)['total'] ?? 0;

    $colCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_collections WHERE user_id = $user_id AND status = 'completed'");
    $totalCompletedCollections = mysqli_fetch_assoc($colCountQuery)['total'] ?? 0;

    $stepsQuery = mysqli_query($conn, "SELECT SUM(c.goal_value) AS total FROM challenge_participants cp JOIN challenges c ON cp.challenge_id = c.id WHERE cp.user_id = $user_id AND cp.completion_status = 'completed' AND c.goal_type = 'steps'");
    $totalSteps = mysqli_fetch_assoc($stepsQuery)['total'] ?? 0;

    $streakQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT DATE(joined_at)) as streak_days FROM challenge_participants WHERE user_id = $user_id");
    $streakDays = mysqli_fetch_assoc($streakQuery)['streak_days'] ?? 0;
}

$badges = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = intval($row['id']);
    $row['related_challenge_id'] = $row['related_challenge_id'] ? intval($row['related_challenge_id']) : null;
    $row['related_collection_id'] = $row['related_collection_id'] ? intval($row['related_collection_id']) : null;
    $row['is_unlocked'] = $row['earned_at'] !== null;
    $row['earned_date'] = $row['earned_at'] ? date('d M Y', strtotime($row['earned_at'])) : null;

    // Calculate dynamic progress
    $progress_current = 0;
    $progress_target = 0;
    $progress_percent = 0;

    if ($row['is_unlocked']) {
        $progress_percent = 100;
    } elseif ($user_id > 0) {
        if ($row['related_challenge_id'] !== null) {
            $checkChal = mysqli_query($conn, "SELECT cp.current_progress, c.goal_value FROM challenge_participants cp JOIN challenges c ON cp.challenge_id = c.id WHERE cp.user_id = $user_id AND cp.challenge_id = " . $row['related_challenge_id']);
            if ($checkChal && mysqli_num_rows($checkChal) > 0) {
                $pData = mysqli_fetch_assoc($checkChal);
                $progress_current = intval($pData['current_progress']);
                $progress_target = intval($pData['goal_value']);
                $progress_percent = $progress_target > 0 ? min(100, round(($progress_current / $progress_target) * 100)) : 0;
            } else {
                $progress_current = 0;
                $progress_target = 1;
                $progress_percent = 0;
            }
        } elseif ($row['related_collection_id'] !== null) {
            $checkCol = mysqli_query($conn, "SELECT progress_percentage FROM user_collections WHERE user_id = $user_id AND collection_id = " . $row['related_collection_id']);
            if ($checkCol && mysqli_num_rows($checkCol) > 0) {
                $progress_percent = intval(mysqli_fetch_assoc($checkCol)['progress_percentage']);
            } else {
                $progress_percent = 0;
            }
        } else {
            $req = strtolower(trim($row['unlock_requirement']));
            if (strpos($req, 'complete 1 challenge') !== false) {
                $progress_current = $totalCompletedChallenges;
                $progress_target = 1;
                $progress_percent = $totalCompletedChallenges >= 1 ? 100 : 0;
            } elseif (strpos($req, 'complete 10 challenges') !== false) {
                $progress_current = min(10, $totalCompletedChallenges);
                $progress_target = 10;
                $progress_percent = round(($progress_current / 10) * 100);
            } elseif (strpos($req, 'walk 10,000 steps') !== false) {
                $progress_current = min(10000, $totalSteps);
                $progress_target = 10000;
                $progress_percent = round(($progress_current / 10000) * 100);
            } elseif (strpos($req, 'complete 3 collections') !== false) {
                $progress_current = min(3, $totalCompletedCollections);
                $progress_target = 3;
                $progress_percent = round(($progress_current / 3) * 100);
            } elseif (strpos($req, 'streak') !== false) {
                preg_match('/\d+/', $req, $matches);
                $reqDays = isset($matches[0]) ? intval($matches[0]) : 7;
                $progress_current = min($reqDays, $streakDays);
                $progress_target = $reqDays;
                $progress_percent = round(($progress_current / $reqDays) * 100);
            }
        }
    }

    $row['progress'] = [
        "current" => $progress_current,
        "target" => $progress_target,
        "percentage" => $progress_percent
    ];

    $badges[] = $row;
}

// If accessed directly as API -> return JSON
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    echo json_encode([
        "success" => true,
        "message" => "Data badges berhasil diambil",
        "total" => count($badges),
        "data" => $badges
    ]);
    exit;
}
?>
