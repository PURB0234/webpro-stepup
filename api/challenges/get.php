<?php
require_once __DIR__ . "/../../services/koneksi.php";
/** @var mysqli $conn */

// Query to get challenges along with dynamically calculated participant and completed counts
$query = "SELECT c.*, 
                 COALESCE(p.p_count, 0) AS participant_count,
                 COALESCE(p.comp_count, 0) AS completed_count
          FROM challenges c
          LEFT JOIN (
              SELECT challenge_id, 
                     COUNT(*) AS p_count,
                     SUM(CASE WHEN completion_status = 'completed' THEN 1 ELSE 0 END) AS comp_count 
              FROM challenge_participants 
              GROUP BY challenge_id
          ) p ON c.id = p.challenge_id
          ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    // If accessed directly as API, return JSON error
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

$challenges = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Standardize data types
    $row['id'] = intval($row['id']);
    $row['goal_value'] = intval($row['goal_value']);
    $row['reward_points'] = intval($row['reward_points']);
    $row['participant_count'] = intval($row['participant_count']);
    $row['completed_count'] = intval($row['completed_count']);
    $challenges[] = $row;
}

// If accessed directly as API -> return JSON
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => true,
        "message" => "Data challenges berhasil diambil",
        "total" => count($challenges),
        "data" => $challenges
    ]);
    exit;
}
// If required from another file, $challenges variable is ready to use
?>
