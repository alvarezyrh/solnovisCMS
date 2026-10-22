<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_admin(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('login.php');
    }
    return $user;
}

function require_role(string $role): array
{
    $user = require_admin();
    if ($user['role'] !== $role) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk membuka halaman ini.');
    }
    return $user;
}
