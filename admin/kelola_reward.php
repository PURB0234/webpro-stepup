<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>
<?php require "../api/rewards/get.php"; ?>

<?php
// Proteksi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}
?>

<div class="main-content">
    <div class="kelola-reward-container">

        <div class="kelola-reward-header">
            <h2>🎁 Reward Management</h2>
            <a href="../admin/tambah_reward.php" class="btn-add-reward">
                <img class="icon" src="../assets/icon/plus.png" alt="add">
                Tambah Reward
            </a>
        </div>

        <div class="kelola-reward-table-wrapper">
            <table class="kelola-reward-table" border="0" cellpadding="10" cellspacing="0" width="100%">
                <thead class="row-header">
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama Reward</th>
                        <th>Deskripsi</th>
                        <th>Poin</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody class="row-body">
                    <?php if (!empty($rewards)): ?>
                        <?php foreach ($rewards as $reward): ?>
                            <tr id="row-reward-<?= $reward['id_reward'] ?>">
                                <td><?= $reward['id_reward']; ?></td>
                                <td>
                                    <img 
                                        src="../uploads/<?= $reward['gambar']; ?>" 
                                        alt="reward" 
                                        class="reward-thumb"
                                        id="thumb-<?= $reward['id_reward'] ?>">
                                </td>
                                <td class="td-name"><?= htmlspecialchars($reward['name_reward']); ?></td>
                                <td class="td-desc"><?= htmlspecialchars($reward['description']); ?></td>
                                <td class="td-poin"><?= $reward['poin']; ?></td>
                                <td class="td-stok"><?= isset($reward['stok']) ? $reward['stok'] : 0; ?></td>
                                <td class="td-aksi">
                                    <button 
                                        class="btn-edit-reward" 
                                        onclick="openEditModal(<?= $reward['id_reward'] ?>, '<?= htmlspecialchars($reward['name_reward'], ENT_QUOTES) ?>', '<?= htmlspecialchars($reward['description'], ENT_QUOTES) ?>', <?= $reward['poin'] ?>, <?= isset($reward['stok']) ? $reward['stok'] : 0 ?>, '<?= $reward['gambar'] ?>')">
                                        ✏️ Edit
                                    </button>
                                    <form action="../api/rewards/delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus reward ini?');">
                                        <input type="hidden" name="id_reward" value="<?= $reward['id_reward']; ?>">
                                        <button type="submit" class="btn-delete-reward">🗑️ Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" align="center" style="padding: 30px; color: #999;">
                                Belum ada reward tersedia
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- ======= MODAL EDIT REWARD ======= -->
<div id="edit-reward-modal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-edit-reward">
        <div class="modal-header">
            <h3>✏️ Edit Reward</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formEditReward" enctype="multipart/form-data">
                <input type="hidden" name="id_reward" id="edit-id-reward">

                <div class="edit-form-group">
                    <label>Nama Reward</label>
                    <input type="text" name="name_reward" id="edit-name-reward" class="edit-input" required>
                </div>

                <div class="edit-form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" id="edit-description" class="edit-textarea" required></textarea>
                </div>

                <div class="edit-form-row">
                    <div class="edit-form-group">
                        <label>Poin</label>
                        <input type="number" name="poin" id="edit-poin" class="edit-input" required>
                    </div>
                    <div class="edit-form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" id="edit-stok" class="edit-input" min="0" required>
                    </div>
                </div>

                <div class="edit-form-group">
                    <label>Gambar (kosongkan jika tidak ingin ganti)</label>
                    <div class="edit-gambar-preview">
                        <img id="edit-gambar-preview" src="" alt="preview" style="display:none;">
                    </div>
                    <input type="file" name="gambar" id="edit-gambar" class="edit-file" accept="image/*">
                </div>

                <div class="edit-form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn-save" id="btn-save-edit">💾 Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==========================
// EDIT MODAL
// ==========================

let currentEditId = 0;

function openEditModal(id, name, desc, poin, stok, gambar) {
    currentEditId = id;
    document.getElementById('edit-id-reward').value = id;
    document.getElementById('edit-name-reward').value = name;
    document.getElementById('edit-description').value = desc;
    document.getElementById('edit-poin').value = poin;
    document.getElementById('edit-stok').value = stok;

    const previewImg = document.getElementById('edit-gambar-preview');
    if (gambar) {
        previewImg.src = '../uploads/' + gambar;
        previewImg.style.display = 'block';
    } else {
        previewImg.style.display = 'none';
    }

    document.getElementById('edit-gambar').value = '';
    document.getElementById('edit-reward-modal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('edit-reward-modal').style.display = 'none';
}

// Preview gambar baru saat dipilih
document.getElementById('edit-gambar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewImg = document.getElementById('edit-gambar-preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.src = ev.target.result;
            previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// ==========================
// SUBMIT UPDATE
// ==========================

document.getElementById('formEditReward').addEventListener('submit', function(e) {
    e.preventDefault();

    const btnSave = document.getElementById('btn-save-edit');
    btnSave.disabled = true;
    btnSave.textContent = 'Menyimpan...';

    const formData = new FormData(this);

    fetch('../api/rewards/update.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btnSave.disabled = false;
        btnSave.textContent = '💾 Simpan';

        if (data.success) {
            closeEditModal();
            showNotification('success', data.message);

            // Update tabel tanpa reload
            const row = document.getElementById('row-reward-' + currentEditId);
            if (row && data.data) {
                row.querySelector('.td-name').textContent = data.data.name_reward;
                row.querySelector('.td-desc').textContent = data.data.description;
                row.querySelector('.td-poin').textContent = data.data.poin;
                row.querySelector('.td-stok').textContent = data.data.stok || 0;

                if (data.data.gambar) {
                    const thumb = row.querySelector('.reward-thumb');
                    if (thumb) {
                        thumb.src = '../uploads/' + data.data.gambar;
                    }
                }
            }
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(err => {
        btnSave.disabled = false;
        btnSave.textContent = '💾 Simpan';
        showNotification('error', 'Terjadi kesalahan koneksi.');
    });
});

// ==========================
// NOTIFICATION
// ==========================

function showNotification(type, message) {
    const old = document.querySelector('.notif-toast');
    if (old) old.remove();

    const notif = document.createElement('div');
    notif.className = 'notif-toast notif-' + type;
    notif.innerHTML = `
        <span>${type === 'success' ? '✅' : '❌'} ${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;font-size:18px;cursor:pointer;">&times;</button>
    `;
    document.body.appendChild(notif);

    setTimeout(() => {
        if (notif.parentElement) {
            notif.style.opacity = '0';
            notif.style.transform = 'translateX(100%)';
            setTimeout(() => notif.remove(), 300);
        }
    }, 4000);
}

// Close modal ketika klik di luar
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
</script>

<?php include "../layouts/footer.php"; ?>
