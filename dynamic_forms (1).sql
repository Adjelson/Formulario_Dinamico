-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16-Mar-2026 às 14:26
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
(9, 'Guilherme dos Santos', 'brigit3e@gmail.com', '$2y$10$./XifMoa7cmyxr.fkiBiE.hBeFxu4Uv2QyYZB1xawDCI4XEew2NXy', 'user', 1, '2026-03-16 11:44:02', '2026-03-16 11:44:02');

-- --------------------------------------------------------

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `responses_trash`
--
ALTER TABLE `responses_trash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
