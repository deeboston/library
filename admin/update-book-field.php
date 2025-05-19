<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Security check
if (!isAdmin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Input validation
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Allowed fields to edit
$allowedFields = ['nombre', 'autor', 'fecha', 'language', 'categoria', 'resumen'];

if ($id <= 0 || !in_array($field, $allowedFields)) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

// Prepare and execute update
$stmt = $conn->prepare("UPDATE libros SET `$field` = ? WHERE id = ?");
$stmt->bind_param('si', $value, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Update failed']);
}
