<?php

    function run_pending_jobs($job_key = null) {
        $database = connectDB();
        
        if(!is_null($job_key)) {
            $sql = "SELECT * FROM jobs WHERE job_key = {$job_key}
            ORDER BY id asc";
        } else {
            $sql = "SELECT * FROM jobs ";
        }
    }