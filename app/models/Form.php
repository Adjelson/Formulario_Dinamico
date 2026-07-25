<?php

class Form extends Model
{
    public function createForm(array $data): int
    {
        $this->db->query(
            'INSERT INTO forms (user_id, title, description, slug, status, cover_image)
             VALUES (:user_id, :title, :description, :slug, :status, :cover_image)'
        )->bind(':user_id', $data['user_id'])
         ->bind(':title', $data['title'])
         ->bind(':description', $data['description'])
         ->bind(':slug', $data['slug'])
         ->bind(':status', $data['status'])
         ->bind(':cover_image', $data['cover_image'] ?? null)
         ->execute();
        return (int) $this->db->lastInsertId();
    }

    public function updateForm(array $data): bool
    {
        return $this->db->query(
            'UPDATE forms SET title = :title, description = :description, slug = :slug,
             status = :status, cover_image = :cover_image WHERE id = :id'
        )->bind(':id', $data['id'])
         ->bind(':title', $data['title'])
         ->bind(':description', $data['description'])
         ->bind(':slug', $data['slug'])
         ->bind(':status', $data['status'])
         ->bind(':cover_image', $data['cover_image'] ?? null)
         ->execute();
    }

    public function getForms(): array
    {
        $this->db->query(
            'SELECT f.*, u.name AS owner_name, COUNT(r.id) AS response_count
             FROM forms f
             LEFT JOIN users u ON u.id = f.user_id
             LEFT JOIN responses r ON r.form_id = f.id
             GROUP BY f.id, u.name
             ORDER BY f.created_at DESC'
        );
        return $this->db->resultSet();
    }

    public function getFormById(int $id): object|false
    {
        $this->db->query('SELECT * FROM forms WHERE id = :id')->bind(':id', $id);
        return $this->db->single();
    }

    public function getFormBySlug(string $slug): object|false
    {
        $this->db->query('SELECT * FROM forms WHERE slug = :slug LIMIT 1')->bind(':slug', $slug);
        return $this->db->single();
    }

    public function getPublishedForms(): array
    {
        $this->db->query(
            "SELECT f.*, COUNT(q.id) AS question_count
             FROM forms f LEFT JOIN questions q ON q.form_id = f.id
             WHERE f.status = 'published'
             GROUP BY f.id ORDER BY f.created_at DESC"
        );
        return $this->db->resultSet();
    }

    public function getRecentForms(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $this->db->query(
            'SELECT f.*, COUNT(r.id) AS response_count
             FROM forms f LEFT JOIN responses r ON r.form_id = f.id
             GROUP BY f.id ORDER BY f.created_at DESC LIMIT :limit'
        )->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalForms(): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM forms');
        return (int) ($this->db->single()->total ?? 0);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM forms WHERE slug = :slug';
        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
        }
        $this->db->query($sql)->bind(':slug', $slug);
        if ($excludeId !== null) {
            $this->db->bind(':id', $excludeId);
        }
        return (bool) $this->db->single();
    }

    public function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base !== '' ? $base : 'formulario';
        $candidate = $slug;
        $suffix = 2;
        while ($this->slugExists($candidate, $excludeId)) {
            $candidate = $slug . '-' . $suffix++;
        }
        return $candidate;
    }

    public function deleteForm(int $id): bool
    {
        return $this->delete('forms', $id);
    }
}
