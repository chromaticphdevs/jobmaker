<?php
    //function for transactions
    function transaction_task($job_record) {
        $database = connectDB();
        
        $currentId = $job_record['current_id'] ?? $job_record['first_id'];
        $lastId = $job_record['last_id'];
        $processedCount = $job_record['processed_count'] ?? 0;

        if($currentId == $job_record['last_id']) {
            echo 'items are already processed';
            exit;
        }
        //work on first id
        $taskWorked = 0;
        
        $updateStatusSQL = "UPDATE jobs 
            set status = 'in-progress'
                WHERE id = '{$job_record['id']}' ";

        $database->query($updateStatusSQL);

        $sql = "
            SELECT * FROM transactions
                WHERE id >= {$currentId}
                AND id <=  {$lastId}            
        ";

        $result = $database->query($sql);

        if($result->num_rows == 0) {
            exit;
        }

        $transactions = [];
        //  && ($taskWorked <= TASK_MAX_LIMIT)
        while($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }


        if(!empty($transactions)) {
            foreach($transactions as $key => $row) {
                $currentId = $row['id'];
                $taskWorked++;
                $processedCount++;

                // resolve the image from the main domain's upload directory
                // and add it into this job's zip at /home/konduefk/migrations/{zip_name}
                $zip_path = $job_record['zip_url'] . '/' . $job_record['zip_name'];

                $images = !empty($row['images']) ? explode(',', $row['images']) : [];

                if(!empty($images)) {
                    foreach($images as $imageKey => $image) {
                        $relative_image   = $image;
                        $full_image_path  = SOURCE_UPLOADS_DIR . '/' . ltrim($relative_image, '/');

                        if (file_exists($full_image_path)) {
                            // zip_add_file creates the archive automatically if it does not exist yet
                            zip_add_file($zip_path, $full_image_path, $relative_image);
                        } else {
                            log_job(
                                $job_record['id'],
                                "File not found: {$full_image_path}",
                                $currentId,
                                'warning'
                            );
                        }
                    }
                }
                

                $updateStatusSQL = "UPDATE jobs 
                    set processed_count = '{$processedCount}',
                    current_id = '{$currentId}'
                    WHERE id = '{$job_record['id']}'
                ";
                $database->query($updateStatusSQL);
            }

            if($currentId == $job_record['last_id']) {
            //finished
                $updateStatusSQL = "UPDATE jobs 
                    set status = 'finished'
                        WHERE id = '{$job_record['id']}'
                ";

                $database->query($updateStatusSQL);
            } else {
                $updateStatusSQL = "UPDATE jobs 
                    set status = 'pending'
                        WHERE id = '{$job_record['id']}'
                ";
                $database->query($updateStatusSQL);
            }
        }
    }
?>