<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// 获取统计数据
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM records WHERE record_date = ?");
$stmt->execute([$today]);
$today_records = $stmt->fetchColumn();

$first_day = date('Y-m-01');
$last_day = date('Y-m-t');
$stmt = $pdo->prepare("SELECT SUM(total_price) FROM records WHERE record_date BETWEEN ? AND ?");
$stmt->execute([$first_day, $last_day]);
$month_total = $stmt->fetchColumn();

$categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$workers = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();

$page_title = "控制面板";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">系统概览</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">今日登记</span>
                        <span class="info-box-number"><?= $today_records ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-yen-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">本月费用</span>
                        <span class="info-box-number">¥<?= number_format($month_total, 2) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-tags"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">品类数量</span>
                        <span class="info-box-number"><?= $categories ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">公司数量</span>
                        <span class="info-box-number"><?= $workers ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">快捷操作</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="add_record.php" class="btn btn-primary btn-block">
                    <i class="fas fa-plus"></i> 登记费用
                </a>
            </div>
            <div class="col-md-3">
                <a href="view_records.php" class="btn btn-success btn-block">
                    <i class="fas fa-search"></i> 查询记录
                </a>
            </div>
            <?php if (isAdmin()): ?>
            <div class="col-md-3">
                <a href="manage_categories.php" class="btn btn-info btn-block">
                    <i class="fas fa-tags"></i> 管理品类
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage_workers.php" class="btn btn-warning btn-block">
                    <i class="fas fa-users-cog"></i> 管理公司
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>