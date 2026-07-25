<?php

class Answer extends Model
{
    public function addAnswer(array $data): bool
    {
        return $this->db->query(
            'INSERT INTO answers
             (response_id, question_id, question_label, question_type, value, file_path,
              original_file_name, file_mime, file_size)
             VALUES (:response_id, :question_id, :question_label, :question_type, :value, :file_path,
                     :original_file_name, :file_mime, :file_size)'
        )->bind(':response_id', $data['response_id'])
         ->bind(':question_id', $data['question_id'])
         ->bind(':question_label', $data['question_label'])
         ->bind(':question_type', $data['question_type'])
         ->bind(':value', $data['value'])
         ->bind(':file_path', $data['file_path'])
         ->bind(':original_file_name', $data['original_file_name'] ?? null)
         ->bind(':file_mime', $data['file_mime'] ?? null)
         ->bind(':file_size', $data['file_size'] ?? null)
         ->execute();
    }

    public function getAnswersByResponseId(int $responseId): array
    {
        $this->db->query(
            'SELECT a.*, COALESCE(a.question_label, q.label) AS question_label,
                    COALESCE(a.question_type, q.type) AS question_type
             FROM answers a LEFT JOIN questions q ON a.question_id = q.id
             WHERE a.response_id = :response_id ORDER BY a.id'
        )->bind(':response_id', $responseId);
        return $this->db->resultSet();
    }

    public function getAnswersByResponseIds(array $responseIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $responseIds))));
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->query("SELECT * FROM answers WHERE response_id IN ({$placeholders}) ORDER BY response_id, id");
        foreach ($ids as $index => $id) {
            $this->db->bind($index + 1, $id, PDO::PARAM_INT);
        }
        return $this->db->resultSet();
    }

    public function getAnswerByFilePath(string $filePath): object|false
    {
        $this->db->query('SELECT * FROM answers WHERE file_path = :file_path LIMIT 1')
            ->bind(':file_path', $filePath);
        return $this->db->single();
    }
}
