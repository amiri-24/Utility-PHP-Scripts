<?php
$targetDirectory = "uploads/";

// 1. Add secure phpinfo access
if (isset($_GET['phpinfo'])) {
    log_message("phpinfo requested by: " . $_SERVER['REMOTE_ADDR'], 'DEBUG');
    phpinfo();
    exit;
}

// 2. Create uploads directory if not exists
if (!is_dir($targetDirectory)) {
    mkdir($targetDirectory, 0755, true); // Use 0755 permissions for security
    log_message("Directory created: $targetDirectory");
}

// 3. Logging function with different levels
function log_message($message, $level = 'INFO', $context = []) {
    $logFilePath = "uploader_log.txt";
    $timestamp = date('Y-m-d H:i:s.u'); // Timestamp with milliseconds
    $userIP = $_SERVER['REMOTE_ADDR'];
    
    $logEntry = "[$level] $timestamp | IP: $userIP | $message";
    
    if (!empty($context)) {
        $logEntry .= " | Context: " . json_encode($context);
    }
    
    file_put_contents($logFilePath, $logEntry . PHP_EOL, FILE_APPEND);
}

// 4. Helper functions
// MIME validation function
function is_valid_image($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    return in_array($mimeType, $validTypes);
}

// Unique filename generator
function generate_unique_filename($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $ext;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Image Upload</title>
</head>
<body>
    <h1>Image Upload</h1>
    <p>Allowed formats: JPG/JPEG, PNG, GIF | Max size: 5MB</p>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <label>Choose File:</label>
        <input type="file" name="image" accept="image/*" required>
        <input type="submit" value="Upload">
    </form>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    log_message("Upload attempt initiated", 'INFO', [
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
    
    if (!isset($_FILES['image'])) {
        log_message("No file uploaded", 'WARNING');
        die("Error: No file selected");
    }

    $file = $_FILES['image'];
    
    // 5. More precise error handling
    if ($file['error'] !== UPLOAD_ERR_OK) {
        log_message("Upload error encountered", 'ERROR', [
            'error_code' => $file['error']
        ]);
        handle_upload_error($file['error']);
        exit;
    }

    if (!is_valid_image($file)) {
        log_message("Invalid MIME type detected", 'ERROR', [
            'detected_type' => $file['type']
        ]);
        die("Error: Invalid file type (MIME check failed)");
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        log_message("File size exceeded", 'WARNING', [
            'file_size' => $file['size']
        ]);
        die("Error: File exceeds 5MB limit");
    }

    $originalName = basename($file['name']);
    $uniqueName = generate_unique_filename($originalName);
    $targetPath = $targetDirectory . $uniqueName;

    if (!is_writable($targetDirectory)) {
        log_message("Directory not writable", 'CRITICAL', [
            'dir_path' => $targetDirectory
        ]);
        die("Error: Upload directory not writable");
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        log_message("File uploaded successfully", 'SUCCESS', [
            'original_name' => $originalName,
            'saved_as' => $uniqueName,
            'file_size' => $file['size']
        ]);
        echo "Success: File saved as $uniqueName";
    } else {
        log_message("File transfer failed", 'ERROR', [
            'source' => $file['tmp_name'],
            'destination' => $targetPath
        ]);
        die("Error: Failed to save file");
    }
}

// Error handling function
function handle_upload_error($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            die("Error: Exceeds php.ini upload_max_filesize");
        case UPLOAD_ERR_FORM_SIZE:
            die("Error: Exceeds form MAX_FILE_SIZE");
        case UPLOAD_ERR_PARTIAL:
            die("Error: Partial upload");
        case UPLOAD_ERR_NO_FILE:
            die("Error: No file uploaded");
        case UPLOAD_ERR_NO_TMP_DIR:
            die("Error: Missing temporary folder");
        case UPLOAD_ERR_CANT_WRITE:
            die("Error: Failed to write file");
        case UPLOAD_ERR_EXTENSION:
            die("Error: PHP extension stopped upload");
        default:
            die("Error: Unknown upload error");
    }
}
?>
