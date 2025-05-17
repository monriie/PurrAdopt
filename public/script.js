const popup = document.getElementById("popup");
const openButton = document.getElementById("openPopup");
const closeButton = document.getElementById("closePopup");

// buka popup
openButton.addEventListener("click", function () {
  popup.classList.remove("hidden");
  popup.showModal();
  document.body.classList.add("overflow-hidden");
});

// tutup popup
closeButton.addEventListener("click", function () {
  popup.classList.add("hidden");
  popup.close();
  document.body.classList.remove("overflow-hidden");
});

// Tutup popup saat mengklik di luar
popup.addEventListener("click", function (event) {
  if (event.target === popup) {
    popup.classList.add("hidden");
    popup.close();
    document.body.classList.remove("overflow-hidden");
  }
});


