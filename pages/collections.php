<?php include "../layouts/header.php"; ?>

<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login_form.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

require_once "../services/koneksi.php";
/** @var mysqli $conn */

// Check and award badges on page load
require_once "../services/badge_helper.php";
checkAndAwardBadges($conn, $user_id);

// Query all active collections from database
$colQuery = mysqli_query($conn, "SELECT * FROM collections WHERE status = 'active' ORDER BY created_at DESC");
$collections = [];

while ($col = mysqli_fetch_assoc($colQuery)) {
    $colId = intval($col['id']);
    
    // Check if user has joined this collection
    $ucQuery = mysqli_query($conn, "SELECT status, progress_percentage FROM user_collections WHERE collection_id = $colId AND user_id = $user_id");
    $uc = mysqli_fetch_assoc($ucQuery);
    $isJoined = $uc !== null;
    $storedStatus = $isJoined ? $uc['status'] : 'unjoined';
    $storedProgress = $isJoined ? intval($uc['progress_percentage']) : 0;

    // Fetch challenges assigned to this collection
    $ccQuery = mysqli_query($conn, "SELECT cc.challenge_id, c.title, c.goal_type, c.goal_value, c.reward_points FROM collection_challenges cc JOIN challenges c ON cc.challenge_id = c.id WHERE cc.collection_id = $colId");
    $challengesList = [];
    $totalChallenges = 0;
    $completedChallenges = 0;
    $joinedChallenges = 0;
    $totalPoints = 0;

    while ($ch = mysqli_fetch_assoc($ccQuery)) {
        $chId = intval($ch['challenge_id']);
        $totalChallenges++;
        $totalPoints += intval($ch['reward_points']);

        // Check if user has participated in this challenge
        $cpQuery = mysqli_query($conn, "SELECT completion_status FROM challenge_participants WHERE challenge_id = $chId AND user_id = $user_id");
        $cp = mysqli_fetch_assoc($cpQuery);
        
        $chStatus = 'unjoined';
        if ($cp) {
            $joinedChallenges++;
            if ($cp['completion_status'] === 'completed') {
                $completedChallenges++;
                $chStatus = 'completed';
            } else {
                $chStatus = 'joined';
            }
        }

        $challengesList[] = [
            'id' => $chId,
            'title' => $ch['title'],
            'goal_type' => $ch['goal_type'],
            'goal_value' => intval($ch['goal_value']),
            'reward_points' => intval($ch['reward_points']),
            'status' => $chStatus
        ];
    }

    $progressPercent = $totalChallenges > 0 ? round(($completedChallenges / $totalChallenges) * 100) : 0;
    $progressPercent = min($progressPercent, 100);

    $finalStatus = $storedStatus;
    if ($isJoined) {
        // Dynamic status check
        if ($progressPercent === 100 && $storedStatus !== 'completed') {
            $finalStatus = 'completed';
            mysqli_query($conn, "UPDATE user_collections SET progress_percentage = 100, status = 'completed' WHERE collection_id = $colId AND user_id = $user_id");
            
            // Set trigger for completion modal
            $_SESSION['completed_collection_trigger'] = [
                'id' => $colId,
                'name' => $col['name'],
                'reward' => $totalPoints,
                'badge' => $col['name'] . ' Champion',
                'date' => date('d M Y')
            ];
        } elseif ($progressPercent < 100 && $progressPercent > 0 && $storedStatus !== 'in_progress') {
            $finalStatus = 'in_progress';
            mysqli_query($conn, "UPDATE user_collections SET progress_percentage = $progressPercent, status = 'in_progress' WHERE collection_id = $colId AND user_id = $user_id");
        } elseif ($progressPercent !== $storedProgress) {
            mysqli_query($conn, "UPDATE user_collections SET progress_percentage = $progressPercent WHERE collection_id = $colId AND user_id = $user_id");
        }
    }

    // Determine Banner Style
    $bannerStyle = "";
    if (!empty($col['cover_image'])) {
        $bannerStyle = "background-image: url('../uploads/" . htmlspecialchars($col['cover_image'], ENT_QUOTES) . "'); background-size: cover; background-position: center;";
    } else {
        switch ($col['difficulty']) {
            case 'easy':
                $bannerStyle = "background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%)";
                break;
            case 'medium':
                $bannerStyle = "background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%)";
                break;
            case 'hard':
            default:
                $bannerStyle = "background: linear-gradient(135deg, #f87171 0%, #dc2626 100%)";
                break;
        }
    }

    $collections[] = [
        'id' => $colId,
        'name' => $col['name'],
        'description' => $col['description'],
        'cover_image' => $col['cover_image'],
        'difficulty' => $col['difficulty'],
        'estimated_duration' => $col['estimated_duration'],
        'banner_gradient' => $bannerStyle,
        'total_challenges' => $totalChallenges,
        'completed_challenges' => $completedChallenges,
        'progress_percent' => $progressPercent,
        'status' => $isJoined ? $finalStatus : 'unjoined',
        'challenges_list' => $challengesList,
        'total_points' => $totalPoints
    ];
}

