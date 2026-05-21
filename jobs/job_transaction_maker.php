<?php
    $limit = 500;
    $sql = "SELECT * FROM jobs
        WHERE job_key = 'transaction'
        ";

    $result = $sql;

    if($result > 0) {
        $lastId = $result->last_id;
    }else{
        $lastId = 0;
    }

    $sql = " SELECT * FROM transactions
        WHERE id > {$lastId}
        ORDER BY id asc LIMIT {$limit}";

    $result = $sql;

    return [
        'first_id' => $result[0]->id,
        'last_id'  => end($result->id)
    ];
?>