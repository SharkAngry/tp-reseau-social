<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administration - Lot 4</title>
</head>
<body style="margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; display: flex;">

    <div style="width: 260px; background: #2c3e50; color: white; height: 100vh; padding: 25px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; position: fixed;">
        <div>
            <h2 style="margin-top: 0; font-size: 1.5em; color: #1abc9c; border-bottom: 2px solid #34495e; padding-bottom: 15px;">Panel Admin</h2>
            
            <div style="margin: 20px 0; background: #34495e; padding: 12px; border-radius: 6px;">
                <p style="margin: 0; font-size: 0.85em; color: #bdc3c7;">Bienvenue,</p>
                <p style="margin: 3px 0 0 0; font-weight: bold; color: #fff;" id="admin-name-display">Chargement...</p>
                <span id="admin-role-badge" style="display: inline-block; font-size: 0.75em; padding: 3px 8px; border-radius: 12px; margin-top: 8px; font-weight: bold; text-transform: uppercase;">...</span>
            </div>

            <ul style="list-style: none; padding: 0; margin: 30px 0 0 0; line-height: 2.8;">
                <li><a href="#" style="color: #1abc9c; text-decoration: none; font-weight: bold;">📊 Vue générale</a></li>
                <li><a href="#" style="color: #ecf0f1; text-decoration: none; display: block; transition: 0.2s;">📝 Gérer les articles</a></li>
                <li><a href="#" style="color: #ecf0f1; text-decoration: none; display: block; transition: 0.2s;">👤 Gérer les utilisateurs</a></li>
                
                <li id="menu-exclusive-admin" style="display: none;">
                    <a href="#" style="color: #e74c3c; text-decoration: none; font-weight: bold; display: block; border-top: 1px solid #34495e; margin-top: 15px; padding-top: 15px;">⚙️ Gestion des Modérateurs</a>
                </li>
            </ul>
        </div>

        <button id="logout-btn" style="width: 100%; padding: 12px; background: #c0392b; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s;">
            🚪 Déconnexion
        </button>
    </div>

    <div style="margin-left: 260px; flex: 1; padding: 40px; box-sizing: border-box;">
        <h1 style="margin-top: 0; color: #2c3e50;">Tableau de bord</h1>
        <p style="color: #7f8c8d; margin-top: -10px; margin-bottom: 40px;">Statistiques globales de la plateforme mises à jour en temps réel.</p>
        
        <div style="display: flex; gap: 25px; margin-bottom: 40px;">
            <div style="flex: 1; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-left: 5px solid #3498db;">
                <h3 style="margin: 0; color: #7f8c8d; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px;">Membres Inscrits</h3>
                <p id="stat-users" style="font-size: 2.2em; font-weight: bold; margin: 15px 0 0 0; color: #2c3e50;">0</p>
            </div>
            <div style="flex: 1; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-left: 5px solid #2ecc71;">
                <h3 style="margin: 0; color: #7f8c8d; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px;">Articles Publiés</h3>
                <p id="stat-articles" style="font-size: 2.2em; font-weight: bold; margin: 15px 0 0 0; color: #2c3e50;">0</p>
            </div>
            <div style="flex: 1; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-left: 5px solid #9b59b6;">
                <h3 style="margin: 0; color: #7f8c8d; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px;">Messages Échangés</h3>
                <p id="stat-messages" style="font-size: 2.2em; font-weight: bold; margin: 15px 0 0 0; color: #2c3e50;">0</p>
            </div>
        </div>

        <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
            <h3 style="margin-top: 0; color: #2c3e50;">Activités et rapports</h3>
            <p style="color: #95a5a6; font-size: 0.95em;">Utilise le menu latéral pour modérer les contenus ou analyser les comptes utilisateurs.</p>
        </div>
    </div>

    <script src="../../assets/js/api.js"></script>

    <script>
        // 1. SÉCURITÉ ET DROITS D'ACCÈS
        const token = sessionStorage.getItem("admin_token");
        const role = sessionStorage.getItem("admin_role");
        const nom = sessionStorage.getItem("admin_nom");

        // Si aucun jeton n'existe, blocage immédiat et redirection vers le login
        if (!token) {
            window.location.href = "login.php";
        }

        // Configuration de l'affichage du profil dans la sidebar
        document.getElementById("admin-name-display").innerText = nom || "Utilisateur";
        
        const badge = document.getElementById("admin-role-badge");
        badge.innerText = role;
        if (role === "admin") {
            badge.style.background = "#e74c3c";
            badge.style.color = "#fff";
            // Affichage exclusif du menu de gestion des équipes pour l'administrateur suprême
            document.getElementById("menu-exclusive-admin").style.display = "block";
        } else {
            badge.style.background = "#f39c12";
            badge.style.color = "#fff";
        }

        // 2. RECUPÉRATION DES STATISTIQUES VIA API
        async function loadDashboardStats() {
            try {
                // Requête AJAX AJAX sans recharger la page via la fonction globale apiRequest
                const response = await apiRequest("lot4/admin/stats.php", "GET");
                
                if (response.status === "success") {
                    // Injection dynamique des résultats SQL COUNT dans le DOM HTML
                    document.getElementById("stat-users").innerText = response.stats.users;
                    document.getElementById("stat-articles").innerText = response.stats.articles;
                    document.getElementById("stat-messages").innerText = response.stats.messages;
                } else {
                    console.error("Erreur renvoyée par l'API :", response.message);
                }
            } catch (error) {
                console.error("Erreur lors de la récupération des statistiques :", error);
            }
        }

        // 3. GESTION DE LA DÉCONNEXION
        document.getElementById("logout-btn").addEventListener("click", () => {
            sessionStorage.clear(); // Nettoyer les jetons
            window.location.href = "login.php"; // Retour à l'accueil de connexion
        });

        // Lancement de la fonction de récupération au chargement initial de la page
        loadDashboardStats();
    </script>
</body>
</html>