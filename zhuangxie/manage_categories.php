<?php
include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

// 添加或更新品类
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];

    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, price) VALUES (?, ?)");
        $stmt->execute([$name, $price]);
        $_SESSION['success'] = "品类添加成功";
    } elseif (isset($_POST['update']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, price = ? WHERE id = ?");
        $stmt->execute([$name, $price, $id]);
        $_SESSION['success'] = "品类更新成功";
    }
}

// 获取当前编辑的品类
$editing = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有品类及关联记录数
$stmt = $pdo->query("SELECT c.*, 
    (SELECT COUNT(*) FROM records WHERE category_id = c.id) AS records_count 
    FROM categories c ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "品类管理";
$breadcrumb = "品类管理";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $editing ? '编辑品类' : '添加品类' ?></h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label>名称</label>
                <input type="text" name="name" class="form-control" required value="<?= $editing ? htmlspecialchars($editing['name']) : '' ?>">
            </div>
            <div class="form-group">
                <label>单价 (元)</label>
                <input type="number" name="price" step="0.01" class="form-control" required value="<?= $editing ? $editing['price'] : '' ?>">
            </div>
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= $editing['id'] ?>">
                <button type="submit" name="update" class="btn btn-primary">更新</button>
                <a href="manage_categories.php" class="btn btn-secondary">取消</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn btn-success">添加</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">品类列表</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
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
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><?= $cat['price'] ?></td>
                        <td><?= $cat['records_count'] ?> 条</td>
                        <td>
                            <a href="manage_categories.php?edit_id=<?= $cat['id'] ?>" class="btn-action" title="编辑">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_category.php?id=<?= $cat['id'] ?>" 
                               class="btn-action text-danger" 
                               title="删除"
                               onclick="return confirmDelete('品类', <?= $cat['records_count'] ?>)">
                               <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
