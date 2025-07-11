<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// 设置页面标题
$page_title = "控制面板";
$breadcrumb = "控制面板";

include 'includes/header.php';
?>

<h3>系统概览</h3>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
    <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.05);">
        <h4>今日登记</h4>
        <?php
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM records WHERE record_date = ?");
        $stmt->execute([$today]);
        $count = $stmt->fetchColumn();
        ?>
        <p style="font-size: 2rem; font-weight: bold;"><?= $count ?></p>
        <p>条记录</p>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.05);">
        <h4>本月费用</h4>
        <?php
        $first_day = date('Y-m-01');
        $last_day = date('Y-m-t');
        $stmt = $pdo->prepare("SELECT SUM(total_price) FROM records WHERE record_date BETWEEN ? AND ?");
        $stmt->execute([$first_day, $last_day]);
        $total = $stmt->fetchColumn();
        ?>
        <p style="font-size: 2rem; font-weight: bold;">¥<?= number_format($total, 2) ?></p>
        <p>总金额</p>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.05);">
        <h4>系统统计</h4>
        <?php
        $categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $workers = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
        ?>
        <p>品类: <?= $categories ?> 种</p>
        <p>公司: <?= $workers ?> 个</p>
    </div>
</div>

<h3 style="margin-top: 30px;">快捷操作</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
    <a href="add_record.php" class="btn" style="text-align: center;">登记费用</a>
    <a href="view_records.php" class="btn" style="text-align: center;">查询记录</a>
    
    <?php if (isAdmin()): ?>
        <a href="manage_categories.php" class="btn" style="text-align: center;">管理品类</a>
        <a href="manage_workers.php" class="btn" style="text-align: center;">管理公司</a>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>