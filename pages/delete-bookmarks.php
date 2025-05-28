<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $user = userIs();
    $email = $user['email'];
    $bookId = intval($_POST['book_id']);

    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE book_id = ? AND email = ?");
    $stmt->bind_param("is", $bookId, $email);
    $stmt->execute();
    $stmt->close();
}

header("Location: save-books.php");
exit;

?>
