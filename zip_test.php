<?php
    require_once 'loader.private.php';
    echo '<pre>';

    // show what zip_url and zip_name are stored in the most recent job
    $db  = connectDB();
    $res = $db->query("SELECT id, zip_url, zip_name FROM jobs ORDER BY id DESC LIMIT 5");
    echo "=== Last 5 jobs in DB ===\n";
    while ($r = $res->fetch_assoc()) {
        echo "id={$r['id']}  zip_url={$r['zip_url']}  zip_name={$r['zip_name']}\n";
    }
    echo "\nMIGRATIONS_DIR = " . MIGRATIONS_DIR . "\n";
    echo "SOURCE_UPLOADS_DIR = " . SOURCE_UPLOADS_DIR . "\n";
    echo "PHP_OS_FAMILY = " . PHP_OS_FAMILY . "\n";
    echo "Using CLI zip: " . (PHP_OS_FAMILY !== 'Windows' ? 'YES (/usr/bin/zip)' : 'NO (ZipArchive)') . "\n";
    echo "=========================\n\n";

    // --- test zip_add_file() using our actual zip_manager functions ---
    $test_zip  = MIGRATIONS_DIR . '/_zip_test.zip';
    $test_file = SOURCE_UPLOADS_DIR . '/' . ltrim(scandir(SOURCE_UPLOADS_DIR)[2] ?? '', '/');

    echo "Test zip path : {$test_zip}\n";
    echo "Test file path: {$test_file}\n\n";

    if (!file_exists($test_file)) {
        echo "[FAIL] No files found in SOURCE_UPLOADS_DIR: " . SOURCE_UPLOADS_DIR . "\n";
    } else {
        echo "[OK] Source file found\n";

        $ok = zip_add_file($test_zip, $test_file, basename($test_file));

        if ($ok && file_exists($test_zip)) {
            echo "[OK] zip_add_file() succeeded\n";
            echo "[OK] Zip exists on disk: {$test_zip}\n";
            $size = round(filesize($test_zip) / 1024, 2);
            echo "[OK] Size: {$size} KB\n";
            unlink($test_zip);
            echo "[OK] Cleanup done\n";
        } else {
            echo "[FAIL] zip_add_file() failed — check logs/app.log\n";
        }
    }

    echo '</pre>';
?>
