<?php

declare(strict_types=1);

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    }
}

loadEnv(__DIR__ . '/../.env');

return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'crm_chamados',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],
    'app_url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'app_env' => $_ENV['APP_ENV'] ?? 'local',
    'app_debug' => ($_ENV['APP_ENV'] ?? '') === 'production'
        ? false
        : filter_var($_ENV['APP_DEBUG'] ?? '0', FILTER_VALIDATE_BOOLEAN),
    'max_upload_mb' => (int) ($_ENV['MAX_UPLOAD_MB'] ?? 10),
    'sla_hours' => [
        'critica' => (int) ($_ENV['SLA_URGENTE_HORAS'] ?? 24),
        'alta' => (int) ($_ENV['SLA_ALTA_HORAS'] ?? 72),
        'media' => (int) ($_ENV['SLA_MEDIA_HORAS'] ?? 120),
        'baixa' => (int) ($_ENV['SLA_BAIXA_HORAS'] ?? 336),
    ],
    'clamav_path' => ($p = $_ENV['CLAMAV_PATH'] ?? '') !== '' ? $p : null,
    'allow_requester_close' => filter_var($_ENV['ALLOW_REQUESTER_CLOSE'] ?? '1', FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['TIMEZONE'] ?? 'America/Sao_Paulo',
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? '',
        'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASS'] ?? '',
        'from' => $_ENV['MAIL_FROM'] ?? 'noreply@localhost',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'T Solutions',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    ],
];
