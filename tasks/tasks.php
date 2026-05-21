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

        // var_dump([$result->num_rows,    $transactions]);
        // die();

        if(!empty($transactions)) {
            foreach($transactions as $key => $row) {
                $currentId = $row['id'];
                $taskWorked++;
                $processedCount++;

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