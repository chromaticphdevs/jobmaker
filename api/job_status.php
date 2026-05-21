<?php
    header('Content-Type: application/json');
    require_once dirname(__DIR__) . '/loader.private.php';

    $job_id = (int) ($_GET['job_id'] ?? 0);

    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Missing job_id']);
        exit;
    }

    $database = connectDB();
    $result   = $database->query(
        "SELECT id, status, processed_count, total_records FROM jobs WHERE id = {$job_id}"
    );

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Job not found']);
        exit;
    }

    $job      = $result->fetch_assoc();
    $total    = (int) $job['total_records'];
    $done     = (int) $job['processed_count'];
    $percent  = $total > 0 ? round(($done / $total) * 100) : 0;

    echo json_encode([
        'success'         => true,
        'job_id'          => (int) $job['id'],
        'status'          => $job['status'],
        'processed_count' => $done,
        'total_records'   => $total,
        'percent'         => $percent,
    ]);
?>
