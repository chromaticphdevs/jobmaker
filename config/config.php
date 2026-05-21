<?php
    define('DBHOST', 'localhost');
    define('DBNAME', 'chrom_mke_hris');
    define('DBUSER', 'root');
    define('DBPASS', '');


    define('LIMIT', '50');
    define('TASK_MAX_LIMIT', 100);

    // __DIR__ = jobmaker/config, so:
    // dirname(__DIR__) x2  = htdocs  -> htdocs/sample-images
    // dirname(__DIR__) x3  = xampp7  -> xampp7/test_migration
    define('SOURCE_UPLOADS_DIR', dirname(dirname(__DIR__)) . '/sample-images');
    define('MIGRATIONS_DIR',     dirname(dirname(__DIR__)) . '/test_migration');

    // PHP CLI executable — update this when deploying to server (e.g. '/usr/bin/php')
    define('PHP_CLI', PHP_OS_FAMILY === 'Windows'
        ? 'C:\\xampp7\\php\\php.exe'
        : '/usr/bin/php'
    );

?>