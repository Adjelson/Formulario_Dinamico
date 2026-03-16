<?php

/**
 * CoverController
 * Serve imagens de capa dos formulários (logotipos/banners).
 * Acesso público — qualquer utilizador pode ver a capa de um formulário publicado.
 * Os ficheiros ficam em storage/covers/ (fora de public/) mas são imagens
 * de apresentação, sem dados sensíveis.
 */
class CoverController extends Controller {

    public function serve() {
        $rawFile  = $this->params['file'] ?? '';
        $fileName = basename(urldecode($rawFile));

        // Só aceita nomes gerados por uniqid() com extensões de imagem
        // Aceita nomes como: cover_abc123.jpg, abc123.jpg, abc-123_x.png
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.(jpg|jpeg|png|gif|webp)$/i', $fileName)) {
            http_response_code(400);
            exit('Ficheiro inválido.');
        }

        $coverDir = APPROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'covers';
        $filePath = $coverDir . DIRECTORY_SEPARATOR . $fileName;

        // Verificar path traversal
        $realDir  = realpath($coverDir);
        $realFile = realpath($filePath);

        if ($realFile === false || strpos($realFile, $realDir) !== 0 || !is_file($realFile)) {
            http_response_code(404);
            exit('Imagem não encontrada.');
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($realFile);

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowed)) {
            http_response_code(415);
            exit('Tipo não suportado.');
        }

        // Cache de 7 dias para imagens de capa (são estáticas)
        header('Content-Type: '   . $mimeType);
        header('Content-Length: ' . filesize($realFile));
        header('Cache-Control: public, max-age=604800');
        header('X-Content-Type-Options: nosniff');

        if (ob_get_level()) ob_end_clean();
        readfile($realFile);
        exit();
    }
}
