<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

use Charcoal\Contracts\Buffers\ReadableBufferInterface;

/**
 * Password helpers.
 */
abstract readonly class PasswordHelper
{
    /**
     * @api Hashes a password with Argon2
     */
    public static function hashArgon2(
        string|ReadableBufferInterface $password,
        int                            $memoryCost = 65536,
        int                            $timeCost = 4,
        int                            $threads = 2
    ): string
    {
        if ($password instanceof ReadableBufferInterface) {
            $password = $password->bytes();
        }

        return password_hash($password, PASSWORD_ARGON2ID, [
            "memory_cost" => $memoryCost,
            "time_cost" => $timeCost,
            "threads" => $threads
        ]);
    }

    /**
     * @api Verify a password with Argon2
     */
    public static function verifyArgon2(string|ReadableBufferInterface $password, string $hash): bool
    {
        if ($password instanceof ReadableBufferInterface) {
            $password = $password->bytes();
        }

        return password_verify($password, $hash);
    }

    /**
     * @api NULL-safe password hashing with BCRYPT
     */
    public static function hashBcrypt(string|ReadableBufferInterface $password): string
    {
        if ($password instanceof ReadableBufferInterface) {
            $password = str_replace("\0", "\1", $password->bytes());
        }

        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @api Verify a password with BCRYPT
     */
    public static function verifyBcrypt(string|ReadableBufferInterface $password, string $hash): bool
    {
        if ($password instanceof ReadableBufferInterface) {
            $password = str_replace("\0", "\1", $password->bytes());
        }

        return password_verify($password, $hash);
    }

    /**
     * @api Simple password strength evaluator
     */
    public static function checkStrength(string $password): int
    {
        $score = 0;
        $passwordLength = strlen($password);

        // Lowercase alphabets... +1
        if (preg_match('/[a-z]/', $password)) $score++;
        // Uppercase alphabets... +1
        if (preg_match('/[A-Z]/', $password)) $score++;
        // Numerals... +1
        if (preg_match('/[0-9]/', $password)) $score++;
        // Special characters... +1
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;

        // Length over or equals 12 ... +1
        if ($passwordLength >= 12) $score++;
        // Length over or equals 16 ... +1
        if ($passwordLength >= 16) $score++;

        // Penalty for repeating characters... -1
        if (preg_match('/(.)\1{2,}/', $password)) $score--;

        return max(0, $score);
    }
}