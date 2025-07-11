<?php
// 启用错误报告（安装完成后应关闭）
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

        // 导入SQL文件
        $sqlFile = __DIR__ . '/sql.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL文件不存在：{$sqlFile}");
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new Exception("无法读取SQL文件");
        }
        $pdo->exec($sql);

        // 创建管理员账户（密码哈希加密）
        $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `role`) VALUES (?, ?, 'admin')");
        $stmt->execute([$adminUser, $hashedPassword]);

        // 生成db.php配置文件（严格使用用户输入）
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
        // 记录详细错误日志
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
        input[type="text"], 
        input[type="password"] {
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
        .text-center { text-align: center; }
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
            <form method="post" onsubmit="return validateForm()">
                <h2>数据库配置</h2>
                
                <div class="form-group">
                    <label for="db_host">数据库主机：</label>
                    <input type="text" id="db_host" name="db_host" placeholder="通常为 localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">数据库名称：</label>
                    <input type="text" id="db_name" name="db_name" placeholder="请输入已创建的数据库名" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">数据库用户名：</label>
                    <input type="text" id="db_user" name="db_user" placeholder="数据库用户名" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">数据库密码：</label>
                    <input type="password" id="db_pass" name="db_pass" placeholder="数据库密码">
                </div>
                
                <h2>管理员账户</h2>
                
                <div class="form-group">
                    <label for="admin_user">管理员用户名：</label>
                    <input type="text" id="admin_user" name="admin_user" placeholder="用于登录系统的账号" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass">管理员密码：</label>
                    <input type="password" id="admin_pass" name="admin_pass" placeholder="建议使用强密码" required>
                </div>
                
                <div class="form-group" style="margin-top: 25px;">
                    <button type="submit">开始安装</button>
                </div>
            </form>
            
            <script>
                function validateForm() {
                    const dbName = document.getElementById('db_name').value.trim();
                    if (dbName.includes(' ')) {
                        alert('数据库名称不能包含空格');
                        return false;
                    }
                    return true;
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>