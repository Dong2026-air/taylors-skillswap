<?php

session_start();

require_once "pdo.php";

// Only logged-in users can view participants.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$session_id = $_GET["id"] ?? "";

if ($session_id == "" || !is_numeric($session_id)) {
    header("Location: my_sessions.php");
    exit;
}

$session_id = (int)$session_id;
$user_id = (int)$_SESSION["user_id"];

// Load the session and make sure the current user is the creator.
$sessionStmt = $pdo->prepare(
    "SELECT session_id, title, capacity
     FROM skill_sessions
     WHERE session_id = ? AND creator_id = ?"
);

$sessionStmt->execute([
    $session_id,
    $user_id
]);

$session = $sessionStmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    header("Location: my_sessions.php");
    exit;
}

// Get all users registered for this session.
$participantStmt = $pdo->prepare(
    "SELECT
        users.name,
        users.email,
        registrations.registered_at
     FROM registrations
     JOIN users
        ON registrations.user_id = users.user_id
     WHERE registrations.session_id = ?
     ORDER BY registrations.registered_at ASC"
);

$participantStmt->execute([$session_id]);
$participants = $participantStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Participants - Taylor's SkillSwap</title>
</head>

<body class="participants-page">

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

    <main class="participants-main">

        <a class="back-link" href="my_sessions.php">&larr; Back to My Sessions</a>

        <div class="page-heading participants-heading">
            <p class="eyebrow">PARTICIPANTS</p>
            <h1><?php echo htmlentities($session["title"]); ?></h1>
            <p>View the students who registered for your session.</p>
        </div>

        <div class="participants-summary">
            <div>
                <span class="info-label">Registered</span>
                <strong><?php echo count($participants); ?> / <?php echo (int)$session["capacity"]; ?></strong>
            </div>

            <div>
                <span class="info-label">Available Spots</span>
                <strong><?php echo max(0, (int)$session["capacity"] - count($participants)); ?></strong>
            </div>
        </div>

        <?php if (count($participants) == 0): ?>

            <div class="empty-card participants-empty">
                <p>No participants have joined this session yet.</p>
                <a class="btn btn-outline btn-small" href="session_details.php?id=<?php echo (int)$session_id; ?>">
                    View Session
                </a>
            </div>

        <?php else: ?>

            <div class="participants-table-card">
                <div class="table-heading-row">
                    <div>
                        <h2>Registered Students</h2>
                        <p><?php echo count($participants); ?> student<?php echo count($participants) == 1 ? "" : "s"; ?> joined this session.</p>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="participants-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($participants as $index => $participant): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlentities($participant["name"]); ?></td>
                                    <td><?php echo htmlentities($participant["email"]); ?></td>
                                    <td>
                                        <?php echo date("d M Y, g:i A", strtotime($participant["registered_at"])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

    </main>

</body>

</html>