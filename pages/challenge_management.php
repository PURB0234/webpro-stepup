<?php include "../layouts/header.php"; ?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<?php
// Proteksi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}

require_once "../services/koneksi.php";
require_once "../api/challenges/get.php"; // Populates $challenges

// Fetch Analytics
$totalChallengesQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM challenges");
$totalChallenges = mysqli_fetch_assoc($totalChallengesQuery)['total'] ?? 0;

$activeParticipantsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM challenge_participants WHERE completion_status = 'in_progress'");
$activeParticipants = mysqli_fetch_assoc($activeParticipantsQuery)['total'] ?? 0;

$completedChallengesQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM challenges WHERE status = 'completed'");
$completedChallenges = mysqli_fetch_assoc($completedChallengesQuery)['total'] ?? 0;

$rewardsDistributedQuery = mysqli_query($conn, "SELECT COALESCE(SUM(c.reward_points), 0) AS total FROM challenge_participants cp JOIN challenges c ON cp.challenge_id = c.id WHERE cp.completion_status = 'completed'");
$rewardsDistributed = mysqli_fetch_assoc($rewardsDistributedQuery)['total'] ?? 0;

// Helper to determine time label
function getChallengeTimeLabel($status, $startDate, $endDate) {
    if ($status === 'archived' || $status === 'completed') {
        return 'Ended';
    }
    if ($status === 'draft') {
        return 'Not started';
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

<link rel="stylesheet" href="../style/challenge_management.css">

<div class="main-content">

    <!-- ========== HERO BANNER ========== -->
    <div class="challenge-hero">
        <div class="challenge-hero-text">
            <h1>Challenge Management Center</h1>
            <p>Manage all active, upcoming, and archived challenges from a single dashboard.</p>
            <div class="challenge-hero-actions">
                <button class="btn-challenge-primary" onclick="openCreateModal()">
                    <img src="../assets/icon/plus.png" alt="add" style="width:14px;height:14px;">
                    Create Challenge
                </button>
                <button class="btn-challenge-secondary" onclick="scrollToAnalytics()">
                    <img src="../assets/icon/chart-2.png" alt="analytics" style="width:14px;height:14px;filter:brightness(10);">
                    View Analytics
                </button>
            </div>
        </div>
        <div class="challenge-hero-image">
            <img class="hero-icon" src="../assets/icon/dumbel.png" alt="challenge icon">
        </div>
    </div>

    <!-- ========== SECTION HEADER ========== -->
    <div class="challenge-section-header">
        <h2>All Challenges</h2>
        <div class="challenge-filter-tabs">
            <button class="active" onclick="filterChallenges('all', this)">All</button>
            <button onclick="filterChallenges('active', this)">Active</button>
            <button onclick="filterChallenges('draft', this)">Draft</button>
            <button onclick="filterChallenges('archived', this)">Archived</button>
        </div>
    </div>

    <!-- ========== CHALLENGE CARDS GRID ========== -->
    <div class="challenge-cards-grid" id="challengeGrid">
        <?php if (!empty($challenges)): ?>
            <?php foreach ($challenges as $challenge): 
                $progressPercent = $challenge['participant_count'] > 0 ? round(($challenge['completed_count'] / $challenge['participant_count']) * 100) : 0;
                
                $bannerStyle = "";
                if (!empty($challenge['banner_image'])) {
                    $bannerStyle = "background-image: url('../uploads/" . htmlspecialchars($challenge['banner_image'], ENT_QUOTES) . "'); background-size: cover; background-position: center;";
                } else {
                    // Fallback gradients based on challenge type
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
            ?>
                <div class="challenge-card" data-status="<?= htmlspecialchars($challenge['status']) ?>" data-id="<?= $challenge['id'] ?>">
                    <div class="challenge-card-banner" style="<?= $bannerStyle ?>">
                        <span class="card-badge-type"><?= htmlspecialchars(ucfirst($challenge['challenge_type'])) ?></span>
                        <span class="card-status status-<?= htmlspecialchars($challenge['status']) ?>"><?= htmlspecialchars(ucfirst($challenge['status'])) ?></span>
                    </div>
                    <div class="challenge-card-body">
                        <h4><?= htmlspecialchars($challenge['title']) ?></h4>
                        <div class="challenge-card-meta">
                            <span>
                                <img src="../assets/icon/circular-alarm-clock-tool.png" alt="time" style="width:13px;height:13px;">
                                <?= htmlspecialchars($timeLabel) ?>
                            </span>
                            <span>
                                <img src="../assets/icon/person.png" alt="users" style="width:13px;height:13px;">
                                <?= $challenge['participant_count'] ?> participant<?= $challenge['participant_count'] != 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <div class="challenge-progress-bar">
                            <div class="challenge-progress-fill" style="width: <?= $progressPercent ?>%;"></div>
                        </div>
                        <div class="challenge-card-actions">
                            <button class="btn-edit-challenge" onclick="openEditModal(
                                <?= $challenge['id'] ?>, 
                                '<?= htmlspecialchars($challenge['title'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($challenge['description'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($challenge['challenge_type'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($challenge['goal_type'], ENT_QUOTES) ?>', 
                                <?= $challenge['goal_value'] ?>, 
                                <?= $challenge['reward_points'] ?>, 
                                '<?= htmlspecialchars($challenge['badge_reward'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($challenge['start_date'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($challenge['end_date'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($challenge['status'], ENT_QUOTES) ?>'
                            )">
                                ✏️ Edit
                            </button>
                            <button class="btn-delete-challenge" onclick="openDeleteModal(<?= $challenge['id'] ?>, '<?= htmlspecialchars($challenge['title'], ENT_QUOTES) ?>')">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280; font-size: 16px;">
                Belum ada challenge tersedia. Klik "Create Challenge" untuk menambahkan.
            </div>
        <?php endif; ?>
    </div>

    <!-- ========== ANALYTICS SECTION ========== -->
    <div id="analyticsSection">
        <div class="challenge-section-header">
            <h2>Challenge Analytics</h2>
        </div>

        <div class="challenge-analytics">
            <div class="analytics-card">
                <div class="analytics-icon icon-total">
                    <img src="../assets/icon/dumbel.png" alt="total">
                </div>
                <div class="analytics-info">
                    <h3><?= number_format($totalChallenges) ?></h3>
                    <p>Total Challenges</p>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-icon icon-active">
                    <img src="../assets/icon/person.png" alt="participants">
                </div>
                <div class="analytics-info">
                    <h3><?= number_format($activeParticipants) ?></h3>
                    <p>Active Participants</p>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-icon icon-completed">
                    <img src="../assets/icon/cup.png" alt="completed">
                </div>
                <div class="analytics-info">
                    <h3><?= number_format($completedChallenges) ?></h3>
                    <p>Completed Challenges</p>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-icon icon-rewards">
                    <img src="../assets/icon/gift.png" alt="rewards">
                </div>
                <div class="analytics-info">
                    <h3><?= number_format($rewardsDistributed) ?></h3>
                    <p>Reward Points Distributed</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ========== CREATE CHALLENGE MODAL ========== -->
<div id="createChallengeModal" class="challenge-modal-overlay">
    <div class="challenge-modal">
        <div class="challenge-modal-header">
            <h3>Create New Challenge</h3>
            <button class="challenge-modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="challenge-modal-body">
            <form id="formCreateChallenge" enctype="multipart/form-data" onsubmit="handleCreate(event)">

                <div class="challenge-form-group">
                    <label>Challenge Name</label>
                    <input type="text" id="create-name" name="title" placeholder="Enter challenge name" required>
                </div>

                <div class="challenge-form-group">
                    <label>Description</label>
                    <textarea id="create-desc" name="description" placeholder="Describe the challenge..." required></textarea>
                </div>

                <div class="challenge-form-group">
                    <label>Banner Upload</label>
                    <input type="file" id="create-banner" name="banner_image" accept="image/*">
                </div>

                <div class="challenge-form-row">
                    <div class="challenge-form-group">
                        <label>Challenge Type</label>
                        <select id="create-type" name="challenge_type" required>
                            <option value="">Select type</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="team">Team</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    <div class="challenge-form-group">
                        <label>Goal Type</label>
                        <select id="create-goal-type" name="goal_type" required>
                            <option value="">Select goal</option>
                            <option value="steps">Steps</option>
                            <option value="distance">Distance (km)</option>
                            <option value="active_minutes">Active Minutes</option>
                        </select>
                    </div>
                </div>

                <div class="challenge-form-row">
                    <div class="challenge-form-group">
                        <label>Goal Value</label>
                        <input type="number" id="create-goal-value" name="goal_value" placeholder="e.g. 50000" required>
                    </div>
                    <div class="challenge-form-group">
                        <label>Reward Points</label>
                        <input type="number" id="create-reward-points" name="reward_points" placeholder="e.g. 200" required>
                    </div>
                </div>

                <div class="challenge-form-group">
                    <label>Badge Reward</label>
                    <input type="text" id="create-badge" name="badge_reward" placeholder="e.g. Gold Walker">
                </div>

                <div class="challenge-form-row">
                    <div class="challenge-form-group">
                        <label>Start Date</label>
                        <input type="date" id="create-start-date" name="start_date" required>
                    </div>
                    <div class="challenge-form-group">
                        <label>End Date</label>
                        <input type="date" id="create-end-date" name="end_date" required>
                    </div>
                </div>

                <div class="challenge-form-group">
                    <label>Status</label>
                    <select id="create-status" name="status" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div class="challenge-form-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="btn-modal-save">Save Challenge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== EDIT CHALLENGE MODAL ========== -->
<div id="editChallengeModal" class="challenge-modal-overlay">
    <div class="challenge-modal">
        <div class="challenge-modal-header">
            <h3>Edit Challenge</h3>
            <button class="challenge-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="challenge-modal-body">
            <form id="formEditChallenge" enctype="multipart/form-data" onsubmit="handleEdit(event)">
                <input type="hidden" id="edit-id" name="id">

                <div class="challenge-form-group">
                    <label>Challenge Name</label>
                    <input type="text" id="edit-name" name="title" placeholder="Enter challenge name" required>
                </div>

                <div class="challenge-form-group">
                    <label>Description</label>
                    <textarea id="edit-desc" name="description" placeholder="Describe the challenge..." required></textarea>
                </div>

                <div class="challenge-form-group">
                    <label>Banner Upload (leave empty to keep current)</label>
                    <input type="file" id="edit-banner" name="banner_image" accept="image/*">
                </div>

                <div class="challenge-form-row">
                    <div class="challenge-form-group">
                        <label>Challenge Type</label>
                        <select id="edit-type" name="challenge_type" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="team">Team</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    <div class="challenge-form-group">
                        <label>Goal Type</label>
                        <select id="edit-goal-type" name="goal_type" required>
                            <option value="steps">Steps</option>
                            <option value="distance">Distance (km)</option>
                            <option value="active_minutes">Active Minutes</option>
                        </select>
                    </div>
                </div>

                <div class="challenge-form-row">
                    <div class="challenge-form-group">
                        <label>Goal Value</label>
                        <input type="number" id="edit-goal-value" name="goal_value" placeholder="e.g. 50000" required>
                    </div>
                    <div class="challenge-form-group">
                        <label>Reward Points</label>
                        <input type="number" id="edit-reward-points" name="reward_points" placeholder="e.g. 200" required>
                    </div>
                </div>

                <div class="challenge-form-group">
                    <label>Badge Reward</label>
                    <input type="text" id="edit-badge" name="badge_reward" placeholder="e.g. Gold Walker">
                </div>

                <div class="challenge-form-row">
                    <div class="challenge-form-group">
                        <label>Start Date</label>
                        <input type="date" id="edit-start-date" name="start_date" required>
                    </div>
                    <div class="challenge-form-group">
                        <label>End Date</label>
                        <input type="date" id="edit-end-date" name="end_date" required>
                    </div>
                </div>

                <div class="challenge-form-group">
                    <label>Status</label>
                    <select id="edit-status" name="status" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div class="challenge-form-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-modal-save">Update Challenge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== DELETE CONFIRMATION MODAL ========== -->
<div id="deleteChallengeModal" class="challenge-modal-overlay">
    <div class="challenge-modal delete-modal">
        <div class="challenge-modal-body">
            <div class="delete-modal-icon">🗑️</div>
            <h4>Are you sure you want to delete this challenge?</h4>
            <p id="deleteChallengeName">This action cannot be undone.</p>
            <input type="hidden" id="delete-id">
            <div class="delete-modal-actions">
                <button class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-modal-delete" onclick="handleDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
// ==============================
// MODAL HANDLERS
// ==============================

function openCreateModal() {
    document.getElementById('formCreateChallenge').reset();
    document.getElementById('createChallengeModal').classList.add('show');
}

function closeCreateModal() {
    document.getElementById('createChallengeModal').classList.remove('show');
}

function openEditModal(id, name, desc, type, goalType, goalValue, rewardPoints, badge, startDate, endDate, status) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-desc').value = desc;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-goal-type').value = goalType;
    document.getElementById('edit-goal-value').value = goalValue;
    document.getElementById('edit-reward-points').value = rewardPoints;
    document.getElementById('edit-badge').value = badge;
    document.getElementById('edit-start-date').value = startDate;
    document.getElementById('edit-end-date').value = endDate;
    document.getElementById('edit-status').value = status;
    document.getElementById('editChallengeModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editChallengeModal').classList.remove('show');
}

