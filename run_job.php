<?php
    require_once 'loader.private.php';
    if(!isset($_GET['job_id'])) {
        return false;
    }

    $jobId = $_GET['job_id'];

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
        case 'attendance_list':
            transaction_task($row);
        break;
    }
?>