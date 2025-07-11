<?php
include 'includes/auth.php';
include 'db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

// 获取记录ID
$record_id = $_GET['id'] ?? 0;

// 获取记录详情
$stmt = $pdo->prepare("SELECT * FROM records WHERE id = ?");
$stmt->execute([$record_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "记录不存在";
    redirect('view_records.php');
}

// 获取所有品类和工人
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$workers = $pdo->query("SELECT * FROM workers")->fetchAll(PDO::FETCH_ASSOC);

// 更新记录
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $worker_id = $_POST['worker_id'];
    $category_id = $_POST['category_id'];
    $quantity = $_POST['quantity'];
    
    // 获取单价并计算总价
    $stmt = $pdo->prepare("SELECT price FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $price = $stmt->fetchColumn();
    $total_price = $price * $quantity;
    
    // 更新记录
    $stmt = $pdo->prepare("UPDATE records SET 
                          record_date = ?, 
                          worker_id = ?, 
                          category_id = ?, 
                          quantity = ?, 
                          total_price = ?
                          WHERE id = ?");
    $stmt->execute([$date, $worker_id, $category_id, $quantity, $total_price, $record_id]);
    
    $_SESSION['success'] = "记录更新成功";
    redirect('view_records.php');
}

$page_title = "编辑记录";
$breadcrumb = "记录编辑";
include 'includes/header.php';
?>

<h2>编辑装卸记录</h2>

<form method="post" class="form-container">
    <div class="form-group">
        <label>日期</label>
        <input type="date" name="date" value="<?= $record['record_date'] ?>" required>
    </div>
    <div class="form-group">
        <label>装卸工人</label>
        <select name="worker_id" required>
            <?php foreach ($workers as $worker): ?>
                <option value="<?= $worker['id'] ?>" <?= $worker['id'] == $record['worker_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($worker['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>品类</label>
        <select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $record['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?> (¥<?= $cat['price'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>数量</label>
        <input type="number" name="quantity" min="1" value="<?= $record['quantity'] ?>" required>
    </div>
    <div class="form-group">
        <button type="submit" class="btn">保存修改</button>
        <a href="view_records.php" class="btn btn-secondary">取消</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>