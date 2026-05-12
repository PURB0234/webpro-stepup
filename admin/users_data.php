<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>
<?php require "../users/read.php"; ?>
<!-- <?php require "../users/delete.php"; ?> -->
<?php require "../users/edit.php"; ?>

<link rel="stylesheet" href="style/users_data_style.css" />
    <link rel="stylesheet" href="style_side_nav_main_footer.css" />
    <link rel="stylesheet" href="style-responsive-layout.css" />

    <div class="main-content">
    <div class="container_data_users">

        <h2>Data Users</h2>

        <table border="0.5" cellpadding="10" cellspacing="0" width="100%">
            <thead class="row-header">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody class="row-body">
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id']; ?></td>
                            <td><?= $user['nama']; ?></td>
                            <td><?= $user['email']; ?></td>
                            <td><?= $user['role']; ?></td>
                            <td><?= $user['status']; ?></td>

                            <td class="user-button">
                                <a href="../users/edit.php?id=<?= $user['id']; ?>">
                                    <button>Edit</button>
                                </a>

                                <a href="../users/delete.php?id=<?= $user['id']; ?>" 
                                   onclick="return confirm('Yakin ingin menghapus user ini?')">
                                    <button>Delete</button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" align="center">
                            Data user kosong
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<?php include "../layouts/footer.php"; ?>