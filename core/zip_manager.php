<?php

    // Windows uses ZipArchive; Linux uses the system zip command (avoids libzip/tmp issues)
    function _zip_use_cli() {
        return PHP_OS_FAMILY !== 'Windows';
    }

    function zip_create($zip_path) {
        $dir = dirname($zip_path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                write_log("zip_create: could not create directory {$dir}");
                return false;
            }
        }

        if (_zip_use_cli()) {
            // create an empty zip via CLI — zip needs at least one file, so we add a placeholder
            $tmp = tempnam(sys_get_temp_dir(), 'zip_init_');
            file_put_contents($tmp, '');
            $cmd    = sprintf('/usr/bin/zip -j %s %s 2>&1', escapeshellarg($zip_path), escapeshellarg($tmp));
            exec($cmd, $output, $code);
            unlink($tmp);

            if ($code !== 0) {
                write_log("zip_create: CLI failed (code {$code}): " . implode(' ', $output));
                return false;
            }
            return true;
        }

        $zip    = new ZipArchive();
        $flags  = file_exists($zip_path) ? ZipArchive::OVERWRITE : ZipArchive::CREATE;
        $result = $zip->open($zip_path, $flags);

        if ($result !== TRUE) {
            write_log("zip_create: ZipArchive failed (code {$result}) -> {$zip_path}");
            return false;
        }

        $zip->close();
        return true;
    }

    /**
     * Add a file into a zip archive. Creates the archive if it does not exist yet.
     * $local_name is the relative path stored inside the zip.
     */
    function zip_add_file($zip_path, $file_path, $local_name = null) {
        if (!file_exists($file_path)) {
            write_log("zip_add_file: source file not found: {$file_path}");
            return false;
        }

        $dir = dirname($zip_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (_zip_use_cli()) {
            $entry = $local_name ?? basename($file_path);

            // cd into SOURCE_UPLOADS_DIR so the relative path is preserved inside the zip
            $cmd = sprintf(
                'cd %s && /usr/bin/zip %s %s 2>&1',
                escapeshellarg(SOURCE_UPLOADS_DIR),
                escapeshellarg($zip_path),
                escapeshellarg(ltrim($entry, '/'))
            );

            exec($cmd, $output, $code);

            if ($code !== 0) {
                write_log("zip_add_file: CLI failed (code {$code}): " . implode(' ', $output));
                return false;
            }

            return true;
        }

        $zip    = new ZipArchive();
        $result = $zip->open($zip_path, ZipArchive::CREATE);
        if ($result !== TRUE) {
            write_log("zip_add_file: ZipArchive could not open {$zip_path} (code {$result})");
            return false;
        }

        $entry_name = $local_name ?? basename($file_path);
        $result     = $zip->addFile($file_path, $entry_name);
        $zip->close();

        return $result;
    }

    /**
     * List all files stored inside a zip archive.
     */
    function zip_list_files($zip_path) {
        if (!file_exists($zip_path)) {
            write_log("zip_list_files: archive not found: {$zip_path}");
            return false;
        }

        if (_zip_use_cli()) {
            $cmd    = sprintf('/usr/bin/unzip -Z1 %s 2>&1', escapeshellarg($zip_path));
            exec($cmd, $output, $code);

            if ($code !== 0) {
                write_log("zip_list_files: CLI failed (code {$code}): " . implode(' ', $output));
                return false;
            }

            return array_filter($output);
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            write_log("zip_list_files: could not open {$zip_path}");
            return false;
        }

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }

        $zip->close();
        return $entries;
    }

    /**
     * Replace or add a file inside the zip using raw string content.
     */
    function zip_update_file_content($zip_path, $local_name, $content) {
        if (!file_exists($zip_path)) {
            write_log("zip_update_file_content: archive not found: {$zip_path}");
            return false;
        }

        if (_zip_use_cli()) {
            $tmp = tempnam(sys_get_temp_dir(), 'zip_upd_');
            file_put_contents($tmp, $content);
            $cmd  = sprintf('/usr/bin/zip -j %s %s 2>&1', escapeshellarg($zip_path), escapeshellarg($tmp));
            exec($cmd, $output, $code);
            unlink($tmp);

            if ($code !== 0) {
                write_log("zip_update_file_content: CLI failed (code {$code}): " . implode(' ', $output));
                return false;
            }

            return true;
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            write_log("zip_update_file_content: could not open {$zip_path}");
            return false;
        }

        $result = $zip->addFromString($local_name, $content);
        $zip->close();

        return $result;
    }

    /**
     * Delete a specific entry from inside the zip.
     */
    function zip_delete_file($zip_path, $local_name) {
        if (!file_exists($zip_path)) {
            write_log("zip_delete_file: archive not found: {$zip_path}");
            return false;
        }

        if (_zip_use_cli()) {
            $cmd  = sprintf('/usr/bin/zip -d %s %s 2>&1', escapeshellarg($zip_path), escapeshellarg($local_name));
            exec($cmd, $output, $code);

            if ($code !== 0) {
                write_log("zip_delete_file: CLI failed (code {$code}): " . implode(' ', $output));
                return false;
            }

            return true;
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            write_log("zip_delete_file: could not open {$zip_path}");
            return false;
        }

        $result = $zip->deleteName($local_name);
        $zip->close();

        return $result;
    }

    /**
     * Delete the zip archive from disk entirely.
     */
    function zip_destroy($zip_path) {
        if (!file_exists($zip_path)) {
            return false;
        }

        return unlink($zip_path);
    }
?>
