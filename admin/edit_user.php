<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<?php
require_once "../services/koneksi.php";
/** @var mysqli $conn */

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body class="edit-user-body">
    <h2 class="edit-user-header">Edit User</h2>

    <form class="edit-user-form" action="../api/users/update.php" method="POST">

        <input class="edit-user-input" type="hidden" name="id" value="<?= $user['id']; ?>">

        <label class="edit-user-label">Name: <?= $user['nama']; ?></label><br>

        <label class="edit-user-label">Email: <?= $user['email']; ?></label><br>


        <label class="edit-user-label">Status:</label><br>
        <select class="edit-user-select" name="status" id="status">
            <option class="edit-user-option" value="active" <?= ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
            <option class="edit-user-option" value="inactive" <?= ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            <option class="edit-user-option" value="suspended" <?= ($user['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
            <option class="edit-user-option" value="banned" <?= ($user['status'] == 'banned') ? 'selected' : ''; ?>>Banned</option>
        </select><br><br>

         <label class="edit-user-label">Role:</label><br>
        <select class="edit-user-select" name="role" id="role">
            <option class="edit-user-option" value="user" <?= ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
            <option class="edit-user-option" value="admin" <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select><br><br>

        <button class="edit-user-button" type="submit" name="update">Update User</button>

        <a href="users_data.php">
            <button class="edit-user-button" type="button">Kembali</button>
        </a>

    </form>
    
</body>
</html>