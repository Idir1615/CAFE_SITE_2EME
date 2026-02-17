-- Création de la base de données
CREATE DATABASE IF NOT EXISTS cmcafe_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cmcafe_db;

-- Création de la table réservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    personnes INT NOT NULL,
    date DATE NOT NULL,
    heure TIME NOT NULL,
    message TEXT,
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_statut (statut),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;