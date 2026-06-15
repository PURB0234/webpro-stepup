<?php include "../layouts/header.php"; ?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<script>
  const currentUserId = <?= isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0 ?>;
  const currentUserName = "<?= isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama'], ENT_QUOTES) : 'User' ?>";
</script>

<div class="main-content">

  <div class="main-content community-container">

    <!-- LEFT CONTENT -->
    <div class="left-content-community">

      <!-- HEADER COMMUNITY -->
      <div class="container-stepup">

        <h1>StepUp Community</h1>

        <span>
          Bagikan perjalanan kebugaran Anda, rayakan pencapaian
        </span>

        <button onclick="bukaPopup()">

          <img
            class="icon"
            src="../assets/icon/plus.png"
            alt="plus">

          Buat Postingan Baru

        </button>

      </div>


      <!-- POPUP -->
      <div id="popup" class="popup">

        <form
          id="formPostingan"
          enctype="multipart/form-data">

          <!-- DESKRIPSI -->
          <div class="popup-group">

            <label class="popup-label">
              Deskripsi
            </label>

            <input
              class="popup-input"
              type="text"
              name="deskripsi"
              placeholder="Masukkan deskripsi">

          </div>


          <!-- GAMBAR -->
          <div class="popup-group">

            <label class="popup-label">
              Upload Gambar
            </label>

            <input
              class="popup-file"
              type="file"
              name="gambar">

          </div>


          <!-- LANGKAH -->
          <div class="popup-group">

            <label class="popup-label">
              Langkah
            </label>

            <input
              class="popup-input"
              type="text"
              name="langkah"
              placeholder="Masukkan jumlah langkah">

          </div>


          <!-- JARAK -->
          <div class="popup-group">

            <label class="popup-label">
              Jarak
            </label>

            <input
              class="popup-input"
              type="text"
              name="jarak"
              placeholder="Masukkan jarak">

          </div>


          <!-- KALORI -->
          <div class="popup-group">

            <label class="popup-label">
              Kalori
            </label>

            <input
              class="popup-input"
              type="text"
              name="kalori"
              placeholder="Masukkan kalori">

          </div>


          <!-- BUTTON SUBMIT -->
          <button
            class="popup-submit"
            type="submit">

            Buat Postingan

          </button>

        </form>


        <!-- BUTTON TUTUP -->
        <button
          class="popup-close"
          onclick="tutupPopup()">

          Tutup

        </button>

      </div>


      <!-- CONTAINER POST DINAMIS -->
      <div id="postContainer"></div>

    </div>


    <!-- COMMENT POPUP OVERLAY -->
    <div id="commentOverlay" class="comment-overlay"></div>

    <!-- COMMENT POPUP -->
    <div id="commentPopup" class="comment-popup">

      <!-- POPUP HEADER -->
      <div class="comment-popup-header">

        <h3>Komentar</h3>

        <button
          class="comment-popup-close"
          onclick="tutupKomentar()">

          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>

        </button>

      </div>

      <!-- COMMENT LIST -->
      <div id="commentList" class="comment-list">
        <div class="comment-empty">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#bcc1ca" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          <p>Belum ada komentar</p>
          <span>Jadilah yang pertama berkomentar!</span>
        </div>
      </div>

      <!-- COMMENT INPUT -->
      <form id="formKomentar" class="comment-form">
        <input type="hidden" id="commentPostId" name="community_id">

        <div class="comment-input-wrapper">
          <img class="avatar" src="../assets/avatar/1.jpg" alt="avatar">
          <input
            type="text"
            id="commentInput"
            name="komentar"
            placeholder="Tulis komentar..."
            autocomplete="off"
            required>
          <button type="submit" class="comment-send-btn" title="Kirim">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
      </form>

    </div>


    <!-- RIGHT CONTENT -->
    <div class="right-content">

      <!-- LEADERBOARD -->
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

                <img
                  class="avatar"
                  src="../assets/avatar/1.jpg"
                  alt="user avatar">

                <span>Adi Wijaya</span>

              </td>

              <td>

                <span>15.400 poin</span>

                <img
                  class="icon"
                  src="../assets/icon/star-badge.png"
                  alt="star-badge">

              </td>

            </tr>


            <tr>

              <td>2.</td>

              <td class="leaderboard-profile">

                <img
                  class="avatar"
                  src="../assets/avatar/1.jpg"
                  alt="user avatar">

                <span>Rina S.</span>

              </td>

              <td>

                <span>900 poin</span>

                <img
                  class="icon"
                  src="../assets/icon/star-badge (1).png"
                  alt="star-badge">

              </td>

            </tr>


            <tr>

              <td>3.</td>

              <td class="leaderboard-profile">

                <img
                  class="avatar"
                  src="../assets/avatar/1.jpg"
                  alt="user avatar">

                <span>Rina S.</span>

              </td>

              <td>

                <span>900 poin</span>

                <img
                  class="icon"
                  src="../assets/icon/star-badge (2).png"
                  alt="star-badge">

              </td>

            </tr>

          </tbody>

        </table>

        <button id="view-all">
          Lihat Semua
        </button>

      </div>


      <!-- INVITE CARD -->
      <div class="invite-card">

        <span>

          <img
            class="icon"
            src="../assets/icon/invite (1).png"
            alt="icon invite">

          Ajak Teman

        </span>

        <p>
          Undang teman untuk bergabung dalam tantangan seru
          dan capai tujuan kebugaran bersama!
        </p>

        <button>
          Ajak Teman Sekarang
        </button>

      </div>

    </div>

  </div>

</div>

<?php include "../layouts/footer.php"; ?>