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

        // run the zip command manually so we can see exact output
        $entry = basename($test_file);
        $cmd   = sprintf(
            'cd %s && /usr/bin/zip %s %s 2>&1',
            escapeshellarg(SOURCE_UPLOADS_DIR),
            escapeshellarg($test_zip),
            escapeshellarg($entry)
        );
        echo "CMD: {$cmd}\n\n";
        exec($cmd, $output, $code);
        echo "Exit code : {$code}\n";
        echo "Output    : " . implode("\n", $output) . "\n\n";

        if ($code === 0 && file_exists($test_zip)) {
            echo "[OK] Zip created successfully\n";
            $size = round(filesize($test_zip) / 1024, 2);
            echo "[OK] Size: {$size} KB\n";
            unlink($test_zip);
            echo "[OK] Cleanup done\n";
        } else {
            echo "[FAIL] zip command failed\n";

            // also check if exec is disabled
            $disabled = explode(',', ini_get('disable_functions'));
            $disabled = array_map('trim', $disabled);
            echo "\ndisable_functions: " . ini_get('disable_functions') . "\n";
            echo "exec disabled: " . (in_array('exec', $disabled) ? 'YES' : 'NO') . "\n";
        }
    }

    echo '</pre>';
?>
