<?php
if (!isset($pdo)) {
    $host = 'localhost';
    $dbname = 'zhuangxie';
    $username = 'zhuangxie';
    $password = 'XIEhuo0723#';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_TIMEOUT => 10,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]);
    } catch (PDOException $e) {
        error_log("数据库连接失败: " . $e->getMessage());
        die("系统暂时不可用，请稍后再试");
    }
}
?>


