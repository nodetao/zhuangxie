<?php
// 添加微小延迟防止请求过快
usleep(100000); // 0.1秒延迟

// 防止缓存问题
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

// 防止重复提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';
    if (!hash_equals($_SESSION['token'] ?? '', $token)) {
        $_SESSION['error'] = "无效的请求";
        header("Location: manage_workers.php");
        exit();
    }
    unset($_SESSION['token']);
}

// 添加公司
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO workers (name) VALUES (?)");
            $stmt->execute([$name]);
            $_SESSION['success'] = "公司添加成功";
        } catch (PDOException $e) {
            $_SESSION['error'] = "添加失败: " . $e->getMessage();
        }
    }
    header("Location: manage_workers.php");
    exit();
}

// 更新公司
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("UPDATE workers SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            $_SESSION['success'] = "公司更新成功";
        } catch (PDOException $e) {
            $_SESSION['error'] = "更新失败: " . $e->getMessage();
        }
    }
    header("Location: manage_workers.php");
    exit();
}

// 获取所有公司及记录数量
try {
    $stmt = $pdo->query("SELECT w.*, 
        (SELECT COUNT(*) FROM records WHERE worker_id = w.id) AS records_count 
        FROM workers w ORDER BY id DESC");
    $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "数据加载失败: " . $e->getMessage();
    $workers = [];
}

$editing_id = $_GET['edit'] ?? null;

$page_title = "公司管理";
$breadcrumb = "公司管理";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">公司列表</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>公司名称</th>
                        <th>关联记录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workers as $worker): ?>
                        <?php if ($editing_id == $worker['id']): ?>
                        <tr>
                            <form method="post">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <td><?= $worker['id'] ?>
                                    <input type="hidden" name="id" value="<?= $worker['id'] ?>">
                                </td>
                                <td>
                                    <input type="text" name="name" value="<?= htmlspecialchars($worker['name']) ?>" class="form-control" required>
                                </td>
                                <td><?= $worker['records_count'] ?> 条</td>
                                <td>
                                    <button type="submit" name="update" class="btn btn-sm btn-success me-1">
                                        <i class="fas fa-check"></i> 保存
                                    </button>
                                    <a href="manage_workers.php" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-times"></i> 取消
                                    </a>
                                </td>
                            </form>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td><?= $worker['id'] ?></td>
                            <td><?= htmlspecialchars($worker['name']) ?></td>
                            <td><?= $worker['records_count'] ?> 条</td>
                            <td>
                                <a href="manage_workers.php?edit=<?= $worker['id'] ?>" 
                                   class="btn-action btn-action-edit" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_worker.php?id=<?= $worker['id'] ?>" 
                                   class="btn-action btn-action-delete" title="删除"
                                   onclick="return confirmDelete('公司', <?= $worker['records_count'] ?>)">
                                   <i class="fas fa-trash-alt"></i>
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
        <h3 class="card-title">添加公司</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>公司名称</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="form-group mt-3">
                <button type="submit" name="add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加公司
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let isSubmitting = false;

function confirmDelete(type, recordCount) {
    if (isSubmitting) {
        return false;
    }
    
    let result;
    if (recordCount > 0) {
        result = confirm(`该${type}还有 ${recordCount} 条关联记录，确定删除吗？删除后相关记录也会被删除。`);
    } else {
        result = confirm(`确定删除该${type}吗？`);
    }
    
    if (result) {
        isSubmitting = true;
        setTimeout(() => { isSubmitting = false; }, 3000);
    }
    
    return result;
}

// 防止表单重复提交
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;
            setTimeout(() => { isSubmitting = false; }, 3000);
        });
    });
});
</script>



