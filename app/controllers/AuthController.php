<?php

class AuthController extends Controller
{
    private User $userModel;
    private Audit $auditModel;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->userModel = $this->model('User');
        $this->auditModel = $this->model('Audit');
    }

    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect(($_SESSION['user_role'] ?? '') === 'admin' ? 'admin/dashboard' : 'home');
        }

        $redirect = safe_internal_path((string) ($_POST['redirect'] ?? $_GET['redirect'] ?? ''));
        $data = ['email' => '', 'email_err' => '', 'password_err' => '', 'general_err' => '', 'redirect' => $redirect];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            if (isset($_GET['expired'])) {
                $data['general_err'] = 'A sessão expirou por inatividade. Inicie sessão novamente.';
            }
            $this->view('auth/login', $data);
            return;
        }

        $this->verifyCsrf();
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $data['email'] = $email;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['email_err'] = 'Introduza um endereço de email válido.';
        }
        if ($password === '') {
            $data['password_err'] = 'Introduza a palavra-passe.';
        }

        $ip = client_ip();
        if ($data['email_err'] === '' && $data['password_err'] === '' && $this->userModel->tooManyAttempts($email, $ip)) {
            $data['general_err'] = 'Foram efetuadas demasiadas tentativas. Aguarde 15 minutos antes de tentar novamente.';
        }

        if ($data['email_err'] === '' && $data['password_err'] === '' && $data['general_err'] === '') {
            $candidate = $this->userModel->findByEmail($email);
            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                $this->userModel->registerLoginAttempt($email, $ip, true, (int) $user->id);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['_last_activity'] = time();
                unset($_SESSION[CSRF_TOKEN_NAME]);
                $this->auditModel->log('auth.login', 'user', (int) $user->id);

                if ($redirect !== '' && $user->role !== 'admin') {
                    $this->redirect($redirect);
                }
                $this->redirect($user->role === 'admin' ? 'admin/dashboard' : 'home');
            }

            $this->userModel->registerLoginAttempt($email, $ip, false, $candidate ? (int) $candidate->id : null);
            $data['general_err'] = 'Email ou palavra-passe incorretos, conta inativa ou temporariamente bloqueada.';
        }

        usleep(250000);
        $this->view('auth/login', $data);
    }

    public function register(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect(($_SESSION['user_role'] ?? '') === 'admin' ? 'admin/dashboard' : 'home');
        }

        $redirect = safe_internal_path((string) ($_POST['redirect'] ?? $_GET['redirect'] ?? ''));
        $data = [
            'name' => '', 'email' => '', 'password' => '', 'confirm_password' => '', 'role' => 'user',
            'name_err' => '', 'email_err' => '', 'password_err' => '', 'confirm_password_err' => '',
            'general_err' => '', 'redirect' => $redirect,
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->view('auth/register', $data);
            return;
        }

        $this->verifyCsrf();
        $data['name'] = trim((string) ($_POST['name'] ?? ''));
        $data['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (text_length($data['name']) < 2 || text_length($data['name']) > 100) {
            $data['name_err'] = 'O nome deve ter entre 2 e 100 caracteres.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['email_err'] = 'Introduza um endereço de email válido.';
        } elseif ($this->userModel->findUserByEmail($data['email'])) {
            $data['email_err'] = 'Este email já se encontra registado.';
        }
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
            $data['password_err'] = 'Use pelo menos 8 caracteres, incluindo maiúscula, minúscula e número.';
        }
        if ($password !== $confirm) {
            $data['confirm_password_err'] = 'As palavras-passe não coincidem.';
        }

        if ($data['name_err'] === '' && $data['email_err'] === '' && $data['password_err'] === '' && $data['confirm_password_err'] === '') {
            try {
                $this->userModel->register([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'user',
                ]);
                flash_set('success', 'Conta criada com sucesso. Já pode iniciar sessão.');
                $this->redirect('login' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
            } catch (Throwable $e) {
                error_log('[Register] ' . $e->getMessage());
                $data['general_err'] = 'Não foi possível criar a conta. Tente novamente.';
            }
        }

        $this->view('auth/register', $data);
    }

    public function logout(): void
    {
        $this->requirePost();
        $this->verifyCsrf();
        if (!empty($_SESSION['user_id'])) {
            $this->auditModel->log('auth.logout', 'user', (int) $_SESSION['user_id']);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ' . URLROOT . '/login');
        exit;
    }
}
