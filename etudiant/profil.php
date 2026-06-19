<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
include("../connexion.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'etudiant') {
    header("Location: ../login.php");
    exit();
}

$etudiant_id = $_SESSION['user_id'];
$erreur = "";
$succes = "";

// Récupérer les infos actuelles
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$etudiant = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nom    = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email  = trim($_POST['email']);
    $numero_etudiant = trim($_POST['numero_etudiant']);

    $nouveau_mdp = trim($_POST['nouveau_mdp']);
    $confirm_mdp = trim($_POST['confirm_mdp']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($numero_etudiant)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {

        // Vérifier si l'email est déjà pris par un AUTRE étudiant
        $stmt = $conn->prepare("SELECT id FROM etudiants WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $etudiant_id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $erreur = "Cet email est déjà utilisé par un autre compte.";
        }
        // Vérifier le numéro étudiant
        elseif (true) {
            $stmt = $conn->prepare("SELECT id FROM etudiants WHERE numero_etudiant = ? AND id != ?");
            $stmt->bind_param("si", $numero_etudiant, $etudiant_id);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                $erreur = "Ce numéro étudiant est déjà utilisé par un autre compte.";
            }
        }

        if (empty($erreur)) {

            // Cas avec changement de mot de passe
            if (!empty($nouveau_mdp) || !empty($confirm_mdp)) {
                if (strlen($nouveau_mdp) < 6) {
                    $erreur = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                } elseif ($nouveau_mdp !== $confirm_mdp) {
                    $erreur = "Les nouveaux mots de passe ne correspondent pas.";
                } else {
                    $mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare(
                        "UPDATE etudiants SET nom=?, prenom=?, email=?, numero_etudiant=?, mot_de_passe=? WHERE id=?"
                    );
                    $stmt->bind_param("sssssi", $nom, $prenom, $email, $numero_etudiant, $mdp_hash, $etudiant_id);
                    $stmt->execute();
                    $succes = "Profil et mot de passe mis à jour avec succès !";
                }
            } else {
                // Sans changement de mot de passe
                $stmt = $conn->prepare(
                    "UPDATE etudiants SET nom=?, prenom=?, email=?, numero_etudiant=? WHERE id=?"
                );
                $stmt->bind_param("ssssi", $nom, $prenom, $email, $numero_etudiant, $etudiant_id);
                $stmt->execute();
                $succes = "Profil mis à jour avec succès !";
            }

            if (empty($erreur)) {
                $_SESSION['user_nom'] = $nom;
                // Recharger les infos à jour
                $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
                $stmt->bind_param("i", $etudiant_id);
                $stmt->execute();
                $etudiant = $stmt->get_result()->fetch_assoc();
            }
        }
    }
}

$initiales = strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil — ESI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-form .form-group { margin-bottom: 18px; max-width: 480px; }
        .profile-form label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        .profile-form input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            background: #fafafa;
            color: #374151;
        }
        .profile-form input:focus {
            border-color: #6366f1;
            background: white;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .profile-form .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1f36;
            margin: 28px 0 12px;
        }
        .profile-form .hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: -10px;
            margin-bottom: 18px;
        }
        .alert-success, .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 18px;
            max-width: 480px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<nav class="mobile-nav">
    <button class="burger" id="burger">☰</button>
    <div class="mobile-logo">🎓 ESI Portal</div>
    <div class="user-avatar small"><?php echo $initiales; ?></div>
</nav>

<div class="overlay" id="overlay"></div>

<div class="page-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">🎓</div>
            <span>ESI Portal</span>
        </div>
        <nav class="sidebar-nav">
            <a href="accueil.php" class="nav-item">
                <span class="nav-icon">🏠</span><span>Tableau de bord</span>
            </a>
            <a href="nouvelle_demande.php" class="nav-item">
                <span class="nav-icon">📋</span><span>Nouvelle demande</span>
            </a>
            <a href="suivi.php" class="nav-item">
                <span class="nav-icon">🔍</span><span>Mes demandes</span>
            </a>
            <a href="profil.php" class="nav-item active">
                <span class="nav-icon">👤</span><span>Mon profil</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../login.php" class="nav-item logout">
                <span class="nav-icon">🚪</span><span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <main class="main-content">

        <div class="top-bar">
            <div>
                <h1 class="page-title">Mon profil</h1>
                <p class="page-subtitle">Gérez vos informations personnelles</p>
            </div>
            <a href="accueil.php" class="user-badge" style="text-decoration:none;color:inherit;">
                <div class="user-avatar"><?php echo $initiales; ?></div>
                <span><?php echo htmlspecialchars($etudiant['prenom']); ?></span>
            </a>
        </div>

        <div class="card">

            <?php if ($succes): ?>
            <div class="alert-success"><span>✅</span><span><?php echo htmlspecialchars($succes); ?></span></div>
            <?php endif; ?>

            <?php if ($erreur): ?>
            <div class="alert-error"><span>❌</span><span><?php echo htmlspecialchars($erreur); ?></span></div>
            <?php endif; ?>

            <form method="POST" action="profil.php" class="profile-form">

                <div class="section-title">Informations personnelles</div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" id="prenom" required
                           value="<?php echo htmlspecialchars($etudiant['prenom']); ?>">
                </div>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required
                           value="<?php echo htmlspecialchars($etudiant['nom']); ?>">
                </div>

                <div class="form-group">
                    <label for="numero_etudiant">Numéro étudiant</label>
                    <input type="text" name="numero_etudiant" id="numero_etudiant" required
                           value="<?php echo htmlspecialchars($etudiant['numero_etudiant']); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo htmlspecialchars($etudiant['email']); ?>">
                </div>

                <div class="section-title">Changer le mot de passe</div>
                <p class="hint">Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.</p>

                <div class="form-group">
                    <label for="nouveau_mdp">Nouveau mot de passe</label>
                    <input type="password" name="nouveau_mdp" id="nouveau_mdp" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label for="confirm_mdp">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_mdp" id="confirm_mdp" placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </form>

        </div>

    </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>
