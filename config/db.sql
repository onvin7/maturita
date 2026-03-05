-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Čtv 27. bře 2025, 22:05
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `cyklistickey`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `admin_access`
--

CREATE TABLE `admin_access` (
  `id` int(11) NOT NULL,
  `page` varchar(255) NOT NULL,
  `role_1` tinyint(1) NOT NULL,
  `role_2` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `admin_access_logs`
--

CREATE TABLE `admin_access_logs` (
  `id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_date` datetime NOT NULL,
  `page` varchar(255) NOT NULL,
  `role_1` tinyint(1) NOT NULL,
  `role_2` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `clanky`
--

CREATE TABLE `clanky` (
  `id` int(11) NOT NULL,
  `nazev` varchar(255) NOT NULL,
  `datum` datetime NOT NULL,
  `viditelnost` tinyint(1) NOT NULL,
  `nahled_foto` varchar(255) DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL COMMENT 'Cesta k audio souboru článku',
  `obsah` text NOT NULL,
  `user_id` int(10) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `clanky_kategorie`
--

CREATE TABLE `clanky_kategorie` (
  `id` int(11) NOT NULL,
  `id_clanku` int(11) NOT NULL,
  `id_kategorie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `kategorie`
--

CREATE TABLE `kategorie` (
  `id` int(11) NOT NULL,
  `nazev_kategorie` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `propagace`
--

CREATE TABLE `propagace` (
  `id` int(11) NOT NULL,
  `id_clanku` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `zacatek` datetime NOT NULL,
  `konec` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reklamy`
--

CREATE TABLE `reklamy` (
  `id` int(11) NOT NULL,
  `nazev` varchar(255) NOT NULL,
  `obrazek` varchar(255) NOT NULL,
  `odkaz` varchar(500) NOT NULL,
  `zacatek` datetime NOT NULL,
  `konec` datetime NOT NULL,
  `aktivni` tinyint(1) NOT NULL DEFAULT 1,
  `vychozi` tinyint(1) NOT NULL DEFAULT 0,
  `frekvence` int(11) NOT NULL DEFAULT 1,
  `user_id` int(11) NOT NULL,
  `vytvoreno` datetime NOT NULL,
  `upraveno` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `socials`
--

CREATE TABLE `socials` (
  `id` int(11) NOT NULL,
  `fa_class` varchar(255) NOT NULL,
  `nazev` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `heslo` varchar(255) NOT NULL,
  `role` tinyint(1) NOT NULL,
  `public_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Zobrazit uzivatele v sekci redakce (1=ano, 0=ne)',
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `profil_foto` varchar(255) NOT NULL,
  `popis` text NOT NULL,
  `datum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `user_social`
--

CREATE TABLE `user_social` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `social_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `views_clanku`
--

CREATE TABLE `views_clanku` (
  `id` int(11) NOT NULL,
  `id_clanku` int(11) NOT NULL,
  `pocet` int(11) NOT NULL DEFAULT 0,
  `datum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `admin_access`
--
ALTER TABLE `admin_access`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `admin_access_logs`
--
ALTER TABLE `admin_access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_admin_access_logs_to_users` (`changed_by`);

--
-- Indexy pro tabulku `clanky`
--
ALTER TABLE `clanky`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id_clanky` (`user_id`);

--
-- Indexy pro tabulku `clanky_kategorie`
--
ALTER TABLE `clanky_kategorie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_clanky_TO_clanky_kategorie` (`id_clanku`),
  ADD KEY `idx_id_kategorie_clanky_kategorie` (`id_kategorie`);

--
-- Indexy pro tabulku `kategorie`
--
ALTER TABLE `kategorie`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id_password_resets` (`user_id`);

--
-- Indexy pro tabulku `propagace`
--
ALTER TABLE `propagace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_users_TO_propagace` (`user_id`),
  ADD KEY `idx_id_clanku_propagace` (`id_clanku`);

--
-- Indexy pro tabulku `reklamy`
--
ALTER TABLE `reklamy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_users_TO_reklamy` (`user_id`);

--
-- Indexy pro tabulku `socials`
--
ALTER TABLE `socials`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `user_social`
--
ALTER TABLE `user_social`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_socials_TO_user_social` (`social_id`);

--
-- Indexy pro tabulku `views_clanku`
--
ALTER TABLE `views_clanku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fk_clanek_views_clanku` (`id_clanku`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `admin_access`
--
ALTER TABLE `admin_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `admin_access_logs`
--
ALTER TABLE `admin_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `clanky`
--
ALTER TABLE `clanky`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `clanky_kategorie`
--
ALTER TABLE `clanky_kategorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `kategorie`
--
ALTER TABLE `kategorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `propagace`
--
ALTER TABLE `propagace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `reklamy`
--
ALTER TABLE `reklamy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `socials`
--
ALTER TABLE `socials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `user_social`
--
ALTER TABLE `user_social`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `views_clanku`
--
ALTER TABLE `views_clanku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `admin_access_logs`
--
ALTER TABLE `admin_access_logs`
  ADD CONSTRAINT `FK_admin_access_logs_to_users` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `clanky`
--
ALTER TABLE `clanky`
  ADD CONSTRAINT `FK_users_TO_clanky` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `clanky_kategorie`
--
ALTER TABLE `clanky_kategorie`
  ADD CONSTRAINT `FK_clanky_TO_clanky_kategorie` FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`),
  ADD CONSTRAINT `FK_kategorie_TO_clanky_kategorie` FOREIGN KEY (`id_kategorie`) REFERENCES `kategorie` (`id`);

--
-- Omezení pro tabulku `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `FK_users_TO_password_resets` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `propagace`
--
ALTER TABLE `propagace`
  ADD CONSTRAINT `FK_clanky_TO_propagace` FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`),
  ADD CONSTRAINT `FK_users_TO_propagace` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `reklamy`
--
ALTER TABLE `reklamy`
  ADD CONSTRAINT `FK_users_TO_reklamy` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `user_social`
--
ALTER TABLE `user_social`
  ADD CONSTRAINT `FK_socials_TO_user_social` FOREIGN KEY (`social_id`) REFERENCES `socials` (`id`),
  ADD CONSTRAINT `FK_users_TO_user_social` FOREIGN KEY (`social_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `views_clanku`
--
ALTER TABLE `views_clanku`
  ADD CONSTRAINT `FK_clanky_TO_views_clanku` FOREIGN KEY (`id_clanku`) REFERENCES `clanky` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
