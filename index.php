<?php
    require_once 'loader.private.php';
    $database = connectDB();
    $jobsSQL = "SELECT * FROM jobs order by id asc";
    $jobsQuer = $database->query($jobsSQL);
    $jobs = [];

    if($jobsQuer->num_rows > 0) {
        while($row = $jobsQuer->fetch_assoc()) {
            $jobs[] = $row;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <table border="1px" cellpadding="10">
        <thead>
            <th>#</th>
            <th>Token</th>
            <th>Table</th>
            <th>Records</th>
            <th>Records Processed</th>
            <th>Status</th>
            <th>Action</th>
        </thead>

        <tbody id="content">
            <?php foreach($jobs as $key => $row) :?>
                <tr>
                    <td><?php echo ++$key?></td>
                    <td><?php echo  $row['token']?></td>
                    <td><?php echo  $row['table_name']?></td>
                    <td><?php echo  $row['total_records']?></td>
                    <td><?php echo  $row['processed_count']?></td>
                    <td><?php echo  $row['status']?></td>
                    <td>
                        <a href="run_job.php?job_id=<?php echo $row['id']?>">RUN</a>
                    </td>
                </tr>
            <?php endforeach?>
        </tbody>
    </table>
</body>
</html>