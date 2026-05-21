<?php

    /*
        CREATE TABLE IF NOT EXISTS job_logs (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            job_id      INT NOT NULL,
            record_id   INT DEFAULT NULL,
            type        ENUM('error','warning','info') NOT NULL DEFAULT 'error',
            message     TEXT NOT NULL,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    */

    // writes to jobmaker/logs/app.log
    function write_log($message, $level = 'ERROR') {
        $log_dir  = dirname(__DIR__) . '/logs';
        $log_file = $log_dir . '/app.log';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $line = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . PHP_EOL;
        file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
    }

    function log_job($job_id, $message, $record_id = null, $type = 'error') {
        $database = connectDB();

        $job_id    = (int) $job_id;
        $record_id = $record_id !== null ? (int) $record_id : 'NULL';
        $type      = $database->real_escape_string($type);
        $message   = $database->real_escape_string($message);

        $sql = "INSERT INTO job_logs (job_id, record_id, type, message)
                VALUES ({$job_id}, {$record_id}, '{$type}', '{$message}')";

        $database->query($sql);
    }
?>
