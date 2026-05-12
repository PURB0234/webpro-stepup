<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>
<?php require "../users/read.php"; ?>

<div class="main-content">
    <div class="container_data_users">

        <h2>Data Users</h2>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>NIM</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id']; ?></td>
                            <td><?= $user['nama']; ?></td>
                            <td><?= $user['email']; ?></td>
                            <td><?= $user['nim']; ?></td>
                            <td><?= $user['role']; ?></td>

                            <td>
                                <a href="edit.php?id=<?= $user['id']; ?>">
                                    <button>Edit</button>
                                </a>

                                <a href="delete.php?id=<?= $user['id']; ?>" 
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