<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once 'DB_Connect.php';
require_once 'totp_lib.php';

define('AUTH_DEBUG', true); // поставь false когда всё заработает

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

function dbg($msg) { if (AUTH_DEBUG) error_log('[AUTH] ' . $msg); }
function out($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit(); }
function normalize_role($r){ return strtolower(trim((string)$r)); }

$db = new DB_Connect();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) $data = [];

$action = $data['action'] ?? 'login';

if ($action === 'status') {
    out(["success"=>true, "logged_in"=>isset($_SESSION['user_id']), "role"=>($_SESSION['role'] ?? null)]);
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"] ?? '/',
            $params["domain"] ?? '',
            (bool)($params["secure"] ?? false),
            (bool)($params["httponly"] ?? true)
        );
    }
    session_destroy();
    out(["success"=>true]);
}

if ($action === 'verify2fa') {
    $code = preg_replace('/\D/', '', ($data['code'] ?? ''));

    if (!isset($_SESSION['2fa_pending_user_id'], $_SESSION['2fa_pending_started_at'])) {
        out(["success"=>false, "message"=>"Сначала введите логин и пароль."]);
    }
    if (time() - (int)$_SESSION['2fa_pending_started_at'] > 300) {
        unset($_SESSION['2fa_pending_user_id'], $_SESSION['2fa_pending_started_at']);
        out(["success"=>false, "message"=>"Время подтверждения истекло. Войдите заново."]);
    }
    if (strlen($code) !== 6) out(["success"=>false, "message"=>"Введите 6-значный код."]);

    $userId = (int)$_SESSION['2fa_pending_user_id'];

    $stmt = $conn->prepare("SELECT role, totp_enabled, totp_secret, totp_last_used FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) out(["success"=>false, "message"=>"Пользователь не найден."]);

    $role = normalize_role($user['role'] ?? '');
    if (!in_array($role, ['lawyer','assistant'], true)) out(["success"=>false, "message"=>"Доступ запрещен."]);

    if ((int)$user['totp_enabled'] !== 1 || empty($user['totp_secret'])) {
        out(["success"=>false, "message"=>"2FA не настроено."]);
    }

    [$ok, $counterUsed] = totp_verify($user['totp_secret'], $code, 4); // окно шире на всякий
    if (!$ok) {
        dbg("verify2fa FAIL user={$userId} role={$role} server_time=" . time() . " code={$code}");
        out(["success"=>false, "message"=>"Код неверный. Проверьте время на телефоне."]);
    }

    $lastUsed = ($user['totp_last_used'] === null) ? -1 : (int)$user['totp_last_used'];
    if ($counterUsed <= $lastUsed) {
        out(["success"=>false, "message"=>"Этот код уже использовался. Подождите новый (30 сек)."]);
    }

    $upd = $conn->prepare("UPDATE users SET totp_last_used = ? WHERE id = ?");
    $upd->bind_param("ii", $counterUsed, $userId);
    $upd->execute();
    $upd->close();

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;

    unset($_SESSION['2fa_pending_user_id'], $_SESSION['2fa_pending_started_at']);

    out(["success"=>true, "role"=>$role]);
}

if ($action === 'confirm2faSetup') {
    $code = preg_replace('/\D/', '', ($data['code'] ?? ''));

    if (!isset($_SESSION['2fa_setup_user_id'], $_SESSION['2fa_setup_secret'], $_SESSION['2fa_setup_started_at'], $_SESSION['2fa_setup_role'])) {
        out(["success"=>false, "message"=>"Сначала введите логин и пароль."]);
    }
    if (time() - (int)$_SESSION['2fa_setup_started_at'] > 600) {
        unset($_SESSION['2fa_setup_user_id'], $_SESSION['2fa_setup_secret'], $_SESSION['2fa_setup_started_at'], $_SESSION['2fa_setup_role']);
        out(["success"=>false, "message"=>"Время настройки истекло. Войдите заново."]);
    }
    if (strlen($code) !== 6) out(["success"=>false, "message"=>"Введите 6-значный код."]);

    $userId = (int)$_SESSION['2fa_setup_user_id'];
    $secret = (string)$_SESSION['2fa_setup_secret'];
    $role = normalize_role($_SESSION['2fa_setup_role']);

    if (!in_array($role, ['lawyer','assistant'], true)) out(["success"=>false, "message"=>"Доступ запрещен."]);

    [$ok, $counterUsed] = totp_verify($secret, $code, 4); // окно шире
    if (!$ok) {
        dbg("confirm2faSetup FAIL user={$userId} role={$role} server_time=" . time() . " code={$code}");
        out(["success"=>false, "message"=>"Код неверный. Проверьте время на телефоне."]);
    }

    $upd = $conn->prepare("UPDATE users SET totp_enabled = 1, totp_secret = ?, totp_last_used = ? WHERE id = ?");
    $upd->bind_param("sii", $secret, $counterUsed, $userId);
    $upd->execute();
    $upd->close();

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;

    unset($_SESSION['2fa_setup_user_id'], $_SESSION['2fa_setup_secret'], $_SESSION['2fa_setup_started_at'], $_SESSION['2fa_setup_role']);

    out(["success"=>true, "role"=>$role]);
}

/* ====== LOGIN (username+password) ====== */

$username = trim((string)($data['username'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($username === '' || $password === '') out(["success"=>false, "message"=>"Введите логин и пароль."]);

$stmt = $conn->prepare("SELECT id, username, password, role, totp_enabled, totp_secret FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) out(["success"=>false, "message"=>"Неверный логин или пароль."]);

$role = normalize_role($user['role'] ?? '');
if (!in_array($role, ['lawyer','assistant'], true)) out(["success"=>false, "message"=>"Доступ запрещен."]);

if (!password_verify($password, $user['password'])) {
    out(["success"=>false, "message"=>"Неверный логин или пароль."]);
}

$userId = (int)$user['id'];
$totpEnabled = (int)($user['totp_enabled'] ?? 0) === 1;
$totpSecret = (string)($user['totp_secret'] ?? '');

if ($totpEnabled && $totpSecret !== '') {
    // если 2FA включена — начинаем ожидание кода
    unset($_SESSION['2fa_setup_user_id'], $_SESSION['2fa_setup_secret'], $_SESSION['2fa_setup_started_at'], $_SESSION['2fa_setup_role']);
    $_SESSION['2fa_pending_user_id'] = $userId;
    $_SESSION['2fa_pending_started_at'] = time();
    out(["success"=>false, "require2fa"=>true, "message"=>"Введите код из Google Authenticator."]);
}

/*
  FIX: если setup уже начат для этого же пользователя и ещё не истёк — НЕ генерим новый secret.
*/
$existingSetupUser = $_SESSION['2fa_setup_user_id'] ?? null;
$existingSetupStarted = $_SESSION['2fa_setup_started_at'] ?? null;
$existingSetupSecret = $_SESSION['2fa_setup_secret'] ?? null;
$existingSetupRole = $_SESSION['2fa_setup_role'] ?? null;

$reuse = (
    $existingSetupUser !== null &&
    (int)$existingSetupUser === $userId &&
    $existingSetupSecret &&
    $existingSetupStarted &&
    (time() - (int)$existingSetupStarted) <= 600 &&
    normalize_role($existingSetupRole) === $role
);

if ($reuse) {
    $secret = (string)$existingSetupSecret;
} else {
    $secret = totp_generate_secret(20);
    $_SESSION['2fa_setup_user_id'] = $userId;
    $_SESSION['2fa_setup_secret'] = $secret;
    $_SESSION['2fa_setup_started_at'] = time();
    $_SESSION['2fa_setup_role'] = $role;
}

$host = $_SERVER['HTTP_HOST'] ?? 'MySite';
$issuer = preg_replace('/[^a-zA-Z0-9._-]/', '', $host);
$otpauth = totp_otpauth_url($issuer, $username, $secret);

out([
    "success"=>false,
    "require2fa_setup"=>true,
    "otpauth_url"=>$otpauth,
    "secret"=>$secret,
    "message"=>"Отсканируйте QR и введите код из приложения."
]);
