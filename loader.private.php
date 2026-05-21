<?php
    // config.local.php defines constants first — config.php fills in anything not overridden
    $local_config = __DIR__ . '/config/config.local.php';
    if (file_exists($local_config)) {
        require_once $local_config;
    }

    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/conn.php';
    require_once __DIR__ . '/core/job_maker.php';
    require_once __DIR__ . '/core/job_logger.php';
    require_once __DIR__ . '/core/zip_manager.php';
    require_once __DIR__ . '/tasks/tasks.php';
?>