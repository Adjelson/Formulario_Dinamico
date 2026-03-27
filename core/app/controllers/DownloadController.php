<?php

/**
 * DownloadController
 *
 * Serve ficheiros guardados em storage/uploads/ (fora de public/).
 * Apenas utilizadores autenticados ou admins podem descarregar ficheiros.
 * O nome real do ficheiro em disco NUNCA é exposto — usa-se o nome gerado
 * por uniqid() que não contém informação sensível.
 */
class DownloadController extends Controller {

    public function serve() {
        // --- 1. Autenticação obrigatória ---
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/login');
            exit();
        }

        // --- 2. Obter e validar o nome do ficheiro ---
        $rawFile = $this->params['file'] ?? '';

        // Descodificar URL encoding (%2F, etc.) e remover qualquer tentativa
        // de path traversal (../, ..\, /absoluto, etc.)
        $fileName = basename(urldecode($rawFile));

        // Só aceita nomes gerados por uniqid(): hex + extensão permitida
        if (!preg_match('/^[a-f0-9]+\.(pdf|png|jpe?g)$/i', $fileName)) {
            http_response_code(400);
            die('Nome de ficheiro inválido.');
        }

        // --- 3. Construir caminho absoluto ---
        $filePath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            die('Ficheiro não encontrado.');
        }

        // --- 4. Verificar que o ficheiro pertence a uma resposta do utilizador
        //        (admins vêem tudo; utilizadores comuns só vêem os seus) ---
        if ($_SESSION['user_role'] !== 'admin') {
            $answerModel   = $this->model('Answer');
            $responseModel = $this->model('Response');

            $answer = $answerModel->getAnswerByFilePath($fileName);
            if (!$answer) {
                http_response_code(403);
                die('Acesso negado.');
            }

            $response = $responseModel->getResponseDetail($answer->response_id);
            if (!$response || $response->user_id != $_SESSION['user_id']) {
                http_response_code(403);
                die('Acesso negado.');
            }
        }

        // --- 5. Detectar MIME type real do ficheiro ---
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        $allowedMimes = [
            'application/pdf',
            'image/png',
            'image/jpeg',
        ];

        if (!in_array($mimeType, $allowedMimes, true)) {
            http_response_code(415);
            die('Tipo de ficheiro não suportado.');
        }

        // --- 6. Enviar o ficheiro ---
        header('Content-Type: '        . $mimeType);
        header('Content-Length: '      . filesize($filePath));
        // "inline" para PDFs/imagens (o browser abre directamente);
        // mude para "attachment" se preferir forçar o download.
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        // Limpar qualquer buffer de saída antes de enviar bytes binários
        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($filePath);
        exit();
    }
}
