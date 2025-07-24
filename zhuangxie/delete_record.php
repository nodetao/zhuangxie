<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isLoggedIn()) {
    redirect('index.php');
}

refreshSession();

$record_id = $_GET['id'] ?? 0;

// 检查记录是否存在
$stmt = $pdo->prepare("SELECT id, recorded_by FROM records WHERE id = ?");
$stmt->execute([$record_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "记录不存在";
    redirect('view_records.php');
}

// 检查权限：管理员可以删除所有记录，普通用户只能删除自己的记录
if (!isAdmin() && $record['recorded_by'] != $_SESSION['user_id']) {
    echo "<script>alert('无权限操作'); window.location.href='view_records.php';</script>";
    exit;
}

// 删除记录
$stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
$stmt->execute([$record_id]);

$_SESSION['success'] = "记录删除成功";
redirect('view_records.php');
?>











