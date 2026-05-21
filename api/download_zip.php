<?php
    require_once dirname(__DIR__) . '/loader.private.php';

    $job_id = (int) ($_GET['job_id'] ?? 0);

    if (!$job_id) {
        http_response_code(400);
        exit('Missing job_id');
    }

    $database = connectDB();
    $result   = $database->query("SELECT * FROM jobs WHERE id = {$job_id}");

    if ($result->num_rows === 0) {
        http_response_code(404);
        exit('Job not found');
    }

    $job = $result->fetch_assoc();

    if ($job['status'] !== 'finished') {
        http_response_code(400);
        exit('Job is not finished yet');
    }

    $zip_path = $job['zip_url'] . '/' . $job['zip_name'];

    if (!file_exists($zip_path)) {
        http_response_code(404);
        exit('Zip file not found on disk');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
    header('Content-Length: ' . filesize($zip_path));
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($zip_path);
    exit;
?>
