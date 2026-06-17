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
/** @var mysqli $conn */

// Retrieve collection ID
$collection_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($collection_id <= 0) {
    header("Location: collection_management.php");
    exit();
}

// Check if collection exists
$colQuery = mysqli_query($conn, "SELECT * FROM collections WHERE id = $collection_id");
if (mysqli_num_rows($colQuery) === 0) {
    header("Location: collection_management.php");
    exit();
}

$collection = mysqli_fetch_assoc($colQuery);

// Fetch assigned challenges
$assignedQuery = mysqli_query($conn, "SELECT c.* FROM collection_challenges cc 
                                     JOIN challenges c ON cc.challenge_id = c.id 
                                     WHERE cc.collection_id = $collection_id
                                     ORDER BY cc.created_at DESC");
$assignedChallenges = [];
$assignedIds = [];
if ($assignedQuery) {
    while ($row = mysqli_fetch_assoc($assignedQuery)) {
        $row['id'] = intval($row['id']);
        $row['goal_value'] = intval($row['goal_value']);
        $row['reward_points'] = intval($row['reward_points']);
        $assignedChallenges[] = $row;
        $assignedIds[] = $row['id'];
    }
}

// Fetch all available challenges (excluding drafts and those already assigned)
$notInClause = !empty($assignedIds) ? "AND id NOT IN (" . implode(',', $assignedIds) . ")" : "";
$availableQuery = mysqli_query($conn, "SELECT id, title, challenge_type, goal_type, goal_value, reward_points 
                                       FROM challenges 
                                       WHERE status != 'draft' $notInClause 
                                       ORDER BY created_at DESC");
$availableChallenges = [];
if ($availableQuery) {
    while ($row = mysqli_fetch_assoc($availableQuery)) {
        $row['id'] = intval($row['id']);
        $row['goal_value'] = intval($row['goal_value']);
        $row['reward_points'] = intval($row['reward_points']);
        $availableChallenges[] = $row;
    }
}

// Determine Banner Style
$bannerStyle = "";
if (!empty($collection['cover_image'])) {
    $bannerStyle = "background-image: url('../uploads/" . htmlspecialchars($collection['cover_image'], ENT_QUOTES) . "'); background-size: cover; background-position: center;";
} else {
    switch ($collection['difficulty']) {
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
?>

<link rel="stylesheet" href="../style/collection_management.css">
<style>
/* Additional specific styles for Detail View */
.detail-banner {
    width: 100%;
    max-width: 1000px;
    height: 180px;
    margin: 0 auto 30px;
    border-radius: 14px;
    display: flex;
    align-items: flex-end;
    padding: 24px 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.detail-banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.1) 100%);
    z-index: 1;
}
.detail-banner-content {
    color: #fff;
    z-index: 2;
    position: relative;
}
.detail-banner-content h1 {
    font-family: Inter, Arial, sans-serif;
    font-size: 1.55rem;
    font-weight: 700;
    margin: 0 0 6px 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.detail-banner-content p {
    font-family: Inter, Arial, sans-serif;
    font-size: 0.88rem;
    color: rgba(255,255,255,0.9);
    margin: 0 0 10px 0;
    max-width: 600px;
}
.detail-badges-row {
    display: flex;
    gap: 8px;
}
.detail-badge {
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 600;
}
.detail-badge.diff { background: rgba(255,255,255,0.25); color: #fff; border: 1px solid rgba(255,255,255,0.3); }
.detail-badge.dur { background: rgba(99, 106, 232, 0.85); color: #fff; }

.assigned-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1000px;
    margin: 0 auto 20px;
}
.assigned-section-header h2 {
    font-family: Inter, Arial, sans-serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a1a2e;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #fafafb;
    border: 1px solid #dee1e6;
    color: #565d6d;
    border-radius: 8px;
    font-family: Inter, Arial, sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-back:hover { background: #e5e7eb; }

.assigned-challenges-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    max-width: 1000px;
    margin: 0 auto 40px;
}

.challenge-card-assigned {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #eef2ff;
    box-shadow: 0 1px 4px rgba(23, 26, 31, 0.04);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 150px;
    transition: all 0.2s;
}
.challenge-card-assigned:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
.assigned-info h4 {
    margin: 0 0 6px 0;
    font-family: Inter, Arial, sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a1a2e;
}
.assigned-meta {
    font-family: Inter, Arial, sans-serif;
    font-size: 0.76rem;
    color: #6b7280;
    margin-bottom: 14px;
}
.assigned-meta div {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 4px;
}
.btn-remove-challenge {
    width: 100%;
    padding: 7px;
    background: #fef2f2;
    color: #dc2626;
    border: none;
    border-radius: 6px;
    font-family: Inter, Arial, sans-serif;
    font-weight: 600;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-remove-challenge:hover { background: #dc2626; color: #fff; }

/* Available Challenges Modal List */
.available-list-container {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #dee1e6;
    border-radius: 8px;
    padding: 8px;
    background: #fafafb;
}
.available-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.82rem;
}
.available-item:last-child { border-bottom: none; }
.available-item input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
.available-item-info {
    flex: 1;
}
.available-item-info strong {
    display: block;
    color: #1a1a2e;
}
.available-item-info span {
    font-size: 0.74rem;
    color: #6b7280;
}
</style>

<div class="main-content">

    <!-- ========== RETURN TO DASHBOARD ========== -->
    <div class="assigned-section-header" style="margin-bottom: 10px;">
        <a href="collection_management.php" class="btn-back">
            ← Back to Collections
        </a>
    </div>

    <!-- ========== COLLECTION DETAIL HERO ========== -->
    <div class="detail-banner" style="<?= $bannerStyle ?>">
        <div class="detail-banner-overlay"></div>
        <div class="detail-banner-content">
            <h1><?= htmlspecialchars($collection['name']) ?></h1>
            <p><?= htmlspecialchars($collection['description'] ?: 'No description provided.') ?></p>
            <div class="detail-badges-row">
                <span class="detail-badge diff">Difficulty: <?= htmlspecialchars(ucfirst($collection['difficulty'])) ?></span>
                <span class="detail-badge dur">Duration: <?= htmlspecialchars($collection['estimated_duration']) ?></span>
                <span class="detail-badge" style="background: <?= $collection['status'] === 'active' ? '#10b981' : '#dc2626' ?>; color: #fff;">
                    <?= htmlspecialchars(ucfirst($collection['status'])) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ========== SECTION HEADER & ACTIONS ========== -->
    <div class="assigned-section-header">
        <h2>Assigned Challenges (<?= count($assignedChallenges) ?>)</h2>
        <button class="btn-create-collection" onclick="openAddModal()">
            ➕ Add Challenge
        </button>
    </div>

    <!-- ========== ASSIGNED CHALLENGES GRID ========== -->
    <div class="assigned-challenges-grid">
        <?php if (!empty($assignedChallenges)): ?>
            <?php foreach ($assignedChallenges as $ch): 
                $goalUnit = '';
                switch ($ch['goal_type']) {
                    case 'steps': $goalUnit = 'steps'; break;
                    case 'distance': $goalUnit = 'km'; break;
                    case 'active_minutes': $goalUnit = 'mins'; break;
                }
            ?>
                <div class="challenge-card-assigned" data-ch-id="<?= $ch['id'] ?>">
                    <div class="assigned-info">
                        <h4><?= htmlspecialchars($ch['title']) ?></h4>
                        <div class="assigned-meta">
                            <div>📋 <span>Type: <?= htmlspecialchars(ucfirst($ch['challenge_type'])) ?></span></div>
                            <div>🎯 <span>Goal: <?= number_format($ch['goal_value']) ?> <?= $goalUnit ?></span></div>
                            <div>⭐ <span>Reward: +<?= $ch['reward_points'] ?> pts</span></div>
                        </div>
                    </div>
                    <button class="btn-remove-challenge" onclick="handleRemoveChallenge(<?= $ch['id'] ?>, '<?= htmlspecialchars($ch['title'], ENT_QUOTES) ?>')">
                        🗑️ Remove Challenge
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px 40px; color: #6b7280; font-size: 16px; background: #fff; border-radius: 12px; border: 1px dashed #dee1e6;">
                Belum ada tantangan yang dimasukkan ke koleksi ini. Klik "Add Challenge" untuk memasukkan.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========== ADD CHALLENGE MODAL ========== -->
<div id="addChallengeModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Challenges to Collection</h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formAddChallenges" onsubmit="handleAddChallengesSubmit(event)">
                <input type="hidden" name="collection_id" value="<?= $collection_id ?>">
                
                <div style="margin-bottom: 14px;">
                    <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Select one or more available challenges to add to <strong><?= htmlspecialchars($collection['name']) ?></strong>.</p>
                </div>

                <div class="available-list-container">
                    <?php if (!empty($availableChallenges)): ?>
                        <?php foreach ($availableChallenges as $av): 
                            $avUnit = '';
                            switch ($av['goal_type']) {
                                case 'steps': $avUnit = 'steps'; break;
                                case 'distance': $avUnit = 'km'; break;
                                case 'active_minutes': $avUnit = 'mins'; break;
                            }
                        ?>
                            <label class="available-item">
                                <input type="checkbox" name="challenge_ids[]" value="<?= $av['id'] ?>">
                                <div class="available-item-info">
                                    <strong><?= htmlspecialchars($av['title']) ?></strong>
                                    <span>Type: <?= htmlspecialchars(ucfirst($av['challenge_type'])) ?> • Goal: <?= number_format($av['goal_value']) ?> <?= $avUnit ?> • Reward: +<?= $av['reward_points'] ?> pts</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 0.82rem;">
                            Tidak ada tantangan lain yang tersedia.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn-modal-submit" id="btnAddSubmit" <?= empty($availableChallenges) ? 'disabled style="opacity:0.6;cursor:default;"' : '' ?>>
                        Add Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==============================
// MODAL CONTROLS
// ==============================
function openAddModal() {
    document.getElementById('formAddChallenges').reset();
    document.getElementById('addChallengeModal').classList.add('show');
}

function closeAddModal() {
    document.getElementById('addChallengeModal').classList.remove('show');
}

// ==============================
// ADD CHALLENGES SUBMISSION
// ==============================
function handleAddChallengesSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('btnAddSubmit');

    // Verify at least one is selected
    const checked = form.querySelectorAll('input[name="challenge_ids[]"]:checked');
    if (checked.length === 0) {
        showToast('error', '❌ Pilih minimal satu tantangan.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';

    const formData = new FormData(form);

    fetch('../api/collections/add_challenges.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Selected';
        if (data.success) {
            showToast('success', '✅ ' + data.message);
            closeAddModal();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Selected';
        showToast('error', '❌ Terjadi kesalahan koneksi.');
    });
}

// ==============================
// REMOVE CHALLENGE ACTION
// ==============================
function handleRemoveChallenge(challengeId, challengeName) {
    if (!confirm(`Yakin ingin menghapus tantangan "${challengeName}" dari koleksi ini?`)) {
        return;
    }

    const card = document.querySelector(`.challenge-card-assigned[data-ch-id="${challengeId}"]`);
    const removeBtn = card ? card.querySelector('.btn-remove-challenge') : null;
    if (removeBtn) {
        removeBtn.disabled = true;
        removeBtn.textContent = 'Removing...';
    }

    const formData = new FormData();
    formData.append('collection_id', <?= $collection_id ?>);
    formData.append('challenge_id', challengeId);

    fetch('../api/collections/remove_challenge.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '✅ ' + data.message);
            if (card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', '❌ ' + data.message);
            if (removeBtn) {
                removeBtn.disabled = false;
                removeBtn.textContent = '🗑️ Remove Challenge';
            }
        }
    })
    .catch(err => {
        showToast('error', '❌ Terjadi kesalahan koneksi.');
        if (removeBtn) {
            removeBtn.disabled = false;
            removeBtn.textContent = '🗑️ Remove Challenge';
        }
    });
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
