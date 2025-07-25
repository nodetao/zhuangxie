<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isLoggedIn()) {
    redirect('index.php');
}

refreshSession();

// 处理密码修改
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // 验证当前密码
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "当前密码错误";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = "新密码至少需要6个字符";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "两次输入的新密码不一致";
    } else {
        // 更新密码
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
            $_SESSION['success'] = "密码修改成功";
        } else {
            $_SESSION['error'] = "密码修改失败，请重试";
        }
    }
}

// 获取用户信息
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "个人中心";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user"></i> 个人信息</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><strong>用户名：</strong></label>
                    <p class="form-control-static"><?= htmlspecialchars($user_info['username']) ?></p>
                </div>
                <div class="form-group">
                    <label><strong>角色：</strong></label>
                    <p class="form-control-static">
                        <?= $user_info['role'] == 'admin' ? '管理员' : '普通用户' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-key"></i> 修改密码</h3>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>当前密码</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>新密码</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <small class="text-muted">至少6个字符</small>
                    </div>
                    
                    <div class="form-group">
                        <label>确认新密码</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 修改密码
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回首页
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: none;
}

.alert-danger {
    background-color: #fee;
    color: #c53030;
    border-left: 4px solid #e53e3e;
}

.alert-success {
    background-color: #f0fff4;
    color: #22543d;
    border-left: 4px solid #38a169;
}

.form-control-static {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    color: #495057;
}
</style>

<?php include 'includes/footer.php'; ?>