<?php

class User extends Model {
    protected $table = 'users';

    public function register($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (name, email, password, role) VALUES (:name, :email, :password, :role)');
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', $data['role']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Usado após registo para fazer login automático sem precisar da password
    public function loginAfterRegister($email) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function login($email, $password) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row->password;
            if (password_verify($password, $hashed_password)) {
                return $row;
            } else {
                return false;
            }
        }
        return false;
    }

    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUsers() {
        $this->db->query('SELECT id, name, email, role, is_active, created_at FROM ' . $this->table);
        return $this->db->resultSet();
    }

    public function getUserById($id) {
        $this->db->query('SELECT id, name, email, role, is_active, created_at FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateUser($data) {
        $this->db->query('UPDATE ' . $this->table . ' SET name = :name, email = :email, role = :role, is_active = :is_active WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':is_active', $data['is_active']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($id) {
        return parent::delete($this->table, $id);
    }
}
