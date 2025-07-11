<?php
include 'db.php';

// 设置管理员账户信息
$username = '刘自涛'; // 管理员用户名
$password = 'admin123'; // 管理员密码（生产环境请使用强密码）

// 生成密码哈希
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // 插入管理员账户
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$username, $hashed_password]);
    
    echo "管理员账户创建成功！<br>";
    echo "用户名: $username<br>";
    echo "密码: $password<br>";
    echo "<strong style='color:red;'>请务必删除此文件！</strong>";
} catch (PDOException $e) {
    die("创建管理员账户失败: " . $e->getMessage());
}
?>