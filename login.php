<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
include("connexion.php");

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $mdp   = trim($_POST['mot_de_passe']);

    $stmt = $conn->prepare("SELECT * FROM etudiants WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $etudiant = $stmt->get_result()->fetch_assoc();

    if ($etudiant && password_verify($mdp, $etudiant['mot_de_passe'])) {
        $_SESSION['user_id']   = $etudiant['id'];
        $_SESSION['user_role'] = 'etudiant';
        $_SESSION['user_nom']  = $etudiant['nom'];
        header("Location: etudiant/accueil.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
        $_SESSION['user_id']   = $admin['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_nom']  = $admin['nom'];
        header("Location: admin/dashboard.php");
        exit();
    }

    $erreur = "Email ou mot de passe incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ESI</title>
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
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            margin: 20px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        .login-logo {
            height: 60px;
            margin-bottom: 16px;
            filter: brightness(10);
        }

        .login-left h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-left > p {
            font-size: 14px;
            opacity: 0.85;
            line-height: 1.5;
            margin-bottom: 40px;
        }

        .login-features {
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

        .login-right {
            width: 420px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-card { width: 100%; }

        .login-card h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1f36;
            margin-bottom: 6px;
        }

        .login-subtitle {
            color: #9ca3af;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
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

        .input-password { position: relative; }

        .input-password input {
            padding-right: 44px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
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

        .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .login-footer {
            margin-top: 24px;
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                margin: 12px;
                min-height: auto;
            }
            .login-left { padding: 32px 24px; }
            .login-right { width: 100%; padding: 32px 24px; }
        }
    </style>
</head>
<body>

<div class="login-container">

    <div class="login-left">
        <img src="logo_esi.png" alt="ESI" class="login-logo">
        <h1>ESI Portal</h1>
        <p>Application de gestion des demandes étudiantes — École Supérieure d'Informatique de Bobo-Dioulasso</p>
        <div class="login-features">
            <div class="feature-item">📋 Soumettez vos demandes administratives</div>
            <div class="feature-item">🔍 Suivez leur traitement en temps réel</div>
            <div class="feature-item">📄 Réclamations, dérogations, duplicatas</div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-card">
            <h2>Connexion</h2>
            <p class="login-subtitle">Accédez à votre espace personnel</p>

            <?php if (isset($_GET['inscription']) && $_GET['inscription'] == 'ok'): ?>
            <div class="alert-success">
                <span>✅</span>
                <span>Compte créé avec succès ! Connectez-vous ci-dessous.</span>
            </div>
            <?php endif; ?>

            <?php if ($erreur): ?>
            <div class="alert-error">
                <span>❌</span>
                <span><?php echo htmlspecialchars($erreur); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" name="email" id="email"
                           placeholder="votre@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="input-password">
                        <input type="password" name="mot_de_passe" id="mot_de_passe"
                               placeholder="••••••••" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Se connecter →</button>
            </form>

            <p class="login-footer">Pas encore de compte ? <a href="inscription.php" style="color:#6366f1;font-weight:600;text-decoration:none;">S&#39;inscrire</a><br>© 2026 — École Supérieure d&#39;Informatique, Bobo-Dioulasso</p>
        </div>
    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('mot_de_passe');
    const btn = document.querySelector('.toggle-password');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>
</body>
</html>
