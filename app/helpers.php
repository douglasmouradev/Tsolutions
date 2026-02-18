<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Middlewares\Csrf::field();
    }
}

if (!function_exists('statusLabel')) {
    function statusLabel(?string $status): string
    {
        $labels = [
            'aberto' => 'Aberto',
            'em_andamento' => 'Em andamento',
            'fechado' => 'Fechado',
            'cancelado' => 'Cancelado',
        ];
        return $labels[$status] ?? str_replace('_', ' ', (string) $status);
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $datetime, string $tz = 'America/Sao_Paulo'): string
    {
        if (!$datetime) {
            return '-';
        }
        try {
            $dt = new \DateTime($datetime, new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone($tz));
            return $dt->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $datetime;
        }
    }
}

if (!function_exists('roleLabel')) {
    function roleLabel(?string $role): string
    {
        $labels = [
            'admin' => 'Admin',
            'agent' => 'Agente',
            'requester' => 'Solicitante',
            'diretoria' => 'Diretoria',
            'externo' => 'Externo',
            'suporte' => 'Suporte',
        ];
        return $labels[$role] ?? ucfirst((string) $role);
    }
}

if (!function_exists('priorityLabel')) {
    function priorityLabel(?string $priority): string
    {
        $labels = [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'critica' => 'Urgente',
        ];
        return $labels[$priority] ?? ucfirst((string) $priority);
    }
}

if (!function_exists('slaStatus')) {
    /** @return 'vencido'|'proximo'|'ok'|null */
    function slaStatus(?string $dueAt, string $status, int $proximoHoras = 24): ?string
    {
        if (!$dueAt || in_array($status, ['fechado', 'cancelado'], true)) {
            return null;
        }
        $due = strtotime($dueAt);
        $now = time();
        if ($due < $now) {
            return 'vencido';
        }
        if (($due - $now) <= $proximoHoras * 3600) {
            return 'proximo';
        }
        return 'ok';
    }
}
