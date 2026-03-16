<?php

class PagesController extends Controller {
    public function __construct($params = []) {
        parent::__construct($params);
    }

    // Página inicial para utilizador normal — lista formulários publicados
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('location:' . URLROOT . '/login'); exit();
        }
        if ($_SESSION['user_role'] == 'admin') {
            header('location:' . URLROOT . '/admin/dashboard'); exit();
        }
        $formModel = $this->model('Form');
        $forms = $formModel->getPublishedForms();
        $this->view('public/forms_list', ['forms' => $forms]);
    }

    // Página de sucesso após submissão de formulário
    public function formSuccess() {
        $slug = $this->params['slug'] ?? '';
        $formModel = $this->model('Form');
        $form = $slug ? $formModel->getFormBySlug($slug) : null;
        $this->view('public/form_success', ['form' => $form, 'slug' => $slug]);
    }
}
