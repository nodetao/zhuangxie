<?php
include 'includes/auth.php';
include 'db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// 获取查询参数
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// 设置CSV文件名
$filename = "装卸记录_" . $start_date . "_" . $end_date . ".csv";

// 查询数据
$sql = "SELECT 
            r.record_date AS '日期',
            w.name AS '装卸工人',
            c.name AS '品类',
            r.quantity AS '数量',
            CONCAT('¥', FORMAT(r.total_price, 2)) AS '总费用',
            u.username AS '登记人'
        FROM records r
        JOIN workers w ON r.worker_id = w.id
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.recorded_by = u.id
        WHERE r.record_date BETWEEN ? AND ?
        ORDER BY r.record_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 设置CSV头
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");

// 打开输出流
$output = fopen("php://output", "w");

// 添加UTF-8 BOM头，解决中文乱码
fwrite($output, "\xEF\xBB\xBF");

// 输出表头
if (count($records) > 0) {
    // 输出列标题
    fputcsv($output, array_keys($records[0]));
    
    // 输出数据行
    foreach ($records as $row) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;