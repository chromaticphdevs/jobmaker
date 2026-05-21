<?php
    require_once 'core/database.php';
    require_once 'core/conn.php';
    require_once 'core/job_maker.php';

    $tableName = $_GET['table_name'];
    $jobKey = $_GET['job_key'];
    $limit = $_GET['limit'];


    //tables
    $database = connectDB();
    
    $result = get_recent_job_by_key($jobKey);

    if(!$result) {
        $lastId = $result['last_id'];
    } else {
        $lastId = 0;
    }

    $result = $database->query(
        "SELECT * FROM {$tableName}
            WHERE id > {$lastId}
            ORDER BY id asc
            LIMIT 500"
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

    $date = date('Y-m-d h:i:s');
    $token = strtotime($date);
    $zipName = $tableName.'_'.$token.'.zip';
    $zipURL  =  '/home/konduefk/migration_digital_ocean/';
    job_maker($token, $jobKey, $tableName, $firstId, $lastId, $zipName, $zipURL);
?>