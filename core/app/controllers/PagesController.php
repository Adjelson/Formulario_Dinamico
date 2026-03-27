<?php

class PagesController extends Controller {

    public function __construct($params = []) {
        parent::__construct($params);
    }

    // Página inicial — lista formulários publicados com badge "já respondido"
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('location:' . URLROOT . '/login'); exit();
        }
        if ($_SESSION['user_role'] == 'admin') {
            header('location:' . URLROOT . '/admin/dashboard'); exit();
        }

        $formModel     = $this->model('Form');
        $responseModel = $this->model('Response');
        $forms         = $formModel->getPublishedForms();

        // Marcar quais formulários o utilizador já respondeu
        $answeredFormIds = [];
        foreach ($forms as $form) {
            $existing = $responseModel->getUserResponseForForm($_SESSION['user_id'], $form->id);
            if ($existing) {
                $answeredFormIds[$form->id] = $existing->id; // form_id => response_id
            }
        }

        $this->view('public/forms_list', [
            'forms'           => $forms,
            'answeredFormIds' => $answeredFormIds,
        ]);
    }

    // Página de sucesso após submissão de formulário
    public function formSuccess() {
        $slug      = $this->params['slug'] ?? '';
        $formModel = $this->model('Form');
        $form      = $slug ? $formModel->getFormBySlug($slug) : null;
        $this->view('public/form_success', ['form' => $form, 'slug' => $slug]);
    }
}
