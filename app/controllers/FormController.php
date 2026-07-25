<?php

class FormController extends Controller
{
    private Form $formModel;
    private Question $questionModel;
    private User $userModel;
    private Audit $auditModel;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->formModel = $this->model('Form');
        $this->questionModel = $this->model('Question');
        $this->userModel = $this->model('User');
        $this->auditModel = $this->model('Audit');
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $this->view('admin/dashboard', [
            'totalForms' => $this->formModel->getTotalForms(),
            'totalResponses' => $this->model('Response')->getTotalResponses(),
            'activeUsers' => $this->userModel->countActiveUsers(),
            'recentForms' => $this->formModel->getRecentForms(5),
        ]);
    }

    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin/forms/index', ['forms' => $this->formModel->getForms()]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->view('admin/forms/create', [
            'title' => '', 'description' => '', 'status' => 'draft',
            'cover_image' => null, 'questions' => [], 'title_err' => '', 'general_err' => '',
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $data = $this->formInput();
        [$questions, $questionError] = $this->normaliseQuestions($_POST['questions'] ?? []);
        $data['questions'] = $questions;

        if ($data['title_err'] !== '' || $questionError !== '') {
            $data['general_err'] = $questionError;
            $this->view('admin/forms/create', $data);
            return;
        }

        $newCover = null;
        try {
            $newCover = $this->processCoverUpload();
            $data['cover_image'] = $newCover;
            $data['slug'] = $this->formModel->uniqueSlug($this->slugify($data['title']));

            $this->formModel->beginTransaction();
            $formId = $this->formModel->createForm($data + ['user_id' => (int) $_SESSION['user_id']]);
            $this->questionModel->syncQuestions($formId, $questions);
            $this->formModel->commit();

            $this->auditModel->log('form.create', 'form', $formId, ['status' => $data['status']]);
            flash_set('success', 'Formulário criado com sucesso.');
            $this->redirect('admin/forms');
        } catch (Throwable $e) {
            $this->formModel->rollBack();
            if ($newCover) {
                $this->deleteCoverFile($newCover);
            }
            error_log('[Form store] ' . $e->getMessage());
            $data['general_err'] = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Não foi possível criar o formulário. Verifique os dados e tente novamente.';
            $this->view('admin/forms/create', $data);
        }
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) ($this->params['id'] ?? 0);
        $form = $this->formModel->getFormById($id);
        if (!$form) {
            flash_set('danger', 'Formulário não encontrado.');
            $this->redirect('admin/forms');
        }

        $this->view('admin/forms/edit', [
            'id' => (int) $form->id,
            'title' => $form->title,
            'description' => $form->description,
            'status' => $form->status,
            'cover_image' => $form->cover_image,
            'questions' => $this->questionModel->getQuestionsByFormId($id),
            'title_err' => '', 'general_err' => '',
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($this->params['id'] ?? 0);
        $existingForm = $this->formModel->getFormById($id);
        if (!$existingForm) {
            flash_set('danger', 'Formulário não encontrado.');
            $this->redirect('admin/forms');
        }

        $data = $this->formInput();
        $data['id'] = $id;
        [$questions, $questionError] = $this->normaliseQuestions($_POST['questions'] ?? []);
        $data['questions'] = $questions;
        $data['cover_image'] = $existingForm->cover_image;

        if ($data['title_err'] !== '' || $questionError !== '') {
            $data['general_err'] = $questionError;
            $data['cover_image'] = $existingForm->cover_image;
            $this->view('admin/forms/edit', $data);
            return;
        }

        $uploadedCover = null;
        $deleteOldAfterCommit = false;
        try {
            $removeCover = ($_POST['remove_cover'] ?? '') === '1';
            $uploadedCover = $this->processCoverUpload();
            if ($uploadedCover !== null) {
                $data['cover_image'] = $uploadedCover;
                $deleteOldAfterCommit = !empty($existingForm->cover_image);
            } elseif ($removeCover) {
                $data['cover_image'] = null;
                $deleteOldAfterCommit = !empty($existingForm->cover_image);
            }

            $data['slug'] = $this->formModel->uniqueSlug($this->slugify($data['title']), $id);

            $existingQuestions = $this->questionModel->getQuestionsByFormId($id);
            $submittedIds = array_values(array_filter(array_map(
                static fn(array $q): int => (int) ($q['id'] ?? 0),
                $questions
            )));

            $this->formModel->beginTransaction();
            $trash = $this->model('Trash');
            foreach ($existingQuestions as $oldQuestion) {
                if (!in_array((int) $oldQuestion->id, $submittedIds, true)) {
                    $trash->archiveQuestion($oldQuestion, (int) $_SESSION['user_id']);
                }
            }

            $this->formModel->updateForm($data);
            $this->questionModel->syncQuestions($id, $questions);
            $this->formModel->commit();

            if ($deleteOldAfterCommit && !empty($existingForm->cover_image)) {
                $this->deleteCoverFile($existingForm->cover_image);
            }
            $this->auditModel->log('form.update', 'form', $id, ['status' => $data['status']]);
            flash_set('success', 'Formulário atualizado com sucesso.');
            $this->redirect('admin/forms');
        } catch (Throwable $e) {
            $this->formModel->rollBack();
            if ($uploadedCover) {
                $this->deleteCoverFile($uploadedCover);
            }
            error_log('[Form update] ' . $e->getMessage());
            $data['general_err'] = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Não foi possível atualizar o formulário.';
            $data['cover_image'] = $existingForm->cover_image;
            $this->view('admin/forms/edit', $data);
        }
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($this->params['id'] ?? 0);
        $form = $this->formModel->getFormById($id);
        if (!$form) {
            flash_set('danger', 'Formulário não encontrado.');
            $this->redirect('admin/forms');
        }

        $this->formModel->beginTransaction();
        try {
            $questions = $this->questionModel->getQuestionsByFormId($id);
            $responses = $this->model('Response')->getResponsesByFormId($id);
            $answerModel = $this->model('Answer');
            $trash = $this->model('Trash');

            $trash->archiveForm($form, $questions, (int) $_SESSION['user_id']);
            foreach ($questions as $question) {
                $trash->archiveQuestion($question, (int) $_SESSION['user_id']);
            }
            foreach ($responses as $response) {
                $trash->archiveResponse(
                    $response,
                    $answerModel->getAnswersByResponseId((int) $response->id),
                    (int) $_SESSION['user_id']
                );
            }

            $this->formModel->deleteForm($id);
            $this->formModel->commit();
            $this->auditModel->log('form.delete', 'form', $id, ['responses_archived' => count($responses)]);
            flash_set('success', 'Formulário e respetivas respostas foram arquivados antes da remoção.');
        } catch (Throwable $e) {
            $this->formModel->rollBack();
            error_log('[Form delete] ' . $e->getMessage());
            flash_set('danger', 'Não foi possível eliminar o formulário. Nenhuma alteração parcial foi guardada.');
        }
        $this->redirect('admin/forms');
    }

    public function show(): void
    {
        $slug = (string) ($this->params['slug'] ?? '');
        $form = $slug !== '' ? $this->formModel->getFormBySlug($slug) : false;
        if (!$form || $form->status !== 'published') {
            http_response_code(404);
            throw new RuntimeException('Este formulário não está disponível.', 404);
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/register?redirect=' . urlencode('forms/' . $slug));
            exit;
        }

        $existingResponse = false;
        $previousAnswers = [];
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $responseModel = $this->model('Response');
            $existingResponse = $responseModel->getUserResponseForForm((int) $_SESSION['user_id'], (int) $form->id);
            if ($existingResponse) {
                $previousAnswers = $this->model('Answer')->getAnswersByResponseId((int) $existingResponse->id);
            }
        }

        $this->view('public/form_fill', [
            'form' => $form,
            'questions' => $this->questionModel->getQuestionsByFormId((int) $form->id),
            'admin_view' => ($_SESSION['user_role'] ?? '') === 'admin',
            'existing_response' => $existingResponse,
            'previous_answers' => $previousAnswers,
        ]);
    }

    private function formInput(): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'closed'], true)
            ? $_POST['status']
            : 'draft';

        return [
            'title' => text_substr($title, 0, 255),
            'description' => text_substr($description, 0, 10000),
            'status' => $status,
            'slug' => '', 'cover_image' => null,
            'title_err' => $title === '' ? 'Introduza o título do formulário.' : '',
            'general_err' => '',
        ];
    }

    private function normaliseQuestions(mixed $rawQuestions): array
    {
        if (!is_array($rawQuestions) || $rawQuestions === []) {
            return [[], 'Adicione pelo menos uma pergunta ao formulário.'];
        }
        if (count($rawQuestions) > MAX_QUESTIONS_PER_FORM) {
            return [[], 'O formulário excede o limite de ' . MAX_QUESTIONS_PER_FORM . ' perguntas.'];
        }

        $allowedTypes = ['short_text', 'long_text', 'numeric', 'date', 'checkbox', 'radio', 'upload'];
        $result = [];
        foreach (array_values($rawQuestions) as $index => $raw) {
            if (!is_array($raw)) {
                return [[], 'Existe uma pergunta com estrutura inválida.'];
            }
            $label = trim((string) ($raw['label'] ?? ''));
            $type = (string) ($raw['type'] ?? 'short_text');
            if ($label === '' || text_length($label) > 500) {
                return [[], 'A pergunta ' . ($index + 1) . ' deve ter um texto válido com até 500 caracteres.'];
            }
            if (!in_array($type, $allowedTypes, true)) {
                return [[], 'O tipo da pergunta ' . ($index + 1) . ' é inválido.'];
            }

            $config = is_array($raw['config'] ?? null) ? $raw['config'] : [];
            $cleanConfig = [];
            if (in_array($type, ['checkbox', 'radio'], true)) {
                $options = array_values(array_unique(array_filter(array_map(
                    static fn($v): string => text_substr(trim((string) $v), 0, 200),
                    is_array($config['options'] ?? null) ? $config['options'] : []
                ), static fn(string $v): bool => $v !== '')));
                if (count($options) < 2) {
                    return [[], 'A pergunta “' . $label . '” precisa de pelo menos duas opções.'];
                }
                $cleanConfig['options'] = array_slice($options, 0, 50);
            } elseif ($type === 'upload') {
                $allowed = array_values(array_intersect(
                    is_array($config['allowed_types'] ?? null) ? $config['allowed_types'] : [],
                    ['pdf', 'png', 'jpeg']
                ));
                $cleanConfig['allowed_types'] = $allowed !== [] ? $allowed : ['pdf'];
            } elseif ($type === 'date') {
                foreach (['date_min', 'date_max'] as $key) {
                    $value = trim((string) ($config[$key] ?? ''));
                    if ($value !== '' && !$this->validDate($value)) {
                        return [[], 'O intervalo da pergunta “' . $label . '” contém uma data inválida.'];
                    }
                    if ($value !== '') {
                        $cleanConfig[$key] = $value;
                    }
                }
                if (isset($cleanConfig['date_min'], $cleanConfig['date_max']) && $cleanConfig['date_min'] > $cleanConfig['date_max']) {
                    return [[], 'Na pergunta “' . $label . '”, a data mínima não pode ser posterior à data máxima.'];
                }
            }

            $result[] = [
                'id' => isset($raw['id']) ? (int) $raw['id'] : 0,
                'label' => $label,
                'type' => $type,
                'is_required' => isset($raw['is_required']) ? 1 : 0,
                'config' => json_encode($cleanConfig, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ];
        }
        return [$result, ''];
    }

    private function processCoverUpload(): ?string
    {
        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES['cover_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('O upload da imagem de capa falhou.');
        }
        if ((int) $file['size'] > MAX_COVER_SIZE) {
            throw new InvalidArgumentException('A imagem de capa não pode exceder 2 MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            throw new InvalidArgumentException('A capa deve ser JPG, PNG ou WEBP.');
        }
        if (!is_dir(COVER_DIR) && !mkdir(COVER_DIR, 0750, true) && !is_dir(COVER_DIR)) {
            throw new RuntimeException('Não foi possível criar a pasta de capas.');
        }

        $name = 'cover_' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], COVER_DIR . DIRECTORY_SEPARATOR . $name)) {
            throw new RuntimeException('Não foi possível guardar a imagem de capa.');
        }
        return $name;
    }

    private function deleteCoverFile(?string $cover): void
    {
        if (!$cover || !preg_match('/^[a-zA-Z0-9_.-]+$/', $cover)) {
            return;
        }
        $path = COVER_DIR . DIRECTORY_SEPARATOR . basename($cover);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? '';
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = $converted !== false ? $converted : $text;
        $text = preg_replace('~[^\w-]+~', '', $text) ?? '';
        $text = strtolower(trim(preg_replace('~-+~', '-', $text) ?? '', '-'));
        return $text !== '' ? $text : 'formulario';
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
