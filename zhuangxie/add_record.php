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
    // 新增：接收商品名称并过滤
    $product_name = trim($_POST['product_name']);
    $product_name = htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');
    
    // 获取单价并计算总价
    $stmt = $pdo->prepare("SELECT price FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $price = $stmt->fetchColumn();
    $total_price = $price * $quantity;
    $recorded_by = $_SESSION['user_id'];
    
    // 修改：在INSERT语句中添加product_name
    $stmt = $pdo->prepare("INSERT INTO records (record_date, worker_id, category_id, quantity, total_price, recorded_by, product_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $worker_id, $category_id, $quantity, $total_price, $recorded_by, $product_name]);
    
    $_SESSION['success'] = "登记成功！";
    redirect('view_records.php');
}

$page_title = "登记装卸费用";
$breadcrumb = "费用登记";
include 'includes/header.php';
?>

<style>
    /* 紧凑表单样式 */
    .compact-form .form-group {
        margin-bottom: 15px;
    }
    
    .compact-form .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .compact-form .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .compact-form .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        padding: 8px 15px; /* 减少内边距 */
        font-weight: 600;
        border-radius: 4px;
        transition: all 0.3s;
    }
    
    .compact-form .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
        transform: translateY(-2px);
    }
    
    .compact-form label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #5a5c69;
    }
    
    .card-header {
        background-color: #4e73df;
        color: white;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .card-title {
        font-weight: 700;
        font-size: 1.25rem;
    }
    
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .form-row {
        margin-right: -5px;
        margin-left: -5px;
    }
    
    .form-row > [class*="col-"] {
        padding-right: 5px;
        padding-left: 5px;
    }
    
    /* 按钮容器优化 */
    .button-container {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
    }
    
    /* 按钮优化 */
    .submit-btn {
        margin-top: 25px;
        padding: 8px 15px; /* 减少内边距 */
        font-size: 0.9rem; /* 减小字体大小 */
        width: auto; /* 自动宽度 */
        max-width: 150px; /* 最大宽度限制 */
    }
    
    /* 按钮内图标调整 */
    .submit-btn i {
        margin-right: 5px; /* 减小图标间距 */
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">登记新装卸记录</h3>
    </div>
    <div class="card-body">
        <form method="post" class="compact-form">
            <!-- 第一行：日期、品类、商品名称 -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>日期</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control form-control-sm" required>
                </div>
                <div class="form-group col-md-4">
                    <label>品类</label>
                    <select name="category_id" class="form-control form-control-sm" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?> (¥<?= $cat['price'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>商品名称</label>
                    <input type="text" name="product_name" class="form-control form-control-sm" required placeholder="请输入商品名称">
                </div>
            </div>
            
            <!-- 第二行：数量、公司、提交按钮 -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>数量</label>
                    <input type="number" name="quantity" min="1" class="form-control form-control-sm" required>
                </div>
                <div class="form-group col-md-4">
                    <label>公司</label>
                    <select name="worker_id" class="form-control form-control-sm" required>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['id'] ?>"><?= htmlspecialchars($worker['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4 button-container">
                    <button type="submit" class="btn btn-primary submit-btn">
                        <i class="fas fa-save"></i> 提交
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
