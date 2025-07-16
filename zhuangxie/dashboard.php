<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$page_title = "控制面板";
include 'includes/header.php';
?>

<h2 class="page-title">系统概览</h2>

<div class="dashboard-grid">
    <!-- 今日登记 -->
    <div class="dashboard-card">
        <h4><i class="fas fa-calendar-day"></i> 今日登记</h4>
        <?php
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM records WHERE record_date = ?");
        $stmt->execute([$today]);
        $count = $stmt->fetchColumn();
        ?>
        <p class="stat-number"><?= $count ?></p>
        <p class="stat-label">条记录</p>
    </div>

    <!-- 本月费用 -->
    <div class="dashboard-card">
        <h4><i class="fas fa-yen-sign"></i> 本月费用</h4>
        <?php
        $first_day = date('Y-m-01');
        $last_day = date('Y-m-t');
        $stmt = $pdo->prepare("SELECT SUM(total_price) FROM records WHERE record_date BETWEEN ? AND ?");
        $stmt->execute([$first_day, $last_day]);
        $total = $stmt->fetchColumn();
        ?>
        <p class="stat-number">¥<?= number_format($total, 2) ?></p>
        <p class="stat-label">共计</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>