window.initPage = function (page) {
  if (page === "login") {
    initLoginForm();
  }
  if (page === "register") {
    initRegisterForm();
  }
};

function initLoginForm() {
  const form = document.getElementById("login-form");
  const errorEl = document.getElementById("login-error");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorEl.textContent = "";

    const email = document.getElementById("login-email").value;
    const password = document.getElementById("login-password").value;

    try {
      const data = await apiRequest("auth/login.php", "POST", {
        email,
        password,
      });
      sessionStorage.setItem("token", data.token);
      sessionStorage.setItem("user", JSON.stringify(data.user));
      window.location.hash = "accueil";
    } catch (err) {
      errorEl.textContent = err.message;
    }
  });
}

function initRegisterForm() {
  // structure similaire, à compléter avec register.html
}
