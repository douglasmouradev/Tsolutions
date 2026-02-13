<?php

declare(strict_types=1);

namespace App\Services;

class UploadService
{
    private const ALLOWED_MIMES = [
        'application/pdf' => 'pdf',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'text/plain' => 'txt',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    private const ALLOWED_EXTENSIONS = ['pdf', 'png', 'jpg', 'jpeg', 'txt', 'docx'];

    /** Assinaturas (magic bytes) para verificação do tipo real do arquivo */
    private const MAGIC_BYTES = [
        'pdf' => ["\x25\x50\x44\x46"], // %PDF
        'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"], // PNG signature
        'jpg' => ["\xFF\xD8\xFF"], // JPEG SOI
        'jpeg' => ["\xFF\xD8\xFF"],
        'docx' => ["\x50\x4B\x03\x04", "\x50\x4B\x05\x06"], // ZIP/DOCX (PK..)
        'txt' => null, // text/plain: validado via UTF-8
    ];

    public function __construct(
        private string $uploadPath,
        private string $quarantinePath,
        private int $maxSizeBytes,
        private ?string $clamavPath = null
    ) {
    }

    public function validate(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Erro no upload do arquivo.'];
        }

        if ($file['size'] > $this->maxSizeBytes) {
            return ['valid' => false, 'message' => 'Arquivo excede o tamanho máximo permitido.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!$detectedMime || !isset(self::ALLOWED_MIMES[$detectedMime])) {
            return ['valid' => false, 'message' => 'Tipo de arquivo não permitido.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return ['valid' => false, 'message' => 'Extensões permitidas: ' . implode(', ', self::ALLOWED_EXTENSIONS)];
        }

        if (self::ALLOWED_MIMES[$detectedMime] !== $ext && !in_array($ext, ['jpg', 'jpeg'], true)) {
            return ['valid' => false, 'message' => 'Extensão não corresponde ao tipo do arquivo.'];
        }

        if (!$this->validateMagicBytes($file['tmp_name'], $ext)) {
            return ['valid' => false, 'message' => 'O conteúdo do arquivo não corresponde ao tipo declarado.'];
        }

        return ['valid' => true, 'mime' => $detectedMime, 'ext' => $ext];
    }

    private function validateMagicBytes(string $tmpPath, string $ext): bool
    {
        $handle = fopen($tmpPath, 'rb');
        if (!$handle) {
            return false;
        }

        $signatures = self::MAGIC_BYTES[$ext] ?? null;

        if ($signatures === null) {
            // txt: verificar se é texto UTF-8 válido
            $content = stream_get_contents($handle);
            fclose($handle);
            return mb_check_encoding($content, 'UTF-8');
        }

        $maxLen = 0;
        foreach ($signatures as $sig) {
            $len = strlen($sig);
            if ($len > $maxLen) {
                $maxLen = $len;
            }
        }

        $header = fread($handle, max($maxLen, 8));
        fclose($handle);

        foreach ($signatures as $sig) {
            if (strlen($header) >= strlen($sig) && substr($header, 0, strlen($sig)) === $sig) {
                return true;
            }
        }

        return false;
    }

    private function scanWithClamAv(string $filePath): bool
    {
        if ($this->clamavPath === null || !is_executable($this->clamavPath)) {
            return true; // ClamAV não configurado: permite (ambiente dev)
        }

        $cmd = sprintf(
            '%s --no-summary %s 2>/dev/null',
            escapeshellcmd($this->clamavPath),
            escapeshellarg($filePath)
        );
        exec($cmd, $output, $code);
        return $code === 0;
    }

    public function store(array $file, int $ticketId, int $userId): ?array
    {
        $validation = $this->validate($file);
        if (!$validation['valid']) {
            return null;
        }

        if (!is_dir($this->quarantinePath)) {
            mkdir($this->quarantinePath, 0750, true);
        }

        $safeExt = $validation['ext'];
        $storedName = bin2hex(random_bytes(16)) . '.' . $safeExt;
        $quarantineFile = $this->quarantinePath . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $quarantineFile)) {
            return null;
        }

        try {
            if (!$this->scanWithClamAv($quarantineFile)) {
                @unlink($quarantineFile);
                return null;
            }

            if (!is_dir($this->uploadPath)) {
                mkdir($this->uploadPath, 0750, true);
            }

            $destination = $this->uploadPath . DIRECTORY_SEPARATOR . $storedName;
            if (!rename($quarantineFile, $destination)) {
                @unlink($quarantineFile);
                return null;
            }

            return [
                'stored_name' => $storedName,
                'original_name' => $file['name'],
                'mime_type' => $validation['mime'],
                'size_bytes' => (int) $file['size'],
            ];
        } catch (\Throwable $e) {
            if (file_exists($quarantineFile)) {
                @unlink($quarantineFile);
            }
            throw $e;
        }
    }

    public function getFilePath(string $storedName): string
    {
        return $this->uploadPath . DIRECTORY_SEPARATOR . $storedName;
    }

    public function fileExists(string $storedName): bool
    {
        return file_exists($this->getFilePath($storedName));
    }
}
