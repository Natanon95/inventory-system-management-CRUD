<?php
require_once __DIR__ . '/../../bootstrap.php';
Auth::check();

$db   = Database::getInstance();
$type = $_GET['type'] ?? '';

$allowed = ['products', 'stock_movements', 'low_stock', 'inventory_value'];
if (!in_array($type, $allowed, true)) {
    http_response_code(400);
    die('Invalid export type.');
}

$filename = $type . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');

// UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

function csvRow(mixed $handle, array $row): void {
    fputcsv($handle, $row);
}

switch ($type) {
    case 'products':
        csvRow($out, ['SKU', 'Name', 'Category', 'Description', 'Price', 'Stock Qty', 'Low Stock Threshold', 'Status', 'Created At']);
        $rows = $db->query("
            SELECT p.sku, p.name, c.name AS category, p.description,
                   p.price, p.stock_qty, p.low_stock_threshold,
                   IF(p.is_active,'Active','Inactive') AS status, p.created_at
            FROM   products p JOIN categories c ON c.id=p.category_id
            ORDER  BY c.name, p.name
        ")->fetchAll();
        foreach ($rows as $r) csvRow($out, array_values($r));
        break;

    case 'stock_movements':
        csvRow($out, ['Date', 'SKU', 'Product', 'Type', 'Quantity', 'Note', 'Recorded By']);
        $rows = $db->query("
            SELECT sm.created_at, p.sku, p.name, sm.type, sm.quantity, sm.note, u.full_name
            FROM   stock_movements sm
            JOIN   products p ON p.id=sm.product_id
            JOIN   users    u ON u.id=sm.user_id
            ORDER  BY sm.created_at DESC
        ")->fetchAll();
        foreach ($rows as $r) csvRow($out, array_values($r));
        break;

    case 'low_stock':
        csvRow($out, ['SKU', 'Name', 'Category', 'Current Stock', 'Threshold', 'Shortage']);
        $rows = $db->query("
            SELECT p.sku, p.name, c.name AS category,
                   p.stock_qty, p.low_stock_threshold,
                   (p.low_stock_threshold - p.stock_qty) AS shortage
            FROM   products p JOIN categories c ON c.id=p.category_id
            WHERE  p.stock_qty <= p.low_stock_threshold AND p.is_active=1
            ORDER  BY shortage DESC
        ")->fetchAll();
        foreach ($rows as $r) csvRow($out, array_values($r));
        break;

    case 'inventory_value':
        csvRow($out, ['SKU', 'Name', 'Category', 'Unit Price', 'Stock Qty', 'Total Value']);
        $rows = $db->query("
            SELECT p.sku, p.name, c.name AS category,
                   p.price, p.stock_qty, (p.price * p.stock_qty) AS total_value
            FROM   products p JOIN categories c ON c.id=p.category_id
            WHERE  p.is_active=1
            ORDER  BY total_value DESC
        ")->fetchAll();
        foreach ($rows as $r) csvRow($out, array_values($r));
        break;
}

fclose($out);
