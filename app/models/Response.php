<?php

class Response extends Model
{
    public function addResponse(array $data): int
    {
        $this->db->query(
            'INSERT INTO responses (form_id, user_id, ip_address)
             VALUES (:form_id, :user_id, :ip_address)'
        )->bind(':form_id', $data['form_id'])
         ->bind(':user_id', $data['user_id'])
         ->bind(':ip_address', $data['ip_address'])
         ->execute();
        return (int) $this->db->lastInsertId();
    }

    public function getResponsesByFormId(int $formId): array
    {
        $this->db->query(
            'SELECT r.*, COALESCE(u.name, "Utilizador removido") AS user_name,
                    COALESCE(u.email, "") AS user_email
             FROM responses r LEFT JOIN users u ON r.user_id = u.id
             WHERE r.form_id = :form_id ORDER BY r.submitted_at DESC'
        )->bind(':form_id', $formId);
        return $this->db->resultSet();
    }

    public function getResponseDetail(int $responseId): object|false
    {
        $this->db->query(
            'SELECT r.*, COALESCE(u.name, "Utilizador removido") AS user_name,
                    COALESCE(u.email, "") AS user_email,
                    f.title AS form_title, f.slug AS form_slug
             FROM responses r
             LEFT JOIN users u ON r.user_id = u.id
             JOIN forms f ON r.form_id = f.id
             WHERE r.id = :response_id'
        )->bind(':response_id', $responseId);
        return $this->db->single();
    }

    public function getResponsesByUserId(int $userId): array
    {
        $this->db->query(
            'SELECT r.*, f.title AS form_title, f.slug AS form_slug
             FROM responses r JOIN forms f ON r.form_id = f.id
             WHERE r.user_id = :user_id ORDER BY r.submitted_at DESC'
        )->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getUserResponseForForm(int $userId, int $formId): object|false
    {
        $this->db->query(
            'SELECT * FROM responses WHERE user_id = :user_id AND form_id = :form_id LIMIT 1'
        )->bind(':user_id', $userId)->bind(':form_id', $formId);
        return $this->db->single();
    }

    public function getAnsweredFormIds(int $userId): array
    {
        $this->db->query('SELECT form_id, id FROM responses WHERE user_id = :user_id')
            ->bind(':user_id', $userId);
        $map = [];
        foreach ($this->db->resultSet() as $row) {
            $map[(int) $row->form_id] = (int) $row->id;
        }
        return $map;
    }

    public function getTotalResponses(): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM responses');
        return (int) ($this->db->single()->total ?? 0);
    }

    public function deleteResponse(int $id): bool
    {
        return $this->delete('responses', $id);
    }
}
