<?php

class Model
{
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function beginTransaction(): void { $this->db->beginTransaction(); }
    public function commit(): void { $this->db->commit(); }
    public function rollBack(): void { $this->db->rollBack(); }

    public function findById(string $table, int $id): object|false
    {
        $this->assertTable($table);
        $this->db->query("SELECT * FROM `{$table}` WHERE id = :id")->bind(':id', $id);
        return $this->db->single();
    }

    public function findAll(string $table): array
    {
        $this->assertTable($table);
        $this->db->query("SELECT * FROM `{$table}` ORDER BY id DESC");
        return $this->db->resultSet();
    }

    public function delete(string $table, int $id): bool
    {
        $this->assertTable($table);
        return $this->db->query("DELETE FROM `{$table}` WHERE id = :id")->bind(':id', $id)->execute();
    }

    private function assertTable(string $table): void
    {
        if (!preg_match('/^[a-z_]+$/i', $table)) {
            throw new InvalidArgumentException('Nome de tabela inválido.');
        }
    }
}
