<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isAdmin()) {
    $_SESSION['error'] = "无权执行此操作";
    redirect('manage_users.php');
}

refreshSession();

$user_id = $_GET['id'] ?? 0;

// 不能删除自己
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "不能删除当前登录的账户";
    redirect('manage_users.php');
}

// 验证用户是否存在
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "用户不存在";
    redirect('manage_users.php');
}

try {
    $pdo->beginTransaction();
    
    // 1. 删除该用户创建的记录
    $stmt = $pdo->prepare("DELETE FROM records WHERE recorded_by = ?");
    $stmt->execute([$user_id]);
    $deleted_records = $stmt->rowCount();
    
    // 2. 删除用户
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $pdo->commit();
    $_SESSION['success'] = "成功删除用户及{$deleted_records}条相关记录";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "删除失败: " . $e->getMessage();
}

redirect('manage_users.php');
