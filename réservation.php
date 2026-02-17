<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'cmcafe_db';
$username = 'root'; // À adapter selon votre config
$password = ''; // À adapter selon votre config

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire
$message_success = '';
$message_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $personnes = (int)$_POST['personnes'];
    $date = htmlspecialchars($_POST['date']);
    $heure = htmlspecialchars($_POST['heure']);
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Validation
    if (empty($nom) || empty($email) || empty($telephone) || empty($date) || empty($heure)) {
        $message_error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Insertion dans la base de données
        $sql = "INSERT INTO reservations (nom, email, telephone, personnes, date, heure, message, statut, date_creation) 
                VALUES (:nom, :email, :telephone, :personnes, :date, :heure, :message, 'en_attente', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':personnes' => $personnes,
            ':date' => $date,
            ':heure' => $heure,
            ':message' => $message
        ]);
        
        $message_success = "Votre réservation a été enregistrée avec succès ! Nous vous confirmerons par email.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - CM CAFÉ</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .reservation-container { padding-top: 150px; padding-bottom: 60px; min-height: 80vh; }
        .reservation-card { max-width: 600px; margin: 20px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .reservation-card h1 { font-family: 'Playfair Display', serif; color: #c9a86a; text-align: center; font-size: 36px; margin-bottom: 10px; }
        .reservation-card p { text-align: center; color: #555; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #c9a86a; font-weight: bold; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <!-- NAVIGATION -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <div class="dropdown">
                <button class="btn btn-menu dropdown-toggle" type="button" data-bs-toggle="dropdown"> ☰</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="index.html">Accueil</a></li>
                    <li><a class="dropdown-item" href="menu.html">Menu</a></li>
                    <li><a class="dropdown-item" href="réservation.php">Réservations</a></li>
                </ul>
            </div>
            <a class="navbar-brand position-absolute start-50 translate-middle-x" href="index.html">
                <img src="logo_cm_cafe.png" alt="Le CM CAFÉ" width="78" height="78">
            </a>
            <a class="btn btn-primary" href="réservation.php">Réservations</a>
        </div>
    </nav>

    <div class="reservation-container">
        <div class="reservation-card">
            <h1>☕ Réserver une table</h1>
            <p>Remplissez le formulaire ci-dessous</p>

            <?php if ($message_success): ?>
                <div class="alert-success">✅ <?php echo $message_success; ?></div>
            <?php endif; ?>

            <?php if ($message_error): ?>
                <div class="alert-error">❌ <?php echo $message_error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom">Nom complet *</label>
                    <input type="text" id="nom" name="nom" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone *</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>

                <div class="form-group">
                    <label for="personnes">Nombre de personnes *</label>
                    <select id="personnes" name="personnes" required>
                        <option value="1">1 personne</option>
                        <option value="2">2 personnes</option>
                        <option value="3">3 personnes</option>
                        <option value="4">4 personnes</option>
                        <option value="5">5 personnes</option>
                        <option value="6">6 personnes</option>
                        <option value="7">7 personnes</option>
                        <option value="8">8 personnes ou plus</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Date de réservation *</label>
                    <input type="date" id="date" name="date" required>
                </div>

                <div class="form-group">
                    <label for="heure">Heure *</label>
                    <input type="time" id="heure" name="heure" required>
                </div>

                <div class="form-group">
                    <label for="message">Message (optionnel)</label>
                    <textarea id="message" name="message" placeholder="Allergies, demandes spéciales..."></textarea>
                </div>

                <button type="submit" class="button" style="width: 100%;">🎉 Confirmer ma réservation</button>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-compact">
            <div class="footer-col">
                <h3>Adresse</h3>
                <p>25 Rue de la Convention<br><strong>75015 PARIS</strong></p>
            </div>
            <div class="footer-col footer-map">
                <iframe width="240" height="140" style="border:0;" loading="lazy" allowfullscreen src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d840.1835266972619!2d2.287580!3d48.847290!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6710070fa38b9%3A0x8223652a6673c877!2sCM%20Caf%C3%A9!5e0!3m2!1sfr!2sfr!4v1713532000000"></iframe>
            </div>
            <div class="footer-col">
                <h3>Contact</h3>
                <p>01.40.59.86.03<br><strong>7j/7 · 6h30 – 1h00</strong></p>
            </div>
        </div>
        <div class="footer-bottom">© 2025 LE CM CAFÉ — Tous droits réservés</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>