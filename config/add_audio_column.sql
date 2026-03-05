-- Přidání sloupce audio do tabulky clanky
-- Tento sloupec bude obsahovat cestu k audio souboru (např. /uploads/audio/123.mp3)

ALTER TABLE `clanky` 
ADD COLUMN `audio` VARCHAR(255) DEFAULT NULL COMMENT 'Cesta k audio souboru článku' AFTER `nahled_foto`;



