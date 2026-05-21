<?php
    function service_order_task($job_record) {
        $database = connectDB();

        $currentId      = $job_record['current_id'] ?? $job_record['first_id'];
        $lastId         = $job_record['last_id'];
        $processedCount = (int) ($job_record['processed_count'] ?? 0);
        $zip_path       = $job_record['zip_url'] . '/' . $job_record['zip_name'];
        $job_id         = $job_record['id'];

        if ($currentId == $lastId) {
            exit('items are already processed');
        }

        $database->query("UPDATE jobs SET status = 'in-progress' WHERE id = '{$job_id}'");

        // use > not >= so a resumed job doesn't reprocess the last completed record
        $result = $database->query("
            SELECT * FROM service_order_items
                WHERE id > {$currentId}
                AND id <= {$lastId}
            ORDER BY id ASC
        ");

        if ($result->num_rows == 0) {
            exit;
        }

        $pending_images = [];  // relative paths to zip in current chunk
        $missing_logs   = [];  // batch log inserts for missing files
        $chunkCount     = 0;

        while ($row = $result->fetch_assoc()) {
            $currentId = $row['id'];
            $processedCount++;
            $chunkCount++;

            //find images

            $findImagesQuery = $database->query(
                "SELECT  * FROM module_meta where meta_key = 'SERVICE_ORDER_ITEMS'
                    AND meta_id = '{$currentId}'"
            );

            if($findImagesQuery->num_rows > 0) {
                while($imageOBJ = $findImagesQuery->fetch_assoc()) {
                    $relative  = ltrim(trim($imageOBJ['meta_value']), '/');
                    $full_path = SOURCE_UPLOADS_DIR . '/' . $relative;

                    if (file_exists($full_path)) {
                        $pending_images[] = $relative;
                    } else {
                        $missing_logs[] = "('{$job_id}', '{$currentId}', 'warning', 'File not found: {$full_path}')";
                    }
                }
            }

            // flush every TASK_MAX_LIMIT records: one zip exec + one DB update
            if ($chunkCount % TASK_MAX_LIMIT === 0) {
                if (!empty($pending_images)) {
                    zip_add_files_batch($zip_path, $pending_images);
                    $pending_images = [];
                }

                if (!empty($missing_logs)) {
                    $database->query("INSERT INTO job_logs (job_id, record_id, type, message) VALUES " . implode(',', $missing_logs));
                    $missing_logs = [];
                }

                $database->query("UPDATE jobs SET processed_count = '{$processedCount}', current_id = '{$currentId}' WHERE id = '{$job_id}'");
            }
        }

        // flush remaining rows that didn't fill a full chunk
        if (!empty($pending_images)) {
            zip_add_files_batch($zip_path, $pending_images);
        }

        if (!empty($missing_logs)) {
            $database->query("INSERT INTO job_logs (job_id, record_id, type, message) VALUES " . implode(',', $missing_logs));
        }

        $finished = ($currentId == $lastId);
        $database->query("UPDATE jobs
            SET status = '" . ($finished ? 'finished' : 'pending') . "',
                processed_count = '{$processedCount}',
                current_id = '{$currentId}'
            WHERE id = '{$job_id}'
        ");
    }
?>
