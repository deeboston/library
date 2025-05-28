<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';


require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();

$user = userIs();
var_dump($user);

if (!$user) {
    die("⚠️ No estás logueado.");
}

$email = $user['email'] ?? null;

if (!$email) {
    die("⚠️ El email del usuario no está disponible en la sesión.");
}


$file = $_POST['file'] ?? null;

if (!$file) {
    die("⚠️ Faltan datos necesarios.");
}

// Buscar ID del libro basado en file_path
$stmt = $conn->prepare("SELECT id, nombre FROM libros WHERE file_path = ?");
$stmt->bind_param("s", $file);
$stmt->execute();
$stmt->bind_result($bookID, $bookName);
$stmt->fetch();
$stmt->close();

if (!$bookID) {
    die("📁 Libro no encontrado.");
}

// Revisar si ya está guardado
$check = $conn->prepare("SELECT email FROM bookmarks WHERE email = ? AND book_id = ?");
$check->bind_param("si", $email, $bookID);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    header("Location: user-dashboard.php?bookmark=exists");
    exit;
}

// Insertar nuevo bookmark
$insert = $conn->prepare("INSERT INTO bookmarks (email, book_id, book_name) VALUES (?, ?, ?)");
$insert->bind_param("sis", $email, $bookID, $bookName);
if ($insert->execute()) {
    header("Location: user-dashboard.php?bookmark=success");
} else {
    echo "❌ Error al guardar bookmark.";
}
$insert->close();
$conn->close();
?>