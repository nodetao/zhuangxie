<?php
// ==================== 会话安全设置 ====================
// 3天有效期（秒）
$session_duration = 259200; // 60*60*24*3

// 设置会话参数
ini_set('session.gc_maxlifetime', $session_duration);  // 服务器端会话有效期
ini_set('session.cookie_lifetime', $session_duration); // 浏览器Cookie有效期

// 降低垃圾回收概率（减少会话被提前清理的机会）
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1000);  // 0.1%的概率触发GC

// 自定义会话存储路径（避免共享主机清理）
$customSessionPath = __DIR__ . '/sessions';
if (!is_dir($customSessionPath)) {
    mkdir($customSessionPath, 0700, true);
}
ini_set('session.save_path', $customSessionPath);

// 增强会话安全设置
ini_set('session.cookie_secure', true);    // 仅通过HTTPS传输
ini_set('session.cookie_httponly', true);  // 禁止JavaScript访问
ini_set('session.cookie_samesite', 'Lax'); // 防止CSRF攻击
ini_set('session.use_strict_mode', true);  // 防止会话固定攻击

// 启动会话
session_start();

// 检查会话是否过期（3天无活动）
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_duration)) {
    // 会话过期，销毁会话并重定向
    session_unset();
    session_destroy();
    
    // 清除会话cookie
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
    
    header("Location: login.php?expired=1");
    exit();
}

// 更新最后活动时间
$_SESSION['last_activity'] = time();

// 定期更新会话ID（每24小时）
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 86400) { // 24小时
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// 检查用户是否已登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if (!isset($page_title)) $page_title = '后台系统';

// 检查用户是否为管理员
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        html {
            overflow-y: scroll;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f4f9ff, #e9f0fb);
            color: #333;
            font-size: 14px; /* 全局字体大小 */
        }

        /* 表格字体调整 */
        .table {
            font-size: 13px;
        }

        header {
            background: #2f54eb;
            color: white;
            padding: 16px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            height: 70px; /* 固定高度防止跳动 */
        }

        .logo-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .nav-links a {
            color: white;
            margin-left: 24px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: #cddfff;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }

        h2.page-title {
            font-size: 22px;
            margin-bottom: 20px;
            border-left: 5px solid #2f54eb;
            padding-left: 12px;
            color: #2f54eb;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
            text-decoration: none;
        }

        .btn i {
            margin-right: 6px;
        }

        .btn-add {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
        }

        .btn-add:hover {
            background: linear-gradient(to right, #5a00c5, #1f63e0);
        }

        .btn-edit {
            background-color: #f59e0b;
            color: white;
        }

        .btn-edit:hover {
            background-color: #d97706;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }

        .welcome {
            font-size: 14px;
            margin-left: 16px;
        }

        .logout-link {
            color: #ffcccc;
            text-decoration: none;
            margin-left: 10px;
            font-weight: 500;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        .top-right {
            display: flex;
            align-items: center;
        }
        
        /* 添加dashboard中的卡片样式 */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .dashboard-card {
            background: linear-gradient(145deg, #f5f7fa, #e4eaf1);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            text-align: center;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transform: translateY(-4px);
        }

        .dashboard-card h4 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #333;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            color: #4a00e0;
            margin: 0;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #777;
        }
        
        /* 添加表格页面样式 */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eaeef5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #2f54eb;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #2f54eb, #1d39c4);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #1d39c4, #2f54eb);
        }
        
        .btn-excel {
            background: linear-gradient(to right, #0d9e4e, #0c8040);
            color: white;
        }
        
        .btn-excel:hover {
            background: linear-gradient(to right, #0c8040, #0d9e4e);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eaeef5;
        }
        
        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #333;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            text-decoration: none;
        }
        
        .btn-edit {
            background-color: #f59e0b;
        }
        
        .btn-delete {
            background-color: #ef4444;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        /* ================= 新增样式 ================= */
        /* 表格中公司名称列优化 */
        .company-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 自定义列宽 */
        .form-group.col-date {
            flex: 0 0 16.66667%; /* 约等于col-md-2 */
            max-width: 16.66667%;
        }

        .form-group.col-company {
            flex: 0 0 41.66667%; /* 约等于col-md-5 */
            max-width: 41.66667%;
        }

        /* 按钮列样式 */
        .form-group.col-button {
            flex: 0 0 25%; /* 约等于col-md-3 */
            max-width: 25%;
            align-self: flex-end;
        }

        /* 响应式调整 - 小屏幕优化 */
        @media (max-width: 768px) {
            .form-group.col-date,
            .form-group.col-company,
            .form-group.col-button {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .form-group.col-button {
                margin-top: 15px;
            }
            
            .wider-select {
                min-width: 100%;
            }
        }

        /* 操作按钮样式 */
        .action-buttons {
            display: flex;
            justify-content: space-around;
        }

        .btn-action {
            font-size: 13px;
            padding: 5px 8px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .btn-action-edit {
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }

        .btn-action-edit:hover {
            background-color: rgba(40, 167, 69, 0.2);
        }

        .btn-action-delete {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .btn-action-delete:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }

        /* 分页样式 */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }

        .pagination-info {
            color: #666;
            font-size: 14px;
        }

        .pagination-custom {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pagination-custom .page-item {
            display: inline-block;
        }

        .pagination-custom .page-link {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            color: #4e73df;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .pagination-custom .page-link:hover {
            background: #f5f7ff;
            border-color: #c0c9f0;
        }

        .pagination-custom .page-item.disabled .page-link {
            color: #aaa;
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .pagination-custom .page-item.active .page-link {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
            font-weight: 500;
        }

        .page-divider {
            color: #666;
            padding: 0 5px;
        }
        
        /* 增加下拉框宽度和选项显示 */
        .wider-select {
            width: 100%;
            min-width: 250px; /* 确保最小宽度 */
        }

        /* 确保下拉选项完整显示 */
        .wider-select option {
            white-space: normal; /* 允许多行显示 */
            padding: 8px 12px;  /* 增加内边距 */
        }
    </style>
    
    <!-- 添加会话保持脚本 -->
    <script>
        // 每5分钟发送一次心跳请求（300000毫秒）
        document.addEventListener('DOMContentLoaded', function() {
            function keepSessionAlive() {
                fetch('/session-keepalive.php', {
                    method: 'HEAD',
                    credentials: 'include' // 包含cookie
                }).catch(error => {
                    console.error('心跳请求失败:', error);
                });
            }
            
            // 初始发送一次
            keepSessionAlive();
            
            // 设置定时器
            setInterval(keepSessionAlive, 300000); // 5分钟
            
            // 用户活动时也发送心跳（鼠标移动、点击、键盘输入）
            ['mousemove', 'keydown', 'click', 'scroll'].forEach(event => {
                window.addEventListener(event, keepSessionAlive, { passive: true });
            });
        });
    </script>
</head>
<body>

<header>
    <div class="logo-title">后台管理系统</div>
    <div class="top-right">
        <nav class="nav-links">
            <a href="dashboard.php">首页</a>
            <a href="add_record.php">添加记录</a>
            <a href="view_records.php">查看记录</a>
            <?php if ($isAdmin): ?>
                <a href="manage_users.php">用户管理</a>
                <a href="manage_workers.php">公司管理</a>
                <a href="manage_categories.php">品类管理</a>
            <?php endif; ?>
        </nav>
        <div class="welcome">
            欢迎您，<?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> 退出</a>
        </div>
    </div>
</header>

<!-- 页面内容容器开始 -->
<div class="container">
