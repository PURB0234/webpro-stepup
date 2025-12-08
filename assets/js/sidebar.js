const btnSide = document.querySelector('.btn-side');
const sidebar = document.querySelector('.sidebar');

btnSide.addEventListener("click", () => {
  sidebar.classList.toggle("active");
});
