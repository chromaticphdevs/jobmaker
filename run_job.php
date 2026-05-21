<?php
    require_once __DIR__ . '/loader.private.php';

    // support both browser (?job_id=) and CLI (--job_id=)
    if (php_sapi_name() === 'cli') {
        $opts  = getopt('', ['job_id:']);
        $jobId = $opts['job_id'] ?? null;
    } else {
        $jobId = $_GET['job_id'] ?? null;
    }

    if (!$jobId) {
        exit('no job_id provided');
    }

    $database = connectDB();

    $sql = "
        SELECT * FROM jobs
            WHERE id = '{$jobId}' 
    ";

    $result = $database->query($sql);

    if ($result->num_rows == 0) {
        echo 'Job not found';
        exit;
    }

    $row = $result->fetch_assoc();

    switch($row['job_key']) {
        case 'transactions':
            transaction_task($row);
        break;
    }
?>