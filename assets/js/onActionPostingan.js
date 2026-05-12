document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formPostingan");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("../api/create_post.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Server error saat membuat postingan");
        }
        return response.json();
    })
    .then(data => {
        alert(data.message || "Postingan dibuat");
        if (data.success !== false) {
            location.reload();
        }
    })
    .catch(error => {
        alert("Gagal membuat postingan: " + error.message);
    });
    // fetch("../api/create_post.php", {
    //   method: "POST",
    //   body: formData,
    // })
    //   .then((response) => response.text())

    //   .then((data) => {
    //     console.log(data);

    //     alert(data);
    //   })
    //   .catch((error) => {
    //     console.log(error);
    //   });
  });
});
