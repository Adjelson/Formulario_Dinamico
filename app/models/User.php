<?php

class User extends Model
{
    protected string $table = 'users';

    public function register(array $data): bool
    {
        return $this->db->query(
            'INSERT INTO users (name, email, password, role, is_active)
             VALUES (:name, :email, :password, :role, 1)'
        )->bind(':name', $data['name'])
         ->bind(':email', strtolower($data['email']))
         ->bind(':password', $data['password'])
         ->bind(':role', $data['role'])
         ->execute();
    }

    public function findByEmail(string $email): object|false
    {
        $this->db->query('SELECT * FROM users WHERE email = :email LIMIT 1')
            ->bind(':email', strtolower(trim($email)));
        return $this->db->single();
    }

    public function findUserByEmail(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }
        $this->db->query($sql)->bind(':email', strtolower(trim($email)));
        if ($excludeId !== null) {
            $this->db->bind(':exclude_id', $excludeId);
        }
        return (bool) $this->db->single();
    }

    public function authenticate(string $email, string $password): object|false
    {
        $user = $this->findByEmail($email);
        if (!$user || !(int) $user->is_active) {
            return false;
        }
        if (!empty($user->locked_until) && strtotime($user->locked_until) > time()) {
            return false;
        }
        return password_verify($password, $user->password) ? $user : false;
    }

    public function registerLoginAttempt(string $email, string $ip, bool $success, ?int $userId = null): void
    {
        $this->db->query(
            'INSERT INTO login_attempts (email, ip_address, was_successful)
             VALUES (:email, :ip, :success)'
        )->bind(':email', strtolower($email))
         ->bind(':ip', $ip)
         ->bind(':success', $success ? 1 : 0)
         ->execute();

        if ($userId === null) {
            return;
        }

        if ($success) {
            $this->db->query(
                'UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW()
                 WHERE id = :id'
            )->bind(':id', $userId)->execute();
            return;
        }

        $this->db->query(
            'UPDATE users
             SET failed_login_attempts = failed_login_attempts + 1,
                 locked_until = CASE
                   WHEN failed_login_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                   ELSE locked_until
                 END
             WHERE id = :id'
        )->bind(':id', $userId)->execute();
    }

    public function tooManyAttempts(string $email, string $ip): bool
    {
        $this->db->query(
            'SELECT COUNT(*) AS total FROM login_attempts
             WHERE email = :email AND ip_address = :ip AND was_successful = 0
               AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        )->bind(':email', strtolower($email))->bind(':ip', $ip);
        $row = $this->db->single();
        return $row && (int) $row->total >= 10;
    }

    public function getUsers(): array
    {
        $this->db->query(
            'SELECT id, name, email, role, is_active, last_login_at, created_at
             FROM users ORDER BY created_at DESC'
        );
        return $this->db->resultSet();
    }

    public function getUserById(int $id): object|false
    {
        $this->db->query(
            'SELECT id, name, email, role, is_active, last_login_at, created_at
             FROM users WHERE id = :id'
        )->bind(':id', $id);
        return $this->db->single();
    }

    public function updateUser(array $data): bool
    {
        return $this->db->query(
            'UPDATE users
             SET name = :name, email = :email, role = :role, is_active = :is_active
             WHERE id = :id'
        )->bind(':id', $data['id'])
         ->bind(':name', $data['name'])
         ->bind(':email', strtolower($data['email']))
         ->bind(':role', $data['role'])
         ->bind(':is_active', $data['is_active'])
         ->execute();
    }

    public function deactivateUser(int $id): bool
    {
        return $this->db->query('UPDATE users SET is_active = 0 WHERE id = :id')
            ->bind(':id', $id)->execute();
    }

    public function countActiveUsers(): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM users WHERE is_active = 1');
        return (int) ($this->db->single()->total ?? 0);
    }

    public function countActiveAdmins(): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND is_active = 1");
        return (int) ($this->db->single()->total ?? 0);
    }
}
