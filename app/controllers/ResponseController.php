<?php

class ResponseController extends Controller {

    protected $responseModel;
    protected $answerModel;
    protected $formModel;
    protected $questionModel;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->responseModel = $this->model("Response");
        $this->answerModel   = $this->model("Answer");
        $this->formModel     = $this->model("Form");
        $this->questionModel = $this->model("Question");
    }

    public function index() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("location:" . URLROOT . "/login"); exit();
        }
        $form_id   = $this->params["id"] ?? null;
        $form      = $this->formModel->getFormById($form_id);
        $responses = $this->responseModel->getResponsesByFormId($form_id);
        $this->view("admin/forms/responses", ["form" => $form, "responses" => $responses]);
    }

    public function store() {
        $slug = $this->params["slug"] ?? null;

        if (!isset($_SESSION["user_id"])) {
            $redirect = urlencode("forms/" . $slug);
            header("location:" . URLROOT . "/register?redirect=" . $redirect); exit();
        }

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("location:" . URLROOT . "/forms/" . $slug); exit();
        }

        $form = $this->formModel->getFormBySlug($slug);
        if (!$form || $form->status != "published") {
            die("Formulário não disponível.");
        }

        if ($_SESSION["user_role"] == "admin") {
            header("location:" . URLROOT . "/admin/forms"); exit();
        }

        $responseId = $this->responseModel->addResponse([
            "form_id"    => $form->id,
            "user_id"    => $_SESSION["user_id"],
            "ip_address" => $_SERVER["REMOTE_ADDR"],
        ]);
        if (!$responseId) { die("Erro ao guardar a resposta."); }

        foreach ($this->questionModel->getQuestionsByFormId($form->id) as $question) {
            $answerValue = null;
            $filePath    = null;

            if ($question->type == "upload") {
                $fileKey = "question_" . $question->id;
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]["error"] == UPLOAD_ERR_OK) {
                    $file     = $_FILES[$fileKey];
                    $fileExt  = strtolower(pathinfo(basename($file["name"]), PATHINFO_EXTENSION));
                    $fileMime = mime_content_type($file["tmp_name"]);
                    $cfg      = json_decode($question->config ?? '{}');
                    $allowed  = $cfg->allowed_types ?? [];
                    $mimeMap  = ["pdf" => "application/pdf", "png" => "image/png", "jpeg" => "image/jpeg"];
                    $allowedMimes = array_map(fn($t) => $mimeMap[$t] ?? '', $allowed);
                    if (!in_array($fileMime, $allowedMimes)) {
                        die("Tipo de ficheiro não permitido: " . htmlspecialchars($question->label));
                    }
                    if ($file["size"] > MAX_UPLOAD_SIZE) {
                        die("Ficheiro muito grande: " . htmlspecialchars($question->label));
                    }
                    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0750, true);
                    $newFileName = uniqid() . "." . $fileExt;
                    if (move_uploaded_file($file["tmp_name"], UPLOAD_DIR . DIRECTORY_SEPARATOR . $newFileName)) {
                        $filePath = $newFileName;
                    } else {
                        die("Erro no upload: " . htmlspecialchars($question->label));
                    }
                }
            } elseif ($question->type == "checkbox") {
                if (isset($_POST["question_" . $question->id])) {
                    $answerValue = json_encode($_POST["question_" . $question->id]);
                }
            } elseif ($question->type == "date") {
                if (isset($_POST["question_" . $question->id])) {
                    $raw = trim($_POST["question_" . $question->id]);
                    // Validar formato YYYY-MM-DD
                    $answerValue = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) ? $raw : null;
                }
            } else {
                if (isset($_POST["question_" . $question->id])) {
                    $answerValue = strip_tags(trim($_POST["question_" . $question->id]));
                }
            }

            if ($question->is_required && empty($answerValue) && empty($filePath)) {
                die("A pergunta '" . htmlspecialchars($question->label) . "' é obrigatória.");
            }

            $this->answerModel->addAnswer([
                "response_id"    => $responseId,
                "question_id"    => $question->id,
                "question_label" => $question->label,
                "question_type"  => $question->type,
                "value"          => $answerValue,
                "file_path"      => $filePath,
            ]);
        }
        header("location:" . URLROOT . "/forms/" . $slug . "/success"); exit();
    }

    public function history() {
        if (!isset($_SESSION["user_id"])) {
            header("location:" . URLROOT . "/login"); exit();
        }
        if ($_SESSION["user_role"] == "admin") {
            header("location:" . URLROOT . "/admin/dashboard"); exit();
        }
        $this->view("public/history", [
            "responses" => $this->responseModel->getResponsesByUserId($_SESSION["user_id"]),
        ]);
    }

    public function detail() {
        if (!isset($_SESSION["user_id"])) {
            header("location:" . URLROOT . "/login"); exit();
        }
        if ($_SESSION["user_role"] == "admin") {
            header("location:" . URLROOT . "/admin/dashboard"); exit();
        }
        $response_id = $this->params["response_id"] ?? null;
        $response    = $this->responseModel->getResponseDetail($response_id);
        $answers     = $this->answerModel->getAnswersByResponseId($response_id);
        if (!$response || $response->user_id != $_SESSION["user_id"]) {
            die("Acesso negado ou resposta não encontrada.");
        }
        $this->view("public/response_detail", ["response" => $response, "answers" => $answers]);
    }

    public function adminDetail() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("location:" . URLROOT . "/login"); exit();
        }
        $response_id = $this->params["id"] ?? null;
        $response    = $this->responseModel->getResponseDetail($response_id);
        $answers     = $this->answerModel->getAnswersByResponseId($response_id);
        if (!$response) { die("Resposta não encontrada."); }
        $this->view("admin/forms/response_detail", ["response" => $response, "answers" => $answers]);
    }

    public function exportCsv() {
        // Redirecionar para exportZip — mantido por compatibilidade de URL
        $this->exportZip();
    }

    public function exportZip() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("location:" . URLROOT . "/login"); exit();
        }
        $form_id   = $this->params["id"] ?? null;
        $form      = $this->formModel->getFormById($form_id);
        if (!$form) die("Formulário não encontrado.");
        $questions = $this->questionModel->getQuestionsByFormId($form_id);
        $responses = $this->responseModel->getResponsesByFormId($form_id);

        if (empty($responses)) {
            header("location:" . URLROOT . "/admin/forms/" . $form_id . "/responses?msg=no_data");
            exit();
        }

        // Verificar extensão ZipArchive
        if (!class_exists('ZipArchive')) {
            // Fallback: CSV se ZipArchive não disponível
            $this->exportCsvFallback($form, $questions, $responses);
            return;
        }

        $tmpDir  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'df_export_' . uniqid();
        mkdir($tmpDir, 0750, true);

        $zipName = 'respostas_' . $form->slug . '_' . date('Ymd_His') . '.zip';
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            die("Erro ao criar o arquivo ZIP.");
        }

        // 1. Ficheiro CSV geral dentro do ZIP
        $csvContent = chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM UTF-8
        $header = ["ID", "Respondente", "Email", "Data Submissão", "IP"];
        foreach ($questions as $q) $header[] = $q->label;
        $csvContent .= $this->arrayToCsvLine($header);

        foreach ($responses as $response) {
            $userName = $response->user_name ?? 'Anonimo';
            $row = [
                $response->id,
                $userName,
                $response->user_email ?? '',
                $response->submitted_at,
                $response->ip_address,
            ];
            $answerMap = [];
            foreach ($this->answerModel->getAnswersByResponseId($response->id) as $a) {
                $answerMap[$a->question_id] = $a;
            }
            foreach ($questions as $q) {
                if (isset($answerMap[$q->id])) {
                    $a = $answerMap[$q->id];
                    if ($q->type == "upload") {
                        $row[] = !empty($a->file_path) ? 'anexo/' . $a->file_path : '';
                    } elseif ($q->type == "checkbox") {
                        $row[] = implode(" | ", json_decode($a->value ?? '[]', true) ?? []);
                    } else {
                        $row[] = $a->value ?? '';
                    }
                } else {
                    $row[] = '';
                }
            }
            $csvContent .= $this->arrayToCsvLine($row);
        }
        $zip->addFromString('respostas.csv', $csvContent);

        // 2. Ficheiro individual por respondente (TXT com nome do respondente)
        foreach ($responses as $response) {
            $userName  = $response->user_name ?? 'Anonimo';
            $safeUser  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $userName);
            $fileName  = $safeUser . '_' . $response->id . '.txt';

            $txt  = "=== RESPOSTA AO FORMULÁRIO: " . $form->title . " ===\n";
            $txt .= "Respondente : " . ($response->user_name ?? 'Anónimo') . "\n";
            $txt .= "Email       : " . ($response->user_email ?? 'N/A') . "\n";
            $txt .= "Data        : " . $response->submitted_at . "\n";
            $txt .= "IP          : " . $response->ip_address . "\n";
            $txt .= str_repeat('-', 60) . "\n\n";

            $answerMap = [];
            foreach ($this->answerModel->getAnswersByResponseId($response->id) as $a) {
                $answerMap[$a->question_id] = $a;
            }
            foreach ($questions as $idx => $q) {
                $txt .= ($idx + 1) . ". " . $q->label . "\n";
                if (isset($answerMap[$q->id])) {
                    $a = $answerMap[$q->id];
                    if ($q->type == "upload") {
                        $txt .= "   [Ficheiro: " . ($a->file_path ?? 'nenhum') . "]\n";
                        // Incluir ficheiro no ZIP se existir
                        if (!empty($a->file_path)) {
                            $filePath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $a->file_path;
                            if (file_exists($filePath)) {
                                $zip->addFile($filePath, 'anexos/' . $a->file_path);
                            }
                        }
                    } elseif ($q->type == "checkbox") {
                        $vals = json_decode($a->value ?? '[]', true) ?? [];
                        $txt .= "   " . implode(", ", $vals) . "\n";
                    } else {
                        $txt .= "   " . ($a->value ?? '(sem resposta)') . "\n";
                    }
                } else {
                    $txt .= "   (sem resposta)\n";
                }
                $txt .= "\n";
            }
            $zip->addFromString('respondentes/' . $fileName, $txt);
        }

        $zip->close();

        // Enviar ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: no-cache');
        if (ob_get_level()) ob_end_clean();
        readfile($zipPath);

        // Limpar ficheiro temporário
        @unlink($zipPath);
        @rmdir($tmpDir);
        exit();
    }

    private function arrayToCsvLine($row) {
        $escaped = array_map(function($v) {
            $v = str_replace('"', '""', (string)$v);
            return '"' . $v . '"';
        }, $row);
        return implode(';', $escaped) . "\n";
    }

    private function exportCsvFallback($form, $questions, $responses) {
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"respostas_" . $form->slug . ".csv\"");
        $output = fopen("php://output", "w");
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $header = ["ID", "Respondente", "Email", "Data Submissão"];
        foreach ($questions as $q) $header[] = $q->label;
        fputcsv($output, $header, ';');
        foreach ($responses as $response) {
            $row = [$response->id, $response->user_name ?? '', $response->user_email ?? '', $response->submitted_at];
            $answerMap = [];
            foreach ($this->answerModel->getAnswersByResponseId($response->id) as $a) $answerMap[$a->question_id] = $a;
            foreach ($questions as $q) {
                if (isset($answerMap[$q->id])) {
                    $a = $answerMap[$q->id];
                    $row[] = $q->type == "checkbox" ? implode(" | ", json_decode($a->value ?? '[]', true) ?? []) : ($a->value ?? '');
                } else $row[] = '';
            }
            fputcsv($output, $row, ';');
        }
        fclose($output); exit();
    }

    public function delete() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
            header("location:" . URLROOT . "/login"); exit();
        }
        $response_id = $this->params["id"] ?? null;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $response = $this->responseModel->getResponseDetail($response_id);
            if (!$response) die("Resposta não encontrada.");

            $answers = $this->answerModel->getAnswersByResponseId($response_id);

            // Arquivar na tabela de rascunho antes de eliminar
            $trash = $this->model("Trash");
            $trash->archiveResponse($response, $answers, $_SESSION["user_id"]);

            // Manter ficheiros de upload (dados arquivados)
            $this->answerModel->deleteAnswersByResponseId($response_id);
            if ($this->responseModel->deleteResponse($response_id)) {
                header("location:" . URLROOT . "/admin/forms/" . $response->form_id . "/responses"); exit();
            }
            die("Erro ao eliminar a resposta.");
        }
    }
}
