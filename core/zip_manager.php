<?php

    /**
     * Create a new zip archive at the given path.
     * Returns the ZipArchive instance on success, false on failure.
     */
    function zip_create($zip_path) {
        $dir = dirname($zip_path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                write_log("zip_create: could not create directory {$dir}");
                return false;
            }
        }

        if (!class_exists('ZipArchive')) {
            write_log("zip_create: ZipArchive extension is not enabled in PHP");
            return false;
        }

        $zip    = new ZipArchive();
        $flags  = file_exists($zip_path) ? ZipArchive::OVERWRITE : ZipArchive::CREATE;
        $result = $zip->open($zip_path, $flags);

        if ($result !== TRUE) {
            $codes = [
                ZipArchive::ER_OK          => 'No error',
                ZipArchive::ER_MULTIDISK   => 'Multi-disk zip not supported',
                ZipArchive::ER_RENAME      => 'Renaming temp file failed',
                ZipArchive::ER_CLOSE       => 'Closing archive failed',
                ZipArchive::ER_SEEK        => 'Seek error',
                ZipArchive::ER_READ        => 'Read error',
                ZipArchive::ER_WRITE       => 'Write error',
                ZipArchive::ER_OPEN        => 'Cannot open file (permissions?)',
                ZipArchive::ER_TMPOPEN     => 'Cannot create temp file',
                ZipArchive::ER_NOENT       => 'No such file or directory',
                ZipArchive::ER_EXISTS      => 'File already exists',
                ZipArchive::ER_MEMORY      => 'Memory allocation failure',
                ZipArchive::ER_NOZIP       => 'Not a zip archive',
                ZipArchive::ER_INVAL       => 'Invalid argument',
            ];
            $reason = $codes[$result] ?? "Unknown error code {$result}";
            write_log("zip_create: failed [{$result}] {$reason} -> {$zip_path}");
            return false;
        }

        $zip->close();
        return true;
    }

    /**
     * Add a file into an existing zip archive.
     * $local_name is the name/path stored inside the zip (optional, defaults to basename).
     * Returns true on success, false on failure.
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

        $zip    = new ZipArchive();
        $result = $zip->open($zip_path, ZipArchive::CREATE);
        if ($result !== TRUE) {
            write_log("zip_add_file: could not open {$zip_path} (code {$result})");
            return false;
        }

        $entry_name = $local_name ?? basename($file_path);
        $result = $zip->addFile($file_path, $entry_name);
        $zip->close();

        return $result;
    }

    /**
     * List all files stored inside a zip archive.
     * Returns an array of entry names, or false if the archive cannot be opened.
     */
    function zip_list_files($zip_path) {
        if (!file_exists($zip_path)) {
            write_log("zip_read: zip archive not found: {$zip_path}");
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            write_log("zip_read: could not open {$zip_path}");
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
     * Replace or add a file inside the zip using new content (string).
     * Useful when you want to update a file's content without touching the filesystem.
     * Returns true on success, false on failure.
     */
    function zip_update_file_content($zip_path, $local_name, $content) {
        if (!file_exists($zip_path)) {
            write_log("zip_update_file_content: zip archive not found: {$zip_path}");
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            write_log("zip_update_file_content: could not open {$zip_path}");
            return false;
        }

        // addFromString overwrites if the entry already exists
        $result = $zip->addFromString($local_name, $content);
        $zip->close();

        return $result;
    }

    /**
     * Delete a specific file entry from inside the zip archive.
     * Returns true on success, false if the entry was not found or archive failed to open.
     */
    function zip_delete_file($zip_path, $local_name) {
        if (!file_exists($zip_path)) {
            write_log("zip_delete_file: zip archive not found: {$zip_path}");
            return false;
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
     * Permanently delete the zip archive from the filesystem.
     * Returns true on success, false if the file does not exist or deletion fails.
     */
    function zip_destroy($zip_path) {
        if (!file_exists($zip_path)) {
            return false;
        }

        return unlink($zip_path);
    }
?>
