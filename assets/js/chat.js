// assets/js/chat.js

let currentReceiverId = null;
let chatInterval = null;

// Récupération de l'utilisateur connecté depuis sessionStorage
const _chatUser = JSON.parse(sessionStorage.getItem("user") || "{}");
let currentUserId = _chatUser.id || null;

// Initialisation de la page chat
const _prevInitPage_chat = window.initPage || function () {};
window.initPage = function (page) {
  _prevInitPage_chat(page);
  if (page === "chat") initChatPage();
};

async function initChatPage() {
  await loadFriendsInSidebar();

  // Filtrage en temps réel dans la sidebar
  const searchInput = document.getElementById("chat-search-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.toLowerCase();
      document.querySelectorAll(".chat-friend-item").forEach((item) => {
        const name = item.dataset.name || "";
        item.style.display = name.includes(query) ? "flex" : "none";
      });
    });
  }
}

// Charge la liste d'amis dans la sidebar
async function loadFriendsInSidebar() {
  const list = document.getElementById("chat-users-list");
  if (!list) return;

  try {
    const data = await apiRequest("friends/get-friends.php", "GET");
    if (!data.friends || data.friends.length === 0) {
      list.innerHTML =
        "<p style='padding:15px; color:#999; font-size:0.9em;'>Aucun ami pour le moment.</p>";
      return;
    }

    list.innerHTML = "";
    data.friends.forEach((friend) => {
      const avatar = friend.avatar || "default-avatar.png";
      const div = document.createElement("div");
      div.className = "chat-friend-item";
      div.dataset.name = `${friend.prenom} ${friend.nom}`.toLowerCase();
      div.style.cssText =
        "display:flex; align-items:center; gap:10px; padding:12px 15px; cursor:pointer; border-bottom:1px solid #eee; transition:background 0.2s;";
      div.innerHTML = `
        <img src="${friend.avatar || "assets/images/default-avatar.png"}"
             style="width:42px; height:42px; border-radius:50%; object-fit:cover;">
        <span style="font-weight:500; color:#1c1e21;">${friend.prenom} ${friend.nom}</span>
      `;
      div.addEventListener(
        "mouseover",
        () => (div.style.background = "#f0f2f5"),
      );
      div.addEventListener("mouseout", () => (div.style.background = ""));
      div.addEventListener("click", () =>
        startChatWithUser(friend.id, `${friend.prenom} ${friend.nom}`),
      );
      list.appendChild(div);
    });
  } catch (error) {
    console.error("Erreur chargement amis sidebar:", error.message);
    list.innerHTML =
      "<p style='padding:15px; color:red; font-size:0.9em;'>Erreur de chargement.</p>";
  }
}

// Charge l'historique des messages
async function loadChatMessages() {
  if (!currentReceiverId) return;

  try {
    const data = await apiRequest(
      `chat/get_messages.php?receiver_id=${currentReceiverId}`,
      "GET",
    );

    if (data.status === "success") {
      const messagesBox = document.getElementById("chat-messages-box");
      const isScrolledToBottom =
        messagesBox.scrollHeight - messagesBox.clientHeight <=
        messagesBox.scrollTop + 50;

      let htmlContent = "";
      data.messages.forEach((msg) => {
        const isMe = parseInt(msg.sender_id) === parseInt(currentUserId);
        const alignStyle = isMe
          ? "align-self:flex-end; background:#0084ff; color:white;"
          : "align-self:flex-start; background:#e4e6eb; color:black;";

        htmlContent += `
          <div style="max-width:60%; padding:10px; border-radius:15px; ${alignStyle}">
            ${msg.contenu ? `<div>${msg.contenu}</div>` : ""}
            ${
              msg.image
                ? `<img src="assets/images/uploads/${msg.image}" 
                        style="max-width:100%; border-radius:10px; margin-top:5px;">`
                : ""
            }
            <small style="display:block; font-size:0.7em; text-align:right; opacity:0.7; margin-top:3px;">
              ${new Date(msg.created_at).toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
              })}
            </small>
          </div>`;
      });

      messagesBox.innerHTML = htmlContent;
      if (isScrolledToBottom || !chatInterval) {
        messagesBox.scrollTop = messagesBox.scrollHeight;
      }
    }
  } catch (error) {
    console.error("Erreur chat:", error.message);
  }
}

// Démarre la discussion avec un ami
function startChatWithUser(receiverId, receiverName) {
  currentReceiverId = receiverId;

  document.getElementById("chat-active-user-header").innerText =
    `Discussion avec ${receiverName}`;
  document.getElementById("chat-receiver-id").value = receiverId;
  document.getElementById("chat-messages-box").innerHTML =
    "<p style='text-align:center; color:#999; margin-top:20px;'>Chargement des messages...</p>";

  loadChatMessages();

  if (chatInterval) clearInterval(chatInterval);
  chatInterval = setInterval(loadChatMessages, 3000);
}

// Envoi d'un message
document.addEventListener("DOMContentLoaded", () => {
  // Le formulaire est injecté dynamiquement, on utilise la délégation d'événement
  document.addEventListener("submit", async (e) => {
    if (!e.target || e.target.id !== "chat-form") return;
    e.preventDefault();

    const messageInput = document.getElementById("chat-message-input");
    const imageInput = document.getElementById("chat-image-input");
    const contenu = messageInput.value.trim();

    if (!contenu && imageInput.files.length === 0) return;
    if (!currentReceiverId) {
      alert("Sélectionnez d'abord un ami pour discuter.");
      return;
    }

    const formData = new FormData();
    formData.append("receiver_id", currentReceiverId);
    formData.append("contenu", contenu);
    if (imageInput.files.length > 0) {
      formData.append("image", imageInput.files[0]);
    }

    try {
      const response = await apiRequest(
        "chat/send_message.php",
        "POST",
        formData,
        true,
      );
      if (response.status === "success") {
        messageInput.value = "";
        imageInput.value = "";
        loadChatMessages();
      }
    } catch (error) {
      alert("Impossible d'envoyer le message : " + error.message);
    }
  });
});
