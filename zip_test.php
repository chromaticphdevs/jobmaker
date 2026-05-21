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
    echo "\nMIGRATIONS_DIR constant = " . MIGRATIONS_DIR . "\n\n";
    echo "=========================\n\n";

    echo "PHP version: " . PHP_VERSION . "\n\n";

    // ZipArchive check
    if (class_exists('ZipArchive')) {
        echo "[OK] ZipArchive is available\n";
    } else {
        echo "[FAIL] ZipArchive is NOT available\n";
    }

    // zip extension loaded
    if (extension_loaded('zip')) {
        echo "[OK] zip extension is loaded\n";
    } else {
        echo "[FAIL] zip extension is NOT loaded\n";
    }

    // test actual file creation
    $test_path = MIGRATIONS_DIR;
    echo "\nAttempting to create: {$test_path}\n";

    if (!is_dir(dirname($test_path))) {
        echo "[FAIL] Directory does not exist: " . dirname($test_path) . "\n";
    } else {
        echo "[OK] Directory exists\n";

        $zip = new ZipArchive();
        $result = $zip->open($test_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result === TRUE) {
            $zip->addFromString('test.txt', 'hello');
            $zip->close();
            echo "[OK] Zip file created successfully\n";
            // unlink($test_path);
            echo "[OK] Cleanup done\n";
        } else {
            echo "[FAIL] ZipArchive::open() returned error code: {$result}\n";
        }
    }

    echo "\nLoaded extensions:\n";
    echo implode(', ', get_loaded_extensions());
    echo '</pre>';
?>
