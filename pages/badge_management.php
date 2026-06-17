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

// Fetch active challenges for modal dropdown
$chalQuery = mysqli_query($conn, "SELECT id, title FROM challenges WHERE status = 'active' ORDER BY title ASC");
$challenges = [];
if ($chalQuery) {
    while ($row = mysqli_fetch_assoc($chalQuery)) {
        $challenges[] = $row;
    }
}

// Fetch active collections for modal dropdown
$colQuery = mysqli_query($conn, "SELECT id, name FROM collections WHERE status = 'active' ORDER BY name ASC");
$collections = [];
if ($colQuery) {
    while ($row = mysqli_fetch_assoc($colQuery)) {
        $collections[] = $row;
    }
}

// Fetch badges with relations
$badgeQuery = mysqli_query($conn, "SELECT b.*, 
                                          c.title AS related_challenge_title, 
                                          col.name AS related_collection_name,
                                          COALESCE(ub.earned_count, 0) AS earned_count
                                   FROM badges b
                                   LEFT JOIN challenges c ON b.related_challenge_id = c.id
                                   LEFT JOIN collections col ON b.related_collection_id = col.id
                                   LEFT JOIN (
                                       SELECT badge_id, COUNT(*) AS earned_count
                                       FROM user_badges
                                       GROUP BY badge_id
                                   ) ub ON b.id = ub.badge_id
                                   ORDER BY b.created_at DESC");
$badges = [];
if ($badgeQuery) {
    while ($row = mysqli_fetch_assoc($badgeQuery)) {
        $row['id'] = intval($row['id']);
        $row['related_challenge_id'] = $row['related_challenge_id'] ? intval($row['related_challenge_id']) : null;
        $row['related_collection_id'] = $row['related_collection_id'] ? intval($row['related_collection_id']) : null;
        $row['earned_count'] = intval($row['earned_count']);
        $badges[] = $row;
    }
}

// Analytics Calculations
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM badges");
$totalBadges = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

$activeQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM badges WHERE status = 'active'");
$activeBadgesCount = mysqli_fetch_assoc($activeQuery)['total'] ?? 0;

// Most Earned Badge
$mostQuery = mysqli_query($conn, "SELECT b.name, COUNT(*) AS count 
                                  FROM user_badges ub 
                                  JOIN badges b ON ub.badge_id = b.id 
                                  GROUP BY ub.badge_id 
                                  ORDER BY count DESC 
                                  LIMIT 1");
$mostData = mysqli_fetch_assoc($mostQuery);
$mostEarnedBadge = $mostData ? $mostData['name'] : 'None';

// Rarest Badge (Unlocked by least users but at least 1)
$rareQuery = mysqli_query($conn, "SELECT b.name, COUNT(*) AS count 
                                  FROM user_badges ub 
                                  JOIN badges b ON ub.badge_id = b.id 
                                  GROUP BY ub.badge_id 
                                  ORDER BY count ASC 
                                  LIMIT 1");
$rareData = mysqli_fetch_assoc($rareQuery);
$rarestBadge = $rareData ? $rareData['name'] : 'None';
?>

<link rel="stylesheet" href="../style/badge_management.css">

<div class="main-content">

    <!-- ========== HERO BANNER ========== -->
    <div class="badges-hero">
        <div class="badges-hero-text">
            <h1>Badge Management</h1>
            <p>Manage achievement badges available throughout the StepUp platform. Link badges to specific challenges or user milestones.</p>
            <button class="btn-hero-action" onclick="openCreateModal()">
                ➕ Create Badge
            </button>
        </div>
        <div class="badges-hero-image">
            <img class="hero-icon" src="../assets/icon/badge_icon/medal.png" alt="badge icon">
        </div>
    </div>

    <!-- ========== ANALYTICS SUMMARY ========== -->
    <div class="badges-analytics">
        <div class="analytics-card">
            <div class="analytics-icon icon-total">🎖️</div>
            <div class="analytics-info">
                <h3><?= number_format($totalBadges) ?></h3>
                <p>Total Badges</p>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon icon-active">🟢</div>
            <div class="analytics-info">
                <h3><?= number_format($activeBadgesCount) ?></h3>
                <p>Active Badges</p>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon icon-most">🔥</div>
            <div class="analytics-info">
                <h3 style="font-size: 0.95rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="<?= htmlspecialchars($mostEarnedBadge) ?>">
                    <?= htmlspecialchars($mostEarnedBadge) ?>
                </h3>
                <p>Most Earned Badge</p>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon icon-rare">💎</div>
            <div class="analytics-info">
                <h3 style="font-size: 0.95rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="<?= htmlspecialchars($rarestBadge) ?>">
                    <?= htmlspecialchars($rarestBadge) ?>
                </h3>
                <p>Rarest Badge</p>
            </div>
        </div>
    </div>

    <!-- ========== TOOLBAR CONTROLS ========== -->
    <div class="management-toolbar">
        <div class="toolbar-left">
            <div class="search-input-wrapper">
                <span class="search-icon-placeholder">🔍</span>
                <input type="text" id="searchBadges" placeholder="Search badge by name..." onkeyup="filterBadgesGrid()">
            </div>
            <select id="filterCategory" class="filter-select" onchange="filterBadgesGrid()">
                <option value="all">All Categories</option>
                <option value="challenge">Challenge</option>
                <option value="collection">Collection</option>
                <option value="activity">Activity</option>
                <option value="streak">Streak</option>
                <option value="special_event">Special Event</option>
            </select>
            <select id="filterStatus" class="filter-select" onchange="filterBadgesGrid()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button class="btn-create-badge" onclick="openCreateModal()">
            ➕ Add Badge
        </button>
    </div>

    <!-- ========== BADGES GRID ========== -->
    <div class="badges-grid" id="badgesGrid">
        <?php if (!empty($badges)): ?>
            <?php foreach ($badges as $b): 
                $iconPath = !empty($b['badge_icon']) ? "../uploads/badge_icons/" . htmlspecialchars($b['badge_icon']) : "../assets/icon/badge_icon/medal.png";
                $rarityLabel = ucfirst($b['rarity']);
                $categoryLabel = ucfirst(str_replace('_', ' ', $b['category']));
            ?>
                <div class="badge-card" 
                     data-name="<?= htmlspecialchars(strtolower($b['name'])) ?>" 
                     data-category="<?= htmlspecialchars($b['category']) ?>" 
                     data-status="<?= htmlspecialchars($b['status']) ?>" 
                     data-id="<?= $b['id'] ?>">
                    
                    <div class="badge-banner">
                        <img src="<?= $iconPath ?>" class="badge-grid-icon" alt="<?= htmlspecialchars($b['name']) ?>">
                        <span class="badge-rarity <?= strtolower($b['rarity']) ?>"><?= $rarityLabel ?></span>
                        <span class="badge-status-tag <?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars(ucfirst($b['status'])) ?></span>
                    </div>

                    <div class="badge-body">
                        <h3><?= htmlspecialchars($b['name']) ?></h3>
                        <p class="badge-desc"><?= htmlspecialchars($b['description'] ?: 'No description.') ?></p>
                        
                        <div class="badge-meta">
                            <span>📂 <?= $categoryLabel ?></span>
                            <span>👥 <?= $b['earned_count'] ?> Earners</span>
                        </div>

                        <div class="badge-actions">
                            <button class="btn-card-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                                ✏️ Edit
                            </button>
                            <button class="btn-card-delete" onclick="openDeleteModal(<?= $b['id'] ?>, <?= htmlspecialchars(json_encode($b['name']), ENT_QUOTES, 'UTF-8') ?>)">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280; font-size: 16px;">
                Belum ada badge tersedia. Klik "Add Badge" untuk menambahkan.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========== CREATE/EDIT BADGE MODAL ========== -->
<div id="badgeModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Create New Badge</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formBadge" enctype="multipart/form-data" onsubmit="handleFormSubmit(event)">
                <input type="hidden" id="badge-id" name="id">

                <div class="form-group">
                    <label>Badge Name</label>
                    <input type="text" id="badge-name" name="name" placeholder="e.g. 10K Steps Master" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="badge-desc" name="description" placeholder="Describe the achievement accomplishment..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Badge Icon</label>
                    <input type="file" id="badge-icon-file" name="badge_icon" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select id="badge-category" name="category" onchange="toggleRelationFields()" required>
                            <option value="challenge">Challenge</option>
                            <option value="collection">Collection</option>
                            <option value="activity">Activity</option>
                            <option value="streak">Streak</option>
                            <option value="special_event">Special Event</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rarity</label>
                        <select id="badge-rarity" name="rarity" required>
                            <option value="common">Common</option>
                            <option value="rare">Rare</option>
                            <option value="epic">Epic</option>
                            <option value="legendary">Legendary</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Unlock Requirement</label>
                    <input type="text" id="badge-requirement" name="unlock_requirement" placeholder="e.g. Walk 10,000 steps" required>
                </div>

                <!-- Related Challenge (Visible for Challenge category) -->
                <div class="form-group" id="challenge-relation-group">
                    <label>Related Challenge (Optional)</label>
                    <select id="badge-challenge-id" name="related_challenge_id">
                        <option value="">-- Select Challenge --</option>
                        <?php foreach ($challenges as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Related Collection (Visible for Collection category) -->
                <div class="form-group" id="collection-relation-group">
                    <label>Related Collection (Optional)</label>
                    <select id="badge-collection-id" name="related_collection_id">
                        <option value="">-- Select Collection --</option>
                        <?php foreach ($collections as $col): ?>
                            <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="badge-status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-modal-submit" id="btnSubmitForm">Save Badge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== DELETE CONFIRMATION MODAL ========== -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box delete-modal">
        <div class="modal-body">
            <div class="delete-modal-icon">🗑️</div>
            <h4>Are you sure you want to delete this badge?</h4>
            <p id="deleteBadgeName">This action cannot be undone.</p>
            <input type="hidden" id="delete-id">
            <div class="modal-actions" style="justify-content: center; border-top: none; padding-top: 0;">
                <button class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-delete-confirm" onclick="handleDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
// ==============================
// MODAL CONTROLS & DYNAMIC FIELDS
// ==============================
function toggleRelationFields() {
    const category = document.getElementById('badge-category').value;
    const chalGroup = document.getElementById('challenge-relation-group');
    const colGroup = document.getElementById('collection-relation-group');

    if (category === 'challenge') {
        chalGroup.style.display = 'block';
        colGroup.style.display = 'none';
        document.getElementById('badge-collection-id').value = '';
    } else if (category === 'collection') {
        chalGroup.style.display = 'none';
        colGroup.style.display = 'block';
        document.getElementById('badge-challenge-id').value = '';
    } else {
        chalGroup.style.display = 'none';
        colGroup.style.display = 'none';
        document.getElementById('badge-challenge-id').value = '';
        document.getElementById('badge-collection-id').value = '';
    }
}

function openCreateModal() {
    document.getElementById('formBadge').reset();
    document.getElementById('badge-id').value = '';
    document.getElementById('modalTitle').textContent = 'Create New Badge';
    document.getElementById('btnSubmitForm').textContent = 'Save Badge';
    
    toggleRelationFields();
    document.getElementById('badgeModal').classList.add('show');
}

function openEditModal(badge) {
    document.getElementById('badge-id').value = badge.id;
    document.getElementById('badge-name').value = badge.name;
    document.getElementById('badge-desc').value = badge.description;
    document.getElementById('badge-category').value = badge.category;
    document.getElementById('badge-rarity').value = badge.rarity;
    document.getElementById('badge-requirement').value = badge.unlock_requirement;
    document.getElementById('badge-challenge-id').value = badge.related_challenge_id || '';
    document.getElementById('badge-collection-id').value = badge.related_collection_id || '';
    document.getElementById('badge-status').value = badge.status;

    document.getElementById('modalTitle').textContent = 'Edit Badge';
    document.getElementById('btnSubmitForm').textContent = 'Update Badge';

    toggleRelationFields();
    document.getElementById('badgeModal').classList.add('show');
}

function closeModal() {
    document.getElementById('badgeModal').classList.remove('show');
}

function openDeleteModal(id, name) {
    document.getElementById('delete-id').value = id;
    document.getElementById('deleteBadgeName').textContent = 
        'Delete "' + name + '"? This action cannot be undone.';
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// ==============================
// FILTER GRID
// ==============================
function filterBadgesGrid() {
    const searchVal = document.getElementById('searchBadges').value.toLowerCase().trim();
    const categoryVal = document.getElementById('filterCategory').value;
    const statusVal = document.getElementById('filterStatus').value;

    const cards = document.querySelectorAll('#badgesGrid .badge-card');
    cards.forEach(card => {
        const name = card.dataset.name;
        const category = card.dataset.category;
        const status = card.dataset.status;

        const matchesSearch = name.includes(searchVal);
        const matchesCategory = (categoryVal === 'all') || (category === categoryVal);
        const matchesStatus = (statusVal === 'all') || (status === statusVal);

        if (matchesSearch && matchesCategory && matchesStatus) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// ==============================
// AJAX SUBMISSIONS
// ==============================
function handleFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const badgeId = document.getElementById('badge-id').value;
    const submitBtn = document.getElementById('btnSubmitForm');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData(form);
    
    // Select correct API url based on Create or Edit
    const url = badgeId ? '../api/badges/update.php' : '../api/badges/post.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = badgeId ? 'Update Badge' : 'Save Badge';
        if (data.success) {
            showToast('success', '✅ ' + data.message);
            closeModal();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = badgeId ? 'Update Badge' : 'Save Badge';
        showToast('error', '❌ Terjadi kesalahan koneksi.');
    });
}

function handleDelete() {
    const id = document.getElementById('delete-id').value;
    const deleteBtn = document.querySelector('.btn-delete-confirm');

    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';

    const formData = new FormData();
    formData.append('id', id);

    fetch('../api/badges/delete.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
        if (data.success) {
            showToast('success', '✅ ' + data.message);
            closeDeleteModal();
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', '❌ ' + data.message);
        }
    })
    .catch(err => {
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
        showToast('error', '❌ Terjadi kesalahan koneksi.');
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
