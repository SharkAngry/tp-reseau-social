window.initPage = window.initPage || function () {};
const originalInitPage = window.initPage;

window.initPage = function (page) {
  originalInitPage(page);
  if (page === "profil") initProfilePage();
};

async function initProfilePage() {
  const data = await apiRequest("users/me.php", "GET");
  const user = data.user;

  document.getElementById("profile-nom").value = user.nom;
  document.getElementById("profile-prenom").value = user.prenom;
  document.getElementById("profile-photo-preview").src =
    user.photo_profil || "assets/images/default-avatar.png";

  document
    .getElementById("profile-photo-input")
    .addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (file) {
        document.getElementById("profile-photo-preview").src =
          URL.createObjectURL(file);
      }
    });

  document
    .getElementById("profile-form")
    .addEventListener("submit", async (e) => {
      e.preventDefault();
      const messageEl = document.getElementById("profile-message");

      const formData = new FormData();
      formData.append("nom", document.getElementById("profile-nom").value);
      formData.append(
        "prenom",
        document.getElementById("profile-prenom").value,
      );

      const photoFile = document.getElementById("profile-photo-input").files[0];
      if (photoFile) formData.append("photo", photoFile);

      try {
        const res = await apiRequest(
          "users/update-profile.php",
          "POST",
          formData,
          true,
        );
        messageEl.style.color = "green";
        messageEl.textContent = res.message;
      } catch (err) {
        messageEl.style.color = "red";
        messageEl.textContent = err.message;
      }
    });

  document
    .getElementById("password-form")
    .addEventListener("submit", async (e) => {
      e.preventDefault();
      const messageEl = document.getElementById("password-message");

      const old_password = document.getElementById("old-password").value;
      const new_password = document.getElementById("new-password").value;

      try {
        const res = await apiRequest("users/update-password.php", "POST", {
          old_password,
          new_password,
        });
        messageEl.style.color = "green";
        messageEl.textContent = res.message;
        e.target.reset();
      } catch (err) {
        messageEl.style.color = "red";
        messageEl.textContent = err.message;
      }
    });
}
