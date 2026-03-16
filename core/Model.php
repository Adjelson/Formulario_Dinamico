<?php

class Model {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Generic method to find a record by ID
    public function findById($table, $id) {
        $this->db->query('SELECT * FROM ' . $table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Generic method to find all records from a table
    public function findAll($table) {
        $this->db->query('SELECT * FROM ' . $table);
        return $this->db->resultSet();
    }

    // Generic method to delete a record by ID
    public function delete($table, $id) {
        $this->db->query('DELETE FROM ' . $table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
