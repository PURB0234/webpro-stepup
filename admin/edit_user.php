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
<body>
    <h2>Edit User</h2>

    <form action="../users/edit.php" method="POST">

        <input type="hidden" name="id" value="<?= $user['id']; ?>">

        <label>Name: <?= $user['nama']; ?></label><br>

        <label>Email: <?= $user['email']; ?></label><br>


        <label>Status:</label><br>
        <select name="status" id="status">
            <option value="active" <?= ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            <option value="suspended" <?= ($user['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
            <option value="banned" <?= ($user['status'] == 'banned') ? 'selected' : ''; ?>>Banned</option>
        </select><br><br>

         <label>Role:</label><br>
        <select name="role" id="role">
            <option value="user" <?= ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
            <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select><br><br>

        <button type="submit" name="update">Update User</button>

    </form>
    
</body>
</html>