<?php

class Audit extends Model
{
    public function log(string $action, ?string $entityType = null, ?int $entityId = null, array $details = []): void
    {
        try {
            $this->db->query(
                'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, details)
                 VALUES (:user_id, :action, :entity_type, :entity_id, :ip, :details)'
            )->bind(':user_id', $_SESSION['user_id'] ?? null)
             ->bind(':action', $action)
             ->bind(':entity_type', $entityType)
             ->bind(':entity_id', $entityId)
             ->bind(':ip', client_ip())
             ->bind(':details', $details === [] ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))
             ->execute();
        } catch (Throwable $e) {
            error_log('[Audit] ' . $e->getMessage());
        }
    }
}
