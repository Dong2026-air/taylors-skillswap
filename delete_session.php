<?php

session_start();

require_once "pdo.php";

// Only logged-in users can delete a session.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$session_id = $_GET["id"] ?? $_POST["session_id"] ?? "";

if ($session_id == "" || !is_numeric($session_id)) {
    header("Location: my_sessions.php");
    exit;
}

$session_id = (int)$session_id;
$user_id = (int)$_SESSION["user_id"];

// Load the session and make sure the current user is the creator.
$stmt = $pdo->prepare(
    "SELECT session_id, title, session_date, session_time, location
     FROM skill_sessions
     WHERE session_id = ? AND creator_id = ?"
);

$stmt->execute([
    $session_id,
    $user_id
]);

$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    header("Location: my_sessions.php");
    exit;
}

// Delete only after the confirmation form is submitted by POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $deleteStmt = $pdo->prepare(
        "DELETE FROM skill_sessions
         WHERE session_id = ? AND creator_id = ?"
    );

    $deleteStmt->execute([
        $session_id,
        $user_id
    ]);

    header("Location: my_sessions.php?deleted=1");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Delete Session - Taylor's SkillSwap</title>
</head>

<body class="delete-page">

    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-mark" href="index.php">
                <span class="brand-icon">&harr;</span>
                <span>SkillSwap</span>
            </a>

            <nav class="main-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="browse_sessions.php">Browse Sessions</a>
                <a class="nav-link" href="create_session.php">Create Session</a>
                <a class="nav-link active" href="my_sessions.php">My Sessions</a>
            </nav>

            <div class="nav-account">
                <a class="user-link" href="profile.php">
                    Hi, <?php echo htmlentities($_SESSION["name"]); ?>
                </a>
                <a class="btn btn-outline btn-small" href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main class="delete-main">

        <a class="back-link" href="my_sessions.php">&larr; Back to My Sessions</a>

        <div class="page-heading delete-heading">
            <p class="eyebrow">DELETE SESSION</p>
            <h1>Confirm deletion</h1>
            <p>Please review the session details before deleting it.</p>
        </div>

        <section class="delete-card">
            <div class="delete-warning-icon">!</div>

            <div class="delete-card-content">
                <p class="delete-warning-label">This action cannot be undone</p>

                <h2><?php echo htmlentities($session["title"]); ?></h2>

                <div class="delete-details-grid">
                    <div class="info-item">
                        <span class="info-label">Date</span>
                        <strong><?php echo date("d M Y", strtotime($session["session_date"])); ?></strong>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Time</span>
                        <strong><?php echo date("g:i A", strtotime($session["session_time"])); ?></strong>
                    </div>

                    <div class="info-item delete-location-item">
                        <span class="info-label">Location</span>
                        <strong><?php echo htmlentities($session["location"]); ?></strong>
                    </div>
                </div>

                <div class="delete-warning-box">
                    Deleting this session will also remove all registrations linked to it.
                </div>

                <form class="delete-form" method="POST" action="delete_session.php">
                    <input
                        type="hidden"
                        name="session_id"
                        value="<?php echo (int)$session_id; ?>"
                    >

                    <div class="form-actions delete-actions">
                        <a class="btn btn-outline" href="my_sessions.php">Cancel</a>
                        <button class="btn delete-button" type="submit">Delete Session</button>
                    </div>
                </form>
            </div>
        </section>

    </main>

</body>

</html>