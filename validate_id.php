<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowedFileTypes = ['image/jpeg', 'image/png'];
    $valid = false;

    if (isset($_FILES['id_image'])) {
        $fileType = $_FILES['id_image']['type'];
        $idType = $_POST['id_type'] ?? '';

        // Debugging: Log received file type and ID type
        error_log("File Type: " . $fileType);
        error_log("ID Type: " . $idType);

        // Check if the file type is allowed
        if (in_array($fileType, $allowedFileTypes)) {
            // Example validation logic
            if ($_FILES['id_image']['size'] < 5000000) { // Example size limit (5MB)
                $valid = true;
            }
        }
    }

    // Debugging: Log validation result
    error_log("Validation Result: " . ($valid ? 'Valid' : 'Invalid'));

    echo json_encode(['valid' => $valid]);
    exit;
}
