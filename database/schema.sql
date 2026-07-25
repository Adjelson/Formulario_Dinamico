CREATE DATABASE IF NOT EXISTS dynamic_forms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dynamic_forms;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS responses_trash;
DROP TABLE IF EXISTS questions_trash;
DROP TABLE IF EXISTS forms_trash;
DROP TABLE IF EXISTS users_trash;
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS responses;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS forms;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  failed_login_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY ux_users_email (email),
  KEY ix_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE forms (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  slug VARCHAR(190) NOT NULL,
  cover_image VARCHAR(255) NULL,
  status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY ux_forms_slug (slug),
  KEY ix_forms_status_created (status, created_at),
  KEY ix_forms_user_id (user_id),
  CONSTRAINT fk_forms_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE questions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(500) NOT NULL,
  type ENUM('short_text','long_text','numeric','date','checkbox','radio','upload') NOT NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  order_index INT UNSIGNED NOT NULL DEFAULT 0,
  config JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_questions_form_order (form_id, order_index),
  CONSTRAINT fk_questions_form FOREIGN KEY (form_id) REFERENCES forms(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE responses (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY ux_responses_form_user (form_id, user_id),
  KEY ix_responses_form_submitted (form_id, submitted_at),
  KEY ix_responses_user_submitted (user_id, submitted_at),
  CONSTRAINT fk_responses_form FOREIGN KEY (form_id) REFERENCES forms(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_responses_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  response_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NULL,
  question_label VARCHAR(500) NOT NULL,
  question_type VARCHAR(50) NOT NULL,
  value LONGTEXT NULL,
  file_path VARCHAR(255) NULL,
  original_file_name VARCHAR(255) NULL,
  file_mime VARCHAR(100) NULL,
  file_size BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY ux_answers_response_question (response_id, question_id),
  KEY ix_answers_response (response_id),
  KEY ix_answers_question (question_id),
  KEY ix_answers_file_path (file_path),
  CONSTRAINT fk_answers_response FOREIGN KEY (response_id) REFERENCES responses(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE forms_trash (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  original_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  slug VARCHAR(190) NULL,
  status VARCHAR(50) NULL,
  cover_image VARCHAR(255) NULL,
  original_data LONGTEXT NOT NULL,
  deleted_by BIGINT UNSIGNED NULL,
  deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_forms_trash_original (original_id),
  KEY ix_forms_trash_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE questions_trash (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  original_id BIGINT UNSIGNED NOT NULL,
  form_id BIGINT UNSIGNED NULL,
  label VARCHAR(500) NULL,
  type VARCHAR(50) NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  order_index INT UNSIGNED NOT NULL DEFAULT 0,
  config LONGTEXT NULL,
  original_data LONGTEXT NOT NULL,
  deleted_by BIGINT UNSIGNED NULL,
  deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_questions_trash_form (form_id),
  KEY ix_questions_trash_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE responses_trash (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  original_id BIGINT UNSIGNED NOT NULL,
  form_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  submitted_at DATETIME NULL,
  ip_address VARCHAR(45) NULL,
  answers_json LONGTEXT NOT NULL,
  deleted_by BIGINT UNSIGNED NULL,
  deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_responses_trash_form (form_id),
  KEY ix_responses_trash_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users_trash (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  original_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NULL,
  email VARCHAR(190) NULL,
  role VARCHAR(20) NULL,
  original_data LONGTEXT NOT NULL,
  deleted_by BIGINT UNSIGNED NULL,
  deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_users_trash_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  was_successful TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_login_attempts_lookup (email, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(100) NULL,
  entity_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NOT NULL,
  details JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_audit_user_created (user_id, created_at),
  KEY ix_audit_entity (entity_type, entity_id),
  KEY ix_audit_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
