<?php require "../layouts/header.php"; ?>
<?php require "../layouts/sidebar.php"; ?>
<?php require "../layouts/navbar.php"; ?>
<?php require "../rewards/read.php"; ?>

<div class="main-content">

  <!-- BALANCE -->
  <div class="balance-card">
    <div>
      <h3>Your Rewards Balance</h3>
      <p>You have <b>3250</b> points available.</p>
    </div>
    <button id="view-history">View History</button>
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

          <article class="card">

            <div class="card_image">
              <img src="../assets/default_reward.png" alt="reward image" />
            </div>

            <div class="card-content">
              <h4><?= $reward['name_reward'] ?? 'Tidak ada' ?></h4>
              <p>
                Tukarkan poin kamu dengan reward menarik ini.
              </p>
            </div>

            <div class="card-points">
              <p>Poin</p>
              <h3><?= $reward['poin'] ?? 0 ?></h3>
            </div>

            <button type="button" class="btn-redeem">Redeem</button>

          </article>

        <?php endforeach; ?>

      <?php else: ?>
        <p>Belum ada reward tersedia</p>
      <?php endif; ?>

    </div>

  </section>

</div>

<?php include "../layouts/footer.php"; ?>