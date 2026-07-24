<?php
header('Content-Type: application/json');
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->query("SELECT id, name, industry, thumbnail FROM site_templates WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 6");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'templates' => $data, 'count' => count($data)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
