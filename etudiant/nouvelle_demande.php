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

// Infos étudiant
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$etudiant = $stmt->get_result()->fetch_assoc();
$initiales = strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1));

$erreur  = "";
$succes  = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $type        = trim($_POST['type_demande']);
    $description = trim($_POST['description']);
    $fichier     = null;

    // Validation
    if (empty($type) || empty($description)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif (strlen($description) < 20) {
        $erreur = "La description doit contenir au moins 20 caractères.";
    } else {
        // Gestion upload fichier
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == 0) {
            $ext_autorisees = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
            $taille_max = 5 * 1024 * 1024; // 5 Mo

            if (!in_array($ext, $ext_autorisees)) {
                $erreur = "Format de fichier non autorisé. Utilisez PDF, Word ou image.";
            } elseif ($_FILES['fichier']['size'] > $taille_max) {
                $erreur = "Le fichier dépasse la taille maximale de 5 Mo.";
            } else {
                $nom_fichier = uniqid('fichier_') . '.' . $ext;
                $dossier     = "../uploads/";
                if (!is_dir($dossier)) mkdir($dossier, 0777, true);
                move_uploaded_file($_FILES['fichier']['tmp_name'], $dossier . $nom_fichier);
                $fichier = $nom_fichier;
            }
        }

        if (empty($erreur)) {
            $stmt = $conn->prepare("
                INSERT INTO demandes (etudiant_id, type_demande, description, fichier, statut, date_creation)
                VALUES (?, ?, ?, ?, 'en attente', NOW())
            ");
            $stmt->bind_param("isss", $etudiant_id, $type, $description, $fichier);

            if ($stmt->execute()) {
                $succes = true;
            } else {
                $erreur = "Une erreur est survenue. Veuillez réessayer.";
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
    <title>Nouvelle Demande — ESI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- NAVBAR MOBILE -->
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
            <a href="nouvelle_demande.php" class="nav-item active">
                <span class="nav-icon">📋</span><span>Nouvelle demande</span>
            </a>
            <a href="suivi.php" class="nav-item">
                <span class="nav-icon">🔍</span><span>Mes demandes</span>
            <a href="profil.php" class="nav-item">
                <span class="nav-icon">👤</span><span>Mon profil</span>
            </a>
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
                <h1 class="page-title">Nouvelle demande</h1>
                <p class="page-subtitle">Remplissez le formulaire pour soumettre votre demande</p>
            </div>
            <a href="profil.php" class="user-badge" style="text-decoration:none;color:inherit;">
                <div class="user-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $etudiant['prenom']; ?></span>
            </a>
        </div>

        <?php if ($succes): ?>
        <div class="alert success">
            <span>✅</span>
            <div>
                <strong>Demande envoyée !</strong>
                <p>Votre demande a été soumise avec succès. Suivez son statut dans "Mes demandes".</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($erreur): ?>
        <div class="alert error">
            <span>❌</span>
            <div>
                <strong>Erreur</strong>
                <p><?php echo htmlspecialchars($erreur); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>📝 Formulaire de demande</h2>
            </div>

            <form method="POST" action="nouvelle_demande.php" enctype="multipart/form-data" class="form">

                <div class="form-group">
                    <label for="type_demande">Type de demande <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select name="type_demande" id="type_demande" required>
                            <option value="">— Sélectionner un type —</option>
                            <option value="réclamation" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] == 'réclamation') ? 'selected' : ''; ?>>🗒️ Réclamation sur les notes</option>
                            <option value="dérogation"  <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] == 'dérogation')  ? 'selected' : ''; ?>>📌 Demande de dérogation</option>
                            <option value="duplicata"   <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] == 'duplicata')   ? 'selected' : ''; ?>>📄 Duplicata / Attestation</option>
                        </select>
                    </div>
                </div>

                <!-- Info dynamique -->
                <div class="info-box" id="info-box" style="display:none;">
                    <span id="info-text"></span>
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea name="description" id="description" rows="5"
                        placeholder="Décrivez votre demande en détail..."
                        required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <span class="hint">Minimum 20 caractères. Soyez précis pour faciliter le traitement.</span>
                </div>

                <!-- UPLOAD -->
                <div class="form-group">
                    <label>Pièce jointe <span class="optional">(optionnel)</span></label>
                    <div class="upload-area" id="upload-area">
                        <input type="file" name="fichier" id="fichier" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="upload-content" id="upload-content">
                            <div class="upload-icon">📎</div>
                            <p class="upload-label">Cliquez ou glissez un fichier ici</p>
                            <p class="upload-hint">PDF, Word, JPG, PNG — Max 5 Mo</p>
                        </div>
                        <div class="upload-preview" id="upload-preview" style="display:none;">
                            <span class="file-icon">📄</span>
                            <span class="file-name" id="file-name"></span>
                            <button type="button" class="file-remove" id="file-remove">✕</button>
                        </div>
                    </div>
                    <span class="hint">Ex: relevé de notes, justificatif médical, pièce d'identité...</span>
                </div>

                <div class="form-actions">
                    <a href="accueil.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" name="submit" class="btn btn-primary">Envoyer →</button>
                </div>

            </form>
        </div>

        <!-- Info cards -->
        <div class="info-cards">
            <div class="info-card">
                <div class="info-card-icon">🗒️</div>
                <h3>Réclamation</h3>
                <p>Contestez une note et demandez une vérification de votre copie.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">📌</div>
                <h3>Dérogation</h3>
                <p>Demandez une exception à une règle administrative.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">📄</div>
                <h3>Duplicata</h3>
                <p>Obtenez une attestation ou copie de diplôme officielle.</p>
            </div>
        </div>

    </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>
