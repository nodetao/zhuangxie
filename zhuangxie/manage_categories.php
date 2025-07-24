<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isAdmin()) {
    redirect('dashboard.php');
}

refreshSession();

// 添加品类
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stmt = $pdo->prepare("INSERT INTO categories (name, price) VALUES (?, ?)");
    $stmt->execute([$name, $price]);
    $_SESSION['success'] = "品类添加成功";
    redirect('manage_categories.php');
}

// 更新品类
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stmt = $pdo->prepare("UPDATE categories SET name = ?, price = ? WHERE id = ?");
    $stmt->execute([$name, $price, $id]);
    $_SESSION['success'] = "品类更新成功";
    redirect('manage_categories.php');
}

// 获取所有品类及关联记录数
$stmt = $pdo->query("SELECT c.*, 
    (SELECT COUNT(*) FROM records WHERE category_id = c.id) AS records_count 
    FROM categories c ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 检查是否正在编辑
$editing_id = $_GET['edit'] ?? null;

$page_title = "品类管理";
$breadcrumb = "品类管理";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">品类列表</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>单价 (元)</th>
                        <th>关联记录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <?php if ($editing_id == $cat['id']): ?>
                        <tr>
                            <form method="post">
                                <td><?= $cat['id'] ?><input type="hidden" name="id" value="<?= $cat['id'] ?>"></td>
                                <td><input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" class="form-control" required></td>
                                <td><input type="number" name="price" step="0.01" value="<?= $cat['price'] ?>" class="form-control" required></td>
                                <td><?= $cat['records_count'] ?> 条</td>
                                <td>
                                    <button type="submit" name="update" class="btn btn-sm btn-success me-1">
                                        <i class="fas fa-check"></i> 保存
                                    </button>
                                    <a href="manage_categories.php" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-times"></i> 取消
                                    </a>
                                </td>
                            </form>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td><?= $cat['price'] ?></td>
                            <td><?= $cat['records_count'] ?> 条</td>
                            <td>
                                <a href="manage_categories.php?edit=<?= $cat['id'] ?>" 
                                   class="btn-action btn-action-edit" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_category.php?id=<?= $cat['id'] ?>" 
                                   class="btn-action btn-action-delete" 
                                   title="删除" 
                                   onclick="return confirmDelete('品类', <?= $cat['records_count'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">添加新品类</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>品类名称</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label>单价 (元)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
            </div>
            <div class="form-group mt-3">
                <button type="submit" name="add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加品类
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

