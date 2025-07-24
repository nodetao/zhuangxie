<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isAdmin()) {
    $_SESSION['error'] = "无权执行此操作";
    redirect('manage_workers.php');
}

refreshSession();

$worker_id = $_GET['id'] ?? 0;

// 验证工人是否存在
$stmt = $pdo->prepare("SELECT id FROM workers WHERE id = ?");
$stmt->execute([$worker_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "工人不存在";
    redirect('manage_workers.php');
}

try {
    $pdo->beginTransaction();
    
    // 1. 删除关联的装卸记录
    $stmt = $pdo->prepare("DELETE FROM records WHERE worker_id = ?");
    $stmt->execute([$worker_id]);
    $deleted_records = $stmt->rowCount();
    
    // 2. 删除工人
    $stmt = $pdo->prepare("DELETE FROM workers WHERE id = ?");
    $stmt->execute([$worker_id]);
    
    $pdo->commit();
    $_SESSION['success'] = "成功删除工人及{$deleted_records}条相关记录";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "删除失败: " . $e->getMessage();
}

redirect('manage_workers.php');

