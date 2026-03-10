<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Progress Analytics</title>
  <link rel="stylesheet" href="style/routeHistory_style.css" />
  <link rel="stylesheet" href="style_side_nav_main_footer.css" />
  <link rel="stylesheet" href="style-responsive-layout.css" />
</head>

<body>
  <div class="container">
    <aside class="sidebar active">
      <div class="logo-container">
        <div class="logo-left">
          <div class="logo-icon">
            <img class="logo-image" src="assets/icon/logo.png" alt="" />
          </div>
          <h2>StepUp</h2>
        </div>
        <button class="btn-side">
          <img class="icon" src="assets/icon/icon-menu.png" alt="" />
        </button>
      </div>

      <div class="sidebar-content">
        <nav>
          <ul>
            <li>
              <a href="dashboard.html" class="sidebar-link">
                <img class="icon" src="assets/icon/grid.png" alt="" />
                Dashboard
              </a>
            </li>

            <li>
              <a href="reward_page.html" class="sidebar-link">
                <img class="icon" src="assets/icon/gift.png" alt="" />
                Rewards
              </a>
            </li>

            <li>
              <a href="leaderboard_page.html" class="sidebar-link">
                <img class="icon" src="assets/icon/cup.png" alt="" />
                Leaderboard
              </a>
            </li>

            <li>
              <a href="settings.html" class="sidebar-link">
                <img class="icon" src="assets/icon/gear.png" alt="" />
                Settings
              </a>
            </li>

            <li>
              <a href="event.html" class="sidebar-link">
                <img class="icon" src="assets/icon/dumbel.png" alt="" />
                My Challenges
              </a>
            </li>
            <li>
              <a href="community_feed.html" class="sidebar-link">
                <img class="icon" src="assets/icon/person.png" alt="" />
                Community
              </a>
            </li>
            <li>
              <a href="analytics.html" class="sidebar-link">
                <img class="icon" src="assets/icon/chart-2.png" alt="" />
                Analytics
              </a>
            </li>
            <li>
                <a href="collections.html" class="sidebar-link">
                  <img class="icon" src="assets/icon/layer.png" alt="" />
                  Collections
                </a>
              </li>
            <li>
              <a href="#" class="sidebar-link">
                <img class="icon" src="assets/icon/history.png" alt="" />
                Route History
              </a>
            </li>
            <li>
                <a href="gps1.html" class="sidebar-link">
                  <img class="icon" src="assets/icon/location.png" alt="" />
                  GPS Tracker
                </a>
              </li>
              <li>
                <a href="library.html" class="sidebar-link">
                  <img class="icon" src="assets/icon/creativity.png" alt="" />
                  Insights Library
                </a>
              </li>
          </ul>
        </nav>

        <div class="container_logout">
          <button class="button_help">Help & Support</button>
          <a href="index.html" class="logout">
            <img class="icon" src="assets/icon/icon_logout.png" alt="" />
            Log Out
          </a>
        </div>
      </div>
    </aside>

    <div class="right_area">
      <header class="navbar">
        <div class="nav_right">
          <!-- <div class="profile">Profile</div> -->
          <div class="nav_search">
            <img class="icon" src="assets/icon/search.png" alt="" />
            <input type="text" placeholder="Search rewards..." />
          </div>
          <img class="icon" src="assets/icon/bell.png" alt="" />

          <img class="avatar" src="assets/avatar/1.jpg" alt="" />
        </div>
      </header>

      <main class="main">
        <div class="main-content">

          <div class="container-history">
            <div class="left-content-history">



              <div class="history-header">
                <h1>Riwayat Rute Anda</h1>
                <p>
                  Jelajahi, saring, dan tinjau semua perjalanan Anda yang telah
                  direkam.
                </p>
              </div>
              <div class="history-bar-menu">
                <div class="history-search-bar">
                  <img src="assets/icon/search.png" alt="" />
                  <input class="history-search-input" type="text" placeholder="Cari rute..." />
                </div>
                <section class="filter-bar">
                  <details class="filter">
                    <summary>
                      Filter Tanggal
                      <span class="arrow"><img src="assets/icon/down-arrow.png" /></span>
                    </summary>
                    <ul class="filter-menu">
                      <li>Hari ini</li>
                      <li>7 Hari Terakhir</li>
                      <li>Bulan Ini</li>
                    </ul>
                  </details>
                  <details class="filter">
                    <summary>
                      Filter Jarak
                      <span class="arrow"><img src="assets/icon/down-arrow.png" /></span>
                    </summary>
                    <ul class="filter-menu">
                      <li>1 km</li>
                      <li>1 – 5 km</li>
                      <li>5 km</li>
                    </ul>
                  </details>

                </section>
              </div>


              <section class="card-section">
                <article class="card">
                  <header class="route-card-header">
                    <time datetime="2024-05-15T07:30">15 Mei 2024, 07:30</time>
                    <span class="badge">Selesai</span>
                  </header>

                  <h3 class="route-title">Rute Pagi di Taman Kota</h3>

                  <div class="route-stats">
                    <div>
                      <span class="label">Jarak</span>
                      <span class="value">5.2 km</span>
                    </div>
                    <div>
                      <span class="label">Waktu</span>
                      <span class="value">45 menit</span>
                    </div>
                    <div>
                      <span class="label">Langkah</span>
                      <span class="value">6.890</span>
                    </div>
                  </div>

                  <figure class="route-image">
                    <img src="assets/images/route-history-1.png" alt="Peta rute" />
                  </figure>

                  <footer class="route-card-footer">
                    <button class="btn-primary">Lihat Rute</button>
                  </footer>
                </article>
                <article class="card">
                  <header class="route-card-header">
                    <time datetime="2024-05-15T07:30">15 Mei 2024, 07:30</time>
                    <span class="badge">Selesai</span>
                  </header>

                  <h3 class="route-title">Rute Pagi di Taman Kota</h3>

                  <div class="route-stats">
                    <div>
                      <span class="label">Jarak</span>
                      <span class="value">5.2 km</span>
                    </div>
                    <div>
                      <span class="label">Waktu</span>
                      <span class="value">45 menit</span>
                    </div>
                    <div>
                      <span class="label">Langkah</span>
                      <span class="value">6.890</span>
                    </div>
                  </div>

                  <figure class="route-image">
                    <img src="assets/images/route-history-2.png" alt="Peta rute" />
                  </figure>

                  <footer class="route-card-footer">
                    <button class="btn-primary">Lihat Rute</button>
                  </footer>
                </article>

                <article class="card">
                  <header class="route-card-header">
                    <time datetime="2024-05-15T07:30">15 Mei 2024, 07:30</time>
                    <span class="badge">Selesai</span>
                  </header>

                  <h3 class="route-title">Rute Pagi di Taman Kota</h3>

                  <div class="route-stats">
                    <div>
                      <span class="label">Jarak</span>
                      <span class="value">5.2 km</span>
                    </div>
                    <div>
                      <span class="label">Waktu</span>
                      <span class="value">45 menit</span>
                    </div>
                    <div>
                      <span class="label">Langkah</span>
                      <span class="value">6.890</span>
                    </div>
                  </div>

                  <figure class="route-image">
                    <img src="assets/images/route-history-3.png" alt="Peta rute" />
                  </figure>

                  <footer class="route-card-footer">
                    <button class="btn-primary">Lihat Rute</button>
                  </footer>
                </article>

                <article class="card">
                  <header class="route-card-header">
                    <time datetime="2024-05-15T07:30">15 Mei 2024, 07:30</time>
                    <span class="badge">Selesai</span>
                  </header>

                  <h3 class="route-title">Rute Pagi di Taman Kota</h3>

                  <div class="route-stats">
                    <div>
                      <span class="label">Jarak</span>
                      <span class="value">5.2 km</span>
                    </div>
                    <div>
                      <span class="label">Waktu</span>
                      <span class="value">45 menit</span>
                    </div>
                    <div>
                      <span class="label">Langkah</span>
                      <span class="value">6.890</span>
                    </div>
                  </div>

                  <figure class="route-image">
                    <img src="assets/images/route-history-4.png" alt="Peta rute" />
                  </figure>

                  <footer class="route-card-footer">
                    <button class="btn-primary">Lihat Rute</button>
                  </footer>
                </article>

                <article class="card">
                  <header class="route-card-header">
                    <time datetime="2024-05-15T07:30">15 Mei 2024, 07:30</time>
                    <span class="badge">Selesai</span>
                  </header>

                  <h3 class="route-title">Rute Pagi di Taman Kota</h3>

                  <div class="route-stats">
                    <div>
                      <span class="label">Jarak</span>
                      <span class="value">5.2 km</span>
                    </div>
                    <div>
                      <span class="label">Waktu</span>
                      <span class="value">45 menit</span>
                    </div>
                    <div>
                      <span class="label">Langkah</span>
                      <span class="value">6.890</span>
                    </div>
                  </div>

                  <figure class="route-image">
                    <img src="assets/images/route-history-5.png" alt="Peta rute" />
                  </figure>

                  <footer class="route-card-footer">
                    <button class="btn-primary">Lihat Rute</button>
                  </footer>
                </article>

                <article class="card">
                  <header class="route-card-header">
                    <time datetime="2024-05-15T07:30">15 Mei 2024, 07:30</time>
                    <span class="badge">Selesai</span>
                  </header>

                  <h3 class="route-title">Rute Pagi di Taman Kota</h3>

                  <div class="route-stats">
                    <div>
                      <span class="label">Jarak</span>
                      <span class="value">5.2 km</span>
                    </div>
                    <div>
                      <span class="label">Waktu</span>
                      <span class="value">45 menit</span>
                    </div>
                    <div>
                      <span class="label">Langkah</span>
                      <span class="value">6.890</span>
                    </div>
                  </div>

                  <figure class="route-image">
                    <img src="assets/images/route-history-5.png" alt="Peta rute" />
                  </figure>

                  <footer class="route-card-footer">
                    <button class="btn-primary">Lihat Rute</button>
                  </footer>
                </article>
            </div>
            <div class="right-content-history">
              <section class="stats">
                <h2>Statistik Rute Terbaik</h2>

                <div class="stats-grid">
                  <article class="stat-card">
                    <div class="stat-left">
                      <img src="assets/icon/compass.png" class="right-content-icon" alt="" />
                      <p>Jarak Terjauh</p>
                    </div>
                    <span class="right-content-badge">7.8 km</span>
                  </article>

                  <article class="stat-card">
                    <div class="stat-left">
                      <img src="assets/icon/footprints.png" class="right-content-icon" alt="" />
                      <p>Langkah Terbanyak</p>
                    </div>
                    <span class="right-content-badge">10.200</span>
                  </article>

                  <article class="stat-card">
                    <div class="stat-left">
                      <img src="assets/icon/circular-alarm-clock-tool.png" class="right-content-icon" alt="" />
                      <p>Durasi Terlama</p>
                    </div>
                    <span class="right-content-badge">1 jam 10 menit</span>
                  </article>

                  <article class="stat-card">
                    <div class="stat-left">
                      <img src="assets/icon/trend.png" class="right-content-icon" alt="" />
                      <p>Rata-rata Kecepatan Terbaik</p>
                    </div>
                    <span class="right-content-badge">9.5 km/jam</span>
                  </article>
                </div>
              </section>
            </div>
          </div>
        </div>


        <footer>
          <div class="copyright-content">
            <span>
              <img class="icon" src="assets/icon/copyright.png" alt="" />
              2025 StepUp Analytics. All rights reserved. </span>
            <span>
              <img class="icon" src="assets/icon/twitter.png" alt="" />
              <img class="icon" src="assets/icon/instagram.png" alt="" />
              <img class="icon" src="assets/icon/facebook.png" alt="" />
            </span>
          </div>

          <div class="footer-content">
            <h5>About Us</h5>
            <div>
              <p>Our Story</p>
              <p>Careers</p>
              <p>Press</p>
            </div>
          </div>

          <div class="footer-content">
            <h5>Support</h5>
            <div>
              <p>FAQ</p>
              <p>Help Center</p>
              <p>Contact Us</p>
            </div>
          </div>

          <div class="footer-content">
            <h5>Legal</h5>
            <div>
              <p>Privacy Policy</p>
              <p>Terms of Service</p>
              <p>Cookie Policy</p>
            </div>
          </div>
        </footer>
      </main>

    </div>
  </div>

  <script src="assets/js/sidebar.js"></script>
</body>

</html>
              
              