// Global analytics counts
$totalCollections = count($collections);
$joinedCollections = 0;
$completedCollectionsCount = 0;
$highestProgressCollection = null;
$highestProgressVal = -1;

foreach ($collections as $c) {
    if ($c['status'] !== 'unjoined') {
        $joinedCollections++;
        if ($c['status'] === 'completed') {
            $completedCollectionsCount++;
        }
        
        // Track active in-progress collection
        if ($c['status'] !== 'completed' && $c['progress_percent'] > $highestProgressVal) {
            $highestProgressVal = $c['progress_percent'];
            $highestProgressCollection = $c;
        }
    }
}

// Fallback tracker widget if all completed or no progress
if ($highestProgressCollection === null && $joinedCollections > 0) {
    foreach ($collections as $c) {
        if ($c['status'] !== 'unjoined') {
            $highestProgressCollection = $c;
            break;
        }
    }
}
?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<link rel="stylesheet" href="../style/collections.css">
<style>
/* Style extension for User Completion Modal Overlay */
.completion-modal {
    width: 420px;
    text-align: center;
}
.completion-badge-graphic {
    width: 90px;
    height: 90px;
    margin: 0 auto 16px;
    background: #f0f2ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.8rem;
    box-shadow: 0 4px 14px rgba(99, 106, 232, 0.2);
    animation: pulseBadge 2s infinite;
}
@keyframes pulseBadge {
    0% { transform: scale(1); }
    50% { transform: scale(1.06); }
    100% { transform: scale(1); }
}
.completion-modal h4 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 4px 0;
}
.completion-modal p {
    font-size: 0.84rem;
    color: #6b7280;
    margin: 0 0 16px 0;
}
.completion-stats-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 22px;
}
.completion-stat-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.82rem;
    margin-bottom: 6px;
}
.completion-stat-row:last-child { margin-bottom: 0; }
.completion-stat-row span { color: #64748b; }
.completion-stat-row strong { color: #1a1a2e; }
.btn-view-achievement {
    width: 100%;
    padding: 11px;
    background: #10b981;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.85rem;
}
.btn-view-achievement:hover { background: #059669; }
</style>

<div class="main-content">

    <!-- ========== HERO BANNER ========== -->
    <div class="collections-hero">
        <div class="collections-hero-text">
            <h1>Curated Challenge Collections</h1>
            <p>Explore challenge packs hand-picked to help you build habits, explore, or burn calories. Progress at your own pace!</p>
            <div class="collections-hero-actions">
                <button class="btn-hero-primary" onclick="scrollToSection('allCollectionsSection')">
                    Browse Collections
                </button>
                <button class="btn-hero-secondary" onclick="filterTab('joined', document.getElementById('tab-joined'))">
                    My Collections
                </button>
            </div>
        </div>
        <div class="collections-hero-image">
            <img class="hero-icon" src="../assets/icon/badge_icon/medal.png" alt="collections icon">
        </div>
    </div>

    <!-- ========== STATS CARDS ========== -->
    <div class="collections-stats">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h3><?= $totalCollections ?></h3>
                <p>Total Collections</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #0369a1; background: #e0f2fe;">👣</div>
            <div class="stat-info">
                <h3><?= $joinedCollections ?></h3>
                <p>Joined Collections</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #16a34a; background: #dcfce7;">🏆</div>
            <div class="stat-info">
                <h3><?= $completedCollectionsCount ?></h3>
                <p>Completed Collections</p>
            </div>
        </div>
    </div>

    <!-- ========== ACTIVE PROGRESS TRACKER ========== -->
    <?php if ($highestProgressCollection !== null): ?>
        <div class="collection-tracker-widget">
            <div class="tracker-header">
                <div class="tracker-title">
                    <p>Current Active Pack</p>
                    <h3><?= htmlspecialchars($highestProgressCollection['name']) ?></h3>
                </div>
                <div class="tracker-percentage"><?= $highestProgressCollection['progress_percent'] ?>%</div>
            </div>
            <div class="collection-progress-container">
                <div class="collection-progress-text">
                    <span><?= $highestProgressCollection['completed_challenges'] ?> of <?= $highestProgressCollection['total_challenges'] ?> Challenges Completed</span>
                    <span>Goal: <?= htmlspecialchars($highestProgressCollection['estimated_duration'] ?: '-') ?></span>
                </div>
                <div class="collection-progress-bar">
                    <div class="collection-progress-fill" style="width: <?= $highestProgressCollection['progress_percent'] ?>%;"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========== ALL COLLECTIONS SECTION ========== -->
    <div class="section-title-container" id="allCollectionsSection">
        <h2>All Curated Collections</h2>
        <div class="collections-filter-tabs">
            <button class="active" id="tab-all" onclick="filterTab('all', this)">All</button>
            <button id="tab-joined" onclick="filterTab('joined', this)">Joined</button>
            <button id="tab-completed" onclick="filterTab('completed', this)">Completed</button>
        </div>
    </div>
    
    <div class="collections-grid-all" id="collectionsGridAll">
        <?php if (!empty($collections)): ?>
            <?php foreach ($collections as $col): 
                $isJoined = $col['status'] !== 'unjoined';
                $isCompleted = $col['status'] === 'completed';
            ?>
                <div class="collection-card" 
                     data-id="<?= $col['id'] ?>" 
                     data-joined="<?= $isJoined ? 'true' : 'false' ?>"
                     data-completed="<?= $isCompleted ? 'true' : 'false' ?>">
                    
                    <div class="collection-banner" style="<?= $col['banner_gradient'] ?>">
                        <span class="badge-difficulty <?= strtolower($col['difficulty']) ?>"><?= htmlspecialchars(ucfirst($col['difficulty'])) ?></span>
                        <?php if ($isCompleted): ?>
                            <span class="badge-status status-completed">Completed</span>
                        <?php elseif ($isJoined): ?>
                            <span class="badge-status status-joined">Joined</span>
                        <?php endif; ?>
                    </div>

                    <div class="collection-body">
                        <h3><?= htmlspecialchars($col['name']) ?></h3>
                        <p class="collection-desc"><?= htmlspecialchars($col['description'] ?: 'No description.') ?></p>
                        
                        <div class="collection-meta">
                            <span>📋 <?= $col['total_challenges'] ?> Challenges</span>
                            <span>⏱️ <?= htmlspecialchars($col['estimated_duration'] ?: '-') ?></span>
                        </div>

                        <?php if ($isJoined): ?>
                            <div class="collection-progress-container">
                                <div class="collection-progress-text">
                                    <span>Progress</span>
                                    <span><?= $col['completed_challenges'] ?>/<?= $col['total_challenges'] ?> completed (<?= $col['progress_percent'] ?>%)</span>
                                </div>
                                <div class="collection-progress-bar">
                                    <div class="collection-progress-fill" style="width: <?= $col['progress_percent'] ?>%;"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="challenge-card-actions" style="gap: 8px;">
                            <?php if ($isJoined): ?>
                                <button class="btn-collection-action" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($col), ENT_QUOTES, 'UTF-8') ?>)">
                                    Continue Collection
                                </button>
                            <?php else: ?>
                                <button class="btn-collection-action" onclick="joinCollection(<?= $col['id'] ?>)" style="background: #636ae8;">
                                    Join Collection
                                </button>
                                <button class="btn-card-edit" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($col), ENT_QUOTES, 'UTF-8') ?>)" style="flex:1;">
                                    View Details
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280; font-size: 16px;">
                Belum ada koleksi tantangan yang tersedia.
            </div>
        <?php endif; ?>

        <!-- Empty State (Shown dynamically via JS filter if empty) -->
        <div id="emptyStatePlaceholder" class="collections-empty-state" style="display: none; grid-column: 1 / -1;">
            <div class="empty-state-icon">⛰️</div>
            <h3>You have not joined any collections yet</h3>
            <p>Explore curated challenge packs and join collections to start tracking your activities!</p>
            <button class="btn-collection-action" style="width: auto; padding: 10px 24px;" onclick="filterTab('all', document.getElementById('tab-all'))">
                Browse Collections
            </button>
        </div>
    </div>

    <!-- ========== RECOMMENDED SECTION ========== -->
    <div class="section-title-container">
        <h2>Recommended For You</h2>
    </div>
    <div class="recommended-row">
        <?php 
            // Select up to 3 active collections to display as recommendations
            $recCount = 0;
            foreach ($collections as $col):
                if ($col['status'] !== 'unjoined') continue;
                if ($recCount >= 3) break;
                $recCount++;
                $icon = '🌱';
                if ($col['difficulty'] === 'medium') $icon = '⛰️';
                elseif ($col['difficulty'] === 'hard') $icon = '⚔️';
        ?>
            <div class="recommended-card" onclick="openDetailById(<?= $col['id'] ?>)">
                <div class="recommended-thumb"><?= $icon ?></div>
                <div class="recommended-info">
                    <h4><?= htmlspecialchars($col['name']) ?></h4>
                    <p><?= $col['total_challenges'] ?> Challenges • <?= htmlspecialchars($col['estimated_duration']) ?> • <?= htmlspecialchars(ucfirst($col['difficulty'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($recCount === 0): ?>
            <div style="grid-column: 1 / -1; color: #9ca3af; font-size: 0.82rem;">
                No recommended collections available at this time.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========== DETAILED CHECKLIST MODAL ========== -->
<div id="collectionDetailModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-banner" id="modalBanner">
            <button class="modal-close-btn" onclick="closeDetailsModal()">&times;</button>
        </div>
        <div class="modal-content-body">
            <div class="modal-title-desc">
                <h3 id="modalName">Beginner Journey</h3>
                <p id="modalDesc">Curated walking packs for beginners.</p>
            </div>

            <div class="modal-stats-row">
                <div class="modal-stat-item">
                    <span>Difficulty</span>
                    <strong id="modalDifficulty">Easy</strong>
                </div>
                <div class="modal-stat-item">
                    <span>Duration</span>
                    <strong id="modalDuration">2 Weeks</strong>
                </div>
                <div class="modal-stat-item">
                    <span>Total Rewards</span>
                    <strong id="modalRewards">200 pts</strong>
                </div>
            </div>

            <div class="modal-challenge-list">
                <h4>Challenges In Pack</h4>
                <div id="modalChallengeItems">
                    <!-- Loaded dynamically via JS -->
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-modal-action-secondary" onclick="closeDetailsModal()">Close</button>
                <button class="btn-modal-action-primary" id="btnModalPrimary" onclick="handleStartCollection()">Join Collection</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== COLLECTION COMPLETED ACHIEVEMENT MODAL ========== -->
<?php if (isset($_SESSION['completed_collection_trigger'])): 
    $trigger = $_SESSION['completed_collection_trigger'];
    // Clear trigger right after reading to ensure it only shows once
    unset($_SESSION['completed_collection_trigger']);
?>
    <div id="completionModal" class="modal-overlay show">
        <div class="modal-box completion-modal">
            <div class="modal-content-body" style="padding: 30px;">
                <div class="completion-badge-graphic">🏆</div>
                <h4>Collection Completed!</h4>
                <p>Congratulations! You have completed all challenges in this curated pack.</p>
                
                <div class="completion-stats-box">
                    <div class="completion-stat-row">
                        <span>Completed Pack:</span>
                        <strong><?= htmlspecialchars($trigger['name']) ?></strong>
                    </div>
                    <div class="completion-stat-row">
                        <span>Reward Earned:</span>
                        <strong>+<?= number_format($trigger['reward']) ?> Points</strong>
                    </div>
                    <div class="completion-stat-row">
                        <span>Badge Awarded:</span>
                        <strong>🎖️ <?= htmlspecialchars($trigger['badge']) ?></strong>
                    </div>
                    <div class="completion-stat-row">
                        <span>Completion Date:</span>
                        <strong><?= htmlspecialchars($trigger['date']) ?></strong>
                    </div>
                </div>

                <button class="btn-view-achievement" onclick="closeCompletionModal()">
                    View Achievement
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
const collectionsList = <?= json_encode($collections) ?>;
let activeCollection = null;

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
    document.querySelectorAll('.collections-filter-tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('#collectionsGridAll .collection-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const isJoined = card.dataset.joined === 'true';
        const isCompleted = card.dataset.completed === 'true';

        if (tab === 'all') {
            card.style.display = 'flex';
            visibleCount++;
        } else if (tab === 'joined') {
            if (isJoined) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        } else if (tab === 'completed') {
            if (isCompleted) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        }

        if (card.style.display === 'flex') {
            card.style.animation = 'modalSlideIn 0.3s ease';
        }
    });

    const emptyState = document.getElementById('emptyStatePlaceholder');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
        if (tab === 'joined') {
            emptyState.querySelector('h3').textContent = "You have not joined any collections yet";
            emptyState.querySelector('p').textContent = "Curated collections make it easy to start active habits.";
            emptyState.querySelector('button').style.display = 'inline-block';
        } else if (tab === 'completed') {
            emptyState.querySelector('h3').textContent = "No completed collections yet";
            emptyState.querySelector('p').textContent = "Finish all challenges in a collection to earn the collection badge!";
            emptyState.querySelector('button').style.display = 'none';
        }
    } else {
        emptyState.style.display = 'none';
    }
}

