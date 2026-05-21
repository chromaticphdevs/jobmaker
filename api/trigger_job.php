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
        $log_dir = dirname(__DIR__) . '/logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        $log = $log_dir . '/cli.log';

        // test 1: can PHP write directly to the log?
        $php_write_ok = file_put_contents($log, "[php_write_test] ok\n", FILE_APPEND) !== false;

        // test 2: can exec run anything?
        exec('echo exec_works >> ' . escapeshellarg($log) . ' 2>&1', $out, $exec_code);

        // nohup + < /dev/null + & fully detaches the process from Apache
        $cmd = sprintf(
            'nohup "%s" "%s" --job_id=%d < /dev/null >> %s 2>&1 &',
            $php, $script, $job_id, escapeshellarg($log)
        );
        exec($cmd, $spawn_out, $spawn_code);

        // return debug info so we can see what worked
        echo json_encode([
            'success'        => true,
            'job_id'         => $job_id,
            'php_write_ok'   => $php_write_ok,
            'exec_code'      => $exec_code,
            'spawn_code'     => $spawn_code,
            'cmd'            => $cmd,
            'log'            => $log,
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'job_id' => $job_id]);
?>
