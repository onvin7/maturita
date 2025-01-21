-- Vytvoření databáze
CREATE DATABASE IF NOT EXISTS `cyklistickey`;
USE `cyklistickey`;

-- Tabulka: admin_access
CREATE TABLE IF NOT EXISTS `admin_access` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `page` VARCHAR(255) NOT NULL, -- Název stránky
    `role_1` tinyint(1) NOT NULL,
    `role_2` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`)
);

-- Tabulka: admin_access_logs
CREATE TABLE IF NOT EXISTS `admin_access_logs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `changed_by` INT NOT NULL, -- ID uživatele, který změnu provedl
    `change_date` DATETIME NOT NULL, -- Datum a čas změny
    `page` VARCHAR(255) NOT NULL, -- Název stránky při změně
    `role_1` tinyint(1) NOT NULL,
    `role_2` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_admin_access_logs_to_users FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`)
);

-- Tabulka: audio
CREATE TABLE IF NOT EXISTS `audio` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nazev_souboru` VARCHAR(255) NOT NULL,
    `id_clanku` INT NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_clanky_TO_audio FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`),
    INDEX idx_id_clanku_audio (`id_clanku`)
);

-- Tabulka: clanky
CREATE TABLE IF NOT EXISTS `clanky` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nazev` VARCHAR(255) NOT NULL,
    `datum` DATETIME NOT NULL,
    `viditelnost` TINYINT(1) NOT NULL,
    `nahled_foto` VARCHAR(255) DEFAULT NULL,
    `obsah` TEXT NOT NULL,
    `user_id` INT(10) NOT NULL,
    `autor` TINYINT(1) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_users_TO_clanky FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    INDEX idx_user_id_clanky (`user_id`)
);

-- Tabulka: clanky_kategorie
CREATE TABLE IF NOT EXISTS `clanky_kategorie` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_clanku` INT NOT NULL,
    `id_kategorie` INT NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_kategorie_TO_clanky_kategorie FOREIGN KEY (`id_kategorie`) REFERENCES `kategorie` (`id`),
    CONSTRAINT FK_clanky_TO_clanky_kategorie FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`),
    INDEX idx_id_kategorie_clanky_kategorie (`id_kategorie`)
);

-- Tabulka: kategorie
CREATE TABLE IF NOT EXISTS `kategorie` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nazev_kategorie` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
);

-- Tabulka: pageviews
CREATE TABLE IF NOT EXISTS `pageviews` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `page` VARCHAR(255) NOT NULL,
    `view_date` DATE NOT NULL,
    `views` INT DEFAULT 0,
    `view_hour` TINYINT(2) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY UQ_page (`page`),
    UNIQUE KEY UQ_view_date (`view_date`)
);

-- Tabulka: password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_users_TO_password_resets FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    INDEX idx_user_id_password_resets (`user_id`)
);

-- Tabulka: propagace
CREATE TABLE IF NOT EXISTS `propagace` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_clanku` INT NOT NULL,
    `user_id` INT NOT NULL,
    `datum` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_users_TO_propagace FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT FK_clanky_TO_propagace FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`),
    INDEX idx_id_clanku_propagace (`id_clanku`)
);

-- Tabulka: users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `heslo` VARCHAR(255) NOT NULL,
    `role` TINYINT(1) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `surname` VARCHAR(255) NOT NULL,
    `profil_foto` VARCHAR(255) NOT NULL,
    `zahlavi_foto` VARCHAR(255) NOT NULL,
    `popis` TEXT NOT NULL,
    `datum` DATE DEFAULT NULL,
    PRIMARY KEY (`id`)
);

-- Tabulka: users_online
CREATE TABLE IF NOT EXISTS `users_online` (
    `session` CHAR(128) NOT NULL,
    `time` INT(11) NOT NULL,
    PRIMARY KEY (`session`)
);

-- Tabulka: user_social
CREATE TABLE IF NOT EXISTS `user_social` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `social_name` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_users_TO_user_social FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);

-- Tabulka: views_clanku
CREATE TABLE IF NOT EXISTS `views_clanku` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_clanku` INT NOT NULL,
    `pocet` INT(11) NOT NULL DEFAULT 0,
    `datum` DATE NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FK_clanky_TO_views_clanku FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`),
    INDEX idx_fk_clanek_views_clanku (`id_clanku`)
);

ALTER TABLE admin_access ENGINE=InnoDB;
ALTER TABLE admin_access_logs ENGINE=InnoDB;
ALTER TABLE audio ENGINE=InnoDB;
ALTER TABLE clanky ENGINE=InnoDB;
ALTER TABLE clanky_kategorie ENGINE=InnoDB;
ALTER TABLE kategorie ENGINE=InnoDB;
ALTER TABLE pageviews ENGINE=InnoDB;
ALTER TABLE password_resets ENGINE=InnoDB;
ALTER TABLE propagace ENGINE=InnoDB;
ALTER TABLE users ENGINE=InnoDB;
ALTER TABLE users_online ENGINE=InnoDB;
ALTER TABLE user_social ENGINE=InnoDB;
ALTER TABLE views_clanku ENGINE=InnoDB;
