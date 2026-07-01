async function toggleCommentsSection(articleId) {
  const commentsArea = document.getElementById(`comments-area-${articleId}`);
  const commentsList = document.getElementById(`comments-list-${articleId}`);
  if (!commentsArea) return;

  if (commentsArea.style.display === "block") {
    commentsArea.style.display = "none";
    return;
  }

  commentsArea.style.display = "block";
  commentsList.innerHTML =
    "<p class='loading-text'>Chargement des commentaires...</p>";

  try {
    const data = await apiRequest(
      `articles/get-comments.php?article_id=${articleId}`,
      "GET",
    );
    commentsList.innerHTML =
      data.comments.length === 0
        ? "<p class='empty'>Aucun commentaire. Soyez le premier à réagir !</p>"
        : "";
    data.comments.forEach((comment) => {
      commentsList.insertAdjacentHTML("beforeend", createCommentHtml(comment));
    });
  } catch (error) {
    commentsList.innerHTML = "<p style='color:red;'>Erreur de chargement.</p>";
  }
}

async function handleCommentSubmit(event, articleId) {
  event.preventDefault();
  const input = document.getElementById(`comment-input-${articleId}`);
  const commentsList = document.getElementById(`comments-list-${articleId}`);
  const contenu = input.value.trim();
  if (!contenu) return;

  try {
    const data = await apiRequest("articles/add-comment.php", "POST", {
      article_id: articleId,
      contenu,
    });
    input.value = "";
    if (commentsList.querySelector("p.empty")) commentsList.innerHTML = "";
    commentsList.insertAdjacentHTML(
      "beforeend",
      createCommentHtml(data.comment),
    );
  } catch (error) {
    alert(error.message);
  }
}

function createCommentHtml(comment) {
  return `
        <div class="comment-bubble">
            <img src="${comment.photo_profil || "assets/images/default-avatar.png"}" class="avatar-xs">
            <div class="comment-content">
                <span class="comment-author">${comment.prenom} ${comment.nom}</span>
                <span class="comment-text">${comment.contenu}</span>
            </div>
        </div>
    `;
}
