<?php include "../layouts/header.php"; ?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<div class="main-content">
  <div class="main-content community-container">
    <div class="left-content-community">
      <div class="container-stepup">
        <h1>StepUp Community</h1>
        <span>Bagikan perjalanan kebugaran Anda, rayakan pencapaian</span>
        <button onclick="bukaPopup()">
          <img class="icon" src="../assets/icon/plus.png" alt="" />
          Buat Postingan Baru
        </button>
      </div>

      <!-- add popup -->
      <div id="popup" class="popup">

        <input type="text" placeholder="deskripsi" name="deskripsi"><br>
        <input type="file" name="gambar"><br>
        <input type="text" placeholder="langkah anda berape" name="langkah"><br>
        <input type="text" placeholder="jarak anda berape" name="jarak"><br>
        <input type="text" placeholder="kalori yang terbakar" name="kalori"><br>

        <button onclick="tutupPopup()">
          Tutup
        </button>

        <button>Buat Postingan</button>

      </div>

      <div class="post-card">
        <div class="post-header">
          <img class="avatar" src="../assets/avatar/1.jpg" alt="" />
          <div>
            <h5>Jabran Vronka</h5>
            <span>5 jam yang lalu</span>
          </div>
        </div>

        <div class="post-content">
          <img class="post-image" src="../assets/images/perjalanan_kota.png" alt="">
        </div>

        <div class="post-footer">
          <p>Senang bisa mencapai target 10.000 langkah saya! Konsistensi adalah kuncinya. Tetap semangat semuanya!</p>
          <div class="post-footer-content">
            <span><img class="icon" src="../assets/icon/footsteps.png" alt="">12.500 Langkah</span>
            <span><img class="icon" src="../assets/icon/route.png" alt="">7.5 km</span>
            <span><img class="icon" src="../assets/icon/flame.png" alt="">500 Kalori</span>
          </div>
          <div class="post-actions">
            <button><img class="icon" src="../assets/icon/like.png" alt="">85 Suka</button>
            <button><img class="icon" src="../assets/icon/chat-bubble.png" alt="">20 Komentar</button>
          </div>
        </div>
      </div>

      <div class="post-card">
        <div class="post-header">
          <img class="avatar" src="../assets/avatar/1.jpg" alt="" />
          <div>
            <h5>Jabran Vronka</h5>
            <span>5 jam yang lalu</span>
          </div>
        </div>

        <div class="post-content">
          <img class="post-image" src="../assets/images/hutan.png" alt="postingan gambar">
        </div>

        <div class="post-footer">
          <p>Selesai menaklukkan rute lari pagi! Pemandangannya menakjubkan dan udaranya segar. Siapa yang sudah keluar hari ini?</p>
          <div class="post-footer-content">
            <span><img class="icon" src="../assets/icon/footsteps.png" alt="footsteps">12.500 Langkah</span>
            <span><img class="icon" src="../assets/icon/route.png" alt="route">7.5 km</span>
            <span><img class="icon" src="../assets/icon/flame.png" alt="flame">500 Kalori</span>
          </div>
          <div class="post-actions">
            <button><img class="icon" src="../assets/icon/like.png" alt="">34 Suka</button>
            <button><img class="icon" src="../assets/icon/chat-bubble.png" alt="">6 Komentar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="right-content">
      <div class="leaderboard-content">
        <h2>Leaderboard Community</h2>
        <span class="filter-group">
          <button>Harian</button>
          <button>Mingguan</button>
        </span>
        <table>
          <tbody>
            <tr>
              <td>1.</td>
              <td class="leaderboard-profile">
                <img class="avatar" src="../assets/avatar/1.jpg" alt="user avatar">
                <span> Adi Wijaya</span>
              </td>
              <td>
                <span>15.400 poin</span>
                <img class="icon" src="../assets/icon/star-badge.png" alt="star-badge">
              </td>
            </tr>

            <tr>
              <td>2.</td>
              <td class="leaderboard-profile">
                <img class="avatar" src="../assets/avatar/1.jpg" alt="user avatar">
                <span>Rina S.</span>
              </td>
              <td>
                <span>900 poin</span>
                <img class="icon" src="../assets/icon/star-badge (1).png" alt="star-badge">
              </td>
            </tr>

            <tr>
              <td>3.</td>
              <td class="leaderboard-profile">
                <img class="avatar" src="../assets/avatar/1.jpg" alt="user avatar">
                <span>Rina S.</span>
              </td>
              <td>
                <span>900 poin</span>
                <img class="icon" src="../assets/icon/star-badge (2).png" alt="star-badge">
              </td>
            </tr>
          </tbody>
        </table>

        <button id="view-all">Lihat Semua</button>
      </div>

      <div class="invite-card">
        <span><img class="icon" src="../assets/icon/invite (1).png" alt="icon invite">Ajak Teman</span>
        <p>Undang teman untuk bergabung dalam tantangan seru dan capai tujuan kebugaran bersama!</p>
        <button>Ajak Teman Sekarang</button>
      </div>
    </div>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>