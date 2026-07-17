DROP TABLE IF EXISTS Liste_lecture;
DROP TABLE IF EXISTS Lecteurs;
DROP TABLE IF EXISTS Livres;

CREATE TABLE Livres (
    id INT NOT NULL AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    description TEXT NULL,
    maison_edition VARCHAR(100) NULL,
    nombre_exemplaire INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_livres_titre (titre),
    KEY idx_livres_auteur (auteur),
    FULLTEXT KEY ft_livres (titre, auteur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Lecteurs (
    id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('lecteur', 'administrateur') NOT NULL DEFAULT 'lecteur',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Liste_lecture (
    id_livre INT NOT NULL,
    id_lecteur INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour DATE NULL,
    PRIMARY KEY (id_livre, id_lecteur, date_emprunt),
    KEY idx_liste_lecture_id_lecteur (id_lecteur),
    CONSTRAINT fk_liste_lecture_livre
        FOREIGN KEY (id_livre) REFERENCES Livres(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_liste_lecture_lecteur
        FOREIGN KEY (id_lecteur) REFERENCES Lecteurs(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO Livres (titre, auteur, description, maison_edition, nombre_exemplaire) VALUES
('L''Assassin royal', 'Robin Hobb', 'Le premier volet de la série des Farseers.', 'J''Ai Lu', 5),
('Le Petit Prince', 'Antoine de Saint-Exupéry', 'Un classique intemporel de la littérature mondiale.', 'Gallimard', 8),
('Dune', 'Frank Herbert', 'Une épopée de science-fiction épique et politique.', 'Robert Laffont', 4),
('Harry Potter à l''école des sorciers', 'J. K. Rowling', 'La découverte du monde magique à Poudlard.', 'Gallimard Jeunesse', 6),
('Les Misérables', 'Victor Hugo', 'Un roman historique monumental sur la justice et la rédemption.', 'Larousse', 3),
('Le Rouge et le Noir', 'Stendhal', 'Portrait psychologique d''un jeune homme ambitieux.', 'Le Livre de Poche', 4),
('Clean Code', 'Robert C. Martin', 'Des principes concrets pour écrire du code lisible et maintenable.', 'Pearson', 7),
('La Peste', 'Albert Camus', 'Une réflexion philosophique sur l''absurde et l''engagement.', 'Gallimard', 2),
('Les Fourmis', 'Bernard Werber', 'Un roman de science-fiction qui explore les sociétés animales.', 'Albin Michel', 5),
('Sapiens', 'Yuval Noah Harari', 'Une histoire globale de l''humanité et de son évolution.', 'Albin Michel', 6);

INSERT INTO Lecteurs (nom, prenom, email, mot_de_passe, role) VALUES
('Dubois', 'Claire', 'claire.dubois@lectoria.test', '$argon2id$v=19$m=65536,t=4,p=1$VUdMTEpTOXNjb2NUcWVEOA$66FUv+W6WxHY+ItrIVk20cH2FxG9mxEwSDfDQUntTnc', 'lecteur'),
('Martin', 'Alex', 'alex.martin@lectoria.test', '$argon2id$v=19$m=65536,t=4,p=1$VUdMTEpTOXNjb2NUcWVEOA$66FUv+W6WxHY+ItrIVk20cH2FxG9mxEwSDfDQUntTnc', 'administrateur');

INSERT INTO Liste_lecture (id_livre, id_lecteur, date_emprunt, date_retour) VALUES
(1, 1, '2026-07-01', '2026-07-10'),
(2, 1, '2026-07-11', NULL),
(3, 2, '2026-06-15', NULL);
