<?php

class Trash extends Model
{
    public function archiveForm(object $form, array $questions, int $deletedBy): bool
    {
        $originalData = json_encode(['form' => $form, 'questions' => $questions], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return $this->db->query(
            'INSERT INTO forms_trash
             (original_id, user_id, title, description, slug, status, cover_image, original_data, deleted_by)
             VALUES (:oid, :uid, :title, :description, :slug, :status, :cover, :data, :deleted_by)'
        )->bind(':oid', $form->id)
         ->bind(':uid', $form->user_id ?? null)
         ->bind(':title', $form->title)
         ->bind(':description', $form->description ?? null)
         ->bind(':slug', $form->slug ?? null)
         ->bind(':status', $form->status ?? null)
         ->bind(':cover', $form->cover_image ?? null)
         ->bind(':data', $originalData)
         ->bind(':deleted_by', $deletedBy)
         ->execute();
    }

    public function archiveQuestion(object $question, int $deletedBy): bool
    {
        return $this->db->query(
            'INSERT INTO questions_trash
             (original_id, form_id, label, type, is_required, order_index, config, original_data, deleted_by)
             VALUES (:oid, :form_id, :label, :type, :required, :order_index, :config, :data, :deleted_by)'
        )->bind(':oid', $question->id)
         ->bind(':form_id', $question->form_id)
         ->bind(':label', $question->label)
         ->bind(':type', $question->type)
         ->bind(':required', $question->is_required)
         ->bind(':order_index', $question->order_index)
         ->bind(':config', $question->config)
         ->bind(':data', json_encode($question, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))
         ->bind(':deleted_by', $deletedBy)
         ->execute();
    }

    public function archiveResponse(object $response, array $answers, int $deletedBy): bool
    {
        return $this->db->query(
            'INSERT INTO responses_trash
             (original_id, form_id, user_id, submitted_at, ip_address, answers_json, deleted_by)
             VALUES (:oid, :form_id, :user_id, :submitted_at, :ip, :answers, :deleted_by)'
        )->bind(':oid', $response->id)
         ->bind(':form_id', $response->form_id ?? null)
         ->bind(':user_id', $response->user_id ?? null)
         ->bind(':submitted_at', $response->submitted_at ?? null)
         ->bind(':ip', $response->ip_address ?? null)
         ->bind(':answers', json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))
         ->bind(':deleted_by', $deletedBy)
         ->execute();
    }

    public function archiveUser(object $user, int $deletedBy): bool
    {
        return $this->db->query(
            'INSERT INTO users_trash (original_id, name, email, role, original_data, deleted_by)
             VALUES (:oid, :name, :email, :role, :data, :deleted_by)'
        )->bind(':oid', $user->id)
         ->bind(':name', $user->name)
         ->bind(':email', $user->email)
         ->bind(':role', $user->role)
         ->bind(':data', json_encode($user, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))
         ->bind(':deleted_by', $deletedBy)
         ->execute();
    }
}
