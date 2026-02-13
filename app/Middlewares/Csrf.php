<?php

declare(strict_types=1);

namespace App\Middlewares;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function validate(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $expected = $_SESSION[self::SESSION_KEY] ?? '';
        return hash_equals($expected, $token);
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
