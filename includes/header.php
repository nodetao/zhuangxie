<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '装卸管理系统'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, 'Microsoft YaHei', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #495057;
            line-height: 1.6;
            font-size: 14px;
        }
        
        .sidebar {
            width: 220px;
            background: #343a40;
            min-height: 100vh;
            position: fixed;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        /* 其他样式保持不变... */
    </style>
</head>
<body>
    <!-- 侧边栏导航 -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>装卸管理系统</h1>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <?php echo $_SESSION['username']; ?>
                <span style="color: #adb5bd;">(<?php echo $_SESSION['role'] == 'admin' ? '管理员' : '用户'; ?>)</span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>控制面板</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="manage_categories.php"><i class="fas fa-tags"></i>品类管理</a></li>
                <li><a href="manage_workers.php"><i class="fas fa-users-cog"></i>工人管理</a></li>
                <li><a href="manage_users.php"><i class="fas fa-user-shield"></i>用户管理</a></li>
            <?php endif; ?>
            <li><a href="add_record.php"><i class="fas fa-clipboard-list"></i>登记费用</a></li>
            <li><a href="view_records.php"><i class="fas fa-search"></i>查询记录</a></li>
            <li style="margin-top: 20px;"><a href="logout.php"><i class="fas fa-sign-out-alt"></i>退出系统</a></li>
        </ul>
    </div>
    
    <!-- 主内容区 -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h2><?php echo $page_title ?? '装卸管理系统'; ?></h2>
                <div class="breadcrumb">
                    <a href="dashboard.php"><i class="fas fa-home"></i> 首页</a> 
                    <?php if (isset($breadcrumb)): ?>
                        &rsaquo; <?php echo $breadcrumb; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (basename($_SERVER['PHP_SELF']) != 'dashboard.php'): ?>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> 返回
                </a>
            <?php endif; ?>
        </div>
        <?php displayMessages(); ?>