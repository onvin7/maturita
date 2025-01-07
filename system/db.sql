CREATE TABLE audio (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nazev_souboru VARCHAR(255) NOT NULL,
    id_clanku INT(11) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE clanky (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nazev VARCHAR(255) NOT NULL,
    datum DATETIME NOT NULL,
    viditelnost TINYINT(1) NOT NULL,
    nahled_foto VARCHAR(255) DEFAULT NULL,
    user_id INT(255) NOT NULL,
    autor TINYINT(1) NOT NULL,
    url VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE clanky_kategorie (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_clanku INT(11) NOT NULL,
    id_kategorie INT(11) NOT NULL, -- Přejmenováno z id_podkategorie
    PRIMARY KEY (id)
);

CREATE TABLE kategorie (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nazev_kategorie VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE pageviews (
    id INT(11) NOT NULL AUTO_INCREMENT,
    page VARCHAR(255) NOT NULL,
    view_date DATE NOT NULL,
    views INT(11) NOT NULL DEFAULT 0,
    view_hour INT(11) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UQ_page (page),
    UNIQUE KEY UQ_view_date (view_date)
);

CREATE TABLE password_resets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE propagace (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_clanku INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    datum DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE user_social (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    social_name VARCHAR(255) NOT NULL,
    link VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    heslo VARCHAR(255) NOT NULL,
    admin TINYINT(1) NOT NULL,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    profil_foto VARCHAR(255) NOT NULL,
    zahlavi_foto VARCHAR(255) NOT NULL,
    popis TEXT NOT NULL,
    datum DATE DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE users_online (
    session CHAR(128) NOT NULL,
    time INT(11) NOT NULL,
    PRIMARY KEY (session)
);

CREATE TABLE views_clanku (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_clanku INT(11) NOT NULL,
    pocet INT(11) NOT NULL DEFAULT 0,
    datum DATE NOT NULL,
    PRIMARY KEY (id)
);

-- Přidání cizích klíčů
ALTER TABLE audio
    ADD CONSTRAINT FK_clanky_TO_audio FOREIGN KEY (id_clanku) REFERENCES clanky (id);

ALTER TABLE clanky
    ADD CONSTRAINT FK_users_TO_clanky FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE clanky_kategorie
    ADD CONSTRAINT FK_kategorie_TO_clanky_kategorie FOREIGN KEY (id_kategorie) REFERENCES kategorie (id),
    ADD CONSTRAINT FK_clanky_TO_clanky_kategorie FOREIGN KEY (id_clanku) REFERENCES clanky (id);

ALTER TABLE password_resets
    ADD CONSTRAINT FK_users_TO_password_resets FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE propagace
    ADD CONSTRAINT FK_users_TO_propagace FOREIGN KEY (user_id) REFERENCES users (id),
    ADD CONSTRAINT FK_clanky_TO_propagace FOREIGN KEY (id_clanku) REFERENCES clanky (id);

ALTER TABLE views_clanku
    ADD CONSTRAINT FK_clanky_TO_views_clanku FOREIGN KEY (id_clanku) REFERENCES clanky (id);

ALTER TABLE user_social
    ADD CONSTRAINT FK_users_TO_user_social FOREIGN KEY (user_id) REFERENCES users (id);

-- Přidání indexů
CREATE INDEX idx_id_clanku_audio ON audio (id_clanku);
CREATE INDEX idx_user_id_clanky ON clanky (user_id);
CREATE INDEX idx_user_id_password_resets ON password_resets (user_id);
CREATE INDEX idx_id_kategorie_clanky_kategorie ON clanky_kategorie (id_kategorie); -- Upravený index
CREATE INDEX idx_id_clanku_propagace ON propagace (id_clanku);
CREATE INDEX idx_fk_clanek_views_clanku ON views_clanku (id_clanku);
