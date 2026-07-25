<?php

class HealthController extends Controller
{
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $status = ['app' => 'ok', 'database' => 'error', 'storage' => 'error', 'version' => APPVERSION];
        try {
            $db = new Database();
            $db->query('SELECT 1 AS ok');
            $status['database'] = $db->single() ? 'ok' : 'error';
        } catch (Throwable) {
            http_response_code(503);
        }
        $status['storage'] = is_writable(STORAGE_DIR) ? 'ok' : 'error';
        if ($status['database'] !== 'ok' || $status['storage'] !== 'ok') {
            http_response_code(503);
        }
        echo json_encode($status, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
