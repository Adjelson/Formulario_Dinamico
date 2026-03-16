<?php

class UserController extends Controller {

    protected $userModel;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->userModel = $this->model("User");

        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("location:" . URLROOT . "/login");
            exit();
        }
    }

    public function index() {
        $this->view("admin/users/index", [
            "users" => $this->userModel->getUsers(),
        ]);
    }

    public function store() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name     = trim(strip_tags($_POST["name"]     ?? ''));
            $email    = trim(strip_tags($_POST["email"]    ?? ''));
            $password = trim($_POST["password"] ?? '');
            $role     = trim($_POST["role"]     ?? 'user');

            $data = [
                "name" => $name, "email" => $email,
                "password" => $password, "role" => $role,
                "name_err" => "", "email_err" => "", "password_err" => "",
            ];

            if (empty($name))  $data["name_err"]  = "Por favor, insira o nome.";
            if (empty($email)) $data["email_err"] = "Por favor, insira o email.";
            elseif ($this->userModel->findUserByEmail($email)) $data["email_err"] = "Email já registado.";

            if (empty($password))          $data["password_err"] = "Por favor, insira a password.";
            elseif (strlen($password) < 6) $data["password_err"] = "A password deve ter pelo menos 6 caracteres.";

            if (empty($data["name_err"]) && empty($data["email_err"]) && empty($data["password_err"])) {
                $data["password"] = password_hash($password, PASSWORD_DEFAULT);
                if ($this->userModel->register($data)) {
                    header("location:" . URLROOT . "/admin/users"); exit();
                }
                die("Erro ao criar o utilizador.");
            } else {
                header("location:" . URLROOT . "/admin/users?err=" . urlencode($data["name_err"] ?: $data["email_err"] ?: $data["password_err"]));
                exit();
            }
        }
    }

    public function update() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $this->params["id"] ?? null;

            $data = [
                "id"        => $id,
                "name"      => trim(strip_tags($_POST["name"]  ?? '')),
                "email"     => trim(strip_tags($_POST["email"] ?? '')),
                "role"      => trim($_POST["role"] ?? 'user'),
                "is_active" => isset($_POST["is_active"]) ? 1 : 0,
                "name_err"  => "",
                "email_err" => "",
            ];

            if (empty($data["name"]))  $data["name_err"]  = "Por favor, insira o nome.";
            if (empty($data["email"])) $data["email_err"] = "Por favor, insira o email.";

            if (empty($data["name_err"]) && empty($data["email_err"])) {
                if ($this->userModel->updateUser($data)) {
                    header("location:" . URLROOT . "/admin/users"); exit();
                }
                die("Erro ao atualizar o utilizador.");
            } else {
                $this->view("admin/users/index", array_merge($data, [
                    "users" => $this->userModel->getUsers(),
                ]));
            }
        }
    }

    public function delete() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id   = $this->params["id"] ?? null;
            $user = $this->userModel->getUserById($id);

            if ($user) {
                // Arquivar antes de eliminar
                $trash = $this->model("Trash");
                $trash->archiveUser($user, $_SESSION["user_id"]);
            }

            if ($this->userModel->deleteUser($id)) {
                header("location:" . URLROOT . "/admin/users"); exit();
            }
            die("Erro ao eliminar o utilizador.");
        }
    }
}
