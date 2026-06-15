<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
include("../connexion.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int)$_GET['id'];

// Charger la demande
$stmt = $conn->prepare("
    SELECT d.*, e.nom, e.prenom, e.numero_etudiant, e.email
    FROM demandes d
    INNER JOIN etudiants e ON d.etudiant_id = e.id
    WHERE d.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$demande = $stmt->get_result()->fetch_assoc();

if (!$demande) {
    die("Demande introuvable.");
}

$message = "";
$message_type = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $statut      = $_POST['statut'];
    $commentaire = trim($_POST['commentaire']);

    $stmt = $conn->prepare("
        UPDATE demandes
        SET statut = ?, commentaire_admin = ?, date_modification = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $statut, $commentaire, $id);

    if ($stmt->execute()) {
        $message      = "✅ Demande mise à jour avec succès.";
        $message_type = "success";
        $demande['statut']           = $statut;
        $demande['commentaire_admin'] = $commentaire;
    } else {
        $message      = "❌ Une erreur est survenue.";
        $message_type = "error";
    }
}

$admin_nom = $_SESSION['user_nom'];
$initiales = strtoupper(substr($admin_nom, 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traitement — ESI Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="mobile-nav">
    <button class="burger" id="burger">☰</button>
    <div class="mobile-logo">🎓 ESI Admin</div>
    <div class="user-avatar small"><?php echo $initiales; ?></div>
</nav>

<div class="overlay" id="overlay"></div>

<div class="page-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">⚙️</div>
            <span>ESI Admin</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span><span>Tableau de bord</span>
            </a>
            <a href="traitement.php" class="nav-item active">
                <span class="nav-icon">✏️</span><span>Traiter une demande</span>
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
                <h1 class="page-title">Traitement de demande</h1>
                <p class="page-subtitle">Demande #<?php echo str_pad($id, 3, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="user-badge">
                <div class="user-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $admin_nom; ?></span>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>">
            <span><?php echo $message_type == 'success' ? '✅' : '❌'; ?></span>
            <div><?php echo $message; ?></div>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;" class="traitement-grid">

            <!-- INFOS ÉTUDIANT -->
            <div class="card">
                <div class="card-header">
                    <h2>👤 Informations étudiant</h2>
                </div>
                <div style="padding:20px;">
                    <div class="info-row">
                        <span class="info-label">Nom complet</span>
                        <span><?php echo $demande['prenom'] . ' ' . $demande['nom']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">N° Étudiant</span>
                        <span><?php echo $demande['numero_etudiant']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span><?php echo $demande['email']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type de demande</span>
                        <span class="demande-type <?php echo $demande['type_demande']; ?>">
                            <?php echo ucfirst($demande['type_demande']); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Soumise le</span>
                        <span><?php echo date('d/m/Y à H:i', strtotime($demande['date_creation'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- DESCRIPTION -->
            <div class="card">
                <div class="card-header">
                    <h2>📝 Description de la demande</h2>
                </div>
                <div style="padding:20px;">
                    <p style="font-size:14px;line-height:1.7;color:#374151;">
                        <?php echo nl2br(htmlspecialchars($demande['description'])); ?>
                    </p>
                    <?php if (!empty($demande['fichier'])): ?>
                    <div style="margin-top:16px;">
                        <a href="../uploads/<?php echo $demande['fichier']; ?>" target="_blank" class="fichier-link">
                            📎 Voir la pièce jointe
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- FORMULAIRE DE TRAITEMENT -->
        <div class="card">
            <div class="card-header">
                <h2>⚙️ Traiter la demande</h2>
            </div>
            <form method="POST" class="form">

                <div class="form-group">
                    <label for="statut">Nouveau statut <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select name="statut" id="statut" required>
                            <option value="en attente"  <?php echo $demande['statut'] == 'en attente' ? 'selected' : ''; ?>>⏳ En attente</option>
                            <option value="en cours"    <?php echo $demande['statut'] == 'en cours'   ? 'selected' : ''; ?>>🔄 En cours</option>
                            <option value="acceptee"    <?php echo $demande['statut'] == 'acceptee'   ? 'selected' : ''; ?>>✅ Acceptée</option>
                            <option value="refusee"     <?php echo $demande['statut'] == 'refusee'    ? 'selected' : ''; ?>>❌ Refusée</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="commentaire">Commentaire pour l'étudiant</label>
                    <textarea name="commentaire" id="commentaire" rows="5"
                        placeholder="Expliquez votre décision à l'étudiant..."
                    ><?php echo htmlspecialchars($demande['commentaire_admin'] ?? ''); ?></textarea>
                    <span class="hint" id="compteur">0 caractère</span>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">← Retour</a>
                    <button type="submit" class="btn btn-primary">Enregistrer la décision</button>
                </div>

            </form>
        </div>

    </main>
</div>

<script src="../js/script.js"></script>
<script>
// Compteur de caractères
const textarea = document.getElementById('commentaire');
const compteur = document.getElementById('compteur');
if (textarea) {
    function updateCompteur() {
        compteur.textContent = textarea.value.length + ' caractère(s)';
    }
    textarea.addEventListener('input', updateCompteur);
    updateCompteur();
}
</script>
</body>
</html>
