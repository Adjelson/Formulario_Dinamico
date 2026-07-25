<?php

class DownloadController extends Controller
{
    public function serve(): void
    {
        $this->requireAuth();
        $fileName = basename(rawurldecode((string) ($this->params['file'] ?? '')));
        if (!preg_match('/^[a-f0-9]{13,64}\.(pdf|png|jpe?g)$/i', $fileName)) {
            throw new RuntimeException('Nome de ficheiro inválido.', 404);
        }

        $answer = $this->model('Answer')->getAnswerByFilePath($fileName);
        if (!$answer) {
            throw new RuntimeException('Ficheiro não encontrado.', 404);
        }
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $response = $this->model('Response')->getResponseDetail((int) $answer->response_id);
            if (!$response || (int) $response->user_id !== (int) $_SESSION['user_id']) {
                throw new RuntimeException('Acesso negado.', 403);
            }
        }

        $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . $fileName;
        $realDir = realpath(UPLOAD_DIR);
        $realPath = realpath($path);
        if (!$realDir || !$realPath || !str_starts_with($realPath, $realDir . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
            throw new RuntimeException('Ficheiro não encontrado.', 404);
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($realPath);
        if (!in_array($mime, ['application/pdf', 'image/png', 'image/jpeg'], true)) {
            throw new RuntimeException('Tipo de ficheiro não suportado.', 415);
        }

        $downloadName = preg_replace('/[^\pL\pN_.-]+/u', '_', basename((string) ($answer->original_file_name ?: $fileName)));
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($realPath));
        header('Content-Disposition: inline; filename="' . rawurlencode($downloadName) . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
        header('Cache-Control: private, no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
        if (ob_get_level()) {
            ob_end_clean();
        }
        readfile($realPath);
        exit;
    }
}
