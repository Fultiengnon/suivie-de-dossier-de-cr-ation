<?php
// Fichier: index.php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();

// Récupérer le nom de l'utilisateur si connecté
$userName = isset($_SESSION['user_prenom']) ? $_SESSION['user_prenom'] : '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi Création Entreprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Logo Cabinet Comptable" class="logo">
            <h1>Cabinet Comptable Pro</h1>
        </div>
        <nav id="main-nav">
            <button class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></button>
            <ul class="nav-list">
                <li><a href="#" class="active" data-section="accueil">Accueil</a></li>
                <li><a href="#" data-section="services">Nos Services</a></li>
                <li><a href="#" data-section="suivi">Suivi Entreprise</a></li>
                <li><a href="#" data-section="documents">Documents</a></li>
                <li><a href="#" data-section="contact">Contact</a></li>
                <?php if ($isAdmin): ?>
                <li><a href="admin/dashboard.php">Administration</a></li>
                <?php endif; ?>
                <li class="auth-buttons">
                    <?php if ($isLoggedIn): ?>
                        <span class="user-welcome">Bonjour, <?php echo htmlspecialchars($userName); ?></span>
                        <a href="logout.php" class="btn-logout">Déconnexion</a>
                    <?php else: ?>
                        <a href="#" class="btn-login" id="login-btn">Connexion</a>
                        <a href="#" class="btn-register" id="register-btn">Inscription</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Section Accueil -->
        <section id="accueil" class="section active">
            <div class="hero">
                <div class="hero-text">
                    <h2>Bienvenue sur votre plateforme de suivi</h2>
                    <p>Nous vous accompagnons dans toutes les étapes de la création de votre entreprise</p>
                    <div class="hero-buttons">
                        <a href="#" class="btn btn-primary" id="suivre-btn">Suivre mon dossier</a>
                        <?php if (!$isLoggedIn): ?>
                            <a href="#" class="btn btn-secondary" id="creer-btn">Créer un compte</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/images/hero-image.jpg" alt="Création d'entreprise">
                </div>
            </div>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-file-signature feature-icon"></i>
                    <h3>Création simplifiée</h3>
                    <p>Nous nous occupons de toutes les démarches administratives</p>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3>Suivi en temps réel</h3>
                    <p>Consultez l'avancement de votre dossier à tout moment</p>
                </div>
                <div class="feature">
                    <i class="fas fa-file-pdf feature-icon"></i>
                    <h3>Documents accessibles</h3>
                    <p>Téléchargez et consultez tous vos documents officiels</p>
                </div>
            </div>
        </section>

        <!-- Section Services -->
        <section id="services" class="section">
            <h2 class="section-title">Nos Services</h2>
            <div class="services-container">
                <div class="service">
                    <div class="service-icon"><i class="fas fa-building"></i></div>
                    <h3>Création d'entreprise</h3>
                    <p>Accompagnement complet dans toutes les démarches de création</p>
                </div>
                <div class="service">
                    <div class="service-icon"><i class="fas fa-file-contract"></i></div>
                    <h3>Formalités juridiques</h3>
                    <p>Rédaction et dépôt de tous les documents officiels</p>
                </div>
                <div class="service">
                    <div class="service-icon"><i class="fas fa-calculator"></i></div>
                    <h3>Conseil fiscal</h3>
                    <p>Optimisation de votre structure et de votre régime fiscal</p>
                </div>
                <div class="service">
                    <div class="service-icon"><i class="fas fa-hands-helping"></i></div>
                    <h3>Accompagnement</h3>
                    <p>Suivi personnalisé tout au long du processus</p>
                </div>
            </div>
        </section>

        <!-- Section Suivi Entreprise -->
        <section id="suivi" class="section">
            <h2 class="section-title">Suivi de votre dossier</h2>
            
            <?php if (!$isLoggedIn): ?>
            <div class="login-required">
                <i class="fas fa-lock"></i>
                <h3>Connexion requise</h3>
                <p>Veuillez vous connecter pour accéder au suivi de votre dossier</p>
                <button class="btn btn-primary login-redirect">Se connecter</button>
            </div>
            <?php else: ?>
            <div class="suivi-content">
                <?php
                // Récupérer les entreprises de l'utilisateur
                $conn = connectDB();
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT * FROM entreprises WHERE user_id = '$user_id'";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    $entreprise = $result->fetch_assoc();
                    
                    // Détermine la classe pour chaque étape en fonction du statut
                    $etapes = array(
                        'depot' => array('completed' => false, 'active' => false),
                        'verification' => array('completed' => false, 'active' => false),
                        'traitement' => array('completed' => false, 'active' => false),
                        'immatriculation' => array('completed' => false, 'active' => false),
                        'finalisation' => array('completed' => false, 'active' => false)
                    );
                    
                    $statut_actuel = $entreprise['statut_creation'];
                    $statuts_ordre = array_keys($etapes);
                    
                    // Remplir les statuts des étapes
                    foreach ($statuts_ordre as $index => $statut) {
                        if ($index < array_search($statut_actuel, $statuts_ordre)) {
                            $etapes[$statut]['completed'] = true;
                        } elseif ($index == array_search($statut_actuel, $statuts_ordre)) {
                            $etapes[$statut]['active'] = true;
                        }
                    }
                ?>
                <div class="progress-tracker">
                    <h3>Progression de votre dossier</h3>
                    <div class="progress-container">
                        <div class="progress-steps">
                            <div class="step <?php echo $etapes['depot']['completed'] ? 'completed' : ($etapes['depot']['active'] ? 'active' : ''); ?>">
                                <div class="step-icon">
                                    <?php if ($etapes['depot']['completed']): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($etapes['depot']['active']): ?>
                                        <i class="fas fa-spinner fa-spin"></i>
                                    <?php else: ?>
                                        <i class="fas fa-hourglass"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label">Dépôt du dossier</div>
                            </div>
                            <div class="step <?php echo $etapes['verification']['completed'] ? 'completed' : ($etapes['verification']['active'] ? 'active' : ''); ?>">
                                <div class="step-icon">
                                    <?php if ($etapes['verification']['completed']): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($etapes['verification']['active']): ?>
                                        <i class="fas fa-spinner fa-spin"></i>
                                    <?php else: ?>
                                        <i class="fas fa-hourglass"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label">Vérification des informations</div>
                            </div>
                            <div class="step <?php echo $etapes['traitement']['completed'] ? 'completed' : ($etapes['traitement']['active'] ? 'active' : ''); ?>">
                                <div class="step-icon">
                                    <?php if ($etapes['traitement']['completed']): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($etapes['traitement']['active']): ?>
                                        <i class="fas fa-spinner fa-spin"></i>
                                    <?php else: ?>
                                        <i class="fas fa-hourglass"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label">Traitement administratif</div>
                            </div>
                            <div class="step <?php echo $etapes['immatriculation']['completed'] ? 'completed' : ($etapes['immatriculation']['active'] ? 'active' : ''); ?>">
                                <div class="step-icon">
                                    <?php if ($etapes['immatriculation']['completed']): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($etapes['immatriculation']['active']): ?>
                                        <i class="fas fa-spinner fa-spin"></i>
                                    <?php else: ?>
                                        <i class="fas fa-hourglass"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label">Immatriculation</div>
                            </div>
                            <div class="step <?php echo $etapes['finalisation']['completed'] ? 'completed' : ($etapes['finalisation']['active'] ? 'active' : ''); ?>">
                                <div class="step-icon">
                                    <?php if ($etapes['finalisation']['completed']): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($etapes['finalisation']['active']): ?>
                                        <i class="fas fa-spinner fa-spin"></i>
                                    <?php else: ?>
                                        <i class="fas fa-flag-checkered"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label">Finalisation</div>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $entreprise['progression_pct']; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="info-entreprise">
                    <h3>Informations de l'entreprise</h3>
                    <div class="info-tabs">
                        <div class="tab-header">
                            <button class="tab-btn active" data-tab="info-personnelles">Informations personnelles</button>
                            <button class="tab-btn" data-tab="info-societe">Informations société</button>
                            <button class="tab-btn" data-tab="activite">Activité</button>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="info-personnelles">
                                <?php
                                // Récupérer les associés
                                $entreprise_id = $entreprise['id'];
                                $sql_associes = "SELECT * FROM associes WHERE entreprise_id = '$entreprise_id'";
                                $result_associes = $conn->query($sql_associes);
                                
                                if ($result_associes->num_rows > 0) {
                                    $count = 1;
                                    while ($associe = $result_associes->fetch_assoc()) {
                                ?>
                                <div class="info-card">
                                    <h4>Associé <?php echo $count; ?></h4>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="info-label">Nom:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['nom']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Prénom:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['prenom']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Date de naissance:</span>
                                            <span class="info-value"><?php echo date('d/m/Y', strtotime($associe['date_naissance'])); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Lieu de naissance:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['lieu_naissance']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Type de pièce:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['type_piece']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Numéro de pièce:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['numero_piece']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Date d'établissement:</span>
                                            <span class="info-value"><?php echo date('d/m/Y', strtotime($associe['date_etablissement'])); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Date d'expiration:</span>
                                            <span class="info-value"><?php echo date('d/m/Y', strtotime($associe['date_expiration'])); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Domicile:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['domicile']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Téléphone:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['telephone']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['email']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Boîte postale:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($associe['boite_postale']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Apport:</span>
                                            <span class="info-value"><?php echo number_format($associe['apport'], 2, ',', ' ') . ' €'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                        $count++;
                                    }
                                } else {
                                    echo "<p>Aucun associé n'a été enregistré.</p>";
                                }
                                ?>
                            </div>
                            
                            <div class="tab-pane" id="info-societe">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Raison sociale:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($entreprise['raison_sociale']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Nom commercial:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($entreprise['nom_commercial']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Type d'entreprise:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($entreprise['type_entreprise']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Capital social:</span>
                                        <span class="info-value"><?php echo number_format($entreprise['capital_social'], 2, ',', ' ') . ' €'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="activite">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Activité exercée:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($entreprise['activite']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Lieu d'exercice:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($entreprise['lieu_exercice']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Date de début d'activité:</span>