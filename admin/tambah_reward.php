<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>
<?php
session_start();

// Proteksi admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Reward</title>
</head>

<body>

    <h2>Add Reward</h2>

    <form action="../rewards/create.php" method="POST" enctype="multipart/form-data">

        <label>Reward Name:</label><br>
        <input type="text" name="name_reward" placeholder="Reward Name"required><br><br>

        <label>Point Required:</label><br>
        <input type="number" name="poin" placeholder="Point Required" required><br><br>

        <label>Description:</label><br>
        <input name="description" placeholder="" id="description" required></input><br><br>

        <label>Image:</label><br>
        <input type="file" name="gambar"><br><br>

        <button type="submit">Add Reward</button>

    </form>

</body>

</html>