<?php
    require_once 'loader.private.php';
    $database = connectDB();
    $jobsSQL  = "SELECT * FROM jobs order by id asc";
    $jobsQuer = $database->query($jobsSQL);
    $jobs     = [];

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
    <title>Job Maker</title>
    <style>
        button.run-btn { cursor: pointer; }
        button.run-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .progress-bar-wrap { background: #ddd; border-radius: 4px; width: 120px; height: 14px; display: inline-block; vertical-align: middle; }
        .progress-bar { background: #4caf50; height: 14px; border-radius: 4px; width: 0%; transition: width 0.3s; }
    </style>
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
            <th>Progress</th>
            <th>Action</th>
        </thead>

        <tbody id="content">
            <?php foreach($jobs as $key => $row):
                $total   = (int) $row['total_records'];
                $done    = (int) $row['processed_count'];
                $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                $running = $row['status'] === 'in-progress';
            ?>
                <tr id="job-row-<?php echo $row['id'] ?>">
                    <td><?php echo ++$key ?></td>
                    <td><?php echo $row['token'] ?></td>
                    <td><?php echo $row['table_name'] ?></td>
                    <td><?php echo $total ?></td>
                    <td class="js-processed"><?php echo $done ?></td>
                    <td class="js-status"><?php echo $row['status'] ?></td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar js-bar" style="width:<?php echo $percent ?>%"></div>
                        </div>
                        <span class="js-percent"><?php echo $percent ?>%</span>
                    </td>
                    <td>
                        <button class="run-btn"
                            data-job-id="<?php echo $row['id'] ?>"
                            <?php echo $running ? 'disabled' : '' ?>>
                            <?php echo $running ? 'Running...' : 'RUN' ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

<script>
    const pollers = {};

    function updateRow(jobId, data) {
        const row = document.getElementById('job-row-' + jobId);
        if (!row) return;

        row.querySelector('.js-processed').textContent = data.processed_count;
        row.querySelector('.js-status').textContent    = data.status;
        row.querySelector('.js-bar').style.width       = data.percent + '%';
        row.querySelector('.js-percent').textContent   = data.percent + '%';

        const btn = row.querySelector('.run-btn');

        if (data.status === 'finished' || data.status === 'pending') {
            btn.disabled    = false;
            btn.textContent = data.status === 'finished' ? 'Done' : 'RUN';
            stopPolling(jobId);
        } else if (data.status === 'in-progress') {
            btn.disabled    = true;
            btn.textContent = 'Running...';
        }
    }

    function startPolling(jobId) {
        if (pollers[jobId]) return;
        pollers[jobId] = setInterval(async () => {
            const res  = await fetch('api/job_status.php?job_id=' + jobId);
            const data = await res.json();
            console.log([
                'polling running',
                res,
                data
            ]);
            if (data.success) updateRow(jobId, data);
        }, 2000);
    }

    function stopPolling(jobId) {
        clearInterval(pollers[jobId]);
        delete pollers[jobId];
    }

    document.querySelectorAll('.run-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const jobId = btn.dataset.jobId;

            btn.disabled    = true;
            btn.textContent = 'Starting...';

            const form = new FormData();
            form.append('job_id', jobId);

            const res  = await fetch('api/trigger_job.php', { method: 'POST', body: form });
            const data = await res.json();

            console.log([
                res,
                data
            ]);
            if (data.success) {
                startPolling(jobId);
            } else {
                btn.disabled    = false;
                btn.textContent = 'RUN';
                alert(data.message);
            }
        });
    });

    // auto-resume polling for any jobs already in-progress on page load
    document.querySelectorAll('.js-status').forEach(el => {
        if (el.textContent.trim() === 'in-progress') {
            const jobId = el.closest('tr').id.replace('job-row-', '');
            startPolling(jobId);
        }
    });
</script>
</body>
</html>