// ==============================
// JOIN COLLECTION ACTION
// ==============================
function joinCollection(collectionId) {
    const card = document.querySelector(`.collection-card[data-id="${collectionId}"]`);
    const actionBtn = card ? card.querySelector('.btn-collection-action') : null;
    if (actionBtn) {
        actionBtn.disabled = true;
        actionBtn.textContent = 'Joining...';
    }

    const formData = new FormData();
    formData.append('collection_id', collectionId);

    fetch('../api/collections/join.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '✅ ' + data.message);
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            showToast('error', '❌ ' + data.message);
            if (actionBtn) {
                actionBtn.disabled = false;
                actionBtn.textContent = 'Join Collection';
            }
        }
    })
    .catch(err => {
        showToast('error', '❌ Terjadi kesalahan koneksi.');
        if (actionBtn) {
            actionBtn.disabled = false;
            actionBtn.textContent = 'Join Collection';
        }
    });
}

// ==============================
// DETAILS CHECKLIST MODAL
// ==============================
function openDetailById(id) {
    const col = collectionsList.find(c => c.id === id);
    if (col) {
        openDetailsModal(col);
    }
}

function openDetailsModal(col) {
    activeCollection = col;
    document.getElementById('modalBanner').style.background = col.banner_gradient;
    document.getElementById('modalName').textContent = col.name;
    document.getElementById('modalDesc').textContent = col.description;
    document.getElementById('modalDifficulty').textContent = col.difficulty.toUpperCase();
    document.getElementById('modalDuration').textContent = col.estimated_duration;
    document.getElementById('modalRewards').textContent = `${col.total_points} pts`;

    // Populate challenges list
    const container = document.getElementById('modalChallengeItems');
    container.innerHTML = '';

    col.challenges_list.forEach(ch => {
        const item = document.createElement('div');
        item.className = 'challenge-checklist-item';

        let statusIcon = '◯';
        let statusClass = 'unjoined';
        if (ch.status === 'completed') {
            statusIcon = '✓';
            statusClass = 'completed';
        } else if (ch.status === 'joined') {
            statusIcon = '⚡';
            statusClass = 'joined';
        }

        let unitLabel = '';
        if (ch.goal_type === 'steps') unitLabel = 'steps';
        else if (ch.goal_type === 'distance') unitLabel = 'km';
        else if (ch.goal_type === 'active_minutes') unitLabel = 'mins';

        item.innerHTML = `
            <div class="item-left">
                <span class="status-indicator ${statusClass}">${statusIcon}</span>
                <span>${ch.title} <small style="color:#6b7280;">(${ch.goal_value.toLocaleString()} ${unitLabel})</small></span>
            </div>
            <div class="item-points">+${ch.reward_points} pts</div>
        `;
        container.appendChild(item);
    });

    // Handle primary action button in modal
    const btn = document.getElementById('btnModalPrimary');
    if (col.status === 'completed') {
        btn.textContent = 'Completed';
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.style.cursor = 'default';
        btn.style.background = '#10b981';
    } else if (col.status === 'joined' || col.status === 'in_progress') {
        btn.textContent = 'Continue Collection';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        btn.style.background = '#10b981';
    } else {
        btn.textContent = 'Join Collection';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        btn.style.background = '#636ae8';
    }

    document.getElementById('collectionDetailModal').classList.add('show');
}

