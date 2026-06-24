// Variables globales pour le module de chat
let currentReceiverId = null;
let chatInterval = null;
let currentUserId = null; // À récupérer dynamiquement lors de la connexion (ex via ton sessionStorage)

// 1. FONCTION : Charger l'historique des messages
async function loadChatMessages() {
    if (!currentReceiverId) return;

    try {
        // On utilise la fonction de tes camarades : apiRequest(endpoint, method, body, isFormData)
        // Comme c'est un GET, les paramètres passent dans l'URL
        // Exemple d'ajustement dans tes appels JS :
        const response = await apiRequest(`lot4/chat/get_messages.php?sender_id=${currentUserId}&receiver_id=${currentReceiverId}`, "GET");
        if (response.status === "success") {
            const messagesBox = document.getElementById("chat-messages-box");
            let htmlContent = "";

            response.messages.forEach(msg => {
                // On vérifie si c'est le message de l'utilisateur connecté ou de son ami
                const isMe = (msg.sender_id == currentUserId);
                const alignStyle = isMe ? "align-self: flex-end; background: #0084ff; color: white;" : "align-self: flex-start; background: #e4e6eb; color: black;";
                
                htmlContent += `
                    <div style="max-width: 60%; padding: 10px; border-radius: 15px; ${alignStyle}">
                        ${msg.contenu ? `<div>${msg.contenu}</div>` : ""}
                        ${msg.image ? `<img src="../../assets/images/uploads/${msg.image}" style="max-width: 100%; border-radius: 10px; margin-top: 5px;" />` : ""}
                        <small style="display: block; font-size: 0.7em; text-align: right; opacity: 0.7; margin-top: 3px;">
                            ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                `;
            });

            // Pour éviter que la barre de défilement remonte toute seule pendant le polling
            const isScrolledToBottom = messagesBox.scrollHeight - messagesBox.clientHeight <= messagesBox.scrollTop + 50;
            
            messagesBox.innerHTML = htmlContent;

            if (isScrolledToBottom || !chatInterval) {
                messagesBox.scrollTop = messagesBox.scrollHeight;
            }
        }
    } catch (error) {
        console.error("Erreur chat:", error.message);
    }
}

// 2. FONCTION : Démarrer la discussion avec un ami
function startChatWithUser(receiverId, receiverName) {
    currentReceiverId = receiverId;
    
    // Mettre à jour l'en-tête de la zone de chat
    document.getElementById("chat-active-user-header").innerText = `Discussion avec ${receiverName}`;
    document.getElementById("chat-receiver-id").value = receiverId;
    document.getElementById("chat-messages-box").innerHTML = "Chargement des messages...";
    
    // Charger immédiatement les messages
    loadChatMessages();
    
    // Activer le Polling (Toutes les 3 secondes comme imposé par le sujet !)
    if (chatInterval) clearInterval(chatInterval);
    chatInterval = setInterval(loadChatMessages, 3000);
}

// 3. ÉVÉNEMENT : Soumission du formulaire (Envoi du message)
document.getElementById("chat-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const messageInput = document.getElementById("chat-message-input");
    const imageInput = document.getElementById("chat-image-input");
    const contenu = messageInput.value.trim();

    if (!contenu && imageInput.files.length === 0) return;

    // On prépare un FormData car il y a potentiellement une image (binaire)
    const formData = new FormData();
    formData.append("sender_id", currentUserId);
    formData.append("receiver_id", currentReceiverId);
    formData.append("contenu", contenu);
    if (imageInput.files.length > 0) {
        formData.append("image", imageInput.files[0]);
    }

    try {
        // On appelle l'API via apiRequest en précisant true pour isFormData
        const response = await apiRequest("lot4/chat/send_message.php", "POST", formData, true);

        if (response.status === "success") {
            // Nettoyer les champs du formulaire
            messageInput.value = "";
            imageInput.value = "";
            
            // Recharger immédiatement le flux pour afficher le nouveau message
            loadChatMessages();
        }
    } catch (error) {
        alert("Impossible d'envoyer le message : " + error.message);
    }
});