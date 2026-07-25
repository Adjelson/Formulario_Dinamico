-- Migração conservadora da estrutura original de março de 2026.
-- Faça backup da base e das pastas storage/uploads e storage/covers.
-- Este script NÃO elimina respostas duplicadas: interrompe para evitar perda silenciosa.

USE dynamic_forms;

DELIMITER $$
DROP PROCEDURE IF EXISTS dynamic_forms_preflight_v2$$
CREATE PROCEDURE dynamic_forms_preflight_v2()
BEGIN
  DECLARE duplicate_groups INT DEFAULT 0;

  SELECT COUNT(*) INTO duplicate_groups
  FROM (
    SELECT form_id, user_id
    FROM responses
    WHERE user_id IS NOT NULL
    GROUP BY form_id, user_id
    HAVING COUNT(*) > 1
  ) AS duplicates_found;

  IF duplicate_groups > 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Migração interrompida: existem respostas duplicadas por utilizador/formulário. Arquive ou escolha quais manter antes de executar novamente.';
  END IF;
END$$
CALL dynamic_forms_preflight_v2()$$
DROP PROCEDURE dynamic_forms_preflight_v2$$
DELIMITER ;

ALTER TABLE questions
  MODIFY type ENUM('short_text','long_text','numeric','date','checkbox','radio','upload') NOT NULL;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS failed_login_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER is_active,
  ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL AFTER failed_login_attempts,
  ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL AFTER locked_until;

ALTER TABLE answers
  ADD COLUMN IF NOT EXISTS original_file_name VARCHAR(255) NULL AFTER file_path,
  ADD COLUMN IF NOT EXISTS file_mime VARCHAR(100) NULL AFTER original_file_name,
  ADD COLUMN IF NOT EXISTS file_size BIGINT UNSIGNED NULL AFTER file_mime;

-- Na base v1, a constraint tem este nome e user_id é INT(11).
ALTER TABLE forms DROP FOREIGN KEY forms_ibfk_1;
ALTER TABLE forms MODIFY user_id INT(11) NULL;
ALTER TABLE forms
  ADD CONSTRAINT fk_forms_user FOREIGN KEY (user_id) REFERENCES users(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE responses
  ADD UNIQUE KEY ux_responses_form_user (form_id, user_id),
  ADD KEY ix_responses_form_submitted (form_id, submitted_at),
  ADD KEY ix_responses_user_submitted (user_id, submitted_at);

ALTER TABLE questions
  ADD KEY ix_questions_form_order (form_id, order_index);

ALTER TABLE answers
  ADD KEY ix_answers_file_path (file_path);

ALTER TABLE users
  ADD KEY ix_users_role_active (role, is_active);

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  was_successful TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_login_attempts_lookup (email, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(11) NULL,
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
