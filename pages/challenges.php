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

// Query to get challenges (excluding drafts) and joined status for this user
$query = "SELECT c.*, 
                 cp.id AS participation_id, 
                 cp.current_progress, 
                 cp.completion_status, 
                 cp.joined_at,
                 (SELECT COUNT(*) FROM challenge_participants WHERE challenge_id = c.id) AS total_participants
          FROM challenges c
          LEFT JOIN challenge_participants cp ON c.id = cp.challenge_id AND cp.user_id = $user_id
          WHERE c.status != 'draft'
          ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $query);
$challenges = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = intval($row['id']);
        $row['goal_value'] = intval($row['goal_value']);
        $row['reward_points'] = intval($row['reward_points']);
        $row['total_participants'] = intval($row['total_participants']);
        $row['current_progress'] = $row['current_progress'] !== null ? intval($row['current_progress']) : null;
        $challenges[] = $row;
    }
}

// Helper for time labeling
function getChallengeTimeLabel($status, $startDate, $endDate) {
    if ($status === 'archived' || $status === 'completed') {
        return 'Ended';
    }
    $now = time();
    $start = strtotime($startDate);
    $end = strtotime($endDate);

    if ($now < $start) {
        $diff = ceil(($start - $now) / 86400);
        return "Starts in $diff day" . ($diff > 1 ? "s" : "");
    }
    if ($now > $end) {
        return 'Ended';
    }
    $diff = ceil(($end - $now) / 86400);
    return "$diff day" . ($diff > 1 ? "s" : "") . " left";
}
?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<link rel="stylesheet" href="../style/challenges.css">

