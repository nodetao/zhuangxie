<?php
// 只在会话未启动时才配置和启动会话
if (session_status() === PHP_SESSION_NONE) {
    // 30天有效期
    $session_duration = 2592000;
    
    // 设置会话cookie参数
    session_set_cookie_params([
        'lifetime' => $session_duration,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    ini_set('session.gc_maxlifetime', $session_duration);
    ini_set('session.cookie_lifetime', $session_duration);
    
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

function refreshSession() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

function checkSessionTimeout() {
    $session_duration = 2592000; // 30天
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive >= $session_duration) {
            session_destroy();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}
?>







