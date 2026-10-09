<?php
declare(strict_types=1);

class AuthService {
    public function __construct(private PDO $pdo) {}

    public function register(string $name, string $email, string $password): array {
        if (!Validator::validateString($name, 2, 80)) return ["Name must be 2 to 80 characters."];
        if (!Validator::validateEmail($email)) return ["Please provide a valid email."];
        if (strlen($password) < 6) return ["Password must be at least 6 characters."];

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ["Email is already registered."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $insert->execute([$name, $email, $hash]);

        return [];
    }

    public function login(string $email, string $password): bool|array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}