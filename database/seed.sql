USE dynamic_forms;

-- Palavra-passe das duas contas: Admin@123
INSERT INTO users (name, email, password, role, is_active)
VALUES
('Administrador', 'admin@example.com', '$2y$12$hr.lG2Y58vFCfLJOelxzvOC6MTiS0yFtZWM9tsyMIlA.ABVlTSiiK', 'admin', 1),
('Utilizador de Demonstração', 'utilizador@example.com', '$2y$12$hr.lG2Y58vFCfLJOelxzvOC6MTiS0yFtZWM9tsyMIlA.ABVlTSiiK', 'user', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role), is_active = 1;

INSERT INTO forms (user_id, title, description, slug, status)
SELECT id, 'Formulário de Demonstração', 'Exemplo para validar os principais tipos de pergunta.', 'formulario-de-demonstracao', 'published'
FROM users WHERE email = 'admin@example.com'
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), status = 'published';

SET @form_id = (SELECT id FROM forms WHERE slug = 'formulario-de-demonstracao' LIMIT 1);

INSERT INTO questions (form_id, label, type, is_required, order_index, config)
SELECT @form_id, 'Nome completo', 'short_text', 1, 0, JSON_OBJECT()
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE form_id = @form_id);
INSERT INTO questions (form_id, label, type, is_required, order_index, config)
SELECT @form_id, 'Data de nascimento', 'date', 1, 1, JSON_OBJECT('date_max', DATE_FORMAT(CURDATE(), '%Y-%m-%d'))
WHERE (SELECT COUNT(*) FROM questions WHERE form_id = @form_id) = 1;
INSERT INTO questions (form_id, label, type, is_required, order_index, config)
SELECT @form_id, 'Área de interesse', 'radio', 1, 2, JSON_OBJECT('options', JSON_ARRAY('Tecnologia','Gestão','Educação'))
WHERE (SELECT COUNT(*) FROM questions WHERE form_id = @form_id) = 2;
