<?php
include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

// 添加工人
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    
    $stmt = $pdo->prepare("INSERT INTO workers (name) VALUES (?)");
    $stmt->execute([$name]);
    $_SESSION['success'] = "工人添加成功";
}

// 获取所有工人及关联记录数
$stmt = $pdo->query("SELECT w.*, 
    (SELECT COUNT(*) FROM records WHERE worker_id = w.id) AS records_count 
    FROM workers w ORDER BY id DESC");
$workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "工人管理";
$breadcrumb = "工人管理";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">工人列表</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>关联记录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workers as $worker): ?>
                    <tr>
                        <td><?= $worker['id'] ?></td>
                        <td><?= htmlspecialchars($worker['name']) ?></td>
                        <td><?= $worker['records_count'] ?></td>
                        <td>
                            <a href="delete_worker.php?id=<?= $worker['id'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirmDelete('工人', <?= $worker['records_count'] ?>)">
                               <i class="fas fa-trash"></i> 删除
                            </a>
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
        <h3 class="card-title">添加工人</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label>工人姓名</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" name="add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加工人
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>