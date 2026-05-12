<?php include "../layouts/header.php"; ?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<div class="main-content">
  <div class="dashboard_container">
    <div class="stepbox_claimrewards">
      <div class="stepbox">
        <h3 class="h3-text">Today's Step</h3>
        <h1>8.450</h1>
        <div class="progress_bar">
          <img src="../assets/icon/star.png" alt="star" id="star" />
          <div class="progress_text">
            <img src="../assets/images/progressbar.png" alt="Progress Bar" class="progress_bar_image" />
            <h3>8.450 points</h3>
          </div>
        </div>
      </div>

      <div class="claimrewards">
        <h3>Claim Your Rewards</h3>

        <div class="claimrewards_card">
          <div class="claimrewards_icon_small">
            <div class="icon_small">
              <img src="../assets/icon/gift-icon.png" alt="gift" id="gift" />
            </div>
            <div class="claimrewards_text_small">
              <h4>Voucher</h4>
              <p>10.000 pts</p>
            </div>
          </div>

          <div class="claimrewards_icon_small">
            <div class="icon_small">
              <img src="../assets/icon/percent.png" alt="percent" />
            </div>
            <div class="claimrewards_text_small">
              <h4>Discount Code</h4>
              <p>7.500 pts</p>
            </div>
          </div>
          <div class="claimrewards_icon_small">
            <div class="icon_small">
              <img src="../assets/icon/coupon.png" alt="coupon" />
            </div>
            <div class="claimrewards_text_small">
              <h4>Coupon</h4>
              <p>5.000 pts</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="graphic_container">
      <div class="graphic_dashboard">
        <!-- <div class="graphic_text">
          <h3 class="h3-text">Weekly Activity</h3>
        </div>

        <div class="graphic">
          <img src="../assets/images/grafik_dash.png" alt="gambar grafik" class="graphic_image" />
        </div>
      </div> -->
        <div style="width: 100%; max-width: 900px; margin: 20px auto;">
          <h3 style="font-family: sans-serif;">Weekly Activity</h3>
          <canvas id="weeklyChart"></canvas>
        </div>
        <script>
          const ctx = document.getElementById('weeklyChart').getContext('2d');

          new Chart(ctx, {
            type: 'line',
            data: {
              labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
              datasets: [{
                label: 'Activity',
                data: [4500, 6200, 5000, 9000, 7000, 5500, 7200],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4, // smooth curve
                fill: false,
                pointRadius: 0,
                borderWidth: 3
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  display: false
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  grid: {
                    color: 'rgba(0,0,0,0.05)'
                  },
                  ticks: {
                    callback: function (value) {
                      return value / 1000 + 'k';
                    }
                  }
                },
                x: {
                  grid: {
                    display: false
                  }
                }
              }
            }
          });
        </script>
      </div>

      <div class="achievement_container">
        <div class="achievement_text">
          <h3 class="h3-text">Your Achievements</h3>
        </div>

        <div class="achievements_card">
          <div class="card">
            <div class="card_content">
              <img src="../assets/icon/medal.png" alt="" />
              <p>Daily Streaker</p>
            </div>
          </div>

          <div class="card">
            <div class="card_content">
              <img src="../assets/icon/target.png" alt="" />
              <p>Marathon Mover</p>
            </div>
          </div>

          <div class="card">
            <div class="card_content">
              <img src="../assets/icon/people.png" alt="" />
              <p>Social Sprinter</p>
            </div>
          </div>

          <div class="card">
            <div class="card_content">
              <img src="../assets/icon/sparkler.png" alt="" />
              <p>Point Collector</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include "../layouts/footer.php"; ?>