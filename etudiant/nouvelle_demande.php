<?php
// Session à vérifier plus tard
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
            <a href="nouvelle_demande.php" class="nav-item active">
                <span class="nav-icon"></span>
                <span>Nouvelle demande</span>
            </a>
            <a href="suivi.php" class="nav-item">
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
                <h1 class="page-title">Nouvelle demande</h1>
                <p class="page-subtitle">Remplissez le formulaire pour soumettre votre demande</p>
            </div>
            <div class="user-badge">
                <div class="user-avatar">SR</div>
                <span>Soma Rahim</span>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert success">
            <span></span>
            <div>
                <strong>Demande envoyée !</strong>
                <p>Votre demande a été soumise. Suivez son statut dans "Mes demandes".</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert error">
            <span></span>
            <div>
                <strong>Erreur</strong>
                <p>Une erreur est survenue. Veuillez réessayer.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Formulaire de demande</h2>
            </div>

            <!-- enctype obligatoire pour l'upload -->
            <form method="POST" action="nouvelle_demande.php" enctype="multipart/form-data" class="form">

                <div class="form-group">
                    <label for="type_demande">Type de demande <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select name="type_demande" id="type_demande" required>
                            <option value="">— Sélectionner un type —</option>
                            <option value="réclamation"> Réclamation sur les notes</option>
                            <option value="dérogation"> Demande de dérogation</option>
                            <option value="duplicata"> Duplicata / Attestation</option>
                        </select>
                    </div>
                </div>

                <!-- Info dynamique -->
                <div class="info-box" id="info-box" style="display:none;">
                    <span id="info-text"></span>
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea
                        name="description"
                        id="description"
                        rows="5"
                        placeholder="Décrivez votre demande en détail..."
                        required
                    ></textarea>
                    <span class="hint">Soyez précis pour faciliter le traitement de votre demande.</span>
                </div>

                <!-- UPLOAD FICHIER -->
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
                    <button type="submit" name="submit" class="btn btn-primary">
                        Envoyer →
                    </button>
                </div>

            </form>
        </div>

        <!-- Info cards -->
        <div class="info-cards">
            <div class="info-card">
                <div class="info-card-icon"></div>
                <h3>Réclamation</h3>
                <p>Contestez une note et demandez une vérification de votre copie.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon"></div>
                <h3>Dérogation</h3>
                <p>Demandez une exception à une règle administrative.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon"></div>
                <h3>Duplicata</h3>
                <p>Obtenez une attestation ou copie de diplôme officielle.</p>
            </div>
        </div>

    </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>