<div class="main-content">

    <!-- ========== HERO BANNER ========== -->
    <div class="challenge-hero">
        <div class="challenge-hero-text">
            <h1>Daily & Weekly Challenges</h1>
            <p>Join active challenges, hit your consistency goals, compete with the community, and earn reward points!</p>
        </div>
        <div class="challenge-hero-image">
            <img class="hero-icon" src="../assets/icon/dumbel.png" alt="challenge icon">
        </div>
    </div>

    <!-- ========== SECTION HEADER ========== -->
    <div class="challenge-section-header">
        <h2>Available Challenges</h2>
        <div class="challenge-filter-tabs">
            <button class="active" onclick="filterChallenges('all', this)">All</button>
            <button onclick="filterChallenges('joined', this)">Joined</button>
            <button onclick="filterChallenges('active', this)">Active</button>
            <button onclick="filterChallenges('completed', this)">Completed</button>
        </div>
    </div>

    <!-- ========== CHALLENGE CARDS GRID ========== -->
    <div class="challenge-cards-grid" id="challengeGrid">
        <?php if (!empty($challenges)): ?>
            <?php foreach ($challenges as $challenge): 
                $isJoined = $challenge['participation_id'] !== null;
                $isCompleted = $challenge['completion_status'] === 'completed';
                $progressPercent = 0;
                if ($isJoined) {
                    $progressPercent = round(($challenge['current_progress'] / $challenge['goal_value']) * 100);
                    $progressPercent = min($progressPercent, 100);
                }

                // Banner styling fallback
                $bannerStyle = "";
                if (!empty($challenge['banner_image'])) {
                    $bannerStyle = "background-image: url('../uploads/" . htmlspecialchars($challenge['banner_image'], ENT_QUOTES) . "'); background-size: cover; background-position: center;";
                } else {
                    switch ($challenge['challenge_type']) {
                        case 'daily':
                            $bannerStyle = "background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);";
                            break;
                        case 'weekly':
                            $bannerStyle = "background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);";
                            break;
                        case 'monthly':
                            $bannerStyle = "background: linear-gradient(135deg, #34d399 0%, #059669 100%);";
                            break;
                        case 'team':
                            $bannerStyle = "background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);";
                            break;
                        case 'event':
                        default:
                            $bannerStyle = "background: linear-gradient(135deg, #f472b6 0%, #ec4899 100%);";
                            break;
                    }
                }

                $timeLabel = getChallengeTimeLabel($challenge['status'], $challenge['start_date'], $challenge['end_date']);
                $goalUnit = '';
                switch ($challenge['goal_type']) {
                    case 'steps':
                        $goalUnit = 'steps';
                        break;
                    case 'distance':
                        $goalUnit = 'km';
                        break;
                    case 'active_minutes':
                        $goalUnit = 'mins';
                        break;
                }
            ?>
                <div class="challenge-card" 
                     data-joined="<?= $isJoined ? 'true' : 'false' ?>" 
                     data-completed="<?= $isCompleted ? 'true' : 'false' ?>"
                     data-status="<?= htmlspecialchars($challenge['status']) ?>" 
                     data-id="<?= $challenge['id'] ?>">
                    
                    <div class="challenge-card-banner" style="<?= $bannerStyle ?>">
                        <span class="card-badge-type"><?= htmlspecialchars(ucfirst($challenge['challenge_type'])) ?></span>
                        <?php if ($isCompleted): ?>
                            <span class="card-status status-completed">Completed</span>
                        <?php else: ?>
                            <span class="card-status status-active"><?= htmlspecialchars(ucfirst($challenge['status'])) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="challenge-card-body">
                        <h4><?= htmlspecialchars($challenge['title']) ?></h4>
                        <p class="challenge-card-desc"><?= htmlspecialchars($challenge['description'] ?: 'No description provided.') ?></p>
                        
                        <div class="challenge-card-meta">
                            <span>
                                <img src="../assets/icon/circular-alarm-clock-tool.png" alt="time" style="width:13px;height:13px;">
                                <?= htmlspecialchars($timeLabel) ?>
                            </span>
                            <span>
                                <img src="../assets/icon/person.png" alt="users" style="width:13px;height:13px;">
                                <?= $challenge['total_participants'] ?> participant<?= $challenge['total_participants'] != 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <!-- Progress Display (If Joined) -->
                        <?php if ($isJoined): ?>
                            <div class="challenge-progress-container">
                                <div class="challenge-progress-text">
                                    <span>Progress</span>
                                    <span><?= number_format($challenge['current_progress']) ?> / <?= number_format($challenge['goal_value']) ?> <?= $goalUnit ?> (<?= $progressPercent ?>%)</span>
                                </div>
                                <div class="challenge-progress-bar">
                                    <div class="challenge-progress-fill" style="width: <?= $progressPercent ?>%;"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="challenge-card-actions">
                            <?php if ($isCompleted): ?>
                                <div class="btn-completed-challenge">✓ Challenge Completed (+<?= $challenge['reward_points'] ?> pts)</div>
                            <?php elseif ($isJoined): ?>
                                <button class="btn-log-progress" onclick="openProgressModal(<?= $challenge['id'] ?>, '<?= htmlspecialchars($challenge['title'], ENT_QUOTES) ?>', '<?= $goalUnit ?>', <?= $challenge['current_progress'] ?>, <?= $challenge['goal_value'] ?>)">
                                    ⚡ Update Progress
                                </button>
                            <?php else: ?>
                                <button class="btn-join-challenge" onclick="joinChallenge(<?= $challenge['id'] ?>)">
                                    ➕ Join Challenge (+<?= $challenge['reward_points'] ?> pts)
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: #6b7280; font-size: 16px; background: #fff; border-radius: 14px; border: 1px dashed #dee1e6;">
                Belum ada tantangan aktif saat ini. Silakan kembali lagi nanti!
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========== UPDATE PROGRESS MODAL ========== -->
<div id="progressModal" class="challenge-modal-overlay">
    <div class="challenge-modal">
        <div class="challenge-modal-header">
            <h3>Update Challenge Progress</h3>
            <button class="challenge-modal-close" onclick="closeProgressModal()">&times;</button>
        </div>
        <div class="challenge-modal-body">
            <form id="formUpdateProgress" onsubmit="handleUpdateProgress(event)">
                <input type="hidden" id="progress-challenge-id">
                
                <div style="margin-bottom: 20px;">
                    <strong id="progress-challenge-title" style="font-size: 14px; color: #1a1a2e; display: block; margin-bottom: 6px;"></strong>
                    <span id="progress-current-status" style="font-size: 13px; color: #6b7280;"></span>
                </div>

                <div class="challenge-form-group">
                    <label id="progress-value-label">Log Activity Value</label>
                    <input type="number" id="progress-value-input" min="1" placeholder="Enter amount to add" required>
                </div>

                <div class="challenge-form-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeProgressModal()">Cancel</button>
                    <button type="submit" class="btn-modal-submit">Update Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==============================
