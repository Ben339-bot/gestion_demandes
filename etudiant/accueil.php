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

// Fonction helper pour compter
function compter($conn, $etudiant_id, $statut) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM demandes WHERE etudiant_id = ? AND statut = ?");
    $stmt->bind_param("is", $etudiant_id, $statut);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

// Total
$stmt = $conn->prepare("SELECT COUNT(*) FROM demandes WHERE etudiant_id = ?");
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_row()[0];

// Stats par statut
$en_attente = compter($conn, $etudiant_id, 'en attente');
$en_cours   = compter($conn, $etudiant_id, 'en cours');
$acceptee   = compter($conn, $etudiant_id, 'acceptee');
$refusee    = compter($conn, $etudiant_id, 'refusee');

// 5 dernières demandes
$stmt = $conn->prepare("SELECT * FROM demandes WHERE etudiant_id = ? ORDER BY date_creation DESC LIMIT 5");
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$demandes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$initiales = strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — ESI</title>
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
            <a href="accueil.php" class="nav-item active">
                <span class="nav-icon">🏠</span><span>Tableau de bord</span>
            </a>
            <a href="nouvelle_demande.php" class="nav-item">
                <span class="nav-icon">📋</span><span>Nouvelle demande</span>
            </a>
            <a href="suivi.php" class="nav-item">
                <span class="nav-icon">🔍</span><span>Mes demandes</span>
            </a>
            <a href="profil.php" class="nav-item">
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
                <h1 class="page-title">Tableau de bord</h1>
                <p class="page-subtitle">Bonjour, <?php echo $etudiant['prenom'] . ' ' . $etudiant['nom']; ?> 👋</p>
            </div>
            <a href="profil.php" class="user-badge" style="text-decoration:none;color:inherit;">
                <div class="user-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $etudiant['prenom']; ?></span>
            </a>
        </div>

        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;">📊</div>
                <div><div class="stat-number"><?php echo $total; ?></div><div class="stat-label">Total</div></div>
            </div>
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
        </div>

        <!-- BOUTON NOUVELLE DEMANDE -->
        <a href="nouvelle_demande.php" class="btn btn-primary" style="margin-bottom:24px;display:inline-flex;">
            + Nouvelle demande
        </a>

        <!-- TABLEAU -->
        <div class="card">
            <div class="card-header-flex">
                <h2>📋 Mes dernières demandes</h2>
                <a href="suivi.php" class="btn btn-secondary btn-sm">Voir toutes →</a>
            </div>

            <div style="overflow-x:auto;">
                <table class="table table-etudiant">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($demandes) > 0): ?>
                        <?php foreach ($demandes as $d): ?>
                        <?php
                            if ($d['statut'] == 'acceptee') {
                                $badge_class = 'badge-accepte';
                                $badge_label = '✅ Acceptée';
                            } elseif ($d['statut'] == 'en cours') {
                                $badge_class = 'badge-cours';
                                $badge_label = '🔄 En cours';
                            } elseif ($d['statut'] == 'refusee') {
                                $badge_class = 'badge-refuse';
                                $badge_label = '❌ Refusée';
                            } else {
                                $badge_class = 'badge-attente';
                                $badge_label = '⏳ En attente';
                            }
                        ?>
                        <tr>
                            <td>#<?php echo str_pad($d['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo ucfirst($d['type_demande']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($d['date_creation'])); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $badge_label; ?></span></td>
                            <td><a href="suivi.php?id=<?php echo $d['id']; ?>" class="btn btn-secondary btn-sm">Voir</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:40px;color:#9ca3af;">
                                📭 Aucune demande pour l'instant
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>
