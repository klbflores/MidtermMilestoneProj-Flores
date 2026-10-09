<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Database.php';

$dbInstance = Database::getInstance();
$pdo = $dbInstance->getConnection();