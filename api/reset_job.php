<?php
    header('Content-Type: application/json');
    require_once dirname(__DIR__) . '/loader.private.php';

    $job_id = (int) ($_POST['job_id'] ?? 0);

    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'Missing job_id']);
        exit;
    }

    $database = connectDB();
    $result   = $database->query("SELECT id, status FROM jobs WHERE id = {$job_id}");

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Job not found']);
        exit;
    }

    $job = $result->fetch_assoc();

    if ($job['status'] !== 'in-progress') {
        echo json_encode(['success' => false, 'message' => 'Only stuck in-progress jobs can be reset']);
        exit;
    }

    $database->query("UPDATE jobs SET status = 'pending' WHERE id = {$job_id}");

    echo json_encode(['success' => true, 'job_id' => $job_id, 'message' => 'Job reset to pending — you can now continue it']);
?>
