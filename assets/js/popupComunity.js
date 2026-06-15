function bukaPopup(){
    document.getElementById("popup").style.display = "block";
}

function tutupPopup(){
    document.getElementById("popup").style.display = "none";
}

// Close comment popup when clicking overlay
document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.getElementById("commentOverlay");
    if (overlay) {
        overlay.addEventListener("click", function () {
            tutupKomentar();
        });
    }
});