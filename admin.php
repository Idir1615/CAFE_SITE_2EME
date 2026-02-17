<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'cmcafe_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction d'envoi d'email
function sendEmail($to, $nom, $date, $heure, $personnes, $telephone, $message, $statut, $motif = '') {
    $subject = '';
    $body = '';
    
    if ($statut === 'confirmee') {
        $subject = "✅ Réservation CONFIRMÉE - CM CAFÉ";
        $body = "Bonjour $nom,\n\n";
        $body .= "Votre réservation au CM CAFÉ est CONFIRMÉE !\n\n";
        $body .= "📅 Date : $date\n";
        $body .= "🕐 Heure : $heure\n";
        $body .= "👥 Personnes : $personnes\n";
        $body .= "📱 Téléphone : $telephone\n\n";
        if (!empty($message)) {
            $body .= "Votre message : $message\n\n";
        }
        $body .= "Nous vous attendons avec plaisir !\n\n";
        $body .= "Cordialement,\nL'équipe CM CAFÉ\n25 Rue de la Convention - 75015 PARIS\n01.40.59.86.03";
    } else {
        $subject = "❌ Réservation REFUSÉE - CM CAFÉ";
        $body = "Bonjour $nom,\n\n";
        $body .= "Nous sommes désolés de vous informer que votre réservation au CM CAFÉ a été refusée.\n\n";
        $body .= "📅 Date demandée : $date\n";
        $body .= "🕐 Heure demandée : $heure\n";
        $body .= "👥 Personnes : $personnes\n\n";
        $body .= "⚠️ Motif du refus : $motif\n\n";
        $body .= "N'hésitez pas à nous contacter au 01.40.59.86.03 pour une autre date.\n\n";
        $body .= "Cordialement,\nL'équipe CM CAFÉ";
    }
    
    $headers = "From: noreply@cmcafe.fr\r\n";
    $headers .= "Reply-To: contact@cmcafe.fr\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // En local, on log l'email au lieu de l'envoyer
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        $log = "\n=== EMAIL SIMULÉ (" . date('Y-m-d H:i:s') . ") ===\n";
        $log .= "À : $to\n";
        $log .= "Sujet : $subject\n";
        $log .= "Message :\n$body\n";
        $log .= "======================================\n";
        file_put_contents('emails_log.txt', $log, FILE_APPEND);
        return true;
    } else {
        return mail($to, $subject, $body, $headers);
    }
}

// Mot de passe admin
$admin_password = "cmcafe2024";

// Vérification du login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "Mot de passe incorrect";
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Confirmer une réservation
if (isset($_POST['confirmer']) && isset($_SESSION['admin_logged'])) {
    $id = (int)$_POST['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE reservations SET statut = 'confirmee' WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // Récupérer les infos pour l'email
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $resa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resa) {
            sendEmail($resa['email'], $resa['nom'], $resa['date'], $resa['heure'], 
                     $resa['personnes'], $resa['telephone'], $resa['message'], 'confirmee');
            $success = "✅ Réservation confirmée ! Email envoyé à " . htmlspecialchars($resa['email']);
        }
    } catch(PDOException $e) {
        $error = "Erreur lors de la confirmation : " . $e->getMessage();
    }
}

// Refuser une réservation
if (isset($_POST['refuser']) && isset($_SESSION['admin_logged'])) {
    $id = (int)$_POST['id'];
    $motif = htmlspecialchars(trim($_POST['motif_refus']));
    
    if (empty($motif)) {
        $error = "⚠️ Vous devez préciser un motif de refus !";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            // Récupérer les infos pour l'email
            $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $resa = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resa) {
                sendEmail($resa['email'], $resa['nom'], $resa['date'], $resa['heure'], 
                         $resa['personnes'], $resa['telephone'], $resa['message'], 'annulee', $motif);
                $success = "❌ Réservation refusée ! Email envoyé à " . htmlspecialchars($resa['email']) . " avec le motif.";
            }
        } catch(PDOException $e) {
            $error = "Erreur lors du refus : " . $e->getMessage();
        }
    }
}

