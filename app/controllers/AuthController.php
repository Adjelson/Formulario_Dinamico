<?php

class AuthController extends Controller {

    protected $userModel;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->userModel = $this->model("User");
    }

    public function showLogin() {
        $this->view("auth/login", [
            'email'        => '',
            'email_err'    => '',
            'password_err' => '',
            'general_err'  => '',
            'redirect'     => trim($_GET['redirect'] ?? ''),
        ]);
    }

    public function login() {
        $redirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? '');

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email    = trim(strip_tags($_POST["email"]    ?? ''));
            $password = trim($_POST["password"] ?? '');

            $data = [
                "email"        => $email,
                "email_err"    => "",
                "password_err" => "",
                "general_err"  => "",
                "redirect"     => $redirect,
            ];

            if (empty($email)) {
                $data["email_err"] = "Por favor, insira o email.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data["email_err"] = "Formato de email inválido.";
            }

            if (empty($password)) {
                $data["password_err"] = "Por favor, insira a password.";
            }

            if (empty($data["email_err"]) && empty($data["password_err"])) {
                if (!$this->userModel->findUserByEmail($email)) {
                    $data["email_err"] = "Credênciais incorretas para este email.";
                } else {
                    $loggedInUser = $this->userModel->login($email, $password);
                    if ($loggedInUser) {
                        $this->createUserSession($loggedInUser, $redirect);
                        return;
                    } else {
                        $data["password_err"] = "Password incorreta. Tente novamente.";
                    }
                }
            }

            $this->view("auth/login", $data);
        } else {
            $this->showLogin();
        }
    }

    public function createUserSession($user, $redirect = '') {
        session_regenerate_id(true);
        $_SESSION["user_id"]    = $user->id;
        $_SESSION["user_email"] = $user->email;
        $_SESSION["user_name"]  = $user->name;
        $_SESSION["user_role"]  = $user->role;

        if (!empty($redirect) && $user->role !== 'admin') {
            $safeRedirect = ltrim(urldecode($redirect), '/');
            if (preg_match('/^(forms\/[a-zA-Z0-9_-]+|home)$/', $safeRedirect)) {
                header("location:" . URLROOT . "/" . $safeRedirect);
                exit();
            }
        }

        if ($user->role == "admin") {
            header("location:" . URLROOT . "/admin/dashboard");
        } else {
            header("location:" . URLROOT . "/home");
        }
        exit();
    }

    public function logout() {
        unset($_SESSION["user_id"], $_SESSION["user_email"],
              $_SESSION["user_name"], $_SESSION["user_role"]);
        session_destroy();
        header("location:" . URLROOT . "/login");
        exit();
    }

    public function register() {
        $redirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? '');

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name             = trim(strip_tags($_POST["name"]             ?? ''));
            $email            = trim(strip_tags($_POST["email"]            ?? ''));
            $password         = trim($_POST["password"]         ?? '');
            $confirm_password = trim($_POST["confirm_password"] ?? '');

            $data = [
                "name" => $name, "email" => $email,
                "password" => $password, "confirm_password" => $confirm_password,
                "role" => "user",
                "name_err" => "", "email_err" => "",
                "password_err" => "", "confirm_password_err" => "",
                "redirect" => $redirect,
            ];

            if (empty($name))  $data["name_err"]  = "Por favor, insira o nome.";
            if (empty($email)) $data["email_err"] = "Por favor, insira o email.";
            elseif ($this->userModel->findUserByEmail($email)) $data["email_err"] = "Email já registado.";

            if (empty($password))          $data["password_err"] = "Por favor, insira a password.";
            elseif (strlen($password) < 6) $data["password_err"] = "A password deve ter pelo menos 6 caracteres.";

            if (empty($confirm_password))            $data["confirm_password_err"] = "Por favor, confirme a password.";
            elseif ($password !== $confirm_password) $data["confirm_password_err"] = "As passwords não coincidem.";

            if (empty($data["name_err"]) && empty($data["email_err"]) &&
                empty($data["password_err"]) && empty($data["confirm_password_err"])) {

                $data["password"] = password_hash($password, PASSWORD_DEFAULT);

                if ($this->userModel->register($data)) {
                    $newUser = $this->userModel->loginAfterRegister($email);
                    if ($newUser) {
                        $this->createUserSession($newUser, $redirect);
                        return;
                    }
                    $qs = $redirect ? '?redirect=' . urlencode($redirect) : '';
                    header("location:" . URLROOT . "/login" . $qs);
                    exit();
                } else {
                    die("Erro ao criar a conta.");
                }
            } else {
                $this->view("auth/register", $data);
            }
        } else {
            $this->view("auth/register", [
                "name" => "", "email" => "", "password" => "",
                "confirm_password" => "", "role" => "user",
                "name_err" => "", "email_err" => "",
                "password_err" => "", "confirm_password_err" => "",
                "redirect" => $redirect,
            ]);
        }
    }
}
