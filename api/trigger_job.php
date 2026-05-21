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

    if ($job['status'] === 'in-progress') {
        echo json_encode(['success' => false, 'message' => 'Job is already running']);
        exit;
    }

    if ($job['status'] === 'finished') {
        echo json_encode(['success' => false, 'message' => 'Job is already finished']);
        exit;
    }

    $php    = PHP_CLI;
    $script = realpath(dirname(__DIR__) . '/run_job.php');

    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = sprintf('cmd /C start /B "" "%s" "%s" --job_id=%d > NUL 2>&1', $php, $script, $job_id);
        pclose(popen($cmd, 'r'));
    } else {
        $cmd = sprintf('"%s" "%s" --job_id=%d > /dev/null 2>&1 &', $php, $script, $job_id);
        shell_exec($cmd);
    }

    echo json_encode(['success' => true, 'job_id' => $job_id]);
?>
