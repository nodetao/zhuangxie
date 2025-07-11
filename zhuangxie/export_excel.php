<?php
require 'vendor/autoload.php';
include 'includes/auth.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isLoggedIn()) {
    redirect('index.php');
}

// 获取查询参数
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// 创建新的Spreadsheet对象
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置表头样式
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4CAF50']
    ]
];

// 设置表头
$sheet->setCellValue('A1', '日期')
      ->setCellValue('B1', '品类')
      ->setCellValue('C1', '商品名称')
      ->setCellValue('D1', '数量')
      ->setCellValue('E1', '金额')
      ->setCellValue('F1', '公司')
      ->setCellValue('G1', '登记人');

// 应用表头样式
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// 查询数据 - 修复SQL语句（移除了PHP注释）
$sql = "SELECT 
            r.record_date,
            w.name AS worker_name,
            c.name AS category_name,
            r.product_name,
            r.quantity,
            r.total_price,
            u.username AS recorded_by
        FROM records r
        JOIN workers w ON r.worker_id = w.id
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.recorded_by = u.id
        WHERE r.record_date BETWEEN ? AND ?
        ORDER BY r.record_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 填充数据
$row = 2;
foreach ($records as $record) {
    $sheet->setCellValue('A'.$row, $record['record_date'])
          ->setCellValue('B'.$row, $record['category_name'])
          ->setCellValue('C'.$row, $record['product_name'])
          ->setCellValue('D'.$row, $record['quantity'])
          ->setCellValue('E'.$row, $record['total_price'])
          ->setCellValue('F'.$row, $record['worker_name'])
          ->setCellValue('G'.$row, $record['recorded_by']);
    
    // 设置金额格式
    $sheet->getStyle('F'.$row)
          ->getNumberFormat()
          ->setFormatCode('¥#,##0.00');
    
    $row++;
}

// 设置自动列宽
foreach (range('A', 'G') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// 设置冻结首行
$sheet->freezePane('A2');

// 设置文件名
$filename = "装卸明细_" . $start_date . "_至_" . $end_date . ".xlsx";

// 输出Excel文件
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'"');
header('Cache-Control: max-age=0');
header('Access-Control-Allow-Origin: *');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
