<?php

class CoverController extends Controller
{
    public function serve(): void
    {
        $fileName = basename(rawurldecode((string) ($this->params['file'] ?? '')));
        if (!preg_match('/^cover_[a-f0-9]{6,64}\.(jpe?g|png|webp)$/i', $fileName)) {
            throw new RuntimeException('Imagem não encontrada.', 404);
        }

        $realDir = realpath(COVER_DIR);
        $realPath = realpath(COVER_DIR . DIRECTORY_SEPARATOR . $fileName);
        if (!$realDir || !$realPath || !str_starts_with($realPath, $realDir . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
            throw new RuntimeException('Imagem não encontrada.', 404);
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($realPath);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Tipo de imagem não suportado.', 415);
        }

        $etag = '"' . sha1_file($realPath) . '"';
        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: public, max-age=604800, immutable');
        header('ETag: ' . $etag);
        header('X-Content-Type-Options: nosniff');
        readfile($realPath);
        exit;
    }
}
