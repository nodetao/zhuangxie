<?php
include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    $_SESSION['error'] = "无权访问此页面";
    redirect('dashboard.php');
}

$user_id = $_GET['id'] ?? 0;

// 获取用户信息
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "用户不存在";
    redirect('manage_users.php');
}

// 更新用户信息
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    
    // 验证用户名
    if (empty($username)) {
        $_SESSION['error'] = "用户名不能为空";
        redirect("edit_user.php?id=$user_id");
    }
    
    // 检查用户名是否已存在（排除当前用户）
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "用户名已存在";
        redirect("edit_user.php?id=$user_id");
    }
    
    // 更新密码（如果填写了新密码）
    $password_update = '';
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            $_SESSION['error'] = "密码至少需要6个字符";
            redirect("edit_user.php?id=$user_id");
        }
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ", password = :password";
    }
    
    // 执行更新
    $sql = "UPDATE users SET username = :username, role = :role $password_update WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':id', $user_id);
    
    if (!empty($_POST['password'])) {
        $stmt->bindParam(':password', $password);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "用户信息更新成功";
        redirect('manage_users.php');
    } else {
        $_SESSION['error'] = "更新用户信息失败";
        redirect("edit_user.php?id=$user_id");
    }
}

$page_title = "编辑用户";
$breadcrumb = "用户管理";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">编辑用户信息</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" class="form-control" 
                       value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>角色</label>
                <select name="role" class="form-control" required>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>普通用户</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>管理员</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>新密码（留空不修改）</label>
                <input type="password" name="password" class="form-control" 
                       placeholder="输入新密码">
                <small class="text-muted">至少6个字符</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 保存更改
                </button>
                <a href="manage_users.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 取消
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>