<?php
include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

$record_id = $_GET['id'] ?? 0;

// 检查记录是否存在
$stmt = $pdo->prepare("SELECT id FROM records WHERE id = ?");
$stmt->execute([$record_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "记录不存在";
    redirect('view_records.php');
}

// 删除记录
$stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
$stmt->execute([$record_id]);

$_SESSION['success'] = "记录删除成功";
redirect('view_records.php');
?>