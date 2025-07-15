<?php
require 'vendor/autoload.php';
include 'includes/auth.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if (!isLoggedIn()) {
    redirect('index.php');
}

// 获取查询参数
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')); // 起始日期筛选参数
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // 终止日期筛选参数
$worker_id = $_GET['worker_id'] ?? ''; // 公司筛选参数

// 创建新的Spreadsheet对象
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置文档属性
$spreadsheet->getProperties()
    ->setCreator("装卸管理系统")
    ->setTitle("费用明细表")
    ->setSubject($start_date . "至" . $end_date . "卸货记录");

// 设置表头样式
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4CAF50']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];

// 设置标题
$sheet->setCellValue('A1', '费用明细表 (' . $start_date . ' 至 ' . $end_date . ')');
$sheet->mergeCells('A1:G1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 设置表头
$sheet->setCellValue('A2', '日期')
      ->setCellValue('B2', '品类')
      ->setCellValue('C2', '商品名称')
      ->setCellValue('D2', '数量')
      ->setCellValue('E2', '金额')
      ->setCellValue('F2', '公司名称')
      ->setCellValue('G2', '登记人');

// 应用表头样式
$sheet->getStyle('A2:G2')->applyFromArray($headerStyle);

// 查询数据 - 添加公司筛选条件
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
        WHERE r.record_date BETWEEN ? AND ?";

$params = [$start_date, $end_date];

// 添加公司筛选条件
if (!empty($worker_id)) {
    $sql .= " AND r.worker_id = ?";
    $params[] = $worker_id;
}

$sql .= " ORDER BY r.record_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 填充数据
$row = 3;
foreach ($records as $record) {
    $sheet->setCellValue('A'.$row, $record['record_date'])
          ->setCellValue('B'.$row, $record['category_name'])
          ->setCellValue('C'.$row, $record['product_name'])
          ->setCellValue('D'.$row, $record['quantity'])
          ->setCellValue('E'.$row, $record['total_price'])
          ->setCellValue('F'.$row, $record['worker_name'])
          ->setCellValue('G'.$row, $record['recorded_by']);
    
    // 设置金额格式
    $sheet->getStyle('E'.$row)
          ->getNumberFormat()
          ->setFormatCode('¥#,##0.00');
    
    $row++;
}

// 设置自动列宽
foreach (range('A', 'G') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// 设置单元格样式
$sheet->getStyle('D3:D'.($row-1))
      ->getNumberFormat()
      ->setFormatCode('#,##0');
      
$sheet->getStyle('A3:A'.($row-1))
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);
      
$sheet->getStyle('D3:D'.($row-1))
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      
$sheet->getStyle('E3:E'.($row-1))
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// 添加边框
$lastRow = $row - 1;
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];
$sheet->getStyle('A2:G'.$lastRow)->applyFromArray($borderStyle);

// 设置冻结首行
$sheet->freezePane('A3');

// 设置文件名
$filename = "卸货明细_" . $start_date . "_至_" . $end_date;
if (!empty($worker_id)) {
    // 获取公司名称用于文件名
    $worker_sql = "SELECT name FROM workers WHERE id = ?";
    $worker_stmt = $pdo->prepare($worker_sql);
    $worker_stmt->execute([$worker_id]);
    $worker_name = $worker_stmt->fetchColumn();
    $filename .= "_" . $worker_name;
}
$filename .= ".xlsx";

// 输出Excel文件
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'"');
header('Cache-Control: max-age=0');
header('Access-Control-Allow-Origin: *');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
