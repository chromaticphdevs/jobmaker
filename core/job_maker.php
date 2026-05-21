<?php
    function job_maker(
        $token, $job_key, $table_name, $first_id,
        $last_id, $zip_name, $zip_url
    ) {
        $database = connectDB();

        $sql = "INSERT INTO jobs(
            token, job_key, table_name, first_id, last_id, current_id,
            status, zip_name, zip_url, processed_count
        )VALUES('{$token}', '{$job_key}', '{$table_name}', '{$first_id}', '{$last_id}', 
            '{$zip_name}', '{$zip_url}')";

        if($database->query($sql) === TRUE) {
            echo "Job {$job_key} created #{$token}";
        } else {
            echo "Job {$job_key} create failed";
        }
    }

    function get_recent_job_by_key($key) {
        $database = connectDB();

        $result = $database->query("SELECT * FROM jobs
            where job_key = '{$key}' ORDER BY id desc");

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
?>