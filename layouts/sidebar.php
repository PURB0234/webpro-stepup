<aside class="sidebar active">

    <div class="logo-container">
        <div class="logo-left">
            <div class="logo-icon">
                <img class="logo-image" src="../assets/icon/logo.png">
            </div>
            <h2>StepUp</h2>
        </div>

        <button class="btn-side">
            <img class="icon" src="../assets/icon/icon-menu.png">
        </button>
    </div>

    <div class="sidebar-content">
        <nav>
            <ul>

                <li>
                    <a href="../pages/dashboard.php" class="sidebar-link">
                        <img class="icon" src="../assets/icon/grid.png">
                        Dashboard
                    </a>
                </li>

                <li>
                    <a href="../pages/reward_page.php" class="sidebar-link">
                        <img class="icon" src="../assets/icon/gift.png">
                        Rewards
                    </a>
                </li>

                <li>
                    <a href="../pages/leaderboard_page.php" class="sidebar-link">
                        <img class="icon" src="../assets/icon/cup.png">
                        Leaderboard
                    </a>
                </li>
                <li>
                    <a href="../pages/collections.php" class="sidebar-link">
                        <img class="icon" src="../assets/icon/layer.png" alt="" />
                        Collections
                    </a>
                </li>

                <li>
                    <a href="../pages/community_feed.php" class="sidebar-link">
                        <img class="icon" src="../assets/icon/person.png" alt="" />
                        Community
                    </a>
                </li>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'):  ?>
                    <li>
                        <a href="../admin/tambah_reward.php" class="sidebar-link">
                            <img class="icon" src="../assets/icon/gift.png">
                            Tambah Reward
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'):  ?>
                    <li>
                        <a href="../admin/users_data.php" class="sidebar-link">
                            <img class="icon" src="../assets/icon/gift.png">
                            Users
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </nav>

        <div class="container_logout">
            <button class="button_help">Help & Support</button>

            <a href="../auth/logout.php" class="logout">
                <img class="icon" src="../assets/icon/icon_logout.png">
                Log Out
            </a>
        </div>

    </div>
</aside>