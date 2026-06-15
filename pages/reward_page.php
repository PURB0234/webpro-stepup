<?php require "../layouts/header.php"; ?>
<?php require "../layouts/sidebar.php"; ?>
<?php require "../layouts/navbar.php"; ?>
<?php require "../api/rewards/get.php"; ?>

<?php
// Ambil poin user dari database
$user_poin = 0;
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    // Pastikan kolom poin ada di tabel users
    $checkPoin = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'poin'");
    if (mysqli_num_rows($checkPoin) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN poin INT DEFAULT 0");
    }
    $poinQuery = mysqli_query($conn, "SELECT poin FROM users WHERE id = $uid");
    $poinData = mysqli_fetch_assoc($poinQuery);
    $user_poin = $poinData ? (int) $poinData['poin'] : 0;
}
?>

<div class="main-content">

  <!-- BALANCE -->
  <div class="balance-card">
    <div>
      <h3>Your Rewards Balance</h3>
      <p>You have <b id="user-poin-display"><?= number_format($user_poin) ?></b> points available.</p>
    </div>
    <button id="view-history" onclick="openHistoryModal()">View History</button>
  </div>

  <!-- REWARDS -->
  <section class="vouchers-section">
    <h3>
      Exclusive Rewards
      <a href="#" class="see-all-link">See All</a>
    </h3>

    <div class="card-grid">

      <?php if (!empty($rewards)): ?>

        <?php foreach ($rewards as $reward): ?>

          <article class="card" id="reward-card-<?= $reward['id_reward'] ?>">

            <div class="card_image">
              <img src="../uploads/<?= $reward['gambar']; ?>" alt="reward image">
            </div>

            <div class="card-content">
              <h4><?= $reward['name_reward'] ?? 'Tidak ada' ?></h4>
              <p>
                <?= $reward['description'] ?? 'Tidak ada deskripsi' ?>
              </p>
            </div>

            <div class="card-points">
              <h5>Poin</h5>
              <h3><?= $reward['poin'] ?? 0 ?></h3>
            </div>

            <!-- STOK INFO -->
            <div class="card-stock">
              <span class="stock-badge <?= (isset($reward['stok']) && $reward['stok'] > 0) ? 'in-stock' : 'out-of-stock' ?>">
                Stok: <b id="stok-<?= $reward['id_reward'] ?>"><?= isset($reward['stok']) ? $reward['stok'] : 0 ?></b>
              </span>
            </div>

            <!-- REDEEM BUTTON -->
            <?php
              $stok = isset($reward['stok']) ? (int)$reward['stok'] : 0;
              $poin_reward = (int)$reward['poin'];
              $bisa_redeem = ($stok > 0 && $user_poin >= $poin_reward);
            ?>
            <button 
              type="button" 
              class="btn-redeem <?= !$bisa_redeem ? 'btn-disabled' : '' ?>"
              id="btn-redeem-<?= $reward['id_reward'] ?>"
              onclick="redeemReward(<?= $reward['id_reward'] ?>, '<?= htmlspecialchars($reward['name_reward'], ENT_QUOTES) ?>', <?= $poin_reward ?>)"
              <?= !$bisa_redeem ? 'disabled' : '' ?>>
              <?php if ($stok <= 0): ?>
                Habis
              <?php elseif ($user_poin < $poin_reward): ?>
                Poin Kurang
              <?php else: ?>
                Redeem
              <?php endif; ?>
            </button>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'):  ?>
              <form action="../api/rewards/delete.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus reward ini?');">
                <input type="hidden" name="id_reward" value="<?= $reward['id_reward']; ?>">
                <button type="submit" class="btn-delete">Delete</button>
              </form>
            <?php endif; ?>

          </article>

        <?php endforeach; ?>

      <?php else: ?>
        <p>Belum ada reward tersedia</p>
      <?php endif; ?>

    </div>

  </section>

</div>

<!-- ======= MODAL HISTORY ======= -->
<div id="history-modal" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-header">
      <h3>📋 Redemption History</h3>
      <button class="modal-close" onclick="closeHistoryModal()">&times;</button>
    </div>
    <div class="modal-body" id="history-content">
      <p style="text-align:center; color:#999;">Memuat data...</p>
    </div>
  </div>
</div>

<!-- ======= MODAL KONFIRMASI REDEEM ======= -->
<div id="redeem-modal" class="modal-overlay" style="display:none;">
  <div class="modal-box modal-sm">
    <div class="modal-header">
      <h3>🎁 Konfirmasi Redeem</h3>
      <button class="modal-close" onclick="closeRedeemModal()">&times;</button>
    </div>
    <div class="modal-body">
      <p>Anda akan menukarkan <b id="redeem-poin-display">0</b> poin untuk:</p>
      <h4 id="redeem-nama-display" style="color:#3366CC; margin:10px 0;">-</h4>
      <p style="color:#666; font-size:13px;">Tindakan ini tidak dapat dibatalkan.</p>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeRedeemModal()">Batal</button>
      <button class="btn-confirm" id="btn-confirm-redeem" onclick="confirmRedeem()">Ya, Redeem!</button>
    </div>
  </div>
</div>

<script>
// ==========================
// REDEEM REWARD
// ==========================

let pendingRedeemId = 0;
let pendingRedeemName = '';
let pendingRedeemPoin = 0;