// Supprimer une réservation
if (isset($_GET['supprimer']) && isset($_SESSION['admin_logged'])) {
    $id = (int)$_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $success = "🗑️ Réservation supprimée définitivement !";
    } catch(PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Tout supprimer
if (isset($_POST['tout_supprimer']) && isset($_SESSION['admin_logged'])) {
    try {
        $pdo->exec("DELETE FROM reservations");
        $success = "🗑️ Toutes les réservations ont été supprimées !";
    } catch(PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Charger les réservations
try {
    $reservations = $pdo->query("SELECT * FROM reservations ORDER BY date_creation DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur lors du chargement des réservations : " . $e->getMessage();
    $reservations = [];
}

// Statistiques
$total = count($reservations);
$en_attente = count(array_filter($reservations, fn($r) => $r['statut'] === 'en_attente'));
$confirmees = count(array_filter($reservations, fn($r) => $r['statut'] === 'confirmee'));
$annulees = count(array_filter($reservations, fn($r) => $r['statut'] === 'annulee'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Réservations CM CAFÉ</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { padding-top: 150px; padding-bottom: 60px; min-height: 80vh; }
        .login-card { max-width: 450px; margin: 20px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); text-align: center; }
        .login-card h1 { font-family: 'Playfair Display', serif; color: #c9a86a; font-size: 32px; margin-bottom: 10px; }
        .login-card input { width: 100%; padding: 14px; font-size: 16px; margin: 15px 0; border-radius: 12px; border: 1px solid #ddd; }
        .dashboard-card { max-width: 1200px; margin: 20px auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .dashboard-header h1 { font-family: 'Playfair Display', serif; color: #c9a86a; text-align: center; font-size: 32px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 30px 0; }
        .stat-box { background: linear-gradient(135deg, #c9a86a 0%, #b89a5c 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; }
        .stat-box .number { font-size: 36px; font-weight: bold; }
        .stat-box .label { font-size: 14px; opacity: 0.9; }
        .controls-bar { display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; justify-content: center; }
        .reservation-item { background: #f8f9fa; padding: 20px; margin-bottom: 15px; border-radius: 12px; border-left: 5px solid #c9a86a; }
        .reservation-item.en_attente { border-left-color: #ffc107; }
        .reservation-item.confirmee { border-left-color: #28a745; }
        .reservation-item.annulee { border-left-color: #dc3545; }
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-confirmee { background: #d4edda; color: #155724; }
        .status-annulee { background: #f8d7da; color: #721c24; }
        .reservation-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0; }
        .detail-item strong { color: #c9a86a; display: block; }
        .reservation-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 15px; }
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .motif-form { margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 8px; }
        .motif-form input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; }
        .button { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; color: white; background: #c9a86a; }
        .button:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <?php if (!isset($_SESSION['admin_logged'])): ?>
    <!-- ÉCRAN LOGIN -->
    <div class="admin-container">
        <div class="login-card">
            <h1>☕ Admin Panel</h1>
            <p>Gestion des Réservations</p>
            <?php if (isset($error)): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Mot de passe : cmcafe2024" required autofocus>
                <button type="submit" name="login" class="button" style="width: 100%;">🔐 Se connecter</button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- DASHBOARD ADMIN -->
    <div class="admin-container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h1>☕ Admin - Réservations CM CAFÉ</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error) && !isset($_POST['login'])): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="number"><?php echo $total; ?></div>
                    <div class="label">Total</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?php echo $en_attente; ?></div>
                    <div class="label">En attente</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?php echo $confirmees; ?></div>
                    <div class="label">Confirmées</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?php echo $annulees; ?></div>
                    <div class="label">Annulées</div>
                </div>
            </div>

            <div class="controls-bar">
                <button class="button" onclick="location.reload()">🔄 Actualiser</button>
                <a href="?logout" class="button">🚪 Déconnexion</a>
                <form method="POST" style="display: inline;" onsubmit="return confirm('⚠️ Supprimer TOUTES les réservations ?')">
                    <button type="submit" name="tout_supprimer" class="button" style="background: #dc3545;">🗑️ Tout supprimer</button>
                </form>
            </div>

            <?php if (empty($reservations)): ?>
                <div style="text-align: center; padding: 60px 20px; color: #999;">
                    <div style="font-size: 3rem;">☕</div>
                    <p style="font-size: 1.2rem; color: #c9a86a;">Aucune réservation</p>
                </div>
            <?php else: ?>
                <?php foreach ($reservations as $r): ?>
                <div class="reservation-item <?php echo $r['statut']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; flex-wrap: wrap;">
                        <div>
                            <div style="font-size: 18px; font-weight: bold;"><?php echo htmlspecialchars($r['nom']); ?></div>
                            <div style="color: #999; font-size: 13px;"><?php echo date('d/m/Y H:i', strtotime($r['date_creation'])); ?></div>
                        </div>
                        <span class="status-badge status-<?php echo $r['statut']; ?>">
                            <?php 
                                echo $r['statut'] === 'en_attente' ? '⏳ En attente' : 
                                     ($r['statut'] === 'confirmee' ? '✅ Confirmée' : '❌ Annulée');
                            ?>
                        </span>
                    </div>
                    <div class="reservation-details">
                        <div class="detail-item"><strong>📧 Email</strong><?php echo htmlspecialchars($r['email']); ?></div>
                        <div class="detail-item"><strong>📱 Téléphone</strong><?php echo htmlspecialchars($r['telephone']); ?></div>
                        <div class="detail-item"><strong>👥 Personnes</strong><?php echo $r['personnes']; ?></div>
                        <div class="detail-item"><strong>📅 Date & Heure</strong><?php echo date('d/m/Y', strtotime($r['date'])) . ' à ' . date('H:i', strtotime($r['heure'])); ?></div>
                        <?php if (!empty($r['message'])): ?>
                        <div class="detail-item" style="grid-column: 1 / -1;"><strong>💬 Message</strong><?php echo htmlspecialchars($r['message']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="reservation-actions">
                        <?php if ($r['statut'] !== 'confirmee'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <button type="submit" name="confirmer" class="button" style="background: #28a745;" onclick="return confirm('✅ Confirmer et envoyer un email ?')">✅ Confirmer</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($r['statut'] !== 'annulee'): ?>
                            <button class="button" style="background: #dc3545;" onclick="toggleMotif(<?php echo $r['id']; ?>)">❌ Refuser</button>
                        <?php endif; ?>
                        
                        <a href="?supprimer=<?php echo $r['id']; ?>" class="button" style="background: #6c757d;" onclick="return confirm('🗑️ Supprimer définitivement ?')">🗑️ Supprimer</a>
                    </div>
                    
                    <?php if ($r['statut'] !== 'annulee'): ?>
                    <form method="POST" id="form-refus-<?php echo $r['id']; ?>" class="motif-form" style="display: none;">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                        <label style="display: block; margin-bottom: 10px; font-weight: bold;">⚠️ Motif du refus :</label>
                        <input type="text" name="motif_refus" placeholder="Ex: Complet, Fermeture exceptionnelle..." required>
                        <button type="submit" name="refuser" class="button" style="background: #dc3545; width: 100%;">📧 Refuser et envoyer l'email</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMotif(id) {
            const form = document.getElementById('form-refus-' + id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>