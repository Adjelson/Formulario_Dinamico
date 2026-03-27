<?php

class Answer extends Model {
    protected $table = 'answers';

    public function addAnswer($data) {
        $this->db->query('INSERT INTO ' . $this->table . ' (response_id, question_id, question_label, question_type, value, file_path) VALUES (:response_id, :question_id, :question_label, :question_type, :value, :file_path)');
        $this->db->bind(':response_id',    $data['response_id']);
        $this->db->bind(':question_id',    $data['question_id']);
        $this->db->bind(':question_label', $data['question_label'] ?? null);
        $this->db->bind(':question_type',  $data['question_type']  ?? null);
        $this->db->bind(':value',          $data['value']);
        $this->db->bind(':file_path',      $data['file_path']);

        return $this->db->execute();
    }

    public function getAnswersByResponseId($response_id) {
        // Usa question_label/question_type guardados na resposta (resistente a edições do formulário)Adjelson
        // Faz LEFT JOIN para compatibilidade com respostas antigas sem essas colunas
        $this->db->query('
            SELECT
                a.*,
                COALESCE(a.question_label, q.label) AS question_label,
                COALESCE(a.question_type,  q.type)  AS question_type
            FROM ' . $this->table . ' a
            LEFT JOIN questions q ON a.question_id = q.id
            WHERE a.response_id = :response_id
        ');
        $this->db->bind(':response_id', $response_id);
        return $this->db->resultSet();
    }

    /**
     * Procura uma resposta pelo nome do ficheiro guardado em disco.
     * Usado pelo DownloadController para verificar propriedade do ficheiro.
     */
    public function getAnswerByFilePath($file_path) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE file_path = :file_path LIMIT 1');
        $this->db->bind(':file_path', $file_path);
        return $this->db->single();
    }

    public function deleteAnswersByResponseId($response_id) {
        $this->db->query('DELETE FROM ' . $this->table . ' WHERE response_id = :response_id');
        $this->db->bind(':response_id', $response_id);
        return $this->db->execute();
    }
}
