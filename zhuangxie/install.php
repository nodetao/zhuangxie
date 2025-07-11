<?php
// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 安装锁定检查
if (file_exists('db.php')) {
    die('<h2>系统已安装</h2>如需重新安装，请先删除根目录下的 db.php 文件');
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取用户输入（无默认值）
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = trim($_POST['db_pass'] ?? '');
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass = trim($_POST['admin_pass'] ?? '');

    // 验证必填字段
    $required = [
        '数据库主机' => $dbHost,
        '数据库名称' => $dbName,
        '数据库用户名' => $dbUser,
        '管理员账号' => $adminUser,
        '管理员密码' => $adminPass
    ];

    foreach ($required as $field => $value) {
        if (empty($value)) {
            die("错误：{$field}不能为空");
        }
    }

    try {
        // 测试数据库连接
        $dsn = "mysql:host={$dbHost};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // 创建数据库（如果不存在）
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `{$dbName}`");

        // 生成SQL文件内容（不包含CREATE DATABASE语句）
        $sqlContent = <<<SQL
-- 品类表
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 工人表
CREATE TABLE IF NOT EXISTS `workers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 用户表
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 记录表
CREATE TABLE IF NOT EXISTS `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_date` date NOT NULL,
  `worker_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL COMMENT '商品名称',
  PRIMARY KEY (`id`),
  KEY `worker_id` (`worker_id`),
  KEY `category_id` (`category_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `records_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`),
  CONSTRAINT `records_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `records_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

        // 创建SQL文件
        if (file_put_contents('sql.sql', $sqlContent) === false) {
            throw new Exception("无法创建SQL文件，请检查目录写入权限");
        }

        // 执行SQL文件
        $pdo->exec($sqlContent);

        // 创建管理员账户
        $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `role`) VALUES (?, ?, 'admin')");
        $stmt->execute([$adminUser, $hashedPassword]);

        // 生成db.php配置文件
        $dbConfig = <<<PHP
<?php
\$host = '{$dbHost}';
\$dbname = '{$dbName}';
\$username = '{$dbUser}';
\$password = '{$dbPass}';

try {
    \$pdo = new PDO("mysql:host=\$host;dbname=\$dbname;charset=utf8mb4", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    die("数据库连接失败: " . \$e->getMessage());
}
PHP;

        if (file_put_contents('db.php', $dbConfig) === false) {
            throw new Exception("无法创建配置文件，请检查目录写入权限");
        }

        // 安装成功
        $success = true;
    } catch (Exception $e) {
        $error = "安装失败: " . $e->getMessage();
        file_put_contents('install_error.log', date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统安装向导</title>
    <style>
        body { font-family: 'Microsoft YaHei', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;
            box-sizing: border-box; font-size: 16px;
        }
        button {
            background: #4CAF50; color: white; border: none; padding: 12px 20px;
            width: 100%; font-size: 16px; border-radius: 4px; cursor: pointer;
        }
        button:hover { background: #45a049; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统安装向导</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <strong>错误：</strong> <?= htmlspecialchars($error) ?>
                <?php if (file_exists('install_error.log')): ?>
                    <div style="margin-top:10px; font-size:12px;">
                        详细错误请查看 install_error.log 文件
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <strong>安装成功！</strong>
                <div style="margin-top:15px;">
                    <a href="index.php" style="color: #2e7d32; font-weight: bold;">点击进入系统</a>
                </div>
                <div style="margin-top:10px; font-size:12px; color: #555;">
                    安全提示：请立即删除 install.php 文件
                </div>
            </div>
        <?php else: ?>
            <form method="post">
                <h2>数据库配置</h2>
                
                <div class="form-group">
                    <label for="db_host">数据库主机：</label>
                    <input type="text" id="db_host" name="db_host" placeholder="通常为 localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">数据库名称：</label>
                    <input type="text" id="db_name" name="db_name" placeholder="请输入要创建的数据库名称" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">数据库用户名：</label>
                    <input type="text" id="db_user" name="db_user" placeholder="请输入数据库用户名" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">数据库密码：</label>
                    <input type="password" id="db_pass" name="db_pass" placeholder="请输入数据库密码">
                </div>
                
                <h2>管理员账户</h2>
                
                <div class="form-group">
                    <label for="admin_user">管理员用户名：</label>
                    <input type="text" id="admin_user" name="admin_user" placeholder="设置管理员账号" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass">管理员密码：</label>
                    <input type="password" id="admin_pass" name="admin_pass" placeholder="设置管理员密码" required>
                </div>
                
                <div class="form-group" style="margin-top: 25px;">
                    <button type="submit">开始安装</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
