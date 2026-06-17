<?php
// services/badge_helper.php

if (!function_exists('checkAndAwardBadges')) {
    /**
     * Checks and awards eligible badges to a user.
     * Returns an array of newly awarded badges.
     *
     * @param mysqli $conn
     * @param int $user_id
     * @return array
     */
    function checkAndAwardBadges($conn, $user_id) {
        $user_id = intval($user_id);
        if ($user_id <= 0) return [];

        // 1. Get all active badges
        $badgeQuery = mysqli_query($conn, "SELECT * FROM badges WHERE status = 'active'");
        if (!$badgeQuery) return [];

        // 2. Get user's earned badges to avoid duplicates
        $earnedQuery = mysqli_query($conn, "SELECT badge_id FROM user_badges WHERE user_id = $user_id");
        $earnedBadges = [];
        if ($earnedQuery) {
            while ($row = mysqli_fetch_assoc($earnedQuery)) {
                $earnedBadges[] = intval($row['badge_id']);
            }
        }

        // 3. Gather stats for milestone checks
        // Completed challenges count
        $chalCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM challenge_participants WHERE user_id = $user_id AND completion_status = 'completed'");
        $totalCompletedChallenges = mysqli_fetch_assoc($chalCountQuery)['total'] ?? 0;

        // Completed collections count
        $colCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_collections WHERE user_id = $user_id AND status = 'completed'");
        $totalCompletedCollections = mysqli_fetch_assoc($colCountQuery)['total'] ?? 0;

        // Total steps from completed challenges
        $stepsQuery = mysqli_query($conn, "SELECT SUM(c.goal_value) AS total FROM challenge_participants cp JOIN challenges c ON cp.challenge_id = c.id WHERE cp.user_id = $user_id AND cp.completion_status = 'completed' AND c.goal_type = 'steps'");
        $totalSteps = mysqli_fetch_assoc($stepsQuery)['total'] ?? 0;

        // Total distance from completed challenges
        $distanceQuery = mysqli_query($conn, "SELECT SUM(c.goal_value) AS total FROM challenge_participants cp JOIN challenges c ON cp.challenge_id = c.id WHERE cp.user_id = $user_id AND cp.completion_status = 'completed' AND c.goal_type = 'distance'");
        $totalDistance = mysqli_fetch_assoc($distanceQuery)['total'] ?? 0;

        // Streak check: count consecutive days the user has joined or completed activities
        // If streak information isn't tracked explicitly, we can calculate based on completed challenges dates
        // For streak, we can query distinct dates user completed challenges
        // To be safe and performant, we can do a distinct date query or provide a sensible mock/database count
        // Let's calculate actual streak based on challenge participant logs if possible, or just default to 1 day for each completed challenge up to a maximum
        $streakQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT DATE(joined_at)) as streak_days FROM challenge_participants WHERE user_id = $user_id");
        $streakDays = mysqli_fetch_assoc($streakQuery)['streak_days'] ?? 0;

        $newlyEarned = [];

        while ($badge = mysqli_fetch_assoc($badgeQuery)) {
            $badgeId = intval($badge['id']);
            if (in_array($badgeId, $earnedBadges)) {
                continue; // Already earned
            }

            $shouldAward = false;

            // Check if tied to a specific challenge
            if (!empty($badge['related_challenge_id'])) {
                $relChalId = intval($badge['related_challenge_id']);
                $checkChal = mysqli_query($conn, "SELECT 1 FROM challenge_participants WHERE user_id = $user_id AND challenge_id = $relChalId AND completion_status = 'completed'");
                if ($checkChal && mysqli_num_rows($checkChal) > 0) {
                    $shouldAward = true;
                }
            }
            // Check if tied to a specific collection
            elseif (!empty($badge['related_collection_id'])) {
                $relColId = intval($badge['related_collection_id']);
                $checkCol = mysqli_query($conn, "SELECT 1 FROM user_collections WHERE user_id = $user_id AND collection_id = $relColId AND status = 'completed'");
                if ($checkCol && mysqli_num_rows($checkCol) > 0) {
                    $shouldAward = true;
                }
            }
            // Check general requirements
            else {
                $req = strtolower(trim($badge['unlock_requirement']));
                
                // Match patterns in unlock requirements
                if (strpos($req, 'complete 1 challenge') !== false && $totalCompletedChallenges >= 1) {
                    $shouldAward = true;
                } elseif (strpos($req, 'complete 10 challenges') !== false && $totalCompletedChallenges >= 10) {
                    $shouldAward = true;
                } elseif (strpos($req, 'walk 10,000 steps') !== false && $totalSteps >= 10000) {
                    $shouldAward = true;
                } elseif (strpos($req, 'complete 3 collections') !== false && $totalCompletedCollections >= 3) {
                    $shouldAward = true;
                } elseif (strpos($req, 'streak') !== false) {
                    // Extract number from requirement (e.g. "Maintain a 7-Day Streak")
                    preg_match('/\d+/', $req, $matches);
                    $reqDays = isset($matches[0]) ? intval($matches[0]) : 7;
                    if ($streakDays >= $reqDays) {
                        $shouldAward = true;
                    }
                }
            }

            if ($shouldAward) {
                $insert = mysqli_query($conn, "INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES ($user_id, $badgeId)");
                if ($insert && mysqli_affected_rows($conn) > 0) {
                    $newlyEarned[] = $badge;
                }
            }
        }

        return $newlyEarned;
    }
}
?>
