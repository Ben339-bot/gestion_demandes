<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
include("../connexion.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===== FILTRES =====
$recherche     = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$filtre_type   = isset($_GET['type']) ? trim($_GET['type']) : '';
$filtre_statut = isset($_GET['statut']) ? trim($_GET['statut']) : '';
$tri           = isset($_GET['tri']) ? trim($_GET['tri']) : 'recent';

$filtres_actifs = ($recherche !== '' || $filtre_type !== '' || $filtre_statut !== '');

// ===== REQUETE AVEC FILTRES =====
$sql = "
    SELECT d.*, e.nom, e.prenom, e.numero_etudiant
    FROM demandes d
    INNER JOIN etudiants e ON d.etudiant_id = e.id
    WHERE 1=1
";
$params = [];
$types  = "";

if ($recherche !== '') {
    $sql .= " AND (e.nom LIKE ? OR e.prenom LIKE ? OR e.numero_etudiant LIKE ?)";
    $like = "%" . $recherche . "%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= "sss";
}

if ($filtre_type !== '') {
    $sql .= " AND d.type_demande = ?";
    $params[] = $filtre_type;
    $types   .= "s";
}

if ($filtre_statut !== '') {
    $sql .= " AND d.statut = ?";
    $params[] = $filtre_statut;
    $types   .= "s";
}

// Tri
switch ($tri) {
    case 'ancien':
        $sql .= " ORDER BY d.date_creation ASC";
        break;
    case 'statut':
        $sql .= " ORDER BY d.statut ASC, d.date_creation DESC";
        break;
    default:
        $sql .= " ORDER BY d.date_creation DESC";
}

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $demandes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $demandes = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// ===== STATS GLOBALES (statut) =====
function compterStatut($conn, $statut) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM demandes WHERE statut = ?");
    $stmt->bind_param("s", $statut);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function compterType($conn, $type) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM demandes WHERE type_demande = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

$total      = $conn->query("SELECT COUNT(*) FROM demandes")->fetch_row()[0];
$en_attente = compterStatut($conn, 'en attente');
$en_cours   = compterStatut($conn, 'en cours');
$acceptee   = compterStatut($conn, 'acceptee');

$nb_reclamation = compterType($conn, 'réclamation');
$nb_derogation  = compterType($conn, 'dérogation');
$nb_duplicata   = compterType($conn, 'duplicata');

$admin_nom = $_SESSION['user_nom'];
$initiales = strtoupper(substr($admin_nom, 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — ESI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="mobile-nav">
    <button class="burger" id="burger">☰</button>
    <div class="mobile-logo">⚙️ ESI Admin</div>
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
            <a href="dashboard.php" class="nav-item active">
                <span class="nav-icon">📊</span><span>Tableau de bord</span>
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
                <h1 class="page-title">Tableau de bord Admin</h1>
                <p class="page-subtitle">Gérez toutes les demandes des étudiants</p>
            </div>
            <div class="user-badge">
                <div class="user-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $admin_nom; ?></span>
            </div>
        </div>

        <!-- STATS PAR STATUT -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;">📊</div>
                <div><div class="stat-number"><?php echo $total; ?></div><div class="stat-label">Total</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--warning-light);color:var(--warning-dark);">⏳</div>
                <div><div class="stat-number"><?php echo $en_attente; ?></div><div class="stat-label">En attente</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--info-light);color:#2563eb;">🔄</div>
                <div><div class="stat-number"><?php echo $en_cours; ?></div><div class="stat-label">En cours</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--success-light);color:var(--success);">✅</div>
                <div><div class="stat-number"><?php echo $acceptee; ?></div><div class="stat-label">Acceptées</div></div>
            </div>
        </div>

        <!-- STATS PAR TYPE -->
        <div class="type-stats-row">
            <div class="type-stat reclamation">
                🗒️ Réclamations <span class="count"><?php echo $nb_reclamation; ?></span>
            </div>
            <div class="type-stat derogation">
                📌 Dérogations <span class="count"><?php echo $nb_derogation; ?></span>
            </div>
            <div class="type-stat duplicata">
                📄 Duplicata / Attestations <span class="count"><?php echo $nb_duplicata; ?></span>
            </div>
        </div>

        <!-- BARRE DE FILTRES -->
        <div class="card filter-card">
            <form method="GET" action="dashboard.php" class="filter-form">

                <div class="filter-group filter-grow">
                    <label for="recherche">🔍 Recherche</label>
                    <input type="text" name="recherche" id="recherche"
                           placeholder="Nom, prénom ou n° INE..."
                           value="<?php echo htmlspecialchars($recherche); ?>">
                </div>

                <div class="filter-group">
                    <label for="type">Type de demande</label>
                    <div class="select-wrapper">
                        <select name="type" id="type">
                            <option value="">Tous les types</option>
                            <option value="réclamation" <?php echo $filtre_type == 'réclamation' ? 'selected' : ''; ?>>🗒️ Réclamation</option>
                            <option value="dérogation"  <?php echo $filtre_type == 'dérogation'  ? 'selected' : ''; ?>>📌 Dérogation</option>
                            <option value="duplicata"   <?php echo $filtre_type == 'duplicata'   ? 'selected' : ''; ?>>📄 Duplicata</option>
                        </select>
                    </div>
                </div>

                <div class="filter-group">
                    <label for="statut">Statut</label>
                    <div class="select-wrapper">
                        <select name="statut" id="statut">
                            <option value="">Tous les statuts</option>
                            <option value="en attente" <?php echo $filtre_statut == 'en attente' ? 'selected' : ''; ?>>⏳ En attente</option>
                            <option value="en cours"   <?php echo $filtre_statut == 'en cours'   ? 'selected' : ''; ?>>🔄 En cours</option>
                            <option value="acceptee"   <?php echo $filtre_statut == 'acceptee'   ? 'selected' : ''; ?>>✅ Acceptée</option>
                            <option value="refusee"    <?php echo $filtre_statut == 'refusee'    ? 'selected' : ''; ?>>❌ Refusée</option>
                        </select>
                    </div>
                </div>

                <div class="filter-group">
                    <label for="tri">Trier par</label>
                    <div class="select-wrapper">
                        <select name="tri" id="tri">
                            <option value="recent" <?php echo $tri == 'recent' ? 'selected' : ''; ?>>Plus récentes</option>
                            <option value="ancien" <?php echo $tri == 'ancien' ? 'selected' : ''; ?>>Plus anciennes</option>
                            <option value="statut" <?php echo $tri == 'statut' ? 'selected' : ''; ?>>Par statut</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                    <?php if ($filtres_actifs || $tri != 'recent'): ?>
                        <a href="dashboard.php" class="btn btn-secondary btn-sm">Réinitialiser</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- TABLEAU -->
        <div class="card">
            <div class="card-header-flex">
                <h2>📋 Demandes</h2>
                <span class="result-count">
                    <?php echo count($demandes); ?> résultat<?php echo count($demandes) != 1 ? 's' : ''; ?>
                    <?php if ($filtres_actifs): ?> (filtré<?php echo count($demandes) != 1 ? 's' : ''; ?>)<?php endif; ?>
                </span>
            </div>

            <div style="overflow-x:auto;">
                <table class="table table-admin">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Étudiant</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($demandes) > 0): ?>
                        <?php foreach ($demandes as $d): ?>
                        <?php
                            if ($d['statut'] == 'acceptee') {
                                $badge_class = 'badge-accepte'; $badge_label = '✅ Acceptée';
                            } elseif ($d['statut'] == 'en cours') {
                                $badge_class = 'badge-cours'; $badge_label = '🔄 En cours';
                            } elseif ($d['statut'] == 'refusee') {
                                $badge_class = 'badge-refuse'; $badge_label = '❌ Refusée';
                            } else {
                                $badge_class = 'badge-attente'; $badge_label = '⏳ En attente';
                            }

                            $type_class = match($d['type_demande']) {
                                'réclamation' => 'reclamation',
                                'dérogation'  => 'derogation',
                                default       => 'duplicata'
                            };
                        ?>
                        <tr>
                            <td>#<?php echo str_pad($d['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><strong><?php echo $d['prenom'] . ' ' . $d['nom']; ?></strong></td>
                            <td><span class="demande-type <?php echo $type_class; ?>"><?php echo ucfirst($d['type_demande']); ?></span></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $badge_label; ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($d['date_creation'])); ?></td>
                            <td><a href="traitement.php?id=<?php echo $d['id']; ?>" class="btn btn-primary btn-sm">Traiter</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:var(--text-light);">
                                🔍 Aucune demande ne correspond à ces critères
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
