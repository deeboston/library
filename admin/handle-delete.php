<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../pages/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];

    if (isset($_POST['delete']) && isset($_POST['book_id'])) {
        $book_id = $_POST['book_id'];

        // Delete the book
        $stmt = $conn->prepare("DELETE FROM libros WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->close();

        // Remove the deletion request
        $conn->query("DELETE FROM delete_requests WHERE id = $request_id");

        $_SESSION['flash'] = "Book deleted successfully.";
    }

    if (isset($_POST['ignore'])) {
        // Just remove the deletion request
        $conn->query("DELETE FROM delete_requests WHERE id = $request_id");
        $_SESSION['flash'] = "Deletion request ignored.";
    }
}

header("Location: delete-requests.php");
exit();
