<?php
    // config.local.php defines constants first — config.php fills in anything not overridden
    $local_config = __DIR__ . '/config/config.local.php';
    if (file_exists($local_config)) {
        require_once $local_config;
    }

    require_once 'config/config.php';
    require_once 'config/conn.php';
    require_once 'core/job_maker.php';
    require_once 'core/job_logger.php';
    require_once 'core/zip_manager.php';
    require_once 'tasks/tasks.php';
?>