<?php

session_start();

require_once "pdo.php";

// Only logged-in users can view My Sessions.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

// Sessions the user has joined.
$joinedStmt = $pdo->prepare(
    "SELECT
        skill_sessions.session_id,
        skill_sessions.title,
        skill_sessions.category,
        skill_sessions.session_date,
        skill_sessions.session_time,
        skill_sessions.location,
        users.name AS creator_name
     FROM registrations
     JOIN skill_sessions
        ON registrations.session_id = skill_sessions.session_id
     JOIN users
        ON skill_sessions.creator_id = users.user_id
     WHERE registrations.user_id = ?
     ORDER BY skill_sessions.session_date ASC, skill_sessions.session_time ASC"
);

$joinedStmt->execute([$user_id]);
$joinedSessions = $joinedStmt->fetchAll(PDO::FETCH_ASSOC);

// Sessions created by the current user.
$hostedStmt = $pdo->prepare(
    "SELECT
        skill_sessions.session_id,
        skill_sessions.title,
        skill_sessions.category,
        skill_sessions.session_date,
        skill_sessions.session_time,
        skill_sessions.location,
        skill_sessions.capacity,
        COUNT(registrations.registration_id) AS joined_count
     FROM skill_sessions
     LEFT JOIN registrations
        ON skill_sessions.session_id = registrations.session_id
     WHERE skill_sessions.creator_id = ?
     GROUP BY
        skill_sessions.session_id,
        skill_sessions.title,
        skill_sessions.category,
        skill_sessions.session_date,
        skill_sessions.session_time,
        skill_sessions.location,
        skill_sessions.capacity
     ORDER BY skill_sessions.session_date ASC, skill_sessions.session_time ASC"
);

$hostedStmt->execute([$user_id]);
$hostedSessions = $hostedStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>My Sessions - Taylor's SkillSwap</title>
</head>

<body class="my-sessions-page">

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

    <main class="my-sessions-main">

        <?php if (isset($_GET["deleted"]) && $_GET["deleted"] == "1"): ?>
            <div class="status-message success-message">Session deleted successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET["cancelled"]) && $_GET["cancelled"] == "1"): ?>
            <div class="status-message success-message">Registration cancelled successfully!</div>
        <?php endif; ?>

        <div class="page-heading">
            <p class="eyebrow">MY SESSIONS</p>
            <h1>Your learning & hosting</h1>
            <p>Keep track of the sessions you joined and the ones you are hosting.</p>
        </div>

        <section class="sessions-panel">
            <div class="sessions-panel-heading">
                <div>
                    <h2>Joined Sessions</h2>
                    <p>Sessions you have registered to attend.</p>
                </div>
                <a class="btn btn-outline btn-small" href="browse_sessions.php">Browse more</a>
            </div>

            <?php if (count($joinedSessions) == 0): ?>

                <div class="empty-card">
                    <p>You have not joined any sessions yet.</p>
                    <a class="btn btn-primary btn-small" href="browse_sessions.php">Browse Sessions</a>
                </div>

            <?php else: ?>

                <div class="session-list">
                    <?php foreach ($joinedSessions as $session): ?>

                        <article class="session-row-card">
                            <div class="session-row-main">
                                <span class="category-badge">
                                    <?php echo htmlentities($session["category"]); ?>
                                </span>

                                <h3><?php echo htmlentities($session["title"]); ?></h3>

                                <p class="session-meta">
                                    <?php echo date("d M", strtotime($session["session_date"])); ?>
                                    ·
                                    <?php echo date("g:i A", strtotime($session["session_time"])); ?>
                                    &nbsp;&bull;&nbsp;
                                    <?php echo htmlentities($session["location"]); ?>
                                </p>

                                <p class="session-host">
                                    Hosted by <?php echo htmlentities($session["creator_name"]); ?>
                                </p>
                            </div>

                            <div class="session-row-actions">
                                <a
                                    class="btn btn-outline btn-small"
                                    href="session_details.php?id=<?php echo (int)$session["session_id"]; ?>"
                                >View Details</a>

                                <form class="inline-action-form" method="POST" action="cancel_registration.php">
                                    <input
                                        type="hidden"
                                        name="session_id"
                                        value="<?php echo (int)$session["session_id"]; ?>"
                                    >
                                    <button class="btn btn-outline btn-small danger-outline" type="submit">
                                        Cancel Registration
                                    </button>
                                </form>
                            </div>
                        </article>

                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </section>

        <section class="sessions-panel">
            <div class="sessions-panel-heading">
                <div>
                    <h2>Hosted Sessions</h2>
                    <p>Sessions you created for other Taylor's students.</p>
                </div>
                <a class="btn btn-primary btn-small" href="create_session.php">Create Session</a>
            </div>

            <?php if (count($hostedSessions) == 0): ?>

                <div class="empty-card">
                    <p>You have not created any sessions yet.</p>
                    <a class="btn btn-primary btn-small" href="create_session.php">Create Your First Session</a>
                </div>

            <?php else: ?>

                <div class="session-list">
                    <?php foreach ($hostedSessions as $session): ?>

                        <article class="session-row-card hosted-row-card">
                            <div class="session-row-main">
                                <span class="category-badge">
                                    <?php echo htmlentities($session["category"]); ?>
                                </span>

                                <h3><?php echo htmlentities($session["title"]); ?></h3>

                                <p class="session-meta">
                                    <?php echo date("d M", strtotime($session["session_date"])); ?>
                                    ·
                                    <?php echo date("g:i A", strtotime($session["session_time"])); ?>
                                    &nbsp;&bull;&nbsp;
                                    <?php echo htmlentities($session["location"]); ?>
                                </p>

                                <p class="session-host">
                                    <?php echo (int)$session["joined_count"]; ?> /
                                    <?php echo (int)$session["capacity"]; ?> participants
                                </p>
                            </div>

                            <div class="session-row-actions hosted-actions">
                                <a
                                    class="btn btn-outline btn-small"
                                    href="session_details.php?id=<?php echo (int)$session["session_id"]; ?>"
                                >View Details</a>

                                <a
                                    class="btn btn-outline btn-small"
                                    href="edit_session.php?id=<?php echo (int)$session["session_id"]; ?>"
                                >Edit</a>

                                <a
                                    class="btn btn-outline btn-small"
                                    href="participants.php?id=<?php echo (int)$session["session_id"]; ?>"
                                >Participants</a>

                                <a
                                    class="btn btn-outline btn-small danger-outline"
                                    href="delete_session.php?id=<?php echo (int)$session["session_id"]; ?>"
                                >Delete</a>
                            </div>
                        </article>

                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </section>

    </main>

</body>

</html>