<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$book_id = $_GET['book_id'] ?? null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $book_id) {
    $reason = trim($_POST['reason']);
    $user_id = getCurrentUserId();

    if (!empty($reason)) {
        $stmt = $conn->prepare("INSERT INTO delete_requests (user_id, book_id, reason) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $book_id, $reason);
        if ($stmt->execute()) {
            $message = "✅ Your deletion request has been sent to the admin.";
        } else {
            $message = "❌ Failed to submit request. Please try again.";
        }
        $stmt->close();
    } else {
        $message = "Please enter a reason for deletion.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Book Deletion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>Request Deletion of Book ID: <?= htmlspecialchars($book_id) ?></h3>

    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$message): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Reason for Deletion</label>
                <textarea name="reason" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-warning">Submit Request</button>
            <a href="dashboard.php" class="btn btn-secondary">← Back</a>
        </form>
    <?php else: ?>
    <a href="<?= $_SESSION['role'] === 'admin' ? '../admin/admin-dashboard.php' : 'user-dashboard.php' ?>" class="btn btn-outline-secondary">← Back</a>
    <?php endif; ?>
</div>

</body>
</html>
