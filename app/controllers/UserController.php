<?php

class UserController extends Controller
{
    private User $userModel;
    private Audit $auditModel;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->userModel = $this->model('User');
        $this->auditModel = $this->model('Audit');
    }

    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin/users/index', ['users' => $this->userModel->getUsers()]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'user'], true) ? $_POST['role'] : 'user';

        if (text_length($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,128}$/', $password)) {
            flash_set('danger', 'Preencha corretamente o nome, email e uma palavra-passe com 8 caracteres, maiúscula, minúscula e número.');
            $this->redirect('admin/users');
        }
        if ($this->userModel->findUserByEmail($email)) {
            flash_set('danger', 'Já existe um utilizador com este email.');
            $this->redirect('admin/users');
        }

        $this->userModel->register([
            'name' => $name, 'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role,
        ]);
        $user = $this->userModel->findByEmail($email);
        $this->auditModel->log('user.create', 'user', $user ? (int) $user->id : null, ['role' => $role]);
        flash_set('success', 'Utilizador criado com sucesso.');
        $this->redirect('admin/users');
    }

    public function update(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($this->params['id'] ?? 0);
        $target = $this->userModel->getUserById($id);
        if (!$target) {
            flash_set('danger', 'Utilizador não encontrado.');
            $this->redirect('admin/users');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $role = in_array($_POST['role'] ?? '', ['admin', 'user'], true) ? $_POST['role'] : 'user';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (text_length($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('danger', 'Nome ou email inválido.');
            $this->redirect('admin/users');
        }
        if ($this->userModel->findUserByEmail($email, $id)) {
            flash_set('danger', 'O email já está associado a outro utilizador.');
            $this->redirect('admin/users');
        }
        if ($id === (int) $_SESSION['user_id'] && ($role !== 'admin' || !$isActive)) {
            flash_set('danger', 'Não pode retirar o seu próprio acesso administrativo nem desativar a sua sessão.');
            $this->redirect('admin/users');
        }
        if ($target->role === 'admin' && ($role !== 'admin' || !$isActive) && $this->userModel->countActiveAdmins() <= 1) {
            flash_set('danger', 'O sistema deve manter pelo menos um administrador ativo.');
            $this->redirect('admin/users');
        }

        $this->userModel->updateUser(compact('id', 'name', 'email', 'role') + ['is_active' => $isActive]);
        $this->auditModel->log('user.update', 'user', $id, ['role' => $role, 'is_active' => $isActive]);
        flash_set('success', 'Utilizador atualizado.');
        $this->redirect('admin/users');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($this->params['id'] ?? 0);
        if ($id === (int) $_SESSION['user_id']) {
            flash_set('danger', 'Não pode desativar a própria conta.');
            $this->redirect('admin/users');
        }
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            flash_set('danger', 'Utilizador não encontrado.');
            $this->redirect('admin/users');
        }
        if ($user->role === 'admin' && $this->userModel->countActiveAdmins() <= 1) {
            flash_set('danger', 'Não é possível desativar o último administrador.');
            $this->redirect('admin/users');
        }

        $this->userModel->beginTransaction();
        try {
            $this->model('Trash')->archiveUser($user, (int) $_SESSION['user_id']);
            $this->userModel->deactivateUser($id);
            $this->userModel->commit();
            $this->auditModel->log('user.deactivate', 'user', $id);
            flash_set('success', 'Utilizador desativado sem eliminar os seus dados históricos.');
        } catch (Throwable $e) {
            $this->userModel->rollBack();
            error_log('[User delete] ' . $e->getMessage());
            flash_set('danger', 'Não foi possível desativar o utilizador.');
        }
        $this->redirect('admin/users');
    }
}
