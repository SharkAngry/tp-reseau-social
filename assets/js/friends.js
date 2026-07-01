const _prevInitPage_friends = window.initPage || function () {};
window.initPage = function (page) {
  _prevInitPage_friends(page);
  if (page === "amis") initFriendsModule();
};

function initFriendsModule() {
  loadSuggestions();
  loadInvitations();
  loadFriendsList();

  const searchInput = document.getElementById("search-users-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) =>
      loadSuggestions(e.target.value),
    );
  }
}

async function loadSuggestions(searchQuery = "") {
  const el = document.getElementById("search-results");
  if (!el) return;
  try {
    const data = await apiRequest(
      `friends/search.php?query=${encodeURIComponent(searchQuery)}`,
      "GET",
    );
    el.innerHTML =
      data.users.length === 0
        ? "<p class='empty'>Aucun utilisateur trouvé.</p>"
        : "";
    data.users.forEach((user) => {
      el.innerHTML += `<div class="user-card">
                <img src="${user.avatar || "assets/images/default-avatar.png"}" class="avatar-sm">
                <h4>${user.prenom} ${user.nom}</h4>
                <div class="card-actions"><button class="btn-action btn-add" onclick="handleFriendAction('send', ${user.id})">Ajouter</button></div>
            </div>`;
    });
  } catch (error) {
    console.error(error);
  }
}

async function loadInvitations() {
  const el = document.getElementById("invitations-list");
  if (!el) return;
  try {
    const data = await apiRequest("friends/get-invitations.php", "GET");
    el.innerHTML =
      data.invitations.length === 0
        ? "<p class='empty'>Aucune invitation.</p>"
        : "";
    data.invitations.forEach((invite) => {
      el.innerHTML += `<div class="user-card alert-card">
                <img src="${invite.avatar || "assets/images/default-avatar.png"}" class="avatar-sm">
                <h4>${invite.prenom} ${invite.nom}</h4>
                <div class="card-actions">
                    <button class="btn-action btn-accept" onclick="handleFriendAction('accept', ${invite.id})">Accepter</button>
                    <button class="btn-action btn-decline" onclick="handleFriendAction('decline', ${invite.id})">Refuser</button>
                </div>
            </div>`;
    });
  } catch (error) {
    console.error(error);
  }
}

async function loadFriendsList() {
  const el = document.getElementById("my-friends-list");
  if (!el) return;
  try {
    const data = await apiRequest("friends/get-friends.php", "GET");
    el.innerHTML =
      data.friends.length === 0
        ? "<p class='empty'>Vous n'avez pas encore d'amis.</p>"
        : "";
    data.friends.forEach((friend) => {
      el.innerHTML += `<div class="user-card">
                <img src="${friend.avatar || "assets/images/default-avatar.png"}" class="avatar-sm">
                <h4>${friend.prenom} ${friend.nom}</h4>
                <div class="card-actions"><button class="btn-action btn-remove" onclick="handleFriendAction('remove', ${friend.id})">Retirer</button></div>
            </div>`;
    });
  } catch (error) {
    console.error(error);
  }
}

async function handleFriendAction(actionType, targetId) {
  try {
    await apiRequest("friends/action.php", "POST", {
      action: actionType,
      target_id: targetId,
    });
    initFriendsModule();
  } catch (error) {
    alert(error.message || "Une erreur est survenue.");
  }
}
