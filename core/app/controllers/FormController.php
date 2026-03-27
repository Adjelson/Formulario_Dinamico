<?php

class FormController extends Controller {

    protected $formModel;
    protected $questionModel;
    protected $userModel;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->formModel     = $this->model("Form");
        $this->questionModel = $this->model("Question");
        $this->userModel     = $this->model("User");
    }

    public function dashboard() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        $this->view("admin/dashboard", [
            "totalForms"     => $this->formModel->getTotalForms(),
            "totalResponses" => $this->model("Response")->getTotalResponses(),
            "activeUsers"    => count($this->userModel->findAll("users")),
            "recentForms"    => $this->formModel->getRecentForms(5),
        ]);
    }

    public function index() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        $this->view("admin/forms/index", ["forms" => $this->formModel->getForms()]);
    }

    public function create() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        $this->view("admin/forms/create", [
            "title" => "", "description" => "", "status" => "draft",
            "cover_image" => null, "questions" => [], "title_err" => "",
        ]);
    }

    public function store() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = trim(strip_tags($_POST["title"] ?? ''));

            // Processar upload da imagem de capa
            $coverImage = $this->processCoverUpload();

            $data = [
                "user_id"     => $_SESSION["user_id"],
                "title"       => $title,
                "description" => trim(strip_tags($_POST["description"] ?? '')),
                "slug"        => $this->slugify($title),
                "status"      => trim($_POST["status"] ?? 'draft'),
                "cover_image" => $coverImage,
                "questions"   => $_POST["questions"] ?? [],
                "title_err"   => "",
            ];

            if (empty($data["title"])) {
                $data["title_err"] = "Por favor, insira o título do formulário.";
                $this->view("admin/forms/create", $data);
                return;
            }

            $formId = $this->formModel->createForm($data);
            if ($formId) {
                foreach ($data["questions"] as $i => $q) {
                    $this->questionModel->addQuestion([
                        "form_id"     => $formId,
                        "label"       => $q["label"],
                        "type"        => $q["type"],
                        "is_required" => isset($q["is_required"]) ? 1 : 0,
                        "order_index" => $i,
                        "config"      => isset($q["config"]) ? json_encode($q["config"]) : null,
                    ]);
                }
                header("Location:" . URLROOT . "/admin/forms"); exit();
            }
            die("Erro ao criar o formulário.");
        }
    }

    public function edit() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        $id   = $this->params["id"] ?? null;
        $form = $id ? $this->formModel->getFormById($id) : null;
        if (!$form) { header("Location:" . URLROOT . "/admin/forms"); exit(); }

        $this->view("admin/forms/edit", [
            "id"           => $form->id,
            "title"        => $form->title,
            "description"  => $form->description,
            "status"       => $form->status,
            "cover_image"  => $form->cover_image ?? null,
            "questions"    => $this->questionModel->getQuestionsByFormId($id),
            "title_err"    => "",
        ]);
    }

    public function update() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        $id = $this->params["id"] ?? null;
        if (!$id) { header("Location:" . URLROOT . "/admin/forms"); exit(); }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = trim(strip_tags($_POST["title"] ?? ''));

            // Obter capa actual do formulário
            $existingForm = $this->formModel->getFormById($id);
            $currentCover = $existingForm->cover_image ?? null;

            // Processar novo upload (se existir), senão manter o actual
            // Se o checkbox "remover capa" estiver marcado, apagar
            if (isset($_POST['remove_cover']) && $_POST['remove_cover'] == '1') {
                $this->deleteCoverFile($currentCover);
                $coverImage = null;
            } else {
                $newCover = $this->processCoverUpload();
                if ($newCover) {
                    // Apagar capa antiga ao substituir
                    $this->deleteCoverFile($currentCover);
                    $coverImage = $newCover;
                } else {
                    $coverImage = $currentCover; // manter a existente
                }
            }

            $data = [
                "id"          => $id,
                "title"       => $title,
                "description" => trim(strip_tags($_POST["description"] ?? '')),
                "slug"        => $this->slugify($title),
                "status"      => trim($_POST["status"] ?? 'draft'),
                "cover_image" => $coverImage,
                "questions"   => $_POST["questions"] ?? [],
                "title_err"   => "",
            ];

            if (empty($data["title"])) {
                $data["title_err"] = "Por favor, insira o título do formulário.";
                $data["questions"] = $this->questionModel->getQuestionsByFormId($id);
                $this->view("admin/forms/edit", $data);
                return;
            }

            if ($this->formModel->updateForm($data)) {
                $this->questionModel->deleteQuestionsByFormId($id);
                foreach ($data["questions"] as $i => $q) {
                    $this->questionModel->addQuestion([
                        "form_id"     => $id,
                        "label"       => $q["label"],
                        "type"        => $q["type"],
                        "is_required" => isset($q["is_required"]) ? 1 : 0,
                        "order_index" => $i,
                        "config"      => isset($q["config"]) ? json_encode($q["config"]) : null,
                    ]);
                }
                header("Location:" . URLROOT . "/admin/forms"); exit();
            }
            die("Erro ao atualizar o formulário.");
        }
    }

    public function delete() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("Location:" . URLROOT . "/login"); exit();
        }
        $id = $this->params["id"] ?? null;
        if (!$id) { header("Location:" . URLROOT . "/admin/forms"); exit(); }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $form      = $this->formModel->getFormById($id);
            $questions = $this->questionModel->getQuestionsByFormId($id);

            // Arquivar na tabela de rascunho antes de eliminar
            $trash = $this->model("Trash");
            $trash->archiveForm($form, $questions, $_SESSION["user_id"]);

            // Manter imagem de capa no disco (dados arquivados, nunca apagar)
            if ($this->formModel->deleteForm($id)) {
                header("Location:" . URLROOT . "/admin/forms"); exit();
            }
            die("Erro ao eliminar o formulário.");
        }
    }

    public function show() {
        $slug = $this->params["slug"] ?? null;
        if (!$slug) { header("Location:" . URLROOT . "/login"); exit(); }

        $form = $this->formModel->getFormBySlug($slug);
        if (!$form || $form->status != "published") {
            http_response_code(404);
            die("Este formulário não está disponível.");
        }

        if (!isset($_SESSION["user_id"])) {
            $redirect = urlencode("forms/" . $slug);
            header("Location:" . URLROOT . "/register?redirect=" . $redirect);
            exit();
        }

        // Verificar se o utilizador já respondeu a este formulário
        $existingResponse = null;
        if ($_SESSION["user_role"] !== "admin") {
            $responseModel    = $this->model("Response");
            $existingResponse = $responseModel->getUserResponseForForm(
                $_SESSION["user_id"], $form->id
            );
        }

        $this->view("public/form_fill", [
            "form"              => $form,
            "questions"         => $this->questionModel->getQuestionsByFormId($form->id),
            "admin_view"        => ($_SESSION["user_role"] == "admin"),
            "existing_response" => $existingResponse,
            "previous_answers"  => $existingResponse
                                   ? $this->model("Answer")->getAnswersByResponseId($existingResponse->id)
                                   : [],
        ]);
    }

    // ── Upload de capa ───────────────────────────────────────────────────
    // ── Upload e redimensionamento da imagem de capa ─────────────────────
    // Dimensão final: 800×400px, crop centrado, guardado como JPEG qualidade 85
    private function processCoverUpload() {
        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] == UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES['cover_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        // Validar MIME real
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowed  = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
        if (!isset($allowed[$mimeType])) return null;

        // Validar tamanho máx. do ficheiro original (5MB)
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $coverDir = APPROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'covers';
        if (!is_dir($coverDir)) mkdir($coverDir, 0750, true);

        // Tentar redimensionar com GD (disponível no XAMPP por defeito)
        if (extension_loaded('gd')) {
            $newFileName = uniqid('cover_') . '.jpg'; // sempre JPG após resize
            $dest        = $coverDir . DIRECTORY_SEPARATOR . $newFileName;
            if ($this->resizeCoverImage($file['tmp_name'], $mimeType, $dest, 800, 400)) {
                return $newFileName;
            }
        }

        // Fallback sem GD: mover ficheiro original sem redimensionar
        $ext         = $allowed[$mimeType];
        $newFileName = uniqid('cover_') . '.' . $ext;
        $dest        = $coverDir . DIRECTORY_SEPARATOR . $newFileName;
        if (move_uploaded_file($file['tmp_name'], $dest)) return $newFileName;

        return null;
    }

    /**
     * Redimensiona para exactamente $targetW x $targetH com crop centrado.
     * Mantém a proporção sem distorção (tipo CSS background-size: cover).
     * Guarda como JPEG qualidade 85 para manter tamanho reduzido.
     */
    private function resizeCoverImage($srcPath, $mimeType, $destPath, $targetW, $targetH) {
        $src = null;
        switch ($mimeType) {
            case 'image/jpeg': $src = @imagecreatefromjpeg($srcPath); break;
            case 'image/png':  $src = @imagecreatefrompng($srcPath);  break;
            case 'image/gif':  $src = @imagecreatefromgif($srcPath);  break;
            case 'image/webp': $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : null; break;
        }
        if (!$src) return false;

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // Calcular crop centrado (cover fit)
        $srcRatio = $srcW / $srcH;
        $tgtRatio = $targetW / $targetH;

        if ($srcRatio > $tgtRatio) {
            // Imagem mais larga — cortar laterais
            $cropH = $srcH;
            $cropW = (int)round($srcH * $tgtRatio);
            $cropX = (int)round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Imagem mais alta — cortar topo/base
            $cropW = $srcW;
            $cropH = (int)round($srcW / $tgtRatio);
            $cropX = 0;
            $cropY = (int)round(($srcH - $cropH) / 2);
        }

        // Canvas de destino com fundo branco
        $canvas = imagecreatetruecolor($targetW, $targetH);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // Redimensionar com qualidade
        imagecopyresampled($canvas, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

        // Guardar JPEG qualidade 85
        $ok = imagejpeg($canvas, $destPath, 85);

        imagedestroy($src);
        imagedestroy($canvas);

        return $ok;
    }

    private function deleteCoverFile($coverImage) {
        if (empty($coverImage)) return;
        $path = APPROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'covers' . DIRECTORY_SEPARATOR . $coverImage;
        if (file_exists($path)) unlink($path);
    }

    private function slugify($text) {
        $text = preg_replace("~[^\\pL\\d]+~u", "-", $text);
        $text = iconv("utf-8", "us-ascii//TRANSLIT", $text);
        $text = preg_replace("~[^\\w]+~", "-", $text);
        $text = trim($text, "-");
        $text = preg_replace("~-+~", "-", $text);
        $text = strtolower($text);
        return empty($text) ? "n-a" : $text;
    }
}
