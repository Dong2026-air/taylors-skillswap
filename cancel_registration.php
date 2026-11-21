<?php

session_start();

require_once "pdo.php";

// Only logged-in users can cancel a registration.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Cancellation must be submitted using POST.
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: my_sessions.php");
    exit;
}

$session_id = $_POST["session_id"] ?? "";

if ($session_id == "" || !is_numeric($session_id)) {
    header("Location: my_sessions.php");
    exit;
}

$session_id = (int)$session_id;
$user_id = (int)$_SESSION["user_id"];

// Check that the current user is actually registered for this session.
$checkStmt = $pdo->prepare(
    "SELECT registration_id
     FROM registrations
     WHERE user_id = ? AND session_id = ?"
);

$checkStmt->execute([
    $user_id,
    $session_id
]);

$registration = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    header("Location: my_sessions.php");
    exit;
}

// Remove only the current user's registration for this session.
$deleteStmt = $pdo->prepare(
    "DELETE FROM registrations
     WHERE user_id = ? AND session_id = ?"
);

$deleteStmt->execute([
    $user_id,
    $session_id
]);

header("Location: my_sessions.php?cancelled=1");
exit;

?>