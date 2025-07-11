<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '装卸管理系统'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* 基础样式重置 */
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
        
        /* 侧边导航栏 */
        .sidebar {
            width: 220px;
            background: #343a40;
            min-height: 100vh;
            position: fixed;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #4b545c;
            margin-bottom: 15px;
        }
        
        .sidebar-header h1 {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .user-info {
            color: #adb5bd;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        
        .user-info i {
            margin-right: 8px;
        }
        
        /* 导航菜单 */
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 3px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: #dee2e6;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .sidebar-menu a i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
            font-size: 0.95rem;
        }
        
        .sidebar-menu a:hover {
            background: #495057;
            color: #fff;
        }
        
        .sidebar-menu .active {
            background: #495057;
            color: #fff;
            border-left: 3px solid #4e73df;
        }
        
        /* 主内容区 */
        .main-content {
            margin-left: 220px;
            padding: 25px;
            background: #fff;
            min-height: 100vh;
        }
        
        /* 页面标题区 */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .page-header h2 {
            color: #343a40;
            font-size: 1.5rem;
            font-weight: 500;
        }
        
        .breadcrumb {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .breadcrumb a {
            color: #4e73df;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        /* 消息提示 */
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* 表格样式 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.85rem;
            box-shadow: 0 0 5px rgba(0,0,0,0.03);
        }
        
        table th {
            background: #f8f9fa;
            padding: 10px 12px;
            text-align: left;
            font-weight: 500;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        table td {
            padding: 8px 12px;
            border-top: 1px solid #dee2e6;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* 表单样式 */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            height: 38px;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        /* 按钮样式 */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }
        
        .btn i {
            margin-right: 8px;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: #4e73df;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a56c0;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-excel {
            background-color: #1cc88a !important;
            color: white;
        }
        
        .btn-excel:hover {
            background-color: #17a673 !important;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background-color: #e74a3b;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #d52a1a;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: #858796;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #6c757d;
            transform: translateY(-1px);
        }
        
        /* 操作按钮组 */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        /* 圆形图标按钮 */
        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 3px;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background-color: #4e73df;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #3a56c0;
            transform: scale(1.1);
        }
        
        .btn-delete {
            background-color: #e74a3b;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #d52a1a;
            transform: scale(1.1);
        }
        
        /* 卡片式布局 */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            margin-bottom: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            body {
                font-size: 13px;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .page-header .btn {
                margin-top: 10px;
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- 左侧边栏 -->
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
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tachometer-alt"></i>控制面板
                </a>
            </li>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li>
                    <a href="manage_categories.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-tags"></i>品类管理
                    </a>
                </li>
                <li>
                    <a href="manage_workers.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_workers.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-users-cog"></i>公司管理
                    </a>
                </li>
                <li>
                    <a href="manage_users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-user-shield"></i>用户管理
                    </a>
                </li>
            <?php endif; ?>
            
            <li>
                <a href="add_record.php" <?php echo basename($_SERVER['PHP_SELF']) == 'add_record.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-clipboard-list"></i>登记费用
                </a>
            </li>
            <li>
                <a href="view_records.php" <?php echo basename($_SERVER['PHP_SELF']) == 'view_records.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-search"></i>查询记录
                </a>
            </li>
            
            <li style="margin-top: 20px; border-top: 1px solid #4b545c; padding-top: 10px;">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>退出系统
                </a>
            </li>
        </ul>
    </div>
    
    <!-- 主内容区域 -->
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
        
        <!-- 消息提示 -->
        <?php if (function_exists('displayMessages')) displayMessages(); ?>
<script>
// 增强型删除确认
function confirmDelete(item, recordsCount) {
    if (recordsCount > 0) {
        return confirm(`警告：将删除该${item}及其${recordsCount}条关联记录！\n\n此操作不可撤销，您确定要继续吗？`);
    }
    return confirm(`确定删除该${item}吗？`);
}
</script>