function redeemReward(rewardId, rewardName, poin) {
  pendingRedeemId = rewardId;
  pendingRedeemName = rewardName;
  pendingRedeemPoin = poin;

  document.getElementById('redeem-poin-display').textContent = poin.toLocaleString();
  document.getElementById('redeem-nama-display').textContent = rewardName;
  document.getElementById('redeem-modal').style.display = 'flex';
}

function closeRedeemModal() {
  document.getElementById('redeem-modal').style.display = 'none';
}

function confirmRedeem() {
  const btnConfirm = document.getElementById('btn-confirm-redeem');
  btnConfirm.disabled = true;
  btnConfirm.textContent = 'Memproses...';

  const formData = new FormData();
  formData.append('reward_id', pendingRedeemId);

  fetch('../api/rewards/redeem.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btnConfirm.disabled = false;
    btnConfirm.textContent = 'Ya, Redeem!';
    closeRedeemModal();

    if (data.success) {
      // Update poin display
      document.getElementById('user-poin-display').textContent = 
        data.data.sisa_poin.toLocaleString();

      // Update stok display
      const stokEl = document.getElementById('stok-' + pendingRedeemId);
      if (stokEl) {
        stokEl.textContent = data.data.sisa_stok;

        // Update stock badge class
        const badge = stokEl.closest('.stock-badge');
        if (data.data.sisa_stok <= 0) {
          badge.classList.remove('in-stock');
          badge.classList.add('out-of-stock');
        }
      }

      // Disable button jika stok habis
      const btnRedeem = document.getElementById('btn-redeem-' + pendingRedeemId);
      if (btnRedeem && data.data.sisa_stok <= 0) {
        btnRedeem.disabled = true;
        btnRedeem.textContent = 'Habis';
        btnRedeem.classList.add('btn-disabled');
      }

      // Cek semua tombol redeem (poin mungkin sudah kurang)
      updateAllRedeemButtons(data.data.sisa_poin);

      // Tampil notifikasi sukses
      showNotification('success', data.message);

    } else {
      showNotification('error', data.message);
    }
  })
  .catch(err => {
    btnConfirm.disabled = false;
    btnConfirm.textContent = 'Ya, Redeem!';
    closeRedeemModal();
    showNotification('error', 'Terjadi kesalahan koneksi.');
  });
}

function updateAllRedeemButtons(sisaPoin) {
  document.querySelectorAll('.btn-redeem').forEach(btn => {
    const id = btn.id.replace('btn-redeem-', '');
    const card = document.getElementById('reward-card-' + id);
    if (!card) return;

    const poinText = card.querySelector('.card-points h3');
    const stokText = card.querySelector('.stock-badge b');
    
    if (!poinText || !stokText) return;

    const poinNeeded = parseInt(poinText.textContent) || 0;
    const stok = parseInt(stokText.textContent) || 0;

    if (stok <= 0) {
      btn.disabled = true;
      btn.textContent = 'Habis';
      btn.classList.add('btn-disabled');
    } else if (sisaPoin < poinNeeded) {
      btn.disabled = true;
      btn.textContent = 'Poin Kurang';
      btn.classList.add('btn-disabled');
    } else {
      btn.disabled = false;
      btn.textContent = 'Redeem';
      btn.classList.remove('btn-disabled');
    }
  });
}

// ==========================
// HISTORY MODAL
// ==========================

function openHistoryModal() {
  document.getElementById('history-modal').style.display = 'flex';
  loadHistory();
}

function closeHistoryModal() {
  document.getElementById('history-modal').style.display = 'none';
}

function loadHistory() {
  const content = document.getElementById('history-content');
  content.innerHTML = '<p style="text-align:center; color:#999;">Memuat data...</p>';

  fetch('../api/rewards/history.php')
  .then(res => res.json())
  .then(data => {
    if (!data.success || data.data.length === 0) {
      content.innerHTML = `
        <div style="text-align:center; padding:30px; color:#999;">
          <p style="font-size:40px; margin-bottom:10px;">🎁</p>
          <p>Belum ada riwayat redeem</p>
        </div>`;
      return;
    }

    let html = '<div class="history-list">';
    data.data.forEach(item => {
      const tanggal = new Date(item.tanggal_redeem).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      });

      html += `
        <div class="history-item">
          <div class="history-icon">🎁</div>
          <div class="history-info">
            <strong>${item.nama_reward}</strong>
            <small>${tanggal}</small>
          </div>
          <div class="history-poin">-${parseInt(item.poin_digunakan).toLocaleString()} pts</div>
        </div>`;
    });
    html += '</div>';

    content.innerHTML = html;
  })
  .catch(err => {
    content.innerHTML = '<p style="text-align:center; color:red;">Gagal memuat data.</p>';
  });
}

// ==========================
// NOTIFICATION
// ==========================

function showNotification(type, message) {
  // Hapus notifikasi lama jika ada
  const old = document.querySelector('.notif-toast');
  if (old) old.remove();

  const notif = document.createElement('div');
  notif.className = 'notif-toast notif-' + type;
  notif.innerHTML = `
    <span>${type === 'success' ? '✅' : '❌'} ${message}</span>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;font-size:18px;cursor:pointer;">&times;</button>
  `;
  document.body.appendChild(notif);

  // Auto remove setelah 4 detik
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