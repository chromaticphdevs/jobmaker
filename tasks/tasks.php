<?php
    //function for transactions
    function transaction_task($job_record) {
        $randomImages = [
            '558346808_3690487074421040_5320401992379599338_n.jpg',
            '558830692_3690486137754467_3222860675773350342_n.jpg',
            '558880904_3689869151149499_1679012280577624642_n.jpg',
            '558889701_3689868924482855_5340670669411132526_n.jpg',
        ];
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
            SELECT * FROM hr_time_sheets
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

                $relative_image   = $randomImages[rand(0, 3)];
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