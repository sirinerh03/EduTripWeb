-- Création de la base de données
CREATE DATABASE IF NOT EXISTS edutrip_reviews CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edutrip_reviews;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    mail VARCHAR(255) NOT NULL UNIQUE,
    tel VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'inactive',
    role VARCHAR(20) DEFAULT 'ROLE_USER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des vols
CREATE TABLE IF NOT EXISTS vol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compagnie VARCHAR(255) NOT NULL,
    numero_vol VARCHAR(20) NOT NULL,
    depart VARCHAR(255) NOT NULL,
    arrivee VARCHAR(255) NOT NULL,
    date_depart DATETIME NOT NULL,
    date_arrivee DATETIME NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    places_disponibles INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des hébergements
CREATE TABLE IF NOT EXISTS hebergement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    adresse TEXT NOT NULL,
    ville VARCHAR(255) NOT NULL,
    pays VARCHAR(255) NOT NULL,
    prix_par_nuit DECIMAL(10,2) NOT NULL,
    description TEXT,
    capacite INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des avis
CREATE TABLE IF NOT EXISTS review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des réservations
CREATE TABLE IF NOT EXISTS reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vol_id INT,
    hebergement_id INT,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    nombre_personnes INT NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    statut VARCHAR(20) DEFAULT 'en_attente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (vol_id) REFERENCES vol(id) ON DELETE SET NULL,
    FOREIGN KEY (hebergement_id) REFERENCES hebergement(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'un utilisateur admin par défaut
INSERT INTO user (nom, prenom, mail, tel, password, status, role) 
VALUES ('Admin', 'EduTrip', 'admin@edutrip.tn', '58795453', '$2y$13$hK0YwX5Q5Q5Q5Q5Q5Q5Q5O5O5O5O5O5O5O5O5O5O5O5O5O5O5O', 'active', 'ROLE_ADMIN'); 