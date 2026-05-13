document.addEventListener("DOMContentLoaded", function () {
  loadPost();
});

// GET POST

function loadPost() {
  fetch("../api/get_post.php")
    .then((response) => response.json())

    .then((data) => {
      let container = document.getElementById("postContainer");

      container.innerHTML = "";

      data.forEach((post) => {
        container.innerHTML += `

            <div class="post-card">

                <!-- HEADER -->
                <div class="post-header">

                    <img
                    class="avatar"
                    src="../assets/avatar/1.jpg"
                    alt="avatar">

                    <div>

                        <h5>User</h5>

                        <span>
                            Postingan Baru
                        </span>

                    </div>

                </div>


                <!-- IMAGE -->
                <div class="post-content">

                    <img
                    class="post-image"
                    src="../uploads/${post.gambar}"
                    alt="gambar postingan">

                </div>


                <!-- FOOTER -->
                <div class="post-footer">

                    <p>
                        ${post.deskripsi}
                    </p>


                    <!-- INFO -->
                    <div class="post-footer-content">

                        <span>

                            <img
                            class="icon"
                            src="../assets/icon/footsteps.png"
                            alt="footsteps">

                            ${post.langkah} Langkah

                        </span>


                        <span>

                            <img
                            class="icon"
                            src="../assets/icon/route.png"
                            alt="route">

                            ${post.jarak} km

                        </span>


                        <span>

                            <img
                            class="icon"
                            src="../assets/icon/flame.png"
                            alt="flame">

                            ${post.kalori} Kalori

                        </span>

                    </div>


                    <!-- ACTION BUTTON -->
                    <div class="post-actions">

                        <!-- LIKE -->
                        <button>

                            <img
                            class="icon"
                            src="../assets/icon/like.png"
                            alt="like">

                            0 Suka

                        </button>


                        <!-- COMMENT -->
                        <button>

                            <img
                            class="icon"
                            src="../assets/icon/chat-bubble.png"
                            alt="comment">
                            
                            0 Komentar
                            
                            </button>
                            
                            
                            <!-- DELETE -->
                            <button
                            onclick="deletePost(${post.id})">
                            <img
                            class="icon"
                            src="../assets/icon/delete.png"
                            alt="delete">

                            Hapus

                        </button>

                    </div>

                </div>

            </div>

            `;
      });
    });
}

// DELETE POST

function deletePost(id) {
  let confirmDelete = confirm("Yakin ingin menghapus postingan ini?");

  if (!confirmDelete) {
    return;
  }

  let formData = new FormData();

  formData.append("id", id);

  fetch("../api/delete_post.php", {
    method: "POST",

    body: formData,
  })
    .then((response) => response.json())

    .then((data) => {
      alert(data.message);

      loadPost();
    })

    .catch((error) => {
      console.log(error);

      alert("Terjadi kesalahan");
    });
}
