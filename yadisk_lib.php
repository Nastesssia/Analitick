<?php
require_once __DIR__ . '/Config.php';

const YD_WEBDAV_BASE = 'https://webdav.yandex.ru';

function yd_encode_path(string $path): string {
    $path = '/' . ltrim($path, '/');
    $parts = explode('/', $path);
    $parts = array_map(fn($p) => $p === '' ? '' : rawurlencode($p), $parts);
    return implode('/', $parts);
}

function yd_request(string $method, string $remotePath, array $headers = [], ?string $bodyFilePath = null): array {
    $remotePath = '/' . ltrim($remotePath, '/');
    $url = YD_WEBDAV_BASE . yd_encode_path($remotePath);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, YD_LOGIN . ':' . YD_APP_PASSWORD);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $fp = null;
    if ($bodyFilePath !== null) {
        $fp = fopen($bodyFilePath, 'rb');
        if ($fp === false) {
            curl_close($ch);
            return ['code' => 0, 'err' => 'Cannot open file: ' . $bodyFilePath, 'body' => ''];
        }
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        $size = @filesize($bodyFilePath);
        if ($size !== false) curl_setopt($ch, CURLOPT_INFILESIZE, $size);
    }

    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (is_resource($fp)) fclose($fp);
    curl_close($ch);

    return ['code' => $code, 'err' => (string)$err, 'body' => is_string($body) ? $body : ''];
}

function yd_mkcol_recursive(string $dir): bool {
    $dir = '/' . trim($dir, '/');
    if ($dir === '/') return true;

    $parts = explode('/', trim($dir, '/'));
    $cur = '';
    foreach ($parts as $p) {
        $cur .= '/' . $p;
        $r = yd_request('MKCOL', $cur);

        if ($r['code'] === 201 || $r['code'] === 405) continue;

        error_log("YADISK MKCOL failed: dir={$cur} code={$r['code']} err={$r['err']} body=" . mb_substr($r['body'], 0, 500));
        return false;
    }
    return true;
}

function yd_put_file(string $localPath, string $remotePath): bool {
    $remotePath = '/' . ltrim($remotePath, '/');
    $dir = dirname($remotePath);
    if (!yd_mkcol_recursive($dir)) return false;

    $r = yd_request('PUT', $remotePath, ['Content-Type: application/octet-stream'], $localPath);

    if (in_array($r['code'], [201, 204], true)) return true;

    error_log("YADISK PUT failed: remote={$remotePath} code={$r['code']} err={$r['err']} body=" . mb_substr($r['body'], 0, 500));
    return false;
}

function yd_copy(string $fromPath, string $toPath, bool $overwrite = true): bool {
    $fromPath = '/' . ltrim($fromPath, '/');
    $toPath   = '/' . ltrim($toPath, '/');

    $destDir = dirname($toPath);
    if (!yd_mkcol_recursive($destDir)) {
        error_log("YADISK COPY: cannot create dest dir: {$destDir}");
        return false;
    }

    $headers = [
        'Destination: ' . YD_WEBDAV_BASE . yd_encode_path($toPath),
        'Overwrite: ' . ($overwrite ? 'T' : 'F')
    ];

    $r = yd_request('COPY', $fromPath, $headers);

    if (in_array($r['code'], [201, 204, 202], true)) return true;

    error_log("YADISK COPY failed: from={$fromPath} to={$toPath} code={$r['code']} err={$r['err']} body=" . mb_substr($r['body'], 0, 500));
    return false;
}

if (!function_exists('yd_mkcol')) {
    function yd_mkcol(string $dir): bool {
        return yd_mkcol_recursive($dir);
    }
}
if (!function_exists('yd_mkdir_p')) {
    function yd_mkdir_p(string $dir): bool {
        return yd_mkcol_recursive($dir);
    }
}
if (!function_exists('yd_ensure_dir')) {
    function yd_ensure_dir(string $dir): bool {
        return yd_mkcol_recursive($dir);
    }
}
