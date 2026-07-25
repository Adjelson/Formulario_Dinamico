<?php

class PagesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->redirect('admin/dashboard');
        }

        $formModel = $this->model('Form');
        $responseModel = $this->model('Response');
        $this->view('public/forms_list', [
            'forms' => $formModel->getPublishedForms(),
            'answeredFormIds' => $responseModel->getAnsweredFormIds((int) $_SESSION['user_id']),
        ]);
    }

    public function formSuccess(): void
    {
        $this->requireAuth();
        $slug = (string) ($this->params['slug'] ?? '');
        $form = $slug !== '' ? $this->model('Form')->getFormBySlug($slug) : false;
        $this->view('public/form_success', ['form' => $form, 'slug' => $slug]);
    }
}
