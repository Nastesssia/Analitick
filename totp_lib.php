<?php

function totp_base32_decode(string $b32): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper($b32);
    $b32 = preg_replace('/[^A-Z2-7]/', '', $b32);

    $bits = '';
    $len = strlen($b32);
    for ($i = 0; $i < $len; $i++) {
        $v = strpos($alphabet, $b32[$i]);
        if ($v === false) continue;
        $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
    }

    $out = '';
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
        $out .= chr(bindec(substr($bits, $i, 8)));
    }
    return $out;
}

function totp_generate_secret(int $length = 20): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $secret;
}

function totp_time_slice(?int $time = null, int $period = 30): int
{
    if ($time === null) $time = time();
    return (int) floor($time / $period);
}

function totp_code(string $secret, int $timeSlice, int $digits = 6): string
{
    $key = totp_base32_decode($secret);

    // 8-byte big-endian counter
    $time = pack('N*', 0) . pack('N*', $timeSlice);

    $hash = hash_hmac('sha1', $time, $key, true);
    $offset = ord($hash[19]) & 0x0F;

    $binCode =
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF);

    $mod = 10 ** $digits;
    return str_pad((string)($binCode % $mod), $digits, '0', STR_PAD_LEFT);
}

/**
 * ВАЖНО: СИГНАТУРА как в твоём login.php:
 * [$ok, $counterUsed] = totp_verify($secret, $code, 2);
 */
function totp_verify(string $secret, string $code, int $window = 1, int $digits = 6, int $period = 30): array
{
    $code = preg_replace('/\D/', '', $code ?? '');
    if ($code === '' || strlen($code) !== $digits) {
        return [false, -1];
    }

    $slice = totp_time_slice(null, $period);

    for ($i = -$window; $i <= $window; $i++) {
        $ts = $slice + $i;
        $calc = totp_code($secret, $ts, $digits);
        if (hash_equals($calc, $code)) {
            return [true, $ts];
        }
    }
    return [false, -1];
}

function totp_otpauth_url(string $issuer, string $account, string $secret, int $digits = 6, int $period = 30): string
{
    // otpauth://totp/Issuer:account?secret=...&issuer=...&digits=6&period=30
    $issuerClean = preg_replace('/[^a-zA-Z0-9._-]/', '', $issuer);
    $label = rawurlencode($issuerClean . ':' . $account);

    $params = http_build_query([
        'secret' => $secret,
        'issuer' => $issuerClean,
        'digits' => $digits,
        'period' => $period
    ]);

    return "otpauth://totp/{$label}?{$params}";
}
