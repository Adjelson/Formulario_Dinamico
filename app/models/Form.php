<?php

class Form extends Model {
    protected $table = 'forms';

    public function createForm($data) {
        $this->db->query('INSERT INTO ' . $this->table .
            ' (user_id, title, description, slug, status, cover_image)' .
            ' VALUES (:user_id, :title, :description, :slug, :status, :cover_image)');
        $this->db->bind(':user_id',      $data['user_id']);
        $this->db->bind(':title',        $data['title']);
        $this->db->bind(':description',  $data['description']);
        $this->db->bind(':slug',         $data['slug']);
        $this->db->bind(':status',       $data['status']);
        $this->db->bind(':cover_image',  $data['cover_image'] ?? null);
        if ($this->db->execute()) return $this->db->lastInsertId();
        return false;
    }

    public function updateForm($data) {
        $this->db->query('UPDATE ' . $this->table .
            ' SET title=:title, description=:description, slug=:slug,' .
            ' status=:status, cover_image=:cover_image WHERE id=:id');
        $this->db->bind(':id',           $data['id']);
        $this->db->bind(':title',        $data['title']);
        $this->db->bind(':description',  $data['description']);
        $this->db->bind(':slug',         $data['slug']);
        $this->db->bind(':status',       $data['status']);
        $this->db->bind(':cover_image',  $data['cover_image'] ?? null);
        return $this->db->execute();
    }

    public function getForms() {
        $this->db->query('SELECT f.*, COUNT(r.id) as response_count ' .
            'FROM ' . $this->table . ' f ' .
            'LEFT JOIN responses r ON f.id=r.form_id ' .
            'GROUP BY f.id ORDER BY f.created_at DESC');
        return $this->db->resultSet();
    }

    public function getFormById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id=:id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getFormBySlug($slug) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE slug=:slug');
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    public function getPublishedForms() {
        $this->db->query('SELECT * FROM ' . $this->table .
            ' WHERE status=:status ORDER BY created_at DESC');
        $this->db->bind(':status', 'published');
        return $this->db->resultSet();
    }

    public function getRecentForms($limit = 5) {
        $this->db->query('SELECT f.*, COUNT(r.id) as response_count ' .
            'FROM ' . $this->table . ' f ' .
            'LEFT JOIN responses r ON f.id=r.form_id ' .
            'GROUP BY f.id ORDER BY f.created_at DESC LIMIT :limit');
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalForms() {
        $this->db->query('SELECT COUNT(*) as total_forms FROM ' . $this->table);
        return $this->db->single()->total_forms;
    }

    public function deleteForm($id) {
        return parent::delete($this->table, $id);
    }
}
