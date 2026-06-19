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

// Toutes les demandes de l'étudiant
$stmt = $conn->prepare("SELECT * FROM demandes WHERE etudiant_id = ? ORDER BY date_creation DESC");
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$demandes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Stats
function compter($conn, $etudiant_id, $statut) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM demandes WHERE etudiant_id = ? AND statut = ?");
    $stmt->bind_param("is", $etudiant_id, $statut);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

$en_attente = compter($conn, $etudiant_id, 'en attente');
$en_cours   = compter($conn, $etudiant_id, 'en cours');
$acceptee   = compter($conn, $etudiant_id, 'acceptee');
$refusee    = compter($conn, $etudiant_id, 'refusee');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes demandes — ESI</title>
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
            <a href="nouvelle_demande.php" class="nav-item">
                <span class="nav-icon">📋</span><span>Nouvelle demande</span>
            </a>
            <a href="suivi.php" class="nav-item active">
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
                <h1 class="page-title">Mes demandes</h1>
                <p class="page-subtitle">Consultez l'état de toutes vos demandes</p>
            </div>
            <a href="profil.php" class="user-badge" style="text-decoration:none;color:inherit;">
                <div class="user-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $etudiant['prenom']; ?></span>
            </a>
        </div>

        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">⏳</div>
                <div><div class="stat-number"><?php echo $en_attente; ?></div><div class="stat-label">En attente</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;">🔄</div>
                <div><div class="stat-number"><?php echo $en_cours; ?></div><div class="stat-label">En cours</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1fae5;color:#059669;">✅</div>
                <div><div class="stat-number"><?php echo $acceptee; ?></div><div class="stat-label">Acceptées</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;">❌</div>
                <div><div class="stat-number"><?php echo $refusee; ?></div><div class="stat-label">Refusées</div></div>
            </div>
        </div>

        <!-- LISTE DES DEMANDES -->
        <div class="card">
            <div class="card-header-flex">
                <h2>📋 Liste des demandes</h2>
                <a href="nouvelle_demande.php" class="btn btn-primary btn-sm">+ Nouvelle demande</a>
            </div>

            <?php if (count($demandes) > 0): ?>
                <?php foreach ($demandes as $i => $d): ?>
                <?php
                    if ($d['statut'] == 'acceptee') {
                        $badge_class = 'badge-accepte'; $badge_label = '✅ Acceptée';
                        $response_class = 'accepte';
                    } elseif ($d['statut'] == 'en cours') {
                        $badge_class = 'badge-cours'; $badge_label = '🔄 En cours';
                        $response_class = 'en-cours';
                    } elseif ($d['statut'] == 'refusee') {
                        $badge_class = 'badge-refuse'; $badge_label = '❌ Refusée';
                        $response_class = 'refuse';
                    } else {
                        $badge_class = 'badge-attente'; $badge_label = '⏳ En attente';
                        $response_class = 'empty';
                    }

                    $type_class = match($d['type_demande']) {
                        'réclamation' => 'reclamation',
                        'dérogation'  => 'derogation',
                        default       => 'duplicata'
                    };
                ?>

                <!-- DEMANDE ITEM -->
                <div class="demande-item" onclick="toggleDetail('d<?php echo $d['id']; ?>')">
                    <div class="demande-left">
                        <div class="demande-type <?php echo $type_class; ?>">
                            <?php echo ucfirst($d['type_demande']); ?>
                        </div>
                        <div class="demande-info">
                            <p class="demande-desc"><?php echo htmlspecialchars(substr($d['description'], 0, 80)) . '...'; ?></p>
                            <p class="demande-date">📅 Soumise le <?php echo date('d/m/Y', strtotime($d['date_creation'])); ?></p>
                        </div>
                    </div>
                    <div class="demande-right">
                        <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_label; ?></span>
                        <span class="toggle-icon" id="icon-d<?php echo $d['id']; ?>">▾</span>
                    </div>
                </div>

                <!-- DETAIL -->
                <div class="demande-detail" id="d<?php echo $d['id']; ?>">
                    <div class="detail-grid">
                        <div class="detail-block">
                            <h4>Description complète</h4>
                            <p><?php echo nl2br(htmlspecialchars($d['description'])); ?></p>
                        </div>
                        <div class="detail-block">
                            <h4>Pièce jointe</h4>
                            <?php if (!empty($d['fichier'])): ?>
                                <a href="../uploads/<?php echo $d['fichier']; ?>" target="_blank" class="fichier-link">
                                    📎 Voir le fichier joint
                                </a>
                            <?php else: ?>
                                <span class="no-file">Aucun fichier joint</span>
                            <?php endif; ?>
                        </div>
                        <div class="detail-block full">
                            <h4>Réponse de l'administration</h4>
                            <div class="admin-response <?php echo $response_class; ?>">
                                <?php if (!empty($d['commentaire_admin'])): ?>
                                    <?php if ($d['statut'] == 'acceptee'): ?>
                                        <strong>✅ Demande acceptée</strong>
                                    <?php elseif ($d['statut'] == 'refusee'): ?>
                                        <strong>❌ Demande refusée</strong>
                                    <?php endif; ?>
                                    <p><?php echo nl2br(htmlspecialchars($d['commentaire_admin'])); ?></p>
                                <?php elseif ($d['statut'] == 'en cours'): ?>
                                    <span>🔄 Votre dossier est en cours d'examen par le service de scolarité.</span>
                                <?php else: ?>
                                    <span>🕐 En attente de traitement par l'administration...</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>

            <?php else: ?>
                <div style="text-align:center;padding:50px;color:#9ca3af;">
                    <div style="font-size:48px;margin-bottom:16px;">📭</div>
                    <p style="font-size:16px;font-weight:500;">Vous n'avez pas encore de demande</p>
                    <a href="nouvelle_demande.php" class="btn btn-primary" style="margin-top:16px;display:inline-flex;">
                        + Soumettre une demande
                    </a>
                </div>
            <?php endif; ?>

        </div>

    </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>
