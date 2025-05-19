<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $bookId = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT file_path FROM libros WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $book = $result->fetch_assoc();
        $filePath = '../books/' . $book['file_path'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        // Load and extract content
        $content = '';
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            require '../vendor/autoload.php';
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            // Grab first 1-2 paragraphs for preview
            $paragraphs = preg_split("/(\r?\n){2,}/", trim($text));
            $preview = isset($paragraphs[0]) ? $paragraphs[0] : '';
            $preview .= isset($paragraphs[1]) ? "\n\n" . $paragraphs[1] : '';

            $summary = substr(trim($text), 0, 500) . '...';

            // Update the resumen and preview fields
            $update = $conn->prepare("UPDATE libros SET resumen = ?, preview = ? WHERE id = ?");
            $update->bind_param("ssi", $summary, $preview, $bookId);
            $update->execute();

            echo "Summary and preview updated.";
        } elseif ($ext === 'txt') {
            $text = file_get_contents($filePath);
            $summary = substr(trim($text), 0, 500) . '...';
            $preview = substr(trim($text), 0, 800);

            $update = $conn->prepare("UPDATE libros SET resumen = ?, preview = ? WHERE id = ?");
            $update->bind_param("ssi", $summary, $preview, $bookId);
            $update->execute();

            echo "Summary and preview updated.";
        } elseif ($ext === 'epub') {
            echo "EPUB summary not yet supported.";
        }
    } else {
        http_response_code(404);
        echo "Book not found";
    }
} else {
    http_response_code(400);
    echo "Invalid request.";
}
?>
