<div class="right_area">

    <header class="navbar">

        <div class="nav_right">

            <div class="nav_search">
                <img class="icon" src="../assets/icon/search.png">
                <input type="text" placeholder="Search rewards...">
            </div>

            <img class="icon" src="../assets/icon/bell.png">

            <a href="../pages/profile.php" class="avatar-link" title="Lihat Profil">
                <img class="avatar" src="<?= !empty($_SESSION['foto_profile']) ? '../uploads/profiles/' . $_SESSION['foto_profile'] : '../assets/avatar/1.jpg' ?>" alt="Avatar">
            </a>

        </div>

    </header>

    <main class="main">
        <div class="main-content">