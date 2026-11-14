# ☕ CM Café : site vitrine et réservation en ligne

Site web du **CM Café** (restaurant, bar et brunch à Paris 15ᵉ), réalisé en 2ᵉ année de BTS SIO (option SLAM).
Il présente l'établissement, sa carte et sa galerie, et permet aux clients de **réserver une table en ligne**. Un **espace administrateur** sert à valider ou refuser les réservations.

## ✨ Fonctionnalités

- **Pages vitrine** : accueil avec carrousel, carte (cafés, viennoiseries, pâtisseries, formules déjeuner), galerie photo, contact
- **Réservation en ligne** : formulaire (date, heure, nombre de personnes, téléphone, message) enregistré en base MySQL
- **Back-office admin** (`admin.php`) : liste des réservations, confirmation ou refus avec motif
- **E-mail de notification** : un e-mail de confirmation ou de refus est généré pour le client (simulé en local dans `emails_log.txt`)
- Balises SEO et Open Graph

## 🛠️ Technologies

HTML5 · CSS3 · PHP 8 (PDO) · MySQL

## 🚀 Installation en local

1. Installer un serveur local (XAMPP, WAMP, Laragon…).
2. Copier le projet dans le dossier web (`htdocs/` ou `www/`).
3. Importer `Cmcafe.sql` dans phpMyAdmin (crée la base `cmcafe_db` et la table `reservations`).
4. Adapter si besoin les identifiants MySQL en haut de `admin.php` et `réservation.php`.
5. Ouvrir `http://localhost/CAFE_SITE_2EME/`.

## 📁 Structure

```
├── index.html          # Accueil
├── menu.html           # Carte
├── galerie.html        # Galerie photo
├── contact.html        # Contact
├── réservation.php     # Formulaire de réservation
├── admin.php           # Gestion des réservations
├── send_email.php      # Génération des e-mails
├── Cmcafe.sql          # Script de la base de données
└── images/             # Médias
```

## 👤 Auteur

**Idir Sehrane**, étudiant en BTS SIO SLAM
