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

<style>
    /* 控制面板样式 */
    .dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .dashboard h3 {
        color: #2c3e50;
        font-size: 1.8rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ecf0f1;
    }
    
    /* 统计卡片样式 */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    
    .stat-card h4 {
        color: #7f8c8d;
        font-size: 1.2rem;
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2c3e50;
        margin: 10px 0;
    }
    
    .stat-label {
        color: #7f8c8d;
        font-size: 1rem;
    }
    
    /* 快捷操作按钮 */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .quick-btn {
        display: block;
        background: #3498db;
        color: white;
        text-align: center;
        padding: 15px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .quick-btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }
    
    /* 响应式调整 */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<div class="dashboard">
    <h3>系统概览</h3>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h4>今日登记</h4>
            <?php
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM records WHERE record_date = ?");
            $stmt->execute([$today]);
            $count = $stmt->fetchColumn();
            ?>
            <div class="stat-value"><?= $count ?></div>
            <div class="stat-label">条记录</div>
        </div>
        
        <div class="stat-card">
            <h4>本月费用</h4>
            <?php
            $first_day = date('Y-m-01');
            $last_day = date('Y-m-t');
            $stmt = $pdo->prepare("SELECT SUM(total_price) FROM records WHERE record_date BETWEEN ? AND ?");
            $stmt->execute([$first_day, $last_day]);
            $total = $stmt->fetchColumn();
            ?>
            <div class="stat-value">¥<?= number_format($total, 2) ?></div>
            <div class="stat-label">总金额</div>
        </div>
        
        <div class="stat-card">
            <h4>系统统计</h4>
            <?php
            $categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            $workers = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
            ?>
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <div class="stat-value" style="font-size: 1.8rem;"><?= $categories ?></div>
                    <div class="stat-label">品类</div>
                </div>
                <div>
                    <div class="stat-value" style="font-size: 1.8rem;"><?= $workers ?></div>
                    <div class="stat-label">公司</div>
                </div>
            </div>
        </div>
    </div>
    
    <h3>快捷操作</h3>
    <div class="quick-actions">
        <a href="add_record.php" class="quick-btn">登记费用</a>
        <a href="view_records.php" class="quick-btn">查询记录</a>
        
        <?php if (isAdmin()): ?>
            <a href="manage_categories.php" class="quick-btn">管理品类</a>
            <a href="manage_workers.php" class="quick-btn">管理公司</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
