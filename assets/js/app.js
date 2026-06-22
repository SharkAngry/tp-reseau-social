const routes = {
  login: "vues/clients/login.html",
  register: "vues/clients/register.html",
  "forgot-password": "vues/clients/forgot-password.html",
  accueil: "vues/clients/accueil.html",
  profil: "vues/clients/profil.html",
  amis: "vues/clients/amis.html",
  chat: "vues/clients/chat.html",
};

const PROTECTED_ROUTES = ["accueil", "profil", "amis", "chat"];

async function router() {
  const hash = window.location.hash.replace("#", "") || "login";
  const isLoggedIn = !!sessionStorage.getItem("token");

  if (PROTECTED_ROUTES.includes(hash) && !isLoggedIn) {
    window.location.hash = "login";
    return;
  }

  if (hash === "login" && isLoggedIn) {
    window.location.hash = "accueil";
    return;
  }

  const viewPath = routes[hash];
  if (!viewPath) {
    document.getElementById("app").innerHTML = "<p>Page introuvable</p>";
    return;
  }

  const response = await fetch(viewPath);
  const html = await response.text();
  document.getElementById("app").innerHTML = html;

  if (typeof window.initPage === "function") {
    window.initPage(hash);
  }
}

window.addEventListener("hashchange", router);
window.addEventListener("DOMContentLoaded", router);
