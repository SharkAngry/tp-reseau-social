// Suite de assets/js/feed.js

function initPublishForm() {
    const form = document.getElementById("add-article-form");
    const fileInput = document.getElementById("article-image");
    const filePreview = document.getElementById("file-name-preview");

    if (!form) return;

    // Petite touche UX pour afficher le nom de l'image 
    fileInput.addEventListener("change", () => {
        if (fileInput.files.length > 0) {
            filePreview.textContent = fileInput.files[0].name;
        } else {
            filePreview.textContent = "Aucun fichier sélectionné";
        }
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const description = document.getElementById("article-description").value;
        
        // Utilisation de FormData car il y a potentiellement un fichier image à envoyer
        const formData = new FormData();
        formData.append("description", description);
        
        if (fileInput.files.length > 0) {
            formData.append("image", fileInput.files[0]);
        }

        try {
            // Ici, comme on envoie un FormData, ton wrapper apiRequest doit être configuré 
            // pour ne pas forcer le header "Content-Type: application/json" quand la méthode reçoit un FormData
            // On appelle l'API de création
            const response = await fetch("api/articles/create-article.php", {
                method: "POST",
                headers: {
                    // Récupérer l'ID du sessionStorage s'il existe pour l'envoyer à l'API
                    "X-User-Id": sessionStorage.getItem("user") ? JSON.parse(sessionStorage.getItem("user")).id : "1"
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // On réinitialise le formulaire
                form.reset();
                filePreview.textContent = "Aucun fichier sélectionné";
                
                // On recharge instantanément la liste des articles (ZÉRO rechargement de page)
                loadArticles();
            } else {
                alert("Erreur : " + data.message);
            }

        } catch (error) {
            console.error("Erreur lors de la publication :", error);
            alert("Impossible de publier pour le moment.");
        }
    });
}

// À rajouter dans assets/js/feed.js

async function handleLike(articleId) {
    const likeButton = document.querySelector(`#post-${articleId} .btn-like`);
    const likeCountSpan = document.getElementById(`like-count-${articleId}`);
    
    // Récupérer l'ID utilisateur connecté
    const userId = sessionStorage.getItem("user") ? JSON.parse(sessionStorage.getItem("user")).id : "1";

    try {
        // Envoi asynchrone de la réaction
        const response = await fetch("api/articles/like-article.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-User-Id": userId
            },
            body: JSON.stringify({ article_id: articleId })
        });

        const data = await response.json();

        if (data.success) {
            // Mise à jour instantanée du DOM sans recharger la page
            likeCountSpan.textContent = data.likes_count;

            if (data.action === "added" || data.action === "updated") {
                likeButton.classList.add("active-like");
            } else if (data.action === "removed") {
                likeButton.classList.remove("active-like");
            }
        } else {
            console.error("Erreur renvoyée par l'API :", data.message);
        }

    } catch (error) {
        console.error("Erreur lors du clic sur le like :", error);
    }
}