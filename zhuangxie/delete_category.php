<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isAdmin()) {
    $_SESSION['error'] = "无权执行此操作";
    redirect('manage_categories.php');
}

refreshSession();

$category_id = $_GET['id'] ?? 0;

// 验证品类是否存在
$stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "品类不存在";
    redirect('manage_categories.php');
}

try {
    $pdo->beginTransaction();
    
    // 1. 删除关联的装卸记录
    $stmt = $pdo->prepare("DELETE FROM records WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $deleted_records = $stmt->rowCount();
    
    // 2. 删除品类
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    $pdo->commit();
    $_SESSION['success'] = "成功删除品类及{$deleted_records}条相关记录";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "删除失败: " . $e->getMessage();
}

redirect('manage_categories.php');
