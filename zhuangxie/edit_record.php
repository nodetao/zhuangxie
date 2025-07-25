<?php
include 'includes/auth.php';
include 'db.php';

if (!checkSessionTimeout() || !isLoggedIn()) {
    redirect('index.php');
}

refreshSession();

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

// 检查权限：管理员可以编辑所有记录，普通用户只能编辑自己的记录
if (!isAdmin() && $record['recorded_by'] != $_SESSION['user_id']) {
    echo "<script>alert('无权限操作'); window.location.href='view_records.php';</script>";
    exit;
}

// 获取所有品类和公司
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$workers = $pdo->query("SELECT * FROM workers")->fetchAll(PDO::FETCH_ASSOC);

// 更新记录
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $product_name = $_POST['product_name'];
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
                          product_name = ?,
                          worker_id = ?, 
                          category_id = ?, 
                          quantity = ?, 
                          total_price = ?
                          WHERE id = ?");
    $stmt->execute([$date, $product_name, $worker_id, $category_id, $quantity, $total_price, $record_id]);
    
    $_SESSION['success'] = "记录更新成功";
    redirect('view_records.php');
}

$page_title = "编辑记录";
$breadcrumb = "记录编辑";
include 'includes/header.php';
?>

<!-- 错误消息提示 -->
<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-triangle"></i>
    <?= $_SESSION['error'] ?>
    <?php unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-edit"></i> 编辑费用记录
    </h2>
    <p class="page-subtitle">修改装卸费用相关信息</p>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h3><i class="fas fa-pencil-alt"></i> 记录信息</h3>
        <span class="record-id">ID: #<?= $record['id'] ?></span>
    </div>
    <div class="form-card-body">
        <form method="post" class="modern-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="date">
                        <i class="fas fa-calendar-alt"></i> 日期
                    </label>
                    <input type="date" name="date" id="date" value="<?= $record['record_date'] ?>" 
                           class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">
                        <i class="fas fa-tags"></i> 品类
                    </label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">请选择品类</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                    data-price="<?= $cat['price'] ?>"
                                    <?= $cat['id'] == $record['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?> (¥<?= $cat['price'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="product_name">
                        <i class="fas fa-box"></i> 商品名称
                    </label>
                    <input type="text" name="product_name" id="product_name" 
                           value="<?= htmlspecialchars($record['product_name']) ?>"
                           class="form-input" required placeholder="请输入商品名称">
                </div>
                
                <div class="form-group">
                    <label for="worker_id">
                        <i class="fas fa-building"></i> 公司
                    </label>
                    <select name="worker_id" id="worker_id" class="form-select" required>
                        <option value="">请选择公司</option>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['id'] ?>" 
                                    <?= $worker['id'] == $record['worker_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($worker['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="quantity">
                        <i class="fas fa-sort-numeric-up"></i> 数量
                    </label>
                    <input type="number" name="quantity" id="quantity" min="1" 
                           value="<?= $record['quantity'] ?>"
                           class="form-input" required placeholder="请输入数量">
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-calculator"></i> 费用估算
                    </label>
                    <div class="price-display">
                        <input type="text" id="price_calc" value="¥<?= number_format($record['total_price'], 2) ?>" 
                               class="form-input price-input" readonly>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> 保存修改
                </button>
                <a href="view_records.php" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> 返回列表
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    border: none;
    display: flex;
    align-items: center;
    font-weight: 500;
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert i {
    margin-right: 10px;
    font-size: 16px;
}

.page-header {
    text-align: center;
    margin-bottom: 30px;
}

.page-title {
    font-size: 28px;
    color: #2f54eb;
    margin-bottom: 8px;
    font-weight: 600;
}

.page-subtitle {
    color: #6c757d;
    font-size: 16px;
    margin: 0;
}

.form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.form-card-header {
    background: linear-gradient(135deg, #2f54eb 0%, #4e73df 100%);
    color: white;
    padding: 25px 30px;
    border-bottom: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.form-card-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.form-card-header i {
    margin-right: 10px;
}

.record-id {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 14px;
    font-weight: 500;
}

.form-card-body {
    padding: 40px 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
}

.form-group label i {
    margin-right: 8px;
    color: #2f54eb;
    width: 16px;
}

.form-input, .form-select {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fafafa;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: #2f54eb;
    background: white;
    box-shadow: 0 0 0 3px rgba(47, 84, 235, 0.1);
}

.price-display {
    position: relative;
}

.price-input {
    background: linear-gradient(135deg, #f4f9ff, #e9f0fb) !important;
    font-weight: 600;
    color: #1d39c4;
    text-align: center;
    font-size: 16px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 35px;
    padding-top: 25px;
    border-top: 1px solid #f0f0f0;
}

.btn-submit {
    background: linear-gradient(135deg, #2f54eb 0%, #4e73df 100%);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(47, 84, 235, 0.3);
}

.btn-cancel {
    background: #6c757d;
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-2px);
    color: white;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-card-body {
        padding: 25px 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-card-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const quantityInput = document.getElementById('quantity');
    const priceCalc = document.getElementById('price_calc');
    
    function calculatePrice() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const total = price * quantity;
        priceCalc.value = `¥${total.toFixed(2)}`;
    }
    
    categorySelect.addEventListener('change', calculatePrice);
    quantityInput.addEventListener('input', calculatePrice);
    
    // 初始计算
    calculatePrice();
});
</script>

<?php include 'includes/footer.php'; ?>












