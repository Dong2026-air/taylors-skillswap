<?php

session_start();

require_once "pdo.php";

// User must be logged in before joining a session.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Joining a session must be done through POST.
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: browse_sessions.php");
    exit;
}

$session_id = $_POST["session_id"] ?? "";

if ($session_id == "" || !is_numeric($session_id)) {
    header("Location: browse_sessions.php");
    exit;
}

$session_id = (int)$session_id;
$user_id = (int)$_SESSION["user_id"];

// Get the session and its current number of registrations.
$stmt = $pdo->prepare(
    "SELECT
        skill_sessions.session_id,
        skill_sessions.creator_id,
        skill_sessions.capacity,
        COUNT(registrations.registration_id) AS joined_count
     FROM skill_sessions
     LEFT JOIN registrations
        ON skill_sessions.session_id = registrations.session_id
     WHERE skill_sessions.session_id = ?
     GROUP BY
        skill_sessions.session_id,
        skill_sessions.creator_id,
        skill_sessions.capacity"
);

$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    header("Location: browse_sessions.php");
    exit;
}

// A creator should not join their own session.
if ($user_id === (int)$session["creator_id"]) {
    $_SESSION["message"] = "You cannot join a session that you are hosting.";
    header("Location: session_details.php?id=" . $session_id);
    exit;
}

// Check whether the user has already joined.
$checkStmt = $pdo->prepare(
    "SELECT registration_id
     FROM registrations
     WHERE user_id = ? AND session_id = ?"
);

$checkStmt->execute([
    $user_id,
    $session_id
]);

if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["message"] = "You have already joined this session.";
    header("Location: session_details.php?id=" . $session_id);
    exit;
}

// Prevent users from joining a full session.
if ((int)$session["joined_count"] >= (int)$session["capacity"]) {
    $_SESSION["message"] = "Sorry, this session is already full.";
    header("Location: session_details.php?id=" . $session_id);
    exit;
}

// Create the registration record.
$insertStmt = $pdo->prepare(
    "INSERT INTO registrations (user_id, session_id)
     VALUES (?, ?)"
);

$insertStmt->execute([
    $user_id,
    $session_id
]);

$_SESSION["message"] = "You have successfully joined this session!";

header("Location: session_details.php?id=" . $session_id);
exit;

?>