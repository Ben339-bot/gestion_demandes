<?php
// ================================================
// Projet : Gestion des demandes étudiantes
// Fichier : inscription.php
// Auteur  : TRAORE Mohamed
// ================================================

ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
include("connexion.php");

$erreur  = "";
$succes  = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom              = trim($_POST['nom']);
    $prenom           = trim($_POST['prenom']);
    $email            = trim($_POST['email']);
    $numero_etudiant  = trim($_POST['numero_etudiant']);
    $mdp              = trim($_POST['mot_de_passe']);
    $mdp_confirm      = trim($_POST['mot_de_passe_confirm']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($numero_etudiant) || empty($mdp)) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif ($mdp !== $mdp_confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {

        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM etudiants WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();

        if ($existe) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO etudiants (nom, prenom, email, mot_de_passe, numero_etudiant) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $nom, $prenom, $email, $mdp_hash, $numero_etudiant);

            if ($stmt->execute()) {
                header("Location: login.php?inscription=ok");
                exit();
            } else {
                $erreur = "Erreur lors de la création du compte. Numéro étudiant peut-être déjà utilisé.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — ESI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 620px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }

        .register-left {
            flex: 1;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        .register-left h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .register-left > p {
            font-size: 14px;
            opacity: 0.85;
            line-height: 1.5;
            margin-bottom: 40px;
        }

        .register-features {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .feature-item {
            font-size: 14px;
            opacity: 0.9;
            padding-left: 12px;
            border-left: 3px solid rgba(255,255,255,0.4);
        }

        .register-right {
            width: 460px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .register-card { width: 100%; }

        .register-card h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1f36;
            margin-bottom: 6px;
        }

        .register-subtitle {
            color: #9ca3af;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-group { margin-bottom: 16px; flex: 1; }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', Arial, sans-serif;
            background: #fafafa;
            color: #374151;
            transition: all 0.2s;
        }

        .form-group input:focus {
            border-color: #6366f1;
            background: white;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', Arial, sans-serif;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }

        .alert-error, .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .register-footer {
            margin-top: 20px;
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }

        .register-footer a {
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                min-height: auto;
            }
            .register-left { padding: 32px 24px; }
            .register-right { width: 100%; padding: 32px 24px; }
            .form-row { flex-direction: column; gap: 0; }
        }
    </style>
</head>
<body>

<div class="register-container">

    <div class="register-left">
        <h1>🎓 ESI Portal</h1>
        <p>Application de gestion des demandes étudiantes — École Supérieure d'Informatique de Bobo-Dioulasso</p>
        <div class="register-features">
            <div class="feature-item">📋 Soumettez vos demandes administratives</div>
            <div class="feature-item">🔍 Suivez leur traitement en temps réel</div>
            <div class="feature-item">📄 Réclamations, dérogations, duplicatas</div>
        </div>
    </div>

    <div class="register-right">
        <div class="register-card">
            <h2>Créer un compte</h2>
            <p class="register-subtitle">Inscrivez-vous en tant qu'étudiant</p>

            <?php if ($erreur): ?>
            <div class="alert-error">
                <span>❌</span>
                <span><?php echo htmlspecialchars($erreur); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($succes): ?>
            <div class="alert-success">
                <span>✅</span>
                <span><?php echo htmlspecialchars($succes); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="inscription.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" id="prenom" required
                               value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" id="nom" required
                               value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="numero_etudiant">Numéro étudiant</label>
                    <input type="text" name="numero_etudiant" id="numero_etudiant" placeholder="ETU2026001" required
                           value="<?php echo isset($_POST['numero_etudiant']) ? htmlspecialchars($_POST['numero_etudiant']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" name="email" id="email" placeholder="votre@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label for="mot_de_passe_confirm">Confirmer</label>
                        <input type="password" name="mot_de_passe_confirm" id="mot_de_passe_confirm" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Créer mon compte →</button>
            </form>

            <p class="register-footer">
                Déjà inscrit ? <a href="login.php">Se connecter</a>
            </p>
        </div>
    </div>

</div>

</body>
</html>
