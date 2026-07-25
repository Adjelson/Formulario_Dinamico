<?php

class Database
{
    private static ?PDO $pdo = null;
    private PDOStatement $stmt;

    public function __construct()
    {
        $this->connection();
    }

    public function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => DB_PERSISTENT,
        ];
        if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
        }

        try {
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return self::$pdo;
        } catch (PDOException $e) {
            error_log('[Database] ' . $e->getMessage());
            throw new RuntimeException('Não foi possível ligar à base de dados. Confirme o ficheiro .env e se o MySQL está ativo.', 0, $e);
        }
    }

    public function query(string $sql): self
    {
        $this->stmt = $this->connection()->prepare($sql);
        return $this;
    }

    public function bind(string|int $param, mixed $value, ?int $type = null): self
    {
        if ($type === null) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function single(): object|false
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->connection()->lastInsertId();
    }

    public function beginTransaction(): void
    {
        if (!$this->connection()->inTransaction()) {
            $this->connection()->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->connection()->inTransaction()) {
            $this->connection()->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->connection()->inTransaction()) {
            $this->connection()->rollBack();
        }
    }

    public function inTransaction(): bool
    {
        return $this->connection()->inTransaction();
    }
}
