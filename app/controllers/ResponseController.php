<?php

class ResponseController extends Controller
{
    private Response $responseModel;
    private Answer $answerModel;
    private Form $formModel;
    private Question $questionModel;
    private Audit $auditModel;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->responseModel = $this->model('Response');
        $this->answerModel = $this->model('Answer');
        $this->formModel = $this->model('Form');
        $this->questionModel = $this->model('Question');
        $this->auditModel = $this->model('Audit');
    }

    public function index(): void
    {
        $this->requireAdmin();
        $formId = (int) ($this->params['id'] ?? 0);
        $form = $this->formModel->getFormById($formId);
        if (!$form) {
            flash_set('danger', 'Formulário não encontrado.');
            $this->redirect('admin/forms');
        }
        $this->view('admin/forms/responses', [
            'form' => $form,
            'responses' => $this->responseModel->getResponsesByFormId($formId),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->verifyCsrf();

        $slug = (string) ($this->params['slug'] ?? '');
        $form = $this->formModel->getFormBySlug($slug);
        if (!$form || $form->status !== 'published') {
            flash_set('danger', 'O formulário não está disponível para receber respostas.');
            $this->redirect('home');
        }
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            flash_set('warning', 'Administradores podem visualizar, mas não submeter formulários.');
            $this->redirect('admin/forms');
        }
        if ($this->responseModel->getUserResponseForForm((int) $_SESSION['user_id'], (int) $form->id)) {
            flash_set('warning', 'Já existe uma resposta sua para este formulário.');
            $this->redirect('forms/' . $slug);
        }

        $questions = $this->questionModel->getQuestionsByFormId((int) $form->id);
        $answers = [];
        $uploadedFiles = [];

        try {
            foreach ($questions as $question) {
                [$answer, $uploaded] = $this->normaliseAnswer($question);
                $answers[] = $answer;
                if ($uploaded) {
                    $uploadedFiles[] = $uploaded;
                }
            }

            $this->responseModel->beginTransaction();
            $responseId = $this->responseModel->addResponse([
                'form_id' => (int) $form->id,
                'user_id' => (int) $_SESSION['user_id'],
                'ip_address' => client_ip(),
            ]);
            foreach ($answers as $answer) {
                $answer['response_id'] = $responseId;
                $this->answerModel->addAnswer($answer);
            }
            $this->responseModel->commit();

            $this->auditModel->log('response.create', 'response', $responseId, ['form_id' => (int) $form->id]);
            $this->redirect('forms/' . $slug . '/success');
        } catch (Throwable $e) {
            $this->responseModel->rollBack();
            foreach ($uploadedFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            error_log('[Response store] ' . $e->getMessage());
            $message = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Não foi possível guardar a resposta. Nenhum dado parcial foi registado.';
            flash_set('danger', $message);
            $this->redirect('forms/' . $slug);
        }
    }

    public function deleteOwn(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->verifyCsrf();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->redirect('admin/dashboard');
        }

        $responseId = (int) ($this->params['id'] ?? 0);
        $slug = (string) ($this->params['slug'] ?? '');
        $response = $this->responseModel->getResponseDetail($responseId);
        if (!$response || (int) $response->user_id !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            throw new RuntimeException('Não tem permissão para eliminar esta resposta.', 403);
        }

        $this->responseModel->beginTransaction();
        try {
            $answers = $this->answerModel->getAnswersByResponseId($responseId);
            $this->model('Trash')->archiveResponse($response, $answers, (int) $_SESSION['user_id']);
            $this->responseModel->deleteResponse($responseId);
            $this->responseModel->commit();
            $this->auditModel->log('response.retract', 'response', $responseId);
            flash_set('success', 'A resposta anterior foi arquivada. Já pode preencher novamente.');
        } catch (Throwable $e) {
            $this->responseModel->rollBack();
            error_log('[Response retract] ' . $e->getMessage());
            flash_set('danger', 'Não foi possível remover a resposta.');
        }
        $this->redirect($slug !== '' ? 'forms/' . $slug : 'home');
    }

    public function history(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->redirect('admin/dashboard');
        }
        $this->view('public/history', [
            'responses' => $this->responseModel->getResponsesByUserId((int) $_SESSION['user_id']),
        ]);
    }

    public function detail(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->redirect('admin/dashboard');
        }
        $responseId = (int) ($this->params['response_id'] ?? 0);
        $response = $this->responseModel->getResponseDetail($responseId);
        if (!$response || (int) $response->user_id !== (int) $_SESSION['user_id']) {
            http_response_code(404);
            throw new RuntimeException('Resposta não encontrada.', 404);
        }
        $this->view('public/response_detail', [
            'response' => $response,
            'answers' => $this->answerModel->getAnswersByResponseId($responseId),
        ]);
    }

    public function adminDetail(): void
    {
        $this->requireAdmin();
        $responseId = (int) ($this->params['id'] ?? 0);
        $response = $this->responseModel->getResponseDetail($responseId);
        if (!$response) {
            flash_set('danger', 'Resposta não encontrada.');
            $this->redirect('admin/forms');
        }
        $this->view('admin/forms/response_detail', [
            'response' => $response,
            'answers' => $this->answerModel->getAnswersByResponseId($responseId),
        ]);
    }

    public function exportCsv(): void
    {
        $this->requireAdmin();
        $this->export(false);
    }

    public function exportZip(): void
    {
        $this->requireAdmin();
        $this->export(true);
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $responseId = (int) ($this->params['id'] ?? 0);
        $response = $this->responseModel->getResponseDetail($responseId);
        if (!$response) {
            flash_set('danger', 'Resposta não encontrada.');
            $this->redirect('admin/forms');
        }

        $this->responseModel->beginTransaction();
        try {
            $answers = $this->answerModel->getAnswersByResponseId($responseId);
            $this->model('Trash')->archiveResponse($response, $answers, (int) $_SESSION['user_id']);
            $this->responseModel->deleteResponse($responseId);
            $this->responseModel->commit();
            $this->auditModel->log('response.delete', 'response', $responseId);
            flash_set('success', 'Resposta arquivada e removida da lista ativa.');
        } catch (Throwable $e) {
            $this->responseModel->rollBack();
            error_log('[Response delete] ' . $e->getMessage());
            flash_set('danger', 'Não foi possível eliminar a resposta.');
        }
        $this->redirect('admin/forms/' . (int) $response->form_id . '/responses');
    }

    private function normaliseAnswer(object $question): array
    {
        $key = 'question_' . $question->id;
        $config = json_decode($question->config ?: '{}', true) ?: [];
        $base = [
            'question_id' => (int) $question->id,
            'question_label' => $question->label,
            'question_type' => $question->type,
            'value' => null,
            'file_path' => null,
            'original_file_name' => null,
            'file_mime' => null,
            'file_size' => null,
        ];

        if ($question->type === 'upload') {
            $file = $_FILES[$key] ?? null;
            if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
                if ((int) $question->is_required === 1) {
                    throw new InvalidArgumentException('O campo “' . $question->label . '” é obrigatório.');
                }
                return [$base, null];
            }
            if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                throw new InvalidArgumentException('O upload de “' . $question->label . '” falhou.');
            }
            if ((int) $file['size'] > MAX_UPLOAD_SIZE) {
                throw new InvalidArgumentException('O ficheiro de “' . $question->label . '” excede 5 MB.');
            }

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $mimeMap = [
                'application/pdf' => 'pdf',
                'image/png' => 'png',
                'image/jpeg' => 'jpeg',
            ];
            $allowed = is_array($config['allowed_types'] ?? null) ? $config['allowed_types'] : ['pdf'];
            $extension = $mimeMap[$mime] ?? null;
            if ($extension === null || !in_array($extension, $allowed, true)) {
                throw new InvalidArgumentException('O tipo de ficheiro enviado em “' . $question->label . '” não é permitido.');
            }
            if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0750, true) && !is_dir(UPLOAD_DIR)) {
                throw new RuntimeException('Não foi possível criar a pasta de uploads.');
            }
            $storedName = bin2hex(random_bytes(20)) . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
            $storedPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedName;
            if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
                throw new RuntimeException('Não foi possível guardar o ficheiro enviado.');
            }
            $base['file_path'] = $storedName;
            $base['original_file_name'] = text_substr(basename((string) $file['name']), 0, 255);
            $base['file_mime'] = $mime;
            $base['file_size'] = (int) $file['size'];
            return [$base, $storedPath];
        }

        $raw = $_POST[$key] ?? null;
        if ($question->type === 'checkbox') {
            $values = is_array($raw) ? array_map('strval', $raw) : [];
            $allowed = is_array($config['options'] ?? null) ? $config['options'] : [];
            $values = array_values(array_unique(array_intersect($values, $allowed)));
            if ((int) $question->is_required === 1 && $values === []) {
                throw new InvalidArgumentException('Selecione pelo menos uma opção em “' . $question->label . '”.');
            }
            $base['value'] = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            return [$base, null];
        }

        $value = is_scalar($raw) ? trim((string) $raw) : '';
        if ((int) $question->is_required === 1 && $value === '') {
            throw new InvalidArgumentException('O campo “' . $question->label . '” é obrigatório.');
        }

        switch ($question->type) {
            case 'short_text':
                if (text_length($value) > 500) {
                    throw new InvalidArgumentException('A resposta de “' . $question->label . '” excede 500 caracteres.');
                }
                break;
            case 'long_text':
                if (text_length($value) > 10000) {
                    throw new InvalidArgumentException('A resposta de “' . $question->label . '” é demasiado longa.');
                }
                break;
            case 'numeric':
                if ($value !== '' && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                    throw new InvalidArgumentException('Introduza um número válido em “' . $question->label . '”.');
                }
                break;
            case 'date':
                if ($value !== '') {
                    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        throw new InvalidArgumentException('Introduza uma data válida em “' . $question->label . '”.');
                    }
                    if (!empty($config['date_min']) && $value < $config['date_min']) {
                        throw new InvalidArgumentException('A data de “' . $question->label . '” é anterior ao limite permitido.');
                    }
                    if (!empty($config['date_max']) && $value > $config['date_max']) {
                        throw new InvalidArgumentException('A data de “' . $question->label . '” é posterior ao limite permitido.');
                    }
                }
                break;
            case 'radio':
                $allowed = is_array($config['options'] ?? null) ? $config['options'] : [];
                if ($value !== '' && !in_array($value, $allowed, true)) {
                    throw new InvalidArgumentException('A opção enviada em “' . $question->label . '” não é válida.');
                }
                break;
        }

        $base['value'] = $value;
        return [$base, null];
    }

    private function export(bool $zipRequested): void
    {
        $formId = (int) ($this->params['id'] ?? 0);
        $form = $this->formModel->getFormById($formId);
        if (!$form) {
            throw new RuntimeException('Formulário não encontrado.', 404);
        }
        $responses = $this->responseModel->getResponsesByFormId($formId);
        if ($responses === []) {
            flash_set('warning', 'Este formulário ainda não possui respostas para exportar.');
            $this->redirect('admin/forms/' . $formId . '/responses');
        }

        $allAnswers = $this->answerModel->getAnswersByResponseIds(array_map(static fn($r) => (int) $r->id, $responses));
        $answersByResponse = [];
        foreach ($allAnswers as $answer) {
            $answersByResponse[(int) $answer->response_id][] = $answer;
        }

        $columns = [];
        foreach ($this->questionModel->getQuestionsByFormId($formId) as $question) {
            $columns['id:' . $question->id] = $question->label;
        }
        foreach ($allAnswers as $answer) {
            $key = $answer->question_id ? 'id:' . $answer->question_id : 'label:' . $answer->question_label;
            $columns[$key] ??= $answer->question_label;
        }

        $csv = $this->buildCsv($responses, $answersByResponse, $columns);
        $safeSlug = preg_replace('/[^a-z0-9_-]/i', '_', $form->slug);

        if (!$zipRequested || !class_exists('ZipArchive')) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="respostas_' . $safeSlug . '.csv"');
            header('X-Content-Type-Options: nosniff');
            echo $csv;
            exit;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'dynamic_forms_');
        if ($tmp === false) {
            throw new RuntimeException('Não foi possível criar o ficheiro temporário.');
        }
        $zipPath = $tmp . '.zip';
        @unlink($tmp);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível gerar o ZIP.');
        }
        $zip->addFromString('respostas.csv', $csv);

        $addedFiles = [];
        foreach ($allAnswers as $answer) {
            if (!$answer->file_path || isset($addedFiles[$answer->file_path])) {
                continue;
            }
            $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . basename($answer->file_path);
            if (is_file($path)) {
                $name = $answer->original_file_name ?: $answer->file_path;
                $zipName = 'anexos/' . $answer->response_id . '_' . preg_replace('/[^\pL\pN_.-]+/u', '_', basename($name));
                $zip->addFile($path, $zipName);
                $addedFiles[$answer->file_path] = true;
            }
        }
        $zip->close();

        $downloadName = 'respostas_' . $safeSlug . '_' . date('Ymd_His') . '.zip';
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('X-Content-Type-Options: nosniff');
        readfile($zipPath);
        @unlink($zipPath);
        exit;
    }

    private function buildCsv(array $responses, array $answersByResponse, array $columns): string
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, array_merge(['ID', 'Respondente', 'Email', 'Data de submissão'], array_values($columns)), ';');

        foreach ($responses as $response) {
            $answerMap = [];
            foreach ($answersByResponse[(int) $response->id] ?? [] as $answer) {
                $key = $answer->question_id ? 'id:' . $answer->question_id : 'label:' . $answer->question_label;
                if ($answer->question_type === 'checkbox') {
                    $value = implode(' | ', json_decode($answer->value ?: '[]', true) ?: []);
                } elseif ($answer->question_type === 'upload') {
                    $value = $answer->original_file_name ?: $answer->file_path;
                } else {
                    $value = $answer->value ?? '';
                }
                $answerMap[$key] = csv_safe($value);
            }

            $row = [
                $response->id,
                csv_safe($response->user_name ?? ''),
                csv_safe($response->user_email ?? ''),
                $response->submitted_at,
            ];
            foreach (array_keys($columns) as $key) {
                $row[] = $answerMap[$key] ?? '';
            }
            fputcsv($stream, $row, ';');
        }
        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);
        return $content;
    }
}
