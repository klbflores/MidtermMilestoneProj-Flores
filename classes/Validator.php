<?php
declare(strict_types=1);

class Validator {
    public static function sanitize(string $input): string {
        return trim($input);
    }

    public static function validateEmail(string $email): bool {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateString(string $val, int $minLen = 1, int $maxLen = 255): bool {
        $len = mb_strlen(trim($val));
        return $len >= $minLen && $len <= $maxLen;
    }

    public static function validateCost(mixed $cost): bool {
        return is_numeric($cost) && (float)$cost >= 0 && (float)$cost <= 10000;
    }
}