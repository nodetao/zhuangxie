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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">新增记录</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="date">日期</label>
                    <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" 
                           class="form-control" required>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="category_id">品类</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-price="<?= $cat['price'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="product_name">商品名称</label>
                    <input type="text" name="product_name" id="product_name" class="form-control" 
                           required placeholder="商品名称">
                </div>
                
                <div class="form-group col-md-6">
                    <label for="worker_id">公司</label>
                    <select name="worker_id" id="worker_id" class="form-control" required>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['id'] ?>"><?= htmlspecialchars($worker['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="quantity">数量</label>
                    <input type="number" name="quantity" id="quantity" min="1" 
                           class="form-control" required>
                </div>
                
                <div class="form-group col-md-6">
                    <label>费用估算</label>
                    <input type="text" id="price_calc" value="0.00" 
                           class="form-control" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <div class="form-group col-md-8">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 提交登记
                    </button>
                </div>
            </div>
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
