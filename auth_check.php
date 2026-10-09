<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_auth(): void {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_guest(): void {
    if (!empty($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}

function current_user_id(): int {
    return (int) ($_SESSION['user_id'] ?? 0);
}