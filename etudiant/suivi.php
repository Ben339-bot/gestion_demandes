<?php
// Session à vérifier plus tard (quand Mohamed aura fini)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de demande — ESI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- NAVBAR MOBILE -->
<nav class="mobile-nav">
    <button class="burger" id="burger">☰</button>
    <div class="mobile-logo">ESI Portal</div>
    <div class="user-avatar small">SR</div>
</nav>

<!-- OVERLAY -->
<div class="overlay" id="overlay"></div>

<div class="page-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon"></div>
            <span>ESI Portal</span>
        </div>
        <nav class="sidebar-nav">
            <a href="accueil.php" class="nav-item">
                <span class="nav-icon"></span>
                <span>Tableau de bord</span>
            </a>
            <a href="nouvelle_demande.php" class="nav-item">
                <span class="nav-icon"></span>
                <span>Nouvelle demande</span>
            </a>
            <a href="suivi.php" class="nav-item active">
                <span class="nav-icon"></span>
                <span>Mes demandes</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../login.php" class="nav-item logout">
                <span class="nav-icon"></span>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">

        <div class="top-bar">
            <div>
                <h1 class="page-title">Mes demandes</h1>
                <p class="page-subtitle">Consultez l'état de toutes vos demandes</p>
            </div>
            <div class="user-badge">
                <div class="user-avatar">SR</div>
                <span>Soma Rahim</span>
            </div>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7; color:#d97706;"></div>
                <div>
                    <div class="stat-number">2</div>
                    <div class="stat-label">En attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe; color:#2563eb;"></div>
                <div>
                    <div class="stat-number">1</div>
                    <div class="stat-label">En cours</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1fae5; color:#059669;"></div>
                <div>
                    <div class="stat-number">3</div>
                    <div class="stat-label">Acceptées</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"></div>
                <div>
                    <div class="stat-number">1</div>
                    <div class="stat-label">Refusées</div>
                </div>
            </div>
        </div>

        <!-- LISTE DES DEMANDES (données statiques pour l'instant) -->
        <div class="card">
            <div class="card-header-flex">
                <h2> Liste des demandes</h2>
                <a href="nouvelle_demande.php" class="btn btn-primary btn-sm">+ Nouvelle demande</a>
            </div>

            <!-- DEMANDE 1 -->
            <div class="demande-item" onclick="toggleDetail('d1')">
                <div class="demande-left">
                    <div class="demande-type reclamation"> Réclamation</div>
                    <div class="demande-info">
                        <p class="demande-desc">Contestation de la note obtenue en Algorithmique S3</p>
                        <p class="demande-date"> Soumise le 02 juin 2026</p>
                    </div>
                </div>
                <div class="demande-right">
                    <span class="badge badge-attente"> En attente</span>
                    <span class="toggle-icon" id="icon-d1">▾</span>
                </div>
            </div>
            <div class="demande-detail" id="d1">
                <div class="detail-grid">
                    <div class="detail-block">
                        <h4>Description</h4>
                        <p>Je souhaite contester ma note de 8/20 obtenue en Algorithmique lors de la session S3. Je pense qu'il y a eu une erreur de correction sur la question 3.</p>
                    </div>
                    <div class="detail-block">
                        <h4>Pièce jointe</h4>
                        <a href="#" class="fichier-link"> releve_notes_s3.pdf</a>
                    </div>
                    <div class="detail-block full">
                        <h4>Réponse de l'administration</h4>
                        <div class="admin-response empty">
                            <span> En attente de traitement par l'administration...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DEMANDE 2 -->
            <div class="demande-item" onclick="toggleDetail('d2')">
                <div class="demande-left">
                    <div class="demande-type derogation"> Dérogation</div>
                    <div class="demande-info">
                        <p class="demande-desc">Demande d'inscription tardive en raison d'hospitalisation</p>
                        <p class="demande-date">Soumise le 28 mai 2026</p>
                    </div>
                </div>
                <div class="demande-right">
                    <span class="badge badge-cours">En cours</span>
                    <span class="toggle-icon" id="icon-d2">▾</span>
                </div>
            </div>
            <div class="demande-detail" id="d2">
                <div class="detail-grid">
                    <div class="detail-block">
                        <h4>Description</h4>
                        <p>Suite à une hospitalisation d'urgence du 20 au 26 mai, je n'ai pas pu effectuer mon inscription dans les délais. Je joins le certificat médical en pièce jointe.</p>
                    </div>
                    <div class="detail-block">
                        <h4>Pièce jointe</h4>
                        <a href="#" class="fichier-link">certificat_medical.pdf</a>
                    </div>
                    <div class="detail-block full">
                        <h4>Réponse de l'administration</h4>
                        <div class="admin-response en-cours">
                            <span>Votre dossier est en cours d'examen par le service de scolarité.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DEMANDE 3 -->
            <div class="demande-item" onclick="toggleDetail('d3')">
                <div class="demande-left">
                    <div class="demande-type duplicata">Duplicata</div>
                    <div class="demande-info">
                        <p class="demande-desc">Attestation de scolarité pour demande de bourse</p>
                        <p class="demande-date">Soumise le 15 mai 2026</p>
                    </div>
                </div>
                <div class="demande-right">
                    <span class="badge badge-accepte">Acceptée</span>
                    <span class="toggle-icon" id="icon-d3">▾</span>
                </div>
            </div>
            <div class="demande-detail" id="d3">
                <div class="detail-grid">
                    <div class="detail-block">
                        <h4>Description</h4>
                        <p>J'ai besoin d'une attestation de scolarité pour l'année 2025-2026 afin de constituer mon dossier de demande de bourse nationale.</p>
                    </div>
                    <div class="detail-block">
                        <h4>Pièce jointe</h4>
                        <span class="no-file">Aucun fichier joint</span>
                    </div>
                    <div class="detail-block full">
                        <h4>Réponse de l'administration</h4>
                        <div class="admin-response accepte">
                            <strong>Demande acceptée</strong>
                            <p>Votre attestation de scolarité est prête. Vous pouvez la récupérer au secrétariat du lundi au vendredi de 8h à 12h.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- fin card -->

        <div class="bottom-action">
            <a href="nouvelle_demande.php" class="btn btn-primary">
                + Soumettre une nouvelle demande
            </a>
        </div>

    </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>