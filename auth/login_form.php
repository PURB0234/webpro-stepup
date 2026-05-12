<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="../style/login_style.css">
</head>

<body>

    <!-- <h2>Login User</h2> -->
    
    <form action="login.php" method="POST">
        <h2>Login User</h2>

        <label>Nama</label><br>
        <input type="text" name="nama" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <button type="submit">Login</button>

    </form>

</body>

</html>