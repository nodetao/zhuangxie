<?php
include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

// 添加用户
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);
    $_SESSION['success'] = "用户添加成功";
}

// 获取所有用户及关联记录数
$stmt = $pdo->query("SELECT u.*, 
    (SELECT COUNT(*) FROM records WHERE recorded_by = u.id) AS records_count 
    FROM users u ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "用户管理";
$breadcrumb = "用户管理";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">用户列表</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>角色</th>
                        <th>关联记录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= $user['role'] == 'admin' ? '管理员' : '普通用户' ?></td>
                        <td><?= $user['records_count'] ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> 编辑
                            </a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmDelete('用户', <?= $user['records_count'] ?>)">
                                   <i class="fas fa-trash"></i> 删除
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">添加新用户</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>用户名</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>密码</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>角色</label>
                    <select name="role" class="form-control" required>
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" name="add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加用户
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>