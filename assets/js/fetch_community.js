document.addEventListener("DOMContentLoaded", function () {
  loadPost();
  setupCommentForm();
});

// GET POST

function loadPost() {
  fetch("../api/community/get.php")
    .then((response) => response.json())
    .then((data) => {
      let container = document.getElementById("postContainer");
      if (!container) return;

      container.innerHTML = "";

      data.forEach((post) => {
        // Nama user pembuat post
        let namaUser = post.nama_user || "User";

        // Tombol hapus hanya muncul jika post milik user yang login
        let deleteButton = "";
        if (post.user_id == currentUserId) {
          deleteButton = `
            <!-- DELETE -->
            <button onclick="deletePost(${post.id})">
              <img
                class="icon"
                src="../assets/icon/delete.png"
                alt="delete">
              Hapus
            </button>
          `;
        }

        container.innerHTML += `

            <div class="post-card">

                <!-- HEADER -->
                <div class="post-header">

                    <img
                    class="avatar"
                    src="${post.foto_user ? '../uploads/profiles/' + post.foto_user : '../assets/avatar/1.jpg'}"
                    alt="avatar">

                    <div>

                        <h5>${namaUser}</h5>

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
                        <button onclick="bukaKomentar(${post.id})">

                            <img
                            class="icon"
                            src="../assets/icon/chat-bubble.png"
                            alt="comment">
                            
                            <span id="commentCount-${post.id}">0</span> Komentar
                            
                        </button>

                        ${deleteButton}

                    </div>

                </div>

            </div>

            `;
      });

      // Load comment counts for all posts
      data.forEach((post) => {
        loadCommentCount(post.id);
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

  fetch("../api/community/delete.php", {
    method: "POST",

    body: formData,
  })
    .then((response) => response.json())

    .then((data) => {
      if (data.success === false) {
        alert(data.message);
      } else {
        alert(data.message);
        loadPost();
      }
    })

    .catch((error) => {
      console.log(error);

      alert("Terjadi kesalahan");
    });
}


// =============================================
// COMMENT POPUP FUNCTIONS
// =============================================

// Open comment popup
function bukaKomentar(postId) {
  document.getElementById("commentPostId").value = postId;
  document.getElementById("commentOverlay").classList.add("active");
  document.getElementById("commentPopup").classList.add("active");
  document.body.style.overflow = "hidden";

  // Load comments for this post
  loadComments(postId);

  // Focus input after animation
  setTimeout(() => {
    document.getElementById("commentInput").focus();
  }, 350);
}

// Close comment popup
function tutupKomentar() {
  document.getElementById("commentOverlay").classList.remove("active");
  document.getElementById("commentPopup").classList.remove("active");
  document.body.style.overflow = "";
  document.getElementById("commentInput").value = "";
}

// Load comments from API
function loadComments(postId) {
  let commentList = document.getElementById("commentList");

  // Show loading
  commentList.innerHTML = `
    <div class="comment-loading">
      <div class="comment-loading-spinner"></div>
      <p>Memuat komentar...</p>
    </div>
  `;

  fetch("../api/community/get_comments.php?community_id=" + postId)
    .then((response) => response.json())
    .then((result) => {
      if (result.success && result.data.length > 0) {
        commentList.innerHTML = "";

        result.data.forEach((comment) => {
          let timeAgo = formatTimeAgo(comment.created_at);
          let namaKomentator = comment.nama_user || "User";

          // Badge jika komentar dari user yang sedang login
          let isOwner = comment.user_id == currentUserId;
          let ownerBadge = isOwner
            ? '<span class="comment-badge-you">Anda</span>'
            : '';

          commentList.innerHTML += `
            <div class="comment-item ${isOwner ? 'comment-mine' : ''}">
              <img class="avatar" src="${comment.foto_user ? '../uploads/profiles/' + comment.foto_user : '../assets/avatar/1.jpg'}" alt="avatar">
              <div class="comment-body">
                <div class="comment-meta">
                  <strong>${namaKomentator}</strong>
                  ${ownerBadge}
                  <span class="comment-time">${timeAgo}</span>
                </div>
                <p class="comment-text">${comment.komentar}</p>
              </div>
            </div>
          `;
        });

        // Scroll to bottom
        commentList.scrollTop = commentList.scrollHeight;
      } else {
        commentList.innerHTML = `
          <div class="comment-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#bcc1ca" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p>Belum ada komentar</p>
            <span>Jadilah yang pertama berkomentar!</span>
          </div>
        `;
      }
    })
    .catch((error) => {
      console.log(error);
      commentList.innerHTML = `
        <div class="comment-empty">
          <p>Gagal memuat komentar</p>
        </div>
      `;
    });
}

// Load comment count for a post
function loadCommentCount(postId) {
  fetch("../api/community/get_comments.php?community_id=" + postId)
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        let countEl = document.getElementById("commentCount-" + postId);
        if (countEl) {
          countEl.textContent = result.data.length;
        }
      }
    })
    .catch((error) => {
      console.log(error);
    });
}

// Setup comment form submission
function setupCommentForm() {
  const form = document.getElementById("formKomentar");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    let postId = document.getElementById("commentPostId").value;
    let komentarInput = document.getElementById("commentInput");
    let komentar = komentarInput.value.trim();

    if (!komentar) return;

    let formData = new FormData();
    formData.append("community_id", postId);
    formData.append("komentar", komentar);

    // Disable input while sending
    komentarInput.disabled = true;

    fetch("../api/community/post_comment.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          komentarInput.value = "";
          komentarInput.disabled = false;
          komentarInput.focus();

          // Reload comments in popup
          loadComments(postId);

          // Update comment count on the post card
          let countEl = document.getElementById("commentCount-" + postId);
          if (countEl && result.total_komentar) {
            countEl.textContent = result.total_komentar;
          }
        } else {
          alert(result.message || "Gagal mengirim komentar");
          komentarInput.disabled = false;
        }
      })
      .catch((error) => {
        console.log(error);
        alert("Terjadi kesalahan saat mengirim komentar");
        komentarInput.disabled = false;
      });
  });
}

// Format time ago helper
function formatTimeAgo(dateString) {
  let date = new Date(dateString);
  let now = new Date();
  let diffMs = now - date;
  let diffSec = Math.floor(diffMs / 1000);
  let diffMin = Math.floor(diffSec / 60);
  let diffHour = Math.floor(diffMin / 60);
  let diffDay = Math.floor(diffHour / 24);

  if (diffSec < 60) return "Baru saja";
  if (diffMin < 60) return diffMin + " menit lalu";
  if (diffHour < 24) return diffHour + " jam lalu";
  if (diffDay < 7) return diffDay + " hari lalu";

  return date.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}
