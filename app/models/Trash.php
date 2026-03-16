<?php

/**
 * Trash Model — Arquivo de dados eliminados
 * Todos os registos eliminados são guardados aqui antes de serem removidos da BD principal.
 * Nunca se perdem dados.
 */
class Trash extends Model {

    /**
     * Arquiva um formulário e todas as suas perguntas antes de eliminar.
     */
    public function archiveForm($form, $questions, $deletedBy) {
        $originalData = json_encode([
            'form'      => $form,
            'questions' => $questions,
        ]);

        $this->db->query(
            'INSERT INTO forms_trash (original_id, user_id, title, description, slug, status, cover_image, original_data, deleted_by)
             VALUES (:oid, :uid, :title, :desc, :slug, :status, :cover, :data, :by)'
        );
        $this->db->bind(':oid',    $form->id);
        $this->db->bind(':uid',    $form->user_id ?? null);
        $this->db->bind(':title',  $form->title);
        $this->db->bind(':desc',   $form->description ?? null);
        $this->db->bind(':slug',   $form->slug ?? null);
        $this->db->bind(':status', $form->status ?? null);
        $this->db->bind(':cover',  $form->cover_image ?? null);
        $this->db->bind(':data',   $originalData);
        $this->db->bind(':by',     $deletedBy);
        return $this->db->execute();
    }

    /**
     * Arquiva uma resposta e todas as suas answers antes de eliminar.
     */
    public function archiveResponse($response, $answers, $deletedBy) {
        $this->db->query(
            'INSERT INTO responses_trash (original_id, form_id, user_id, submitted_at, ip_address, answers_json, deleted_by)
             VALUES (:oid, :fid, :uid, :sat, :ip, :answers, :by)'
        );
        $this->db->bind(':oid',     $response->id);
        $this->db->bind(':fid',     $response->form_id ?? null);
        $this->db->bind(':uid',     $response->user_id ?? null);
        $this->db->bind(':sat',     $response->submitted_at ?? null);
        $this->db->bind(':ip',      $response->ip_address ?? null);
        $this->db->bind(':answers', json_encode($answers));
        $this->db->bind(':by',      $deletedBy);
        return $this->db->execute();
    }

    /**
     * Arquiva um utilizador antes de eliminar.
     */
    public function archiveUser($user, $deletedBy) {
        $this->db->query(
            'INSERT INTO users_trash (original_id, name, email, role, original_data, deleted_by)
             VALUES (:oid, :name, :email, :role, :data, :by)'
        );
        $this->db->bind(':oid',   $user->id);
        $this->db->bind(':name',  $user->name);
        $this->db->bind(':email', $user->email);
        $this->db->bind(':role',  $user->role);
        $this->db->bind(':data',  json_encode($user));
        $this->db->bind(':by',    $deletedBy);
        return $this->db->execute();
    }

    public function getFormTrash() {
        $this->db->query('SELECT * FROM forms_trash ORDER BY deleted_at DESC');
        return $this->db->resultSet();
    }

    public function getResponseTrash() {
        $this->db->query('SELECT * FROM responses_trash ORDER BY deleted_at DESC');
        return $this->db->resultSet();
    }
}
