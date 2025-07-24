<?php
if (session_status() === PHP_SESSION_NONE) {
    // 设置Session配置
    ini_set('session.gc_maxlifetime', 86400); // 24小时
    ini_set('session.cookie_lifetime', 86400); // 24小时
    session_set_cookie_params([
        'lifetime' => 86400, // 24小时
        'path' => '/',
        'domain' => '',
        'secure' => false, // 如果使用HTTPS设为true
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

// 刷新Session过期时间
function refreshSession() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

// 检查Session是否过期
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive >= 86400) { // 24小时
            session_destroy();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}
?>


