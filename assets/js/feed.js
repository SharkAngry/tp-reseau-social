const _prevInitPage_feed = window.initPage || function () {};
window.initPage = function (page) {
  _prevInitPage_feed(page);
  if (page === "accueil") {
    initPublishForm();
    loadArticles();
  }
};

function initPublishForm() {
  const form = document.getElementById("add-article-form");
  const fileInput = document.getElementById("article-image");
  const filePreview = document.getElementById("file-name-preview");
  if (!form) return;

  fileInput.addEventListener("change", () => {
    filePreview.textContent =
      fileInput.files.length > 0
        ? fileInput.files[0].name
        : "Aucun fichier sélectionné";
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const description = document.getElementById("article-description").value;

    const formData = new FormData();
    formData.append("description", description);
    if (fileInput.files.length > 0)
      formData.append("image", fileInput.files[0]);

    try {
      await apiRequest("articles/create-article.php", "POST", formData, true);
      form.reset();
      filePreview.textContent = "Aucun fichier sélectionné";
      loadArticles();
    } catch (error) {
      alert("Erreur : " + error.message);
    }
  });
}

async function loadArticles() {
  const container = document.getElementById("articles-flux");
  if (!container) return;

  try {
    const data = await apiRequest("articles/get-articles.php", "GET");
    container.innerHTML =
      data.articles.length === 0
        ? "<p class='empty'>Aucune publication pour le moment.</p>"
        : "";
    data.articles.forEach((article) => {
      container.insertAdjacentHTML("beforeend", createArticleHtml(article));
    });
  } catch (error) {
    container.innerHTML =
      "<p style='color:red;'>Erreur de chargement du fil.</p>";
  }
}

function createArticleHtml(article) {
  const avatar = article.photo_profil || "assets/images/default-avatar.png";
  const imageHtml = article.image
    ? `<img src="assets/images/posts/${article.image}" class="post-image">`
    : "";
  const likedClass = article.my_reaction === "like" ? "active-like" : "";
  const dislikedClass =
    article.my_reaction === "dislike" ? "active-dislike" : "";

  return `
        <div class="post-card" id="post-${article.id}">
            <div class="post-header">
                <img src="${avatar}" class="avatar-sm">
                <div>
                    <h4>${article.prenom} ${article.nom}</h4>
                    <span class="post-date">${new Date(article.created_at).toLocaleString("fr-FR")}</span>
                </div>
            </div>
            <p class="post-description">${article.description}</p>
            ${imageHtml}
            <div class="post-actions">
                <button class="btn-like ${likedClass}" onclick="handleReaction(${article.id}, 'like')">
                    👍 <span id="like-count-${article.id}">${article.likes_count}</span>
                </button>
                <button class="btn-dislike ${dislikedClass}" onclick="handleReaction(${article.id}, 'dislike')">
                    👎 <span id="dislike-count-${article.id}">${article.dislikes_count}</span>
                </button>
                <button class="btn-comment" onclick="toggleCommentsSection(${article.id})">💬 Commenter</button>
            </div>
            <div id="comments-area-${article.id}" class="comments-area" style="display:none;">
                <div id="comments-list-${article.id}"></div>
                <form onsubmit="handleCommentSubmit(event, ${article.id})" class="comment-form">
                    <input type="text" id="comment-input-${article.id}" placeholder="Écrire un commentaire...">
                    <button type="submit">Envoyer</button>
                </form>
            </div>
        </div>
    `;
}
// Function to handle like/dislike reactions
async function handleReaction(articleId, type) {
  try {
    const data = await apiRequest("articles/like-article.php", "POST", {
      article_id: articleId,
      type,
    });
    document.getElementById(`like-count-${articleId}`).textContent =
      data.likes_count;
    document.getElementById(`dislike-count-${articleId}`).textContent =
      data.dislikes_count;

    const likeBtn = document.querySelector(`#post-${articleId} .btn-like`);
    const dislikeBtn = document.querySelector(
      `#post-${articleId} .btn-dislike`,
    );
    likeBtn.classList.remove("active-like");
    dislikeBtn.classList.remove("active-dislike");

    if (data.action !== "removed") {
      (data.type === "like" ? likeBtn : dislikeBtn).classList.add(
        data.type === "like" ? "active-like" : "active-dislike",
      );
    }
  } catch (error) {
    console.error(error);
  }
}
