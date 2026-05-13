<?php include "../layouts/header.php"; ?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

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