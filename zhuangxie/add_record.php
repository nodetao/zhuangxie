<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$workers = $pdo->query("SELECT * FROM workers")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $worker_id = $_POST['worker_id'];
    $category_id = $_POST['category_id'];
    $quantity = (int)$_POST['quantity'];
    $product_name = trim($_POST['product_name']);
    $product_name = htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');
    
    $stmt = $pdo->prepare("SELECT price FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $price = $stmt->fetchColumn();
    $total_price = $price * $quantity;
    $recorded_by = $_SESSION['user_id'];
    
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
    /* 重置边距 */
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
    }
    
    /* 基础样式 */
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
    }
    
    .container {
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
        background: white;
    }
    
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    input[type="text"],
    input[type="number"],
    input[type="date"],
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }
    
    button:hover {
        background-color: #45a049;
    }
    
    /* 下拉菜单样式 */
    select {
        height: auto;
        min-height: 40px;
        overflow-y: auto;
    }
    
    select option {
        padding: 8px 10px;
        white-space: normal;
        height: auto;
        line-height: 1.5;
    }
    
    select:focus {
        min-height: auto;
        height: auto;
    }
    
    /* 响应式调整 */
    @media (min-width: 768px) {
        .form-container {
            padding: 30px;
        }
        
        button {
            width: auto;
            padding: 10px 30px;
        }
    }
</style>

<div class="container">
    <div class="form-container">
        <h2>登记新装卸记录</h2>
        <form method="post">
            <div class="form-group">
                <label for="date">日期</label>
                <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">品类</label>
                <select name="category_id" id="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-price="<?= $cat['price'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="product_name">商品名称</label>
                <input type="text" name="product_name" id="product_name" required placeholder="例如：iPhone 13, 实木餐桌, 东北大米等">
            </div>
            
            <div class="form-group">
                <label for="worker_id">公司</label>
                <select name="worker_id" id="worker_id" required>
                    <?php foreach ($workers as $worker): ?>
                        <option value="<?= $worker['id'] ?>"><?= htmlspecialchars($worker['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quantity">数量</label>
                <input type="number" name="quantity" id="quantity" min="1" required>
            </div>
            
            <div class="form-group">
                <label>费用估算</label>
                <input type="text" id="price_calc" value="¥0.00" readonly>
            </div>
            
            <button type="submit">提交登记</button>
        </form>
    </div>
</div>

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
        
        calculatePrice();
    });
</script>

<?php include 'includes/footer.php'; ?>