// FILTER TABS
// ==============================
function filterChallenges(tab, btn) {
    // Update active class
    document.querySelectorAll('.challenge-filter-tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter grid cards
    const cards = document.querySelectorAll('.challenge-card');
    cards.forEach(card => {
        const isJoined = card.dataset.joined === 'true';
        const isCompleted = card.dataset.completed === 'true';
        const status = card.dataset.status;

        if (tab === 'all') {
            card.style.display = 'flex';
        } else if (tab === 'joined') {
            card.style.display = isJoined ? 'flex' : 'none';
        } else if (tab === 'active') {
            card.style.display = (status === 'active' && !isCompleted) ? 'flex' : 'none';
        } else if (tab === 'completed') {
            card.style.display = isCompleted ? 'flex' : 'none';
        }
        
        if (card.style.display === 'flex') {
            card.style.animation = 'modalSlideIn 0.3s ease';
        }
    });
}

// ==============================
// JOIN CHALLENGE ACTION
// ==============================
function joinChallenge(challengeId) {
    const card = document.querySelector(`.challenge-card[data-id="${challengeId}"]`);
    const joinBtn = card ? card.querySelector('.btn-join-challenge') : null;
    if (joinBtn) {
        joinBtn.disabled = true;
        joinBtn.textContent = 'Joining...';
    }

    const formData = new FormData();
    formData.append('challenge_id', challengeId);

    fetch('../api/challenges/join.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showChallengeToast('success', '✅ ' + data.message);
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            showChallengeToast('error', '❌ ' + data.message);
            if (joinBtn) {
                joinBtn.disabled = false;
                joinBtn.innerHTML = '➕ Join Challenge';
            }
        }
    })
    .catch(err => {
        showChallengeToast('error', '❌ Terjadi kesalahan koneksi.');
        if (joinBtn) {
            joinBtn.disabled = false;
            joinBtn.innerHTML = '➕ Join Challenge';
        }
    });
}

// ==============================
// LOG PROGRESS MODAL
// ==============================
function openProgressModal(id, title, unit, current, goal) {
    document.getElementById('progress-challenge-id').value = id;
    document.getElementById('progress-challenge-title').textContent = title;
    document.getElementById('progress-current-status').textContent = `Current Progress: ${current.toLocaleString()} / ${goal.toLocaleString()} ${unit}`;
    document.getElementById('progress-value-label').textContent = `Add Activity Progress (${unit})`;
    document.getElementById('progress-value-input').value = '';
    document.getElementById('progress-value-input').placeholder = `e.g. 1000`;
    document.getElementById('progressModal').classList.add('show');
}

function closeProgressModal() {
    document.getElementById('progressModal').classList.remove('show');
}

function handleUpdateProgress(e) {
    e.preventDefault();
    const challengeId = document.getElementById('progress-challenge-id').value;
    const progressValue = document.getElementById('progress-value-input').value;
    const submitBtn = document.querySelector('#formUpdateProgress button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData();
    formData.append('challenge_id', challengeId);
    formData.append('progress_value', progressValue);

    fetch('../api/challenges/log_progress.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Progress';
        if (data.success) {
            showChallengeToast('success', '✅ ' + data.message);
            closeProgressModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showChallengeToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Progress';
        showChallengeToast('error', '❌ Terjadi kesalahan koneksi.');
    });
}

// ==============================
// TOAST NOTIFICATION
// ==============================
function showChallengeToast(type, message) {
    const old = document.querySelector('.challenge-toast');
    if (old) old.remove();

    const toast = document.createElement('div');
    toast.className = 'challenge-toast toast-' + type;
    toast.innerHTML = '<span>' + message + '</span>' +
        '<button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;font-size:16px;cursor:pointer;margin-left:8px;">&times;</button>';
    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4500);
}

// CLOSE MODAL ON OVERLAY CLICK
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('challenge-modal-overlay')) {
        e.target.classList.remove('show');
    }
});
</script>

<?php include "../layouts/footer.php"; ?>