function openDeleteModal(id, name) {
    document.getElementById('delete-id').value = id;
    document.getElementById('deleteChallengeName').textContent = 
        'Delete "' + name + '"? This action cannot be undone.';
    document.getElementById('deleteChallengeModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteChallengeModal').classList.remove('show');
}

// ==============================
// FORM SUBMISSIONS (AJAX Integration)
// ==============================

function handleCreate(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData(form);

    fetch('../api/challenges/post.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Challenge';
        if (data.success) {
            showChallengeToast('success', '✅ ' + data.message);
            closeCreateModal();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showChallengeToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Challenge';
        showChallengeToast('error', '❌ Terjadi kesalahan koneksi.');
    });
}

function handleEdit(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';

    const formData = new FormData(form);

    fetch('../api/challenges/update.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Challenge';
        if (data.success) {
            showChallengeToast('success', '✅ ' + data.message);
            closeEditModal();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showChallengeToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Challenge';
        showChallengeToast('error', '❌ Terjadi kesalahan koneksi.');
    });
}

function handleDelete() {
    const id = document.getElementById('delete-id').value;
    const deleteBtn = document.querySelector('.btn-modal-delete');
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';

    const formData = new FormData();
    formData.append('id', id);

    fetch('../api/challenges/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
        if (data.success) {
            const card = document.querySelector('.challenge-card[data-id="' + id + '"]');
            if (card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }
            showChallengeToast('success', '✅ ' + data.message);
            closeDeleteModal();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showChallengeToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
        showChallengeToast('error', '❌ Terjadi kesalahan koneksi.');
    });
}

// ==============================
// FILTER TABS
// ==============================

function filterChallenges(status, btn) {
    // Update active tab
    document.querySelectorAll('.challenge-filter-tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter cards
    const cards = document.querySelectorAll('.challenge-card');
    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = '';
            card.style.animation = 'modalSlideIn 0.3s ease';
        } else {
            card.style.display = 'none';
        }
    });
}

// ==============================
// SCROLL TO ANALYTICS
// ==============================

function scrollToAnalytics() {
    document.getElementById('analyticsSection').scrollIntoView({ behavior: 'smooth' });
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
    }, 4000);
}

// ==============================
// CLOSE MODAL ON OVERLAY CLICK
// ==============================

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('challenge-modal-overlay')) {
        e.target.classList.remove('show');
    }
});
</script>

<?php include "../layouts/footer.php"; ?>
