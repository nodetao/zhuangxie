<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// 获取所有公司列表
$workers = $pdo->query("SELECT * FROM workers")->fetchAll(PDO::FETCH_ASSOC);

// 设置默认日期范围和公司筛选
$start_date = date('Y-m-d', strtotime('-7 days'));
$end_date = date('Y-m-d');
$selected_worker = 'all'; // 默认选择所有公司

// 分页设置
$records_per_page = 30; // 每页显示30条记录
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $selected_worker = $_POST['worker_id'];
    $current_page = 1; // 提交查询时重置到第一页
    $offset = 0;
}

// 构建基础查询语句
$base_sql = "SELECT r.id, r.record_date, w.name AS worker_name, 
               c.name AS category_name, r.quantity, 
               r.total_price, u.username AS recorded_by,
               r.product_name
        FROM records r
        JOIN workers w ON r.worker_id = w.id
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.recorded_by = u.id
        WHERE r.record_date BETWEEN ? AND ?";

// 添加公司筛选条件
$params = [$start_date, $end_date];
if ($selected_worker != 'all') {
    $base_sql .= " AND r.worker_id = ?";
    $params[] = $selected_worker;
}

// 获取总记录数
$count_sql = "SELECT COUNT(*) AS total_records FROM ($base_sql) AS base_query";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

// 计算总页数
$total_pages = ceil($total_records / $records_per_page);

// 添加排序和分页
$sql = $base_sql . " ORDER BY r.record_date DESC LIMIT $records_per_page OFFSET $offset";

// 执行查询
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "装卸记录查询";
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">查询条件</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-row">
                <!-- 开始日期 -->
                <div class="form-group col-date">
                    <label>开始日期</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control" required>
                </div>
                
                <!-- 结束日期 -->
                <div class="form-group col-date">
                    <label>结束日期</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control" required>
                </div>
                
                <!-- 公司筛选 - 增加宽度 -->
                <div class="form-group col-company">
                    <label>公司筛选</label>
                    <select name="worker_id" class="form-control wider-select">
                        <option value="all" <?= $selected_worker == 'all' ? 'selected' : '' ?>>所有公司</option>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['id'] ?>" <?= $selected_worker == $worker['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($worker['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 查询按钮 -->
                <div class="form-group col-button">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> 查询
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">查询结果</h3>
        <div>
            <a href="export_excel.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&worker_id=<?= urlencode($selected_worker) ?>" 
               class="btn btn-excel">
               <i class="fas fa-file-excel"></i> 导出Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th>商品名称</th>
                        <th>品类</th>
                        <th>公司名称</th>
                        <th>数量</th>
                        <th>金额</th>
                        <th>登记人</th>
                        <?php if (isAdmin()): ?>
                            <th>操作</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= $record['record_date'] ?></td>
                        <td><?= htmlspecialchars($record['product_name']) ?></td>
                        <td><?= htmlspecialchars($record['category_name']) ?></td>
                        <td class="company-cell" title="<?= htmlspecialchars($record['worker_name']) ?>">
                            <?= htmlspecialchars($record['worker_name']) ?>
                        </td>
                        <td><?= $record['quantity'] ?></td>
                        <td>¥<?= number_format($record['total_price'], 2) ?></td>
                        <td><?= $record['recorded_by'] ?></td>
                        <?php if (isAdmin()): ?>
                            <td class="action-buttons">
                                <a href="edit_record.php?id=<?= $record['id'] ?>" 
                                   class="btn-action btn-action-edit" title="编辑">
                                   <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_record.php?id=<?= $record['id'] ?>" 
                                   class="btn-action btn-action-delete" 
                                   title="删除"
                                   onclick="return confirm('确定删除这条记录吗？')">
                                   <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="<?= isAdmin() ? 8 : 7 ?>" class="text-center">
                                <i class="fas fa-info-circle"></i> 没有找到符合条件的记录
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 分页信息与导航 -->
        <?php if ($total_records > 0): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                共 <?= $total_records ?> 条记录
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination-custom">
                <!-- 上一页 -->
                <div class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $current_page - 1)])) ?>">
                        上一页
                    </a>
                </div>
                
                <!-- 当前页码和总页数 -->
                <div class="page-item active">
                    <span class="page-link">
                        <?= $current_page ?>
                    </span>
                </div>
                
                <span class="page-divider">/</span>
                
                <div class="page-item">
                    <span class="page-link" style="border: none; background: transparent;">
                        <?= $total_pages ?>
                    </span>
                </div>
                
                <!-- 下一页 -->
                <div class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => min($total_pages, $current_page + 1)])) ?>">
                        下一页
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>