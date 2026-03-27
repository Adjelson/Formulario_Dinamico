-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27-Mar-2026 às 10:06
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `dynamic_forms`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `response_id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `question_label` varchar(500) DEFAULT NULL,
  `question_type` varchar(50) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `answers`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `forms`
--

CREATE TABLE `forms` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `forms`
--

INSERT INTO `forms` (`id`, `user_id`, `title`, `description`, `slug`, `cover_image`, `status`, `created_at`, `updated_at`) VALUES
(6, 4, 'IOF', '', 'iof', 'cover_69b7f740c7e0e.jpg', 'published', '2026-03-16 11:08:24', '2026-03-16 12:27:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `forms_trash`
--

CREATE TABLE `forms_trash` (
  `id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL COMMENT 'ID original na tabela forms',
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `original_data` longtext DEFAULT NULL COMMENT 'JSON com todos os dados originais',
  `deleted_by` int(11) DEFAULT NULL COMMENT 'ID do admin que eliminou',
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `label` text NOT NULL,
  `type` enum('short_text','long_text','numeric','checkbox','radio','upload') NOT NULL,
  `is_required` tinyint(4) NOT NULL DEFAULT 0,
  `order_index` int(11) NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `questions`
--

INSERT INTO `questions` (`id`, `form_id`, `label`, `type`, `is_required`, `order_index`, `config`, `created_at`) VALUES
(106, 6, 'hbdfausbfua', 'short_text', 1, 0, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44'),
(107, 6, 'Data de nascimento', 'short_text', 1, 1, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44'),
(108, 6, 'Tamanho de Bota', 'numeric', 0, 2, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44'),
(109, 6, 'Numero de WatsApp', 'numeric', 1, 3, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44'),
(110, 6, 'Numero de BI', 'short_text', 1, 4, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44'),
(111, 6, 'Sexo', 'radio', 0, 5, '{\"date_min\":\"\",\"date_max\":\"\",\"options\":[\"Masculino\",\"Feminino\"]}', '2026-03-16 12:27:44'),
(112, 6, 'Numero de Conta', 'short_text', 1, 6, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44'),
(113, 6, 'Banco ', 'radio', 1, 7, '{\"date_min\":\"\",\"date_max\":\"\",\"options\":[\"BISTP\",\"AFRILAND\",\"GTI\"]}', '2026-03-16 12:27:44'),
(114, 6, 'Documento BI', 'upload', 1, 8, '{\"date_min\":\"\",\"date_max\":\"\",\"allowed_types\":[\"pdf\",\"png\",\"jpeg\"]}', '2026-03-16 12:27:44'),
(115, 6, 'data', 'short_text', 0, 9, '{\"date_min\":\"\",\"date_max\":\"\"}', '2026-03-16 12:27:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `questions_trash`
--

CREATE TABLE `questions_trash` (
  `id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `label` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_required` tinyint(4) DEFAULT 0,
  `order_index` int(11) DEFAULT 0,
  `config` longtext DEFAULT NULL,
  `original_data` longtext DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `responses`
--

INSERT INTO `responses` (`id`, `form_id`, `user_id`, `submitted_at`, `ip_address`) VALUES
(16, 6, 11, '2026-03-18 17:53:13', '::1'),
(17, 6, 12, '2026-03-19 11:20:23', '::1'),
(18, 6, 12, '2026-03-19 11:20:33', '::1'),
(19, 6, 12, '2026-03-19 11:20:50', '::1'),
(20, 6, 12, '2026-03-19 11:21:12', '::1'),
(21, 6, 12, '2026-03-19 11:32:43', '::1'),
(22, 6, 12, '2026-03-19 11:33:34', '::1'),
(23, 6, 12, '2026-03-19 11:38:17', '::1');

-- --------------------------------------------------------

--
-- Estrutura da tabela `responses_trash`
--

CREATE TABLE `responses_trash` (
  `id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `answers_json` longtext DEFAULT NULL COMMENT 'JSON com todas as respostas',
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `responses_trash`
--

INSERT INTO `responses_trash` (`id`, `original_id`, `form_id`, `user_id`, `submitted_at`, `ip_address`, `answers_json`, `deleted_by`, `deleted_at`) VALUES
(1, 11, 6, 11, '2026-03-18 16:27:04', '::1', '[{\"id\":39,\"response_id\":11,\"question_id\":106,\"question_label\":\"hbdfausbfua\",\"question_type\":\"short_text\",\"value\":\"sda\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":40,\"response_id\":11,\"question_id\":107,\"question_label\":\"Data de nascimento\",\"question_type\":\"short_text\",\"value\":\"acad\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":41,\"response_id\":11,\"question_id\":108,\"question_label\":\"Tamanho de Bota\",\"question_type\":\"numeric\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":42,\"response_id\":11,\"question_id\":109,\"question_label\":\"Numero de WatsApp\",\"question_type\":\"numeric\",\"value\":\"323\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":43,\"response_id\":11,\"question_id\":110,\"question_label\":\"Numero de BI\",\"question_type\":\"short_text\",\"value\":\"222\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":44,\"response_id\":11,\"question_id\":111,\"question_label\":\"Sexo\",\"question_type\":\"radio\",\"value\":null,\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":45,\"response_id\":11,\"question_id\":112,\"question_label\":\"Numero de Conta\",\"question_type\":\"short_text\",\"value\":\"2\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":46,\"response_id\":11,\"question_id\":113,\"question_label\":\"Banco \",\"question_type\":\"radio\",\"value\":\"GTI\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":47,\"response_id\":11,\"question_id\":114,\"question_label\":\"Documento BI\",\"question_type\":\"upload\",\"value\":null,\"file_path\":\"69bad258f1149.pdf\",\"created_at\":\"2026-03-18 16:27:04\"},{\"id\":48,\"response_id\":11,\"question_id\":115,\"question_label\":\"data\",\"question_type\":\"short_text\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 16:27:04\"}]', 4, '2026-03-18 16:27:18'),
(2, 10, 6, 11, '2026-03-18 16:25:15', '::1', '[{\"id\":29,\"response_id\":10,\"question_id\":106,\"question_label\":\"hbdfausbfua\",\"question_type\":\"short_text\",\"value\":\"adjelson\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":30,\"response_id\":10,\"question_id\":107,\"question_label\":\"Data de nascimento\",\"question_type\":\"short_text\",\"value\":\"sca\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":31,\"response_id\":10,\"question_id\":108,\"question_label\":\"Tamanho de Bota\",\"question_type\":\"numeric\",\"value\":\"45\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":32,\"response_id\":10,\"question_id\":109,\"question_label\":\"Numero de WatsApp\",\"question_type\":\"numeric\",\"value\":\"684684\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":33,\"response_id\":10,\"question_id\":110,\"question_label\":\"Numero de BI\",\"question_type\":\"short_text\",\"value\":\"5165\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":34,\"response_id\":10,\"question_id\":111,\"question_label\":\"Sexo\",\"question_type\":\"radio\",\"value\":\"Masculino\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":35,\"response_id\":10,\"question_id\":112,\"question_label\":\"Numero de Conta\",\"question_type\":\"short_text\",\"value\":\"adada\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":36,\"response_id\":10,\"question_id\":113,\"question_label\":\"Banco \",\"question_type\":\"radio\",\"value\":\"BISTP\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":37,\"response_id\":10,\"question_id\":114,\"question_label\":\"Documento BI\",\"question_type\":\"upload\",\"value\":null,\"file_path\":\"69bad1eb7c3fb.pdf\",\"created_at\":\"2026-03-18 16:25:15\"},{\"id\":38,\"response_id\":10,\"question_id\":115,\"question_label\":\"data\",\"question_type\":\"short_text\",\"value\":\"afasaas646\",\"file_path\":null,\"created_at\":\"2026-03-18 16:25:15\"}]', 11, '2026-03-18 17:22:27'),
(3, 12, 6, 11, '2026-03-18 17:23:27', '::1', '[{\"id\":49,\"response_id\":12,\"question_id\":106,\"question_label\":\"hbdfausbfua\",\"question_type\":\"short_text\",\"value\":\"ascsd\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":50,\"response_id\":12,\"question_id\":107,\"question_label\":\"Data de nascimento\",\"question_type\":\"short_text\",\"value\":\"sfsd\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":51,\"response_id\":12,\"question_id\":108,\"question_label\":\"Tamanho de Bota\",\"question_type\":\"numeric\",\"value\":\"451\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":52,\"response_id\":12,\"question_id\":109,\"question_label\":\"Numero de WatsApp\",\"question_type\":\"numeric\",\"value\":\"656\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":53,\"response_id\":12,\"question_id\":110,\"question_label\":\"Numero de BI\",\"question_type\":\"short_text\",\"value\":\"cacas\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":54,\"response_id\":12,\"question_id\":111,\"question_label\":\"Sexo\",\"question_type\":\"radio\",\"value\":null,\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":55,\"response_id\":12,\"question_id\":112,\"question_label\":\"Numero de Conta\",\"question_type\":\"short_text\",\"value\":\"4654\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":56,\"response_id\":12,\"question_id\":113,\"question_label\":\"Banco \",\"question_type\":\"radio\",\"value\":\"BISTP\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":57,\"response_id\":12,\"question_id\":114,\"question_label\":\"Documento BI\",\"question_type\":\"upload\",\"value\":null,\"file_path\":\"69badf8f399f0.pdf\",\"created_at\":\"2026-03-18 17:23:27\"},{\"id\":58,\"response_id\":12,\"question_id\":115,\"question_label\":\"data\",\"question_type\":\"short_text\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 17:23:27\"}]', 11, '2026-03-18 17:23:39'),
(4, 13, 6, 11, '2026-03-18 17:40:18', '::1', '[{\"id\":59,\"response_id\":13,\"question_id\":106,\"question_label\":\"hbdfausbfua\",\"question_type\":\"short_text\",\"value\":\"sfsdfs\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":60,\"response_id\":13,\"question_id\":107,\"question_label\":\"Data de nascimento\",\"question_type\":\"short_text\",\"value\":\"fsasfs\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":61,\"response_id\":13,\"question_id\":108,\"question_label\":\"Tamanho de Bota\",\"question_type\":\"numeric\",\"value\":\"2323\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":62,\"response_id\":13,\"question_id\":109,\"question_label\":\"Numero de WatsApp\",\"question_type\":\"numeric\",\"value\":\"3233\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":63,\"response_id\":13,\"question_id\":110,\"question_label\":\"Numero de BI\",\"question_type\":\"short_text\",\"value\":\"21e12\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":64,\"response_id\":13,\"question_id\":111,\"question_label\":\"Sexo\",\"question_type\":\"radio\",\"value\":\"Masculino\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":65,\"response_id\":13,\"question_id\":112,\"question_label\":\"Numero de Conta\",\"question_type\":\"short_text\",\"value\":\"1321\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":66,\"response_id\":13,\"question_id\":113,\"question_label\":\"Banco \",\"question_type\":\"radio\",\"value\":\"BISTP\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":67,\"response_id\":13,\"question_id\":114,\"question_label\":\"Documento BI\",\"question_type\":\"upload\",\"value\":null,\"file_path\":\"69bae3827f246.pdf\",\"created_at\":\"2026-03-18 17:40:18\"},{\"id\":68,\"response_id\":13,\"question_id\":115,\"question_label\":\"data\",\"question_type\":\"short_text\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 17:40:18\"}]', 11, '2026-03-18 17:40:25'),
(5, 14, 6, 11, '2026-03-18 17:42:37', '::1', '[{\"id\":69,\"response_id\":14,\"question_id\":106,\"question_label\":\"hbdfausbfua\",\"question_type\":\"short_text\",\"value\":\"asfa\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":70,\"response_id\":14,\"question_id\":107,\"question_label\":\"Data de nascimento\",\"question_type\":\"short_text\",\"value\":\"aff\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":71,\"response_id\":14,\"question_id\":108,\"question_label\":\"Tamanho de Bota\",\"question_type\":\"numeric\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":72,\"response_id\":14,\"question_id\":109,\"question_label\":\"Numero de WatsApp\",\"question_type\":\"numeric\",\"value\":\"213\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":73,\"response_id\":14,\"question_id\":110,\"question_label\":\"Numero de BI\",\"question_type\":\"short_text\",\"value\":\"32\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":74,\"response_id\":14,\"question_id\":111,\"question_label\":\"Sexo\",\"question_type\":\"radio\",\"value\":null,\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":75,\"response_id\":14,\"question_id\":112,\"question_label\":\"Numero de Conta\",\"question_type\":\"short_text\",\"value\":\"121\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":76,\"response_id\":14,\"question_id\":113,\"question_label\":\"Banco \",\"question_type\":\"radio\",\"value\":\"BISTP\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":77,\"response_id\":14,\"question_id\":114,\"question_label\":\"Documento BI\",\"question_type\":\"upload\",\"value\":null,\"file_path\":\"69bae40d0fe1b.pdf\",\"created_at\":\"2026-03-18 17:42:37\"},{\"id\":78,\"response_id\":14,\"question_id\":115,\"question_label\":\"data\",\"question_type\":\"short_text\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 17:42:37\"}]', 11, '2026-03-18 17:44:01'),
(6, 15, 6, 11, '2026-03-18 17:51:27', '::1', '[{\"id\":79,\"response_id\":15,\"question_id\":106,\"question_label\":\"hbdfausbfua\",\"question_type\":\"short_text\",\"value\":\"adasd\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":80,\"response_id\":15,\"question_id\":107,\"question_label\":\"Data de nascimento\",\"question_type\":\"short_text\",\"value\":\"ada\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":81,\"response_id\":15,\"question_id\":108,\"question_label\":\"Tamanho de Bota\",\"question_type\":\"numeric\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":82,\"response_id\":15,\"question_id\":109,\"question_label\":\"Numero de WatsApp\",\"question_type\":\"numeric\",\"value\":\"12\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":83,\"response_id\":15,\"question_id\":110,\"question_label\":\"Numero de BI\",\"question_type\":\"short_text\",\"value\":\"211\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":84,\"response_id\":15,\"question_id\":111,\"question_label\":\"Sexo\",\"question_type\":\"radio\",\"value\":null,\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":85,\"response_id\":15,\"question_id\":112,\"question_label\":\"Numero de Conta\",\"question_type\":\"short_text\",\"value\":\"31\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":86,\"response_id\":15,\"question_id\":113,\"question_label\":\"Banco \",\"question_type\":\"radio\",\"value\":\"GTI\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":87,\"response_id\":15,\"question_id\":114,\"question_label\":\"Documento BI\",\"question_type\":\"upload\",\"value\":null,\"file_path\":\"69bae61f3dc35.pdf\",\"created_at\":\"2026-03-18 17:51:27\"},{\"id\":88,\"response_id\":15,\"question_id\":115,\"question_label\":\"data\",\"question_type\":\"short_text\",\"value\":\"\",\"file_path\":null,\"created_at\":\"2026-03-18 17:51:27\"}]', 11, '2026-03-18 17:51:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2024-07-28 16:07:33', '2024-07-28 16:07:33'),
(3, 'Userjnj2', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2024-07-28 16:07:33', '2026-03-14 19:54:44'),
(4, 'Adjelson Neves', 'gpt@gmail.com', '$2y$10$.XF2WyFh8q9r4NYEufKCjeunYy/aZJdKHVfIf5Zj1tkvMfKPGsfbe', 'admin', 1, '2026-03-14 18:21:49', '2026-03-14 18:52:49'),
---------------------------------------------

--
-- Estrutura da tabela `users_trash`
--

CREATE TABLE `users_trash` (
  `id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `original_data` longtext DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `response_id` (`response_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Índices para tabela `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `forms_trash`
--
ALTER TABLE `forms_trash`
  ADD PRIMARY KEY (`id`),
  ADD KEY `original_id` (`original_id`);

--
-- Índices para tabela `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`);

--
-- Índices para tabela `questions_trash`
--
ALTER TABLE `questions_trash`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `responses_trash`
--
ALTER TABLE `responses_trash`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `users_trash`
--
ALTER TABLE `users_trash`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT de tabela `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `forms_trash`
--
ALTER TABLE `forms_trash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de tabela `questions_trash`
--
ALTER TABLE `questions_trash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `responses_trash`
--
ALTER TABLE `responses_trash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `users_trash`
--
ALTER TABLE `users_trash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`response_id`) REFERENCES `responses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `forms`
--
ALTER TABLE `forms`
  ADD CONSTRAINT `forms_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
