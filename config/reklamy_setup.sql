-- Vytvoření tabulky `reklamy` + demo seed bannerů (MySQL/MariaDB)

CREATE TABLE IF NOT EXISTS `reklamy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `upraveno` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_TO_reklamy` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pokud už tabulku máš a jen chceš seed, spouštěj jen blok INSERT níže.
-- Pozn.: `user_id` je nastaven na 1 (uprav podle toho, jaké máš ID admina v tabulce `users`).

INSERT INTO `reklamy`
  (`nazev`, `obrazek`, `odkaz`, `zacatek`, `konec`, `aktivni`, `vychozi`, `frekvence`, `user_id`, `vytvoreno`)
VALUES
  ('Banner 1', 'banner1.jpg', 'https://example.com/banner1', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 0, 1, 1, NOW()),
  ('Banner 2', 'banner2.jpg', 'https://example.com/banner2', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 0, 1, 1, NOW()),
  ('Banner 3', 'banner3.jpg', 'https://example.com/banner3', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 0, 1, 1, NOW()),
  ('Banner 4', 'banner4.jpg', 'https://example.com/banner4', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 0, 5, 1, NOW()),
  ('Banner 5', 'banner5.jpg', 'https://example.com/banner5', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 0, 1, 1, NOW()),
  ('Banner 6', 'banner6.jpg', 'https://example.com/banner6', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 0, 1, 1, NOW()),
  ('Banner 7 (výchozí)', 'banner7.jpg', 'https://example.com/banner7', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), 1, 1, 1, 1, NOW());

