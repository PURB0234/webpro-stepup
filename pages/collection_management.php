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
require_once "../api/collections/get.php"; // Populates $collections

// Fetch Analytics Summary
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM collections");
$totalCollections = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

$activeQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM collections WHERE status = 'active'");
$activeCollections = mysqli_fetch_assoc($activeQuery)['total'] ?? 0;

$assignedQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM collection_challenges");
$totalAssigned = mysqli_fetch_assoc($assignedQuery)['total'] ?? 0;

$popularQuery = mysqli_query($conn, "SELECT c.name, COUNT(*) AS count 
                                     FROM user_collections uc 
                                     JOIN collections c ON uc.collection_id = c.id 
                                     GROUP BY uc.collection_id 
                                     ORDER BY count DESC 
                                     LIMIT 1");
$popularCol = mysqli_fetch_assoc($popularQuery);
$mostPopular = $popularCol ? $popularCol['name'] : 'None';
?>

<link rel="stylesheet" href="../style/collection_management.css">

<div class="main-content">

    <!-- ========== HERO BANNER ========== -->
    <div class="collections-hero">
        <div class="collections-hero-text">
            <h1>Curated Collections Management</h1>
            <p>Group walking challenges into curated training packs. Users can subscribe to collections and trace their journey milestones.</p>
            <button class="btn-hero-action" onclick="openCreateModal()">
                ➕ Create Collection
            </button>
        </div>
        <div class="collections-hero-image">
            <img class="hero-icon" src="../assets/icon/badge_icon/medal.png" alt="medals icon">
        </div>
    </div>

    <!-- ========== ANALYTICS SUMMARY ========== -->
    <div class="collections-analytics">
        <div class="analytics-card">
            <div class="analytics-icon icon-total">📚</div>
            <div class="analytics-info">
                <h3><?= number_format($totalCollections) ?></h3>
                <p>Total Collections</p>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon icon-active">🟢</div>
            <div class="analytics-info">
                <h3><?= number_format($activeCollections) ?></h3>
                <p>Active Collections</p>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon icon-assigned">📋</div>
            <div class="analytics-info">
                <h3><?= number_format($totalAssigned) ?></h3>
                <p>Total Assigned Challenges</p>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon icon-popular">⭐</div>
            <div class="analytics-info">
                <h3 style="font-size: 0.95rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="<?= htmlspecialchars($mostPopular) ?>">
                    <?= htmlspecialchars($mostPopular) ?>
                </h3>
                <p>Most Popular Collection</p>
            </div>
        </div>
    </div>

    <!-- ========== TOOLBAR CONTROLS ========== -->
    <div class="management-toolbar">
        <div class="toolbar-left">
            <div class="search-input-wrapper">
                <span class="search-icon-placeholder">🔍</span>
                <input type="text" id="searchCollections" placeholder="Search collection by name..." onkeyup="filterCollectionsGrid()">
            </div>
            <select id="filterStatus" class="filter-select" onchange="filterCollectionsGrid()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button class="btn-create-collection" onclick="openCreateModal()">
            ➕ Add Collection
        </button>
    </div>

    <!-- ========== COLLECTIONS GRID ========== -->
    <div class="collections-grid" id="collectionsGrid">
        <?php if (!empty($collections)): ?>
            <?php foreach ($collections as $col): 
                // Fallback banner styles
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
            ?>
                <div class="collection-card" 
                     data-name="<?= htmlspecialchars(strtolower($col['name'])) ?>" 
                     data-status="<?= htmlspecialchars($col['status']) ?>" 
                     data-id="<?= $col['id'] ?>">
                    
                    <div class="collection-banner" style="<?= $bannerStyle ?>">
                        <span class="badge-difficulty <?= strtolower($col['difficulty']) ?>"><?= htmlspecialchars(ucfirst($col['difficulty'])) ?></span>
                        <span class="badge-status <?= htmlspecialchars($col['status']) ?>"><?= htmlspecialchars(ucfirst($col['status'])) ?></span>
                    </div>

                    <div class="collection-body">
                        <h3><?= htmlspecialchars($col['name']) ?></h3>
                        <p class="collection-desc"><?= htmlspecialchars($col['description'] ?: 'No description provided.') ?></p>
                        
                        <div class="collection-meta">
                            <span>📋 <?= $col['total_challenges'] ?> Challenges Assigned</span>
                            <span>⏱️ <?= htmlspecialchars($col['estimated_duration'] ?: '-') ?></span>
                        </div>

                        <div class="collection-actions">
                            <a href="collection_details.php?id=<?= $col['id'] ?>" class="btn-card-view">
                                👁️ View & Manage
                            </a>
                            <button class="btn-card-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($col), ENT_QUOTES, 'UTF-8') ?>)">
                                ✏️ Edit
                            </button>
                            <button class="btn-card-delete" onclick="openDeleteModal(<?= $col['id'] ?>, <?= htmlspecialchars(json_encode($col['name']), ENT_QUOTES, 'UTF-8') ?>)">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280; font-size: 16px;">
                Belum ada koleksi tersedia. Klik "Add Collection" untuk menambahkan.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========== CREATE/EDIT COLLECTION MODAL ========== -->
<div id="collectionModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Create New Collection</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formCollection" enctype="multipart/form-data" onsubmit="handleFormSubmit(event)">
                <input type="hidden" id="col-id" name="id">

                <div class="form-group">
                    <label>Collection Name</label>
                    <input type="text" id="col-name" name="name" placeholder="Enter collection name" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="col-desc" name="description" placeholder="Describe this collection pack..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" id="col-cover" name="cover_image" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Difficulty</label>
                        <select id="col-difficulty" name="difficulty" required>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estimated Duration</label>
                        <input type="text" id="col-duration" name="estimated_duration" placeholder="e.g. 3 Weeks" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="col-status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-modal-submit" id="btnSubmitForm">Save Collection</button>
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
            <h4>Are you sure you want to delete this collection?</h4>
            <p id="deleteCollectionName">This action cannot be undone.</p>
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
// MODAL CONTROLS
// ==============================
function openCreateModal() {
    document.getElementById('formCollection').reset();
    document.getElementById('col-id').value = '';
    document.getElementById('modalTitle').textContent = 'Create New Collection';
    document.getElementById('btnSubmitForm').textContent = 'Save Collection';
    document.getElementById('collectionModal').classList.add('show');
}

function openEditModal(col) {
    document.getElementById('col-id').value = col.id;
    document.getElementById('col-name').value = col.name;
    document.getElementById('col-desc').value = col.description;
    document.getElementById('col-difficulty').value = col.difficulty;
    document.getElementById('col-duration').value = col.estimated_duration;
    document.getElementById('col-status').value = col.status;

    document.getElementById('modalTitle').textContent = 'Edit Collection';
    document.getElementById('btnSubmitForm').textContent = 'Update Collection';
    document.getElementById('collectionModal').classList.add('show');
}

function closeModal() {
    document.getElementById('collectionModal').classList.remove('show');
}

function openDeleteModal(id, name) {
    document.getElementById('delete-id').value = id;
    document.getElementById('deleteCollectionName').textContent = 
        'Delete "' + name + '"? This action cannot be undone.';
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// ==============================
// FILTER GRID
// ==============================
function filterCollectionsGrid() {
    const searchVal = document.getElementById('searchCollections').value.toLowerCase().trim();
    const statusVal = document.getElementById('filterStatus').value;

    const cards = document.querySelectorAll('#collectionsGrid .collection-card');
    cards.forEach(card => {
        const name = card.dataset.name;
        const status = card.dataset.status;

        const matchesSearch = name.includes(searchVal);
        const matchesStatus = (statusVal === 'all') || (status === statusVal);

        if (matchesSearch && matchesStatus) {
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
    const colId = document.getElementById('col-id').value;
    const submitBtn = document.getElementById('btnSubmitForm');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const formData = new FormData(form);
    
    // Select correct API url based on Create or Edit
    const url = colId ? '../api/collections/update.php' : '../api/collections/post.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = colId ? 'Update Collection' : 'Save Collection';
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
        submitBtn.textContent = colId ? 'Update Collection' : 'Save Collection';
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

    fetch('../api/collections/delete.php', {
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
