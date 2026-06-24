<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration - Lot 4</title>
</head>
<body style="margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width: 100%; max-width: 400px; box-sizing: border-box;">
        <h2 style="margin-top: 0; margin-bottom: 20px; color: #333; text-align: center;">Espace Administratif</h2>
        <p style="color: #666; font-size: 0.9em; text-align: center; margin-bottom: 30px;">Réservé aux administrateurs et modérateurs du réseau.</p>
        
        <div id="error-message" style="display: none; background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; border: 1px solid #f5c6cb;"></div>

        <form id="admin-login-form">
            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 8px; color: #555; font-weight: bold; font-size: 0.9em;">Adresse Email</label>
                <input type="email" id="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 100%;">
            </div>

            <div style="margin-bottom: 25px;">
                <label for="password" style="display: block; margin-bottom: 8px; color: #555; font-weight: bold; font-size: 0.9em;">Mot de passe</label>
                <input type="password" id="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 100%;">
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background: #1abc9c; color: white; border: none; border-radius: 4px; font-size: 1em; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                Se connecter
            </button>
        </form>
    </div>

    <script src="../../assets/js/api.js"></script>

    <script>
        document.getElementById("admin-login-form").addEventListener("submit", async (e) => {
            e.preventDefault();

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const errorBox = document.getElementById("error-message");

            // Masquer l'erreur précédente
            errorBox.style.display = "none";

            // Préparation des données brutes en objet pour correspondre au format JSON attendu par login.php
            const credentials = {
                email: email,
                password: password
            };

            try {
                // Appel de l'API configurée dans ton Lot 4
                const response = await apiRequest("lot4/admin/login.php", "POST", credentials);

                if (response.status === "success") {
                    // Sauvegarde des informations de session requises par le sujet
                    sessionStorage.setItem("admin_token", response.token);
                    sessionStorage.setItem("admin_role", response.role);
                    sessionStorage.setItem("admin_nom", response.nom);

                    // Redirection directe vers le tableau de bord
                    window.location.href = "dashboard.php";
                } else {
                    // Affichage de l'erreur renvoyée par le serveur
                    errorBox.innerText = response.message || "Identifiants incorrects.";
                    errorBox.style.display = "block";
                }
            } catch (error) {
                errorBox.innerText = "Une erreur est survenue lors de la connexion.";
                errorBox.style.display = "block";
                console.error("Erreur login admin :", error);
            }
        });
    </script>
</body>
</html>