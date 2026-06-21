<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../style/login_style.css">
</head>

<body>


    <form action="register.php" method="POST">
        <h2>Register User</h2>

        <label>Nama</label><br>
        <input type="text" name="nama" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

</body>

</html>