function closeDetailsModal() {
    document.getElementById('collectionDetailModal').classList.remove('show');
}

function handleStartCollection() {
    if (!activeCollection) return;

    if (activeCollection.status !== 'unjoined') {
        // Redirect to challenges page to track progress
        window.location.href = 'challenges.php';
        return;
    }

    // Call join API
    closeDetailsModal();
    joinCollection(activeCollection.id);
}

// ==============================
// ACHIEVEMENT COMPLETION POPUP
// ==============================
function closeCompletionModal() {
    document.getElementById('completionModal').classList.remove('show');
}

// ==============================
// TOAST NOTIFICATION
// ==============================
function showToast(type, message) {
    const old = document.querySelector('.toast-notif');
    if (old) old.remove();

    const toast = document.createElement('div');
    toast.className = 'toast-notif toast-' + type;
    toast.style.position = 'fixed';
    toast.style.top = '24px';
    toast.style.right = '24px';
    toast.style.padding = '14px 22px';
    toast.style.borderRadius = '10px';
    toast.style.zIndex = '2000';
    toast.style.boxShadow = '0 8px 24px rgba(0,0,0,0.12)';
    toast.style.fontFamily = 'Inter, Arial, sans-serif';
    toast.style.fontSize = '0.85rem';
    toast.style.animation = 'modalSlide 0.35s ease';

    if (type === 'success') {
        toast.style.background = '#dcfce7';
        toast.style.color = '#16a34a';
        toast.style.border = '1px solid #bbf7d0';
    } else {
        toast.style.background = '#fef2f2';
        toast.style.color = '#dc2626';
        toast.style.border = '1px solid #fecaca';
    }

    toast.innerHTML = '<span>' + message + '</span>' +
        '<button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;font-size:16px;cursor:pointer;margin-left:8px;">&times;</button>';
    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4500);
}

// Close modal when click outside modal-box
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});
</script>

<?php include "../layouts/footer.php"; ?>