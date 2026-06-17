<?php include "../layouts/header.php"; ?>

<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login_form.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

require_once "../services/koneksi.php";
require_once "../services/badge_helper.php";
/** @var mysqli $conn */

// Run badge awarding checker
$newlyEarned = checkAndAwardBadges($conn, $user_id);

// Fetch all active badges and their unlock status
$query = "SELECT b.*, 
                 c.title AS related_challenge_title, 
                 col.name AS related_collection_name,
                 ub.earned_at
          FROM badges b
          LEFT JOIN challenges c ON b.related_challenge_id = c.id
          LEFT JOIN collections col ON b.related_collection_id = col.id
          LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = $user_id
          WHERE b.status = 'active'
          ORDER BY b.created_at DESC";

$result = mysqli_query($conn, $query);
$badges = [];
$totalEarned = 0;
$rareEarned = 0;

// Gather stats for milestone checks
$chalCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM challenge_participants WHERE user_id = $user_id AND completion_status = 'completed'");
$totalCompletedChallenges = mysqli_fetch_assoc($chalCountQuery)['total'] ?? 0;

$colCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_collections WHERE user_id = $user_id AND status = 'completed'");
$totalCompletedCollections = mysqli_fetch_assoc($colCountQuery)['total'] ?? 0;

$stepsQuery = mysqli_query($conn, "SELECT SUM(c.goal_value) AS total FROM challenge_participants cp JOIN challenges c ON cp.challenge_id = c.id WHERE cp.user_id = $user_id AND cp.completion_status = 'completed' AND c.goal_type = 'steps'");
$totalSteps = mysqli_fetch_assoc($stepsQuery)['total'] ?? 0;

$streakQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT DATE(joined_at)) as streak_days FROM challenge_participants WHERE user_id = $user_id");
$streakDays = mysqli_fetch_assoc($streakQuery)['streak_days'] ?? 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = intval($row['id']);
    $row['related_challenge_id'] = $row['related_challenge_id'] ? intval($row['related_challenge_id']) : null;
    $row['related_collection_id'] = $row['related_collection_id'] ? intval($row['related_collection_id']) : null;
    $row['is_unlocked'] = $row['earned_at'] !== null;
    $row['earned_date'] = $row['earned_at'] ? date('d M Y', strtotime($row['earned_at'])) : null;

    if ($row['is_unlocked']) {
        $totalEarned++;
        if (in_array($row['rarity'], ['epic', 'legendary'])) {
            $rareEarned++;
        }
    }

    // Progress calculations
    $progress_current = 0;
    $progress_target = 0;
    $progress_percent = 0;

    if ($row['is_unlocked']) {
        $progress_percent = 100;
        $progress_current = 1;
        $progress_target = 1;
    } else {
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
                $progress_current = $progress_percent;
                $progress_target = 100;
            } else {
                $progress_percent = 0;
                $progress_current = 0;
                $progress_target = 100;
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

$totalBadges = count($badges);
$completionRate = $totalBadges > 0 ? round(($totalEarned / $totalBadges) * 100) : 0;
$achievementLevel = $totalEarned > 0 ? floor($totalEarned / 3) + 1 : 1;
?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<link rel="stylesheet" href="../style/badges.css">

<div class="main-content">

    <!-- ========== HERO BANNER ========== -->
    <div class="badges-hero">
        <div class="badges-hero-text">
            <h1>Achievements & Badges</h1>
            <p>Track your accomplishments and unlock exclusive badges as you complete challenges and collections.</p>
            <div class="badges-hero-actions">
                <button class="btn-hero-primary" onclick="scrollToSection('badgeCollectionSection')">
                    View Progress
                </button>
                <a href="challenges.php" class="btn-hero-secondary">
                    Explore Challenges
                </a>
            </div>
        </div>
        <div class="badges-hero-image">
            <img class="hero-icon" src="../assets/icon/badge_icon/medal.png" alt="achievements icon">
        </div>
    </div>

    <!-- ========== STATS CARDS ========== -->
    <div class="badges-stats">
        <div class="stat-card">
            <div class="stat-icon icon-earned">🎖️</div>
            <div class="stat-info">
                <h3><?= $totalEarned ?> / <?= $totalBadges ?></h3>
                <p>Total Badges Earned</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-rate">📈</div>
            <div class="stat-info">
                <h3><?= $completionRate ?>%</h3>
                <p>Completion Rate</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-rare">💎</div>
            <div class="stat-info">
                <h3><?= $rareEarned ?></h3>
                <p>Rare Badges</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-level">⭐</div>
            <div class="stat-info">
                <h3>Lvl <?= $achievementLevel ?></h3>
                <p>Achievement Level</p>
            </div>
        </div>
    </div>

    <!-- ========== BADGE COLLECTION SECTION ========== -->
    <div class="section-title-container" id="badgeCollectionSection">
        <h2>Badge Collection</h2>
        <div class="badges-filter-tabs">
            <button class="active" id="tab-all" onclick="filterTab('all', this)">All</button>
            <button id="tab-unlocked" onclick="filterTab('unlocked', this)">Unlocked</button>
            <button id="tab-locked" onclick="filterTab('locked', this)">Locked</button>
        </div>
    </div>
    
    <div class="badges-grid" id="badgesGrid">
        <?php if (!empty($badges)): ?>
            <?php foreach ($badges as $b): 
                $iconPath = !empty($b['badge_icon']) ? "../uploads/badge_icons/" . htmlspecialchars($b['badge_icon']) : "../assets/icon/badge_icon/medal.png";
                $lockClass = $b['is_unlocked'] ? "unlocked" : "locked";
                $rarityLabel = ucfirst($b['rarity']);
                $categoryLabel = ucfirst(str_replace('_', ' ', $b['category']));
            ?>
                <div class="badge-card <?= $lockClass ?>" 
                     data-id="<?= $b['id'] ?>" 
                     data-unlocked="<?= $b['is_unlocked'] ? 'true' : 'false' ?>"
                     onclick="openBadgeDetail(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                    
                    <div class="badge-card-icon-wrapper">
                        <img src="<?= $iconPath ?>" class="badge-card-icon" alt="<?= htmlspecialchars($b['name']) ?>">
                        <?php if (!$b['is_unlocked']): ?>
                            <div class="badge-lock-overlay">🔒</div>
                        <?php endif; ?>
                    </div>

                    <div class="badge-card-info">
                        <span class="badge-rarity <?= strtolower($b['rarity']) ?>"><?= $rarityLabel ?></span>
                        <h3><?= htmlspecialchars($b['name']) ?></h3>
                        <p class="badge-category"><?= $categoryLabel ?></p>
                        
                        <?php if ($b['is_unlocked']): ?>
                            <span class="badge-date">Earned <?= htmlspecialchars($b['earned_date']) ?></span>
                        <?php else: ?>
                            <span class="badge-requirement-preview">Locked</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280; font-size: 16px;">
                Belum ada badge yang tersedia.
            </div>
        <?php endif; ?>

        <!-- Empty State Placeholder -->
        <div id="emptyStatePlaceholder" class="badges-empty-state" style="display: none; grid-column: 1 / -1;">
            <div class="empty-state-icon">⛰️</div>
            <h3>You have not unlocked any badges yet</h3>
            <p>Complete challenges and collections to earn your first badge!</p>
            <a href="challenges.php" class="btn-hero-primary" style="display: inline-block; text-decoration: none; margin-top: 15px;">
                Browse Challenges
            </a>
        </div>
    </div>

    <!-- ========== ACHIEVEMENT PROGRESS SECTION ========== -->
    <div class="section-title-container">
        <h2>Achievement Progress</h2>
    </div>
    <div class="progress-cards-row">
        <div class="progress-metrics-card">
            <div class="metric-icon">👣</div>
            <div class="metric-details">
                <h4>Walking Progress</h4>
                <h3><?= number_format($totalSteps) ?> Steps</h3>
                <p>From completed challenges</p>
            </div>
        </div>
        <div class="progress-metrics-card">
            <div class="metric-icon">🎯</div>
            <div class="metric-details">
                <h4>Challenges Completed</h4>
                <h3><?= number_format($totalCompletedChallenges) ?></h3>
                <p>Active challenge completions</p>
            </div>
        </div>
        <div class="progress-metrics-card">
            <div class="metric-icon">📚</div>
            <div class="metric-details">
                <h4>Collections Completed</h4>
                <h3><?= number_format($totalCompletedCollections) ?></h3>
                <p>Training pack completions</p>
            </div>
        </div>
        <div class="progress-metrics-card">
            <div class="metric-icon">⚡</div>
            <div class="metric-details">
                <h4>Current Streak</h4>
                <h3><?= number_format($streakDays) ?> Days</h3>
                <p>Consecutive active days</p>
            </div>
        </div>
    </div>

</div>

<!-- ========== DETAILED BADGE MODAL ========== -->
<div id="badgeDetailModal" class="badge-modal-overlay">
    <div class="badge-modal-box">
        <div class="badge-modal-header">
            <h3 id="modalBadgeName">Badge Details</h3>
            <button class="badge-modal-close" onclick="closeBadgeDetail()">&times;</button>
        </div>
        <div class="badge-modal-body" style="text-align: center; padding: 30px 24px;">
            <div class="modal-badge-graphic" id="modalBadgeGraphic">
                <img src="" id="modalBadgeIcon" alt="Badge Icon">
                <div class="modal-badge-lock" id="modalBadgeLock">🔒</div>
            </div>
            
            <span class="badge-rarity" id="modalRarity" style="display: inline-block; margin-bottom: 12px;">Common</span>
            <h4 id="modalName" style="font-size: 1.25rem; font-weight: 700; margin: 0 0 6px 0; color: #1a1a2e;">First Steps</h4>
            <p id="modalCategory" style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase; margin: 0 0 16px 0;">Challenge</p>

            <div class="modal-badge-details-box">
                <div class="detail-row">
                    <span>Description:</span>
                    <strong id="modalDesc">Walk 1,000 steps to earn this badge.</strong>
                </div>
                <div class="detail-row">
                    <span>Unlock Requirement:</span>
                    <strong id="modalRequirement">Complete the beginner challenge.</strong>
                </div>
                <div class="detail-row" id="modalDateRow">
                    <span>Earned Date:</span>
                    <strong id="modalEarnedDate">-</strong>
                </div>
                <div class="detail-row" id="modalChallengeRow">
                    <span>Related Challenge:</span>
                    <strong id="modalRelatedChallenge">-</strong>
                </div>
                <div class="detail-row" id="modalCollectionRow">
                    <span>Related Collection:</span>
                    <strong id="modalRelatedCollection">-</strong>
                </div>
            </div>

            <!-- Progress tracker -->
            <div class="modal-progress-section" id="modalProgressSection">
                <div class="modal-progress-text">
                    <span>Progress to Unlock</span>
                    <span id="modalProgressLabel">70%</span>
                </div>
                <div class="modal-progress-bar">
                    <div class="modal-progress-fill" id="modalProgressFill" style="width: 70%;"></div>
                </div>
            </div>

            <div class="badge-modal-actions" style="justify-content: center; border-top: none; padding-top: 0; margin-top: 24px;">
                <button class="badge-btn-modal-cancel" onclick="closeBadgeDetail()" style="width: 100%; max-width: 200px;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// ==============================
// SCROLL TO ELEMENT
// ==============================
function scrollToSection(id) {
    document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
}

// ==============================
// FILTER TAB
// ==============================
function filterTab(tab, btn) {
    document.querySelectorAll('.badges-filter-tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('#badgesGrid .badge-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const isUnlocked = card.dataset.unlocked === 'true';

        if (tab === 'all') {
            card.style.display = 'flex';
            visibleCount++;
        } else if (tab === 'unlocked') {
            if (isUnlocked) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        } else if (tab === 'locked') {
            if (!isUnlocked) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        }

        if (card.style.display === 'flex') {
            card.style.animation = 'modalSlide 0.3s ease';
        }
    });

    const emptyState = document.getElementById('emptyStatePlaceholder');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
        if (tab === 'unlocked') {
            emptyState.querySelector('h3').textContent = "You have not unlocked any badges yet";
            emptyState.querySelector('p').textContent = "Complete challenges and collections to earn achievements.";
        } else if (tab === 'locked') {
            emptyState.querySelector('h3').textContent = "You unlocked all badges!";
            emptyState.querySelector('p').textContent = "Wow! You have collected every achievement available.";
        }
    } else {
        emptyState.style.display = 'none';
    }
}

