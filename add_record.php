<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// 获取所有品类和工人
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$workers = $pdo->query("SELECT * FROM workers")->fetchAll(PDO::FETCH_ASSOC);

// 添加记录
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $worker_id = $_POST['worker_id'];
    $category_id = $_POST['category_id'];
    $quantity = (int)$_POST['quantity'];
    
    // 获取单价并计算总价
    $stmt = $pdo->prepare("SELECT price FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $price = $stmt->fetchColumn();
    $total_price = $price * $quantity;
    $recorded_by = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO records (record_date, worker_id, category_id, quantity, total_price, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $worker_id, $category_id, $quantity, $total_price, $recorded_by]);
    
    $_SESSION['success'] = "登记成功！";
    redirect('view_records.php');
}

$page_title = "登记装卸费用";
$breadcrumb = "费用登记";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">登记新装卸记录</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>日期</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>装卸工人</label>
                    <select name="worker_id" class="form-control" required>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['id'] ?>"><?= htmlspecialchars($worker['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>品类</label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?> (¥<?= $cat['price'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>数量</label>
                    <input type="number" name="quantity" min="1" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 提交登记
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>