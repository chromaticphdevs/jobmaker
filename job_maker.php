<?php
    require_once 'loader.private.php';
    $tableName = $_GET['table_name'];
    $jobKey = $_GET['job_key'];
    $limit = $_GET['limit'] ?? LIMIT;


    //tables
    $database = connectDB();
    
    $result = get_recent_job_by_key($jobKey);

    if($result) {
        $lastId = $result['last_id'];
    } else {
        $lastId = 0;
    }

    $limit = LIMIT;
    $result = $database->query(
        "SELECT * FROM {$tableName}
            WHERE id > {$lastId}
            ORDER BY id asc
            LIMIT {$limit}"
    );

    $tableItems = [];
    
    if($result->num_rows > 0) {
         while($row = $result->fetch_assoc()) {
            $tableItems[] = $row;
        }
    }

    if(!$tableItems) {
        echo "There are no more items to be added into jobs";
        die();
    }

    $lastId = end($tableItems)['id'];
    $firstId = $tableItems[0]['id'];

    $token = round(microtime(true) * 1000);
    $zipName = $tableName.'_'.$token.'.zip';
    $zipURL  =  '/home/konduefk/migration_digital_ocean/';
    job_maker($token, $jobKey, $tableName, $firstId, $lastId, count($tableItems), $zipName, $zipURL);
?>