<?php

class Question extends Model
{
    public function addQuestion(array $data): int
    {
        $this->db->query(
            'INSERT INTO questions (form_id, label, type, is_required, order_index, config)
             VALUES (:form_id, :label, :type, :is_required, :order_index, :config)'
        )->bind(':form_id', $data['form_id'])
         ->bind(':label', $data['label'])
         ->bind(':type', $data['type'])
         ->bind(':is_required', $data['is_required'])
         ->bind(':order_index', $data['order_index'])
         ->bind(':config', $data['config'])
         ->execute();
        return (int) $this->db->lastInsertId();
    }

    public function updateQuestion(array $data): bool
    {
        return $this->db->query(
            'UPDATE questions SET label = :label, type = :type, is_required = :is_required,
             order_index = :order_index, config = :config
             WHERE id = :id AND form_id = :form_id'
        )->bind(':id', $data['id'])
         ->bind(':form_id', $data['form_id'])
         ->bind(':label', $data['label'])
         ->bind(':type', $data['type'])
         ->bind(':is_required', $data['is_required'])
         ->bind(':order_index', $data['order_index'])
         ->bind(':config', $data['config'])
         ->execute();
    }

    public function getQuestionsByFormId(int $formId): array
    {
        $this->db->query('SELECT * FROM questions WHERE form_id = :form_id ORDER BY order_index, id')
            ->bind(':form_id', $formId);
        return $this->db->resultSet();
    }

    public function syncQuestions(int $formId, array $questions): void
    {
        $existing = $this->getQuestionsByFormId($formId);
        $existingIds = array_map(static fn($q) => (int) $q->id, $existing);
        $keptIds = [];

        foreach ($questions as $index => $question) {
            $payload = $question + ['form_id' => $formId, 'order_index' => $index];
            $id = isset($question['id']) ? (int) $question['id'] : 0;
            if ($id > 0 && in_array($id, $existingIds, true)) {
                $payload['id'] = $id;
                $this->updateQuestion($payload);
                $keptIds[] = $id;
            } else {
                $keptIds[] = $this->addQuestion($payload);
            }
        }

        $removed = array_values(array_diff($existingIds, $keptIds));
        foreach ($removed as $id) {
            $this->deleteQuestion($id, $formId);
        }
    }

    public function deleteQuestion(int $id, ?int $formId = null): bool
    {
        $sql = 'DELETE FROM questions WHERE id = :id';
        if ($formId !== null) {
            $sql .= ' AND form_id = :form_id';
        }
        $this->db->query($sql)->bind(':id', $id);
        if ($formId !== null) {
            $this->db->bind(':form_id', $formId);
        }
        return $this->db->execute();
    }
}
