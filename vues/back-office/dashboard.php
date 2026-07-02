<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administration</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #2c3e50; color: white; padding: 24px 16px; display: flex; flex-direction: column; justify-content: space-between; position: fixed; height: 100vh; }
        .sidebar h2 { color: #1abc9c; font-size: 1.2em; border-bottom: 1px solid #34495e; padding-bottom: 12px; margin-bottom: 16px; }
        .admin-info { background: #34495e; padding: 10px 12px; border-radius: 6px; margin-bottom: 24px; font-size: 0.9em; }
        .admin-info .role { display: inline-block; margin-top: 6px; font-size: 0.75em; padding: 2px 8px; border-radius: 10px; font-weight: bold; }
        .role-admin { background: #e74c3c; }
        .role-moderateur { background: #3498db; }
        nav a { display: block; padding: 10px 12px; color: #ecf0f1; text-decoration: none; border-radius: 6px; margin-bottom: 4px; font-size: 0.95em; }
        nav a:hover, nav a.active { background: #34495e; color: #1abc9c; }
        #btn-admin-logout { width: 100%; padding: 10px; background: #c0392b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .main { margin-left: 250px; flex: 1; padding: 32px; }
        .main h1 { color: #2c3e50; margin-bottom: 6px; }
        .subtitle { color: #7f8c8d; margin-bottom: 32px; font-size: 0.95em; }
        .stats-row { display: flex; gap: 20px; margin-bottom: 32px; }
        .stat-card { flex: 1; background: white; padding: 22px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 0.8em; color: #7f8c8d; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .number { font-size: 2em; font-weight: bold; color: #2c3e50; }
        .stat-card.blue { border-left: 4px solid #3498db; }
        .stat-card.green { border-left: 4px solid #2ecc71; }
        .stat-card.purple { border-left: 4px solid #9b59b6; }
        .section { background: white; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 24px; display: none; }
        .section.active { display: block; }
        .section h2 { margin-bottom: 16px; color: #2c3e50; font-size: 1.1em; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
        th { text-align: left; padding: 10px 12px; background: #f4f6f9; color: #7f8c8d; font-size: 0.8em; text-transform: uppercase; }
        td { padding: 10px 12px; border-bottom: 1px solid #f0f2f5; color: #2c3e50; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85em; }
        .btn-delete:hover { background: #c0392b; }
        .form-inline { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .form-inline input, .form-inline select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9em; }
        .btn-add { background: #2ecc71; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        #section-moderateurs { display: none; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <h2>Panel Admin</h2>
            <div class="admin-info">
                <div>Bienvenue,</div>
                <strong id="admin-name">...</strong>
                <span id="admin-role-badge" class="role">...</span>
            </div>
            <nav>
                <a href="#" class="active" onclick="showSection('stats', this)">📊 Vue générale</a>
                <a href="#" onclick="showSection('articles', this)">📝 Articles</a>
                <a href="#" onclick="showSection('users', this)">👤 Utilisateurs</a>
                <a href="#" id="link-moderateurs" onclick="showSection('moderateurs', this)" style="display:none;">⚙️ Modérateurs</a>
            </nav>
        </div>
        <button id="btn-admin-logout">🚪 Déconnexion</button>
    </div>

    <div class="main">
        <h1>Tableau de bord</h1>
        <p class="subtitle">Bienvenue dans l'espace d'administration.</p>

        <div class="stats-row">
            <div class="stat-card blue"><h3>Membres inscrits</h3><div class="number" id="stat-users">—</div></div>
            <div class="stat-card green"><h3>Articles publiés</h3><div class="number" id="stat-articles">—</div></div>
            <div class="stat-card purple"><h3>Messages échangés</h3><div class="number" id="stat-messages">—</div></div>
        </div>

        <div id="section-articles" class="section active">
            <h2>Gestion des articles</h2>
            <table id="table-articles">
                <thead><tr><th>Auteur</th><th>Contenu</th><th>Date</th><th>Action</th></tr></thead>
                <tbody id="tbody-articles"><tr><td colspan="4">Chargement...</td></tr></tbody>
            </table>
        </div>

        <div id="section-users" class="section">
            <h2>Gestion des utilisateurs</h2>
            <table>
                <thead><tr><th>Nom</th><th>Email</th><th>Inscrit le</th><th>Action</th></tr></thead>
                <tbody id="tbody-users"><tr><td colspan="4">Chargement...</td></tr></tbody>
            </table>
        </div>

        <div id="section-moderateurs" class="section">
            <h2>Gestion des modérateurs</h2>
            <div class="form-inline">
                <input type="text" id="mod-nom" placeholder="Nom">
                <input type="email" id="mod-email" placeholder="Email">
                <input type="password" id="mod-password" placeholder="Mot de passe">
                <select id="mod-role">
                    <option value="moderateur">Modérateur</option>
                    <option value="admin">Administrateur</option>
                </select>
                <button class="btn-add" onclick="addMod()">Ajouter</button>
            </div>
            <table>
                <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Créé le</th><th>Action</th></tr></thead>
                <tbody id="tbody-mods"><tr><td colspan="5">Chargement...</td></tr></tbody>
            </table>
        </div>
    </div>

    <script src="../../assets/js/api.js"></script>
    <script>
        const adminToken = sessionStorage.getItem('admin_token');
        const adminRole  = sessionStorage.getItem('admin_role');
        const adminNom   = sessionStorage.getItem('admin_nom');

        if (!adminToken) { window.location.href = 'login.php'; }

        document.getElementById('admin-name').textContent = adminNom || '?';
        const badge = document.getElementById('admin-role-badge');
        badge.textContent = adminRole;
        badge.className = 'role ' + (adminRole === 'admin' ? 'role-admin' : 'role-moderateur');

        if (adminRole === 'admin') {
            document.getElementById('link-moderateurs').style.display = 'block';
        }

        document.getElementById('btn-admin-logout').addEventListener('click', () => {
            sessionStorage.removeItem('admin_token');
            sessionStorage.removeItem('admin_role');
            sessionStorage.removeItem('admin_nom');
            window.location.href = 'login.php';
        });

        function adminRequest(endpoint, method = 'GET', body = null) {
            const options = {
                method,
                headers: { 'Authorization': 'Bearer ' + adminToken }
            };
            if (body) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }
            return fetch('../../api/admin/' + endpoint, options).then(r => r.json());
        }

        function isApiSuccess(data) {
            return data?.success === true || data?.status === 'success';
        }

        function showSection(name, el) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
            el.classList.add('active');

            if (name === 'stats') {
                document.getElementById('section-articles').classList.add('active');
                loadArticles();
            } else if (name === 'articles') {
                document.getElementById('section-articles').classList.add('active');
                loadArticles();
            } else if (name === 'users') {
                document.getElementById('section-users').classList.add('active');
                loadUsers();
            } else if (name === 'moderateurs') {
                document.getElementById('section-moderateurs').classList.add('active');
                loadMods();
            }
            return false;
        }

        async function loadStats() {
            const data = await adminRequest('stats.php');
            if (isApiSuccess(data)) {
                document.getElementById('stat-users').textContent    = data.stats.users;
                document.getElementById('stat-articles').textContent = data.stats.articles;
                document.getElementById('stat-messages').textContent = data.stats.messages;
            }
        }

        async function loadArticles() {
            const data = await adminRequest('get-articles.php');
            const tbody = document.getElementById('tbody-articles');
            if (!isApiSuccess(data)) { tbody.innerHTML = '<tr><td colspan="4">Erreur.</td></tr>'; return; }
            tbody.innerHTML = data.articles.map(a => `
                <tr>
                    <td>${a.prenom} ${a.nom}</td>
                    <td>${a.description.substring(0, 60)}${a.description.length > 60 ? '...' : ''}</td>
                    <td>${new Date(a.created_at).toLocaleDateString('fr-FR')}</td>
                    <td><button class="btn-delete" onclick="deleteArticle(${a.id})">Supprimer</button></td>
                </tr>`).join('') || '<tr><td colspan="4">Aucun article.</td></tr>';
        }

        async function deleteArticle(id) {
            if (!confirm('Supprimer cet article ?')) return;
            const data = await adminRequest('delete-article.php', 'POST', { id });
            if (isApiSuccess(data)) loadArticles();
            else alert(data.error);
        }

        async function loadUsers() {
            const data = await adminRequest('get-users.php');
            const tbody = document.getElementById('tbody-users');
            if (!isApiSuccess(data)) { tbody.innerHTML = '<tr><td colspan="4">Erreur.</td></tr>'; return; }
            tbody.innerHTML = data.users.map(u => `
                <tr>
                    <td>${u.prenom} ${u.nom}</td>
                    <td>${u.email}</td>
                    <td>${new Date(u.created_at).toLocaleDateString('fr-FR')}</td>
                    <td><button class="btn-delete" onclick="deleteUser(${u.id})">Supprimer</button></td>
                </tr>`).join('') || '<tr><td colspan="4">Aucun utilisateur.</td></tr>';
        }

        async function deleteUser(id) {
            if (!confirm('Supprimer cet utilisateur et tout son contenu ?')) return;
            const data = await adminRequest('delete-user.php', 'POST', { id });
            if (isApiSuccess(data)) { loadUsers(); loadStats(); }
            else alert(data.error);
        }

        async function loadMods() {
            const data = await adminRequest('manage-moderateurs.php');
            const tbody = document.getElementById('tbody-mods');
            if (!isApiSuccess(data)) { tbody.innerHTML = '<tr><td colspan="5">Erreur.</td></tr>'; return; }
            tbody.innerHTML = data.moderateurs.map(m => `
                <tr>
                    <td>${m.nom}</td>
                    <td>${m.email}</td>
                    <td><span class="role ${m.role === 'admin' ? 'role-admin' : 'role-moderateur'}">${m.role}</span></td>
                    <td>${new Date(m.created_at).toLocaleDateString('fr-FR')}</td>
                    <td><button class="btn-delete" onclick="deleteMod(${m.id})">Supprimer</button></td>
                </tr>`).join('') || '<tr><td colspan="5">Aucun compte.</td></tr>';
        }

        async function addMod() {
            const nom      = document.getElementById('mod-nom').value.trim();
            const email    = document.getElementById('mod-email').value.trim();
            const password = document.getElementById('mod-password').value;
            const role     = document.getElementById('mod-role').value;
            if (!nom || !email || !password) { alert('Remplis tous les champs'); return; }
            const data = await adminRequest('manage-moderateurs.php', 'POST', { action: 'add', nom, email, password, role });
            if (isApiSuccess(data)) { loadMods(); document.getElementById('mod-nom').value = ''; document.getElementById('mod-email').value = ''; document.getElementById('mod-password').value = ''; }
            else alert(data.error);
        }

        async function deleteMod(id) {
            if (!confirm('Supprimer ce compte ?')) return;
            const data = await adminRequest('manage-moderateurs.php', 'POST', { action: 'delete', id });
            if (isApiSuccess(data)) loadMods();
            else alert(data.error);
        }

        loadStats();
        loadArticles();
    </script>
</body>
</html>