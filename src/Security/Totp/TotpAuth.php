<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security\Totp;

use Charcoal\Base\Encoding\Base32;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Objects\Traits\NotSerializableTrait;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;

/**
 * This class provides functionality for generating and verifying Time-based One-time Passwords (TOTP).
 * It implements the RFC 6238 standard for TOTP generation and validation, leveraging an HMAC-based
 * algorithm for secure time-sensitive code generation.
 * @api
 */
final readonly class TotpAuth
{
    protected string $secret;
    public int $seedLength;

    use NoDumpTrait;
    use NotSerializableTrait;
    use NotCloneableTrait;

    /**
     * @param string|ReadableBufferInterface $secret
     * @param int $digits
     * @param int $period
     * @param TotpHmacAlgo $algorithm
     */
    public function __construct(
        #[\SensitiveParameter]
        string|ReadableBufferInterface $secret,
        protected int                  $digits = 6,
        protected int                  $period = 30,
        protected TotpHmacAlgo         $algorithm = TotpHmacAlgo::SHA1,
    )
    {
        $this->secret = $secret instanceof ReadableBufferInterface ?
            $secret->bytes() : Base32::decode($secret);

        $this->seedLength = strlen($this->secret);
        if ($this->seedLength < 10 || $this->seedLength > 32) {
            throw new \LengthException("Invalid TOTP secret length: " . $this->seedLength);
        }
    }

    /**
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     * @api
     */
    public static function generateSecret(int $length = 16): string
    {
        return Base32::encode(random_bytes($length));
    }

    /**
     * @param int|null $timestamp
     * @return string
     */
    public function generateTOTP(?int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        $timeStep = floor($timestamp / $this->period);
        $binaryTime = pack("N2", 0, $timeStep);
        $hash = $this->algorithm->createHmac($binaryTime, $this->secret);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary =
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % (10 ** $this->digits);
        return str_pad((string)$otp, $this->digits, "0", STR_PAD_LEFT);
    }

    /**
     * @param string $code
     * @param int $discrepancy
     * @param int|null $timestamp
     * @return bool
     * @api
     */
    public function verifyTOTP(string $code, int $discrepancy = 1, ?int $timestamp = null): bool
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $testTimestamp = $timestamp + ($i * $this->period);
            $calcCode = $this->generateTOTP($testTimestamp);
            if (hash_equals($calcCode, $code)) {
                return true;
            }
        }

        return false;
    }
}