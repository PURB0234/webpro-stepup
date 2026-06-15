<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>
<?php
// session_start();

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

   <div class="reward-page">

    <div class="reward-container">

        <h2 class="reward-title">
            Add Reward
        </h2>

        <form
            class="reward-form"
            id="formTambahReward"
            method="POST"
            enctype="multipart/form-data">

            <div class="reward-group">

                <label class="reward-label">
                    Reward Name
                </label>

                <input
                    class="reward-input"
                    type="text"
                    name="name_reward"
                    placeholder="Reward Name"
                    required>

            </div>


            <div class="reward-group">

                <label class="reward-label">
                    Point Required
                </label>

                <input
                    class="reward-input"
                    type="number"
                    name="poin"
                    placeholder="Point Required"
                    required>

            </div>


            <div class="reward-group">

                <label class="reward-label">
                    Description
                </label>

                <textarea
                    class="reward-textarea"
                    name="description"
                    placeholder="Description"
                    required></textarea>

            </div>


            <div class="reward-group">

                <label class="reward-label">
                    Image
                </label>

                <input
                    class="reward-file"
                    type="file"
                    name="gambar">

            </div>


            <button
                class="reward-button"
                type="submit">

                Add Reward

            </button>

        </form>

    </div>

</div>

<script>
document.getElementById("formTambahReward").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("../api/rewards/post.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            window.location.href = "../pages/reward_page.php?success=1";
        }
    })
    .catch(error => {
        alert("Terjadi kesalahan: " + error.message);
    });
});
</script>

</body>

</html>