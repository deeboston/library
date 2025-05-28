<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'], $_POST['reason'])) {
    $userId = $_SESSION['user_id'];
    $bookId = intval($_POST['book_id']);
    $reason = trim($_POST['reason']);

    $stmt = $conn->prepare("INSERT INTO delete_requests (user_id, book_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $bookId, $reason);
    $stmt->execute();

    header("Location: user-dashboard.php?msg=Request+sent");
    exit;
}
?>
