<?php

class Response extends Model {
    protected $table = 'responses';

    public function addResponse($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (form_id, user_id, ip_address) VALUES (:form_id, :user_id, :ip_address)');
        $this->db->bind(':form_id', $data['form_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':ip_address', $data['ip_address']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function getResponsesByFormId($form_id) {
        $this->db->query('SELECT r.*, u.name as user_name, u.email as user_email FROM ' . $this->table . ' r LEFT JOIN users u ON r.user_id = u.id WHERE r.form_id = :form_id ORDER BY r.submitted_at DESC');
        $this->db->bind(':form_id', $form_id);
        return $this->db->resultSet();
    }

    public function getResponseDetail($response_id) {
        $this->db->query('SELECT r.*, u.name as user_name, u.email as user_email, f.title as form_title FROM ' . $this->table . ' r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN forms f ON r.form_id = f.id WHERE r.id = :response_id');
        $this->db->bind(':response_id', $response_id);
        return $this->db->single();
    }

    public function getResponsesByUserId($user_id) {
        $this->db->query(
            'SELECT r.*, f.title as form_title, f.slug as form_slug ' .
            'FROM ' . $this->table . ' r ' .
            'JOIN forms f ON r.form_id = f.id ' .
            'WHERE r.user_id = :user_id ' .
            'ORDER BY r.submitted_at DESC'
        );
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getUserResponseForForm($user_id, $form_id) {
        $this->db->query(
            'SELECT * FROM ' . $this->table .
            ' WHERE user_id = :user_id AND form_id = :form_id ORDER BY submitted_at DESC LIMIT 1'
        );
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':form_id', $form_id);
        return $this->db->single();
    }

    public function deleteResponse($id) {
        return parent::delete($this->table, $id);
    }

    public function getTotalResponses() {
        $this->db->query('SELECT COUNT(*) as total_responses FROM ' . $this->table);
        return $this->db->single()->total_responses;
    }
}
