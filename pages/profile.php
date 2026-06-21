<?php
include "../layouts/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login_form.php");
    exit();
}

// Ambil data user dari session atau database (api)
$user_id = $_SESSION['user_id'];
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$foto_profile = isset($_SESSION['foto_profile']) ? $_SESSION['foto_profile'] : '';
?>

<?php include "../layouts/sidebar.php"; ?>
<?php include "../layouts/navbar.php"; ?>

<div class="profile-container">
    
    <!-- Left Column: Profile Card -->
    <div class="profile-card profile-sidebar">
        <div class="avatar-wrapper" onclick="document.getElementById('input-foto').click()" title="Klik untuk mengganti foto">
            <img id="avatar-preview" src="<?= !empty($foto_profile) ? '../uploads/profiles/' . htmlspecialchars($foto_profile) : '../assets/avatar/1.jpg' ?>" alt="Foto Profil">
            <div class="avatar-overlay">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                    <circle cx="12" cy="13" r="4"></circle>
                </svg>
                Ganti Foto
            </div>
        </div>
        
        <h2 id="display-nama"><?= htmlspecialchars($nama) ?></h2>
        <p class="email-text"><?= htmlspecialchars($email) ?></p>
        
        <div class="user-badges">
            <span class="badge-item badge-role <?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></span>
        </div>
    </div>
    
    <!-- Right Column: Profile & Password Forms -->
    <div class="profile-content">
        
        <!-- Edit Profile Details Card -->
        <div class="profile-card">
            <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--bg-primary);">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Detail Profil
            </h3>
            
            <div id="profile-alert" class="alert"></div>
            
            <form id="form-edit-profil" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="input-nama">Nama Lengkap</label>
                    <input type="text" id="input-nama" name="nama" class="form-control" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="input-email">Alamat Email</label>
                    <input type="email" id="input-email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
                    <small style="color: #9095a0; margin-top: 4px;">Email tidak dapat diubah.</small>
                </div>
                
                <!-- Hidden file input -->
                <input type="file" id="input-foto" name="foto_profile" accept="image/*" style="display: none;">
                
                <div class="form-group">
                    <label>Foto Profil Baru</label>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <button type="button" class="btn-save" style="background-color: #f1f2fd; color: var(--bg-primary); border: 1px solid #dee1e6;" onclick="document.getElementById('input-foto').click()">
                            Pilih File Gambar
                        </button>
                        <span id="file-name-display" style="font-size: 13px; color: #565d6d;">Belum ada file terpilih</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-save">
                    Simpan Perubahan
                </button>
            </form>
        </div>
        
        <!-- Change Password Card -->
        <div class="profile-card">
            <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #c92a2a;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Keamanan & Password
            </h3>
            
            <div id="password-alert" class="alert"></div>
            
            <form id="form-edit-password">
                <div class="form-group">
                    <label for="password_lama">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" class="form-control" required placeholder="Masukkan password saat ini">
                </div>
                
                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="form-control" required placeholder="Minimal 6 karakter">
                </div>
                
                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" required placeholder="Ulangi password baru">
                </div>
                
                <button type="submit" class="btn-save">
                    Ganti Password
                </button>
            </form>
        </div>
        
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputFoto = document.getElementById("input-foto");
    const avatarPreview = document.getElementById("avatar-preview");
    const fileNameDisplay = document.getElementById("file-name-display");
    
    const formProfil = document.getElementById("form-edit-profil");
    const profileAlert = document.getElementById("profile-alert");
    
    const formPassword = document.getElementById("form-edit-password");
    const passwordAlert = document.getElementById("password-alert");

    // Preview image on selection
    inputFoto.addEventListener("change", function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            
            // Tampilkan nama file
            fileNameDisplay.textContent = file.name;
            
            // Preview
            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            fileNameDisplay.textContent = "Belum ada file terpilih";
        }
    });

    // Update Profile Submit
    formProfil.addEventListener("submit", function (e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Disable button while saving
        const submitBtn = formProfil.querySelector("button[type='submit']");
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = "Menyimpan...";
        
        profileAlert.style.display = "none";
        
        fetch("../api/profile/update_profile.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            if (data.success) {
                profileAlert.className = "alert alert-success";
                profileAlert.textContent = data.message;
                profileAlert.style.display = "block";
                
                // Update display nama
                const newName = document.getElementById("input-nama").value;
                document.getElementById("display-nama").textContent = newName;
                
                // Update all avatars (navbar and sidebar)
                const newSrc = avatarPreview.src;
                document.querySelectorAll(".avatar").forEach(img => {
                    img.src = newSrc;
                });
                
                // Reset file input display
                fileNameDisplay.textContent = "Belum ada file terpilih";
                inputFoto.value = "";
                
                // Scroll to top of form
                profileAlert.scrollIntoView({ behavior: "smooth", block: "nearest" });
            } else {
                profileAlert.className = "alert alert-danger";
                profileAlert.textContent = data.message;
                profileAlert.style.display = "block";
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            profileAlert.className = "alert alert-danger";
            profileAlert.textContent = "Terjadi kesalahan koneksi atau server.";
            profileAlert.style.display = "block";
        });
    });

    // Update Password Submit
    formPassword.addEventListener("submit", function (e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Disable button
        const submitBtn = formPassword.querySelector("button[type='submit']");
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = "Mengubah...";
        
        passwordAlert.style.display = "none";
        
        fetch("../api/profile/update_password.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            if (data.success) {
                passwordAlert.className = "alert alert-success";
                passwordAlert.textContent = data.message;
                passwordAlert.style.display = "block";
                
                // Reset form fields
                formPassword.reset();
                
                passwordAlert.scrollIntoView({ behavior: "smooth", block: "nearest" });
                
                setTimeout(() => {
                    passwordAlert.style.display = "none";
                }, 4000);
            } else {
                passwordAlert.className = "alert alert-danger";
                passwordAlert.textContent = data.message;
                passwordAlert.style.display = "block";
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            passwordAlert.className = "alert alert-danger";
            passwordAlert.textContent = "Terjadi kesalahan koneksi atau server.";
            passwordAlert.style.display = "block";
        });
    });
});
</script>

<?php include "../layouts/footer.php"; ?>
