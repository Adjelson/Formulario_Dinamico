<?php

class Controller
{
    protected array $params = [];

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    protected function model(string $model): object
    {
        $file = APPROOT . '/app/models/' . $model . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Modelo não encontrado: ' . $model);
        }
        require_once $file;
        return new $model();
    }

    protected function view(string $view, array $data = []): void
    {
        $file = APPROOT . '/app/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('View não encontrada: ' . $view);
        }
        $data['flash'] = flash_take();
        require $file;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . URLROOT . '/' . ltrim($path, '/'));
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('login');
        }
        $last = (int) ($_SESSION['_last_activity'] ?? time());
        if ((time() - $last) > SESSION_LIFETIME) {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . URLROOT . '/login?expired=1');
            exit;
        }
        $_SESSION['_last_activity'] = time();
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            throw new RuntimeException('Não tem permissão para executar esta ação.', 403);
        }
    }

    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            throw new RuntimeException('Método não permitido.', 405);
        }
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!csrf_is_valid(is_string($token) ? $token : null)) {
            http_response_code(419);
            throw new RuntimeException('A sessão do formulário expirou. Atualize a página e tente novamente.', 419);
        }
    }
}