// ==============================
// BADGE DETAIL MODAL
// ==============================
function openBadgeDetail(badge) {
    const isUnlocked = badge.is_unlocked;
    const iconPath = badge.badge_icon ? "../uploads/badge_icons/" + badge.badge_icon : "../assets/icon/badge_icon/medal.png";

    document.getElementById('modalBadgeIcon').src = iconPath;
    document.getElementById('modalName').textContent = badge.name;
    
    // Rarity classes
    const rarityBadge = document.getElementById('modalRarity');
    rarityBadge.textContent = badge.rarity.toUpperCase();
    rarityBadge.className = 'badge-rarity ' + badge.rarity.toLowerCase();

    // Category
    document.getElementById('modalCategory').textContent = badge.category.replace('_', ' ').toUpperCase();

    // Status / Details
    const modalBadgeGraphic = document.getElementById('modalBadgeGraphic');
    const modalBadgeLock = document.getElementById('modalBadgeLock');
    if (isUnlocked) {
        modalBadgeGraphic.classList.remove('locked');
        modalBadgeLock.style.display = 'none';
        
        // Hide requirement and progress for unlocked badges
        document.getElementById('modalDesc').textContent = badge.description || "No description provided.";
        document.getElementById('modalRequirement').textContent = badge.unlock_requirement;
        document.getElementById('modalDateRow').style.display = 'flex';
        document.getElementById('modalEarnedDate').textContent = badge.earned_date;
        document.getElementById('modalProgressSection').style.display = 'none';
    } else {
        modalBadgeGraphic.classList.add('locked');
        modalBadgeLock.style.display = 'flex';
        
        // Hide description and unlock requirements for locked badges, show placeholder
        document.getElementById('modalDesc').textContent = "Locked Achievement";
        document.getElementById('modalRequirement').textContent = "Complete requirements to unlock this achievement and reveal details.";
        document.getElementById('modalDateRow').style.display = 'none';
        
        // Show progress tracker
        document.getElementById('modalProgressSection').style.display = 'block';
        
        const current = badge.progress.current;
        const target = badge.progress.target;
        const percentage = badge.progress.percentage;
        
        let labelText = percentage + "%";
        if (target > 1 && badge.related_challenge_id === null && badge.related_collection_id === null) {
            labelText = current.toLocaleString() + " / " + target.toLocaleString() + " (" + percentage + "%)";
        }
        
        document.getElementById('modalProgressLabel').textContent = labelText;
        document.getElementById('modalProgressFill').style.width = percentage + "%";
    }

    // Related challenge
    const chalRow = document.getElementById('modalChallengeRow');
    if (badge.related_challenge_id && badge.related_challenge_title) {
        chalRow.style.display = 'flex';
        document.getElementById('modalRelatedChallenge').textContent = badge.related_challenge_title;
    } else {
        chalRow.style.display = 'none';
    }

    // Related collection
    const colRow = document.getElementById('modalCollectionRow');
    if (badge.related_collection_id && badge.related_collection_name) {
        colRow.style.display = 'flex';
        document.getElementById('modalRelatedCollection').textContent = badge.related_collection_name;
    } else {
        colRow.style.display = 'none';
    }

    document.getElementById('badgeDetailModal').classList.add('show');
}

function closeBadgeDetail() {
    document.getElementById('badgeDetailModal').classList.remove('show');
}

// Close modal when click outside modal-box
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('badge-modal-overlay')) {
        e.target.classList.remove('show');
    }
});
</script>

<?php include "../layouts/footer.php"; ?>
