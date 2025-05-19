<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = $_FILES['book_files'];
    $cover = $_FILES['cover_image'] ?? null;

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            continue; // Skip non-PDFs
        }

        $newFileName = uniqid() . '-' . basename($files['name'][$i]);
        $destPath = "../books/" . $newFileName;

        if (!move_uploaded_file($files['tmp_name'][$i], $destPath)) {
            continue;
        }

        $title = pathinfo($files['name'][$i], PATHINFO_FILENAME);
        $author = 'Unknown';
        $year = date('Y');
        $language = 'Unknown';

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($destPath);
            $meta = $pdf->getDetails();

            if (!empty($meta['Title'])) {
                $title = $meta['Title'];
            }
            if (!empty($meta['Author'])) {
                $author = $meta['Author'];
            }
            if (!empty($meta['CreationDate'])) {
                $year = preg_replace('/[^0-9]/', '', substr($meta['CreationDate'], 0, 8));
            }
        } catch (Exception $e) {
            error_log("PDF metadata error: " . $e->getMessage());
        }

        $coverName = '';
        if ($cover && $cover['error'] === UPLOAD_ERR_OK) {
            $coverName = uniqid() . '-' . basename($cover['name']);
            move_uploaded_file($cover['tmp_name'], "../assets/images/" . $coverName);
        }

        $stmt = $conn->prepare("INSERT INTO libros (nombre, autor, fecha, language, file_path, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $author, $year, $language, $newFileName, $coverName);
        $stmt->execute();
    }

    header('Location: user-dashboard.php?upload=success');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bulk Upload PDF Books</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2>📤 Bulk Upload PDF Books</h2>
  <form action="upload-form.php" method="POST" enctype="multipart/form-data">
  <label>Select PDF files:</label>
  <input type="file" name="book_files[]" multiple accept=".pdf" class="form-control" required>
  <button type="submit">Upload</button>
</form>
</div>
</body>
</html>
