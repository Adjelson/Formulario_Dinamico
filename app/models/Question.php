<?php

class Question extends Model {
    protected $table = 'questions';

    public function addQuestion($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (form_id, label, type, is_required, order_index, config) VALUES (:form_id, :label, :type, :is_required, :order_index, :config)');
        $this->db->bind(':form_id', $data['form_id']);
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':is_required', $data['is_required']);
        $this->db->bind(':order_index', $data['order_index']);
        $this->db->bind(':config', $data['config']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function updateQuestion($data) {
        $this->db->query('UPDATE ' . $this->table . ' SET label = :label, type = :type, is_required = :is_required, order_index = :order_index, config = :config WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':label', $data['label']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':is_required', $data['is_required']);
        $this->db->bind(':order_index', $data['order_index']);
        $this->db->bind(':config', $data['config']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getQuestionsByFormId($form_id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE form_id = :form_id ORDER BY order_index ASC');
        $this->db->bind(':form_id', $form_id);
        return $this->db->resultSet();
    }

    public function deleteQuestion($id) {
        return parent::delete($this->table, $id);
    }

    public function deleteQuestionsByFormId($form_id) {
        $this->db->query('DELETE FROM ' . $this->table . ' WHERE form_id = :form_id');
        $this->db->bind(':form_id', $form_id);
        return $this->db->execute();
    }
}
