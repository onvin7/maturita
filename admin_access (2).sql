-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Počítač: md413.wedos.net:3306
-- Vygenerováno: Sob 07. bře 2026, 19:51
-- Verze serveru: 10.4.34-MariaDB-log
-- Verze PHP: 5.4.23

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `d340619_blog`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `admin_access`
--

CREATE TABLE IF NOT EXISTS `admin_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL,
  `role_1` tinyint(1) NOT NULL,
  `role_2` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=55 ;

--
-- Vypisuji data pro tabulku `admin_access`
--

INSERT INTO `admin_access` (`id`, `page`, `role_1`, `role_2`) VALUES
(1, 'statistics', 1, 1),
(2, 'statistics/top', 0, 1),
(3, 'statistics/view', 0, 1),
(4, 'articles', 1, 1),
(5, 'articles/create', 1, 1),
(6, 'articles/store', 1, 1),
(7, 'articles/edit', 1, 1),
(8, 'articles/update', 1, 1),
(9, 'articles/delete', 0, 1),
(10, 'categories', 1, 1),
(11, 'categories/create', 0, 0),
(12, 'categories/store', 0, 0),
(13, 'categories/edit', 0, 0),
(14, 'categories/update', 0, 0),
(15, 'categories/delete', 0, 0),
(16, 'users', 0, 1),
(17, 'users/edit', 0, 0),
(18, 'users/update', 0, 0),
(19, 'users/delete', 0, 0),
(20, 'access-control', 0, 0),
(21, 'access-control/update', 0, 0),
(22, 'logout', 1, 1),
(23, 'promotions', 1, 1),
(24, 'promotions/create', 0, 1),
(25, 'promotions/store', 0, 1),
(26, 'promotions/upcoming', 0, 1),
(27, 'promotions/history', 0, 1),
(28, 'promotions/delete', 0, 1),
(29, 'settings', 1, 1),
(30, 'settings/update', 1, 1),
(31, 'social-sites', 0, 0),
(32, 'social-sites/save', 0, 0),
(33, 'social-sites/delete', 0, 0),
(34, 'statistics/articles', 0, 1),
(35, 'statistics/categories', 0, 1),
(36, 'statistics/authors', 0, 1),
(37, 'statistics/performance', 0, 1),
(38, 'statistics/views', 0, 1),
(39, 'statistics/article-details', 0, 1),
(40, 'statistics/category-details', 0, 1),
(41, 'statistics/author-details', 0, 1),
(42, 'articles/edit', 1, 1),
(43, 'articles/update', 1, 1),
(44, 'articles/delete', 0, 1),
(45, 'upload-image', 1, 1),
(46, 'categories/edit', 0, 0),
(47, 'categories/update', 0, 0),
(48, 'categories/delete', 0, 0),
(49, 'users/edit', 0, 0),
(50, 'users/update', 0, 0),
(51, 'users/delete', 0, 0),
(52, 'promotions/delete', 0, 1),
(53, 'social-sites/delete', 0, 0),
(54, 'articles/preview', 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
