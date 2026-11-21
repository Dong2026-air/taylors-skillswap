<?php

session_start();

require_once "pdo.php";

$session_id = $_GET["id"] ?? "";

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$browse_params = [];

if ($search !== "") {
    $browse_params["search"] = $search;
}

if ($category !== "") {
    $browse_params["category"] = $category;
}

$back_url = "browse_sessions.php";

if (!empty($browse_params)) {
    $back_url .= "?" . http_build_query($browse_params);
}

if ($session_id == "" || !is_numeric($session_id)) {
    header("Location: browse_sessions.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT
        skill_sessions.session_id,
        skill_sessions.creator_id,
        skill_sessions.title,
        skill_sessions.category,
        skill_sessions.description,
        skill_sessions.session_date,
        skill_sessions.session_time,
        skill_sessions.location,
        skill_sessions.capacity,
        users.name AS creator_name,
        COUNT(registrations.registration_id) AS joined_count
     FROM skill_sessions
     JOIN users
        ON skill_sessions.creator_id = users.user_id
     LEFT JOIN registrations
        ON skill_sessions.session_id = registrations.session_id
     WHERE skill_sessions.session_id = ?
     GROUP BY
        skill_sessions.session_id,
        skill_sessions.creator_id,
        skill_sessions.title,
        skill_sessions.category,
        skill_sessions.description,
        skill_sessions.session_date,
        skill_sessions.session_time,
        skill_sessions.location,
        skill_sessions.capacity,
        users.name"
);

$stmt->execute([(int)$session_id]);

$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    header("Location: browse_sessions.php");
    exit;
}

$spots_left = (int)$session["capacity"] - (int)$session["joined_count"];

if ($spots_left < 0) {
    $spots_left = 0;
}

$is_creator = isset($_SESSION["user_id"])
    && (int)$_SESSION["user_id"] === (int)$session["creator_id"];

$is_joined = false;

if (isset($_SESSION["user_id"])) {
    $joinStmt = $pdo->prepare(
        "SELECT registration_id
         FROM registrations
         WHERE user_id = ? AND session_id = ?"
    );

    $joinStmt->execute([
        $_SESSION["user_id"],
        $session["session_id"]
    ]);

    $is_joined = (bool)$joinStmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .details-page .details-card h1 {
            font-size: 2.15rem !important;
            line-height: 1.08 !important;
        }

        .details-page .details-card h2 {
            font-size: 1.25rem !important;
            line-height: 1.2 !important;
        }

        .details-page .join-card h2 {
            font-size: 1.25rem !important;
            line-height: 1.25 !important;
        }

        @media (max-width: 760px) {
            .details-page .details-card h1 {
                font-size: 1.85rem !important;
            }

            .details-page .details-card h2,
            .details-page .join-card h2 {
                font-size: 1.1rem !important;
            }
        }
    </style>
    <title><?php echo htmlentities($session["title"]); ?> - Taylor's SkillSwap</title>
</head>

<body class="details-page">

    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-mark" href="index.php">
                <span class="brand-icon">&harr;</span>
                <span>SkillSwap</span>
            </a>

            <nav class="main-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link active" href="browse_sessions.php">Browse Sessions</a>

                <?php if (isset($_SESSION["user_id"])): ?>
                    <a class="nav-link" href="create_session.php">Create Session</a>
                    <a class="nav-link" href="my_sessions.php">My Sessions</a>
                <?php endif; ?>
            </nav>

            <div class="nav-account">
                <?php if (isset($_SESSION["user_id"])): ?>
                    <a class="user-link" href="profile.php">
                        Hi, <?php echo htmlentities($_SESSION["name"]); ?>
                    </a>
                    <a class="btn btn-outline btn-small" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline btn-small" href="login.php">Login</a>
                    <a class="btn btn-primary btn-small" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="details-main">

        <?php if (isset($_GET["updated"]) && $_GET["updated"] == "1"): ?>
            <div class="status-message success-message">Session updated successfully!</div>
        <?php endif; ?>

        <?php if (isset($_SESSION["message"])): ?>
            <div class="status-message success-message">
                <?php echo htmlentities($_SESSION["message"]); ?>
            </div>
            <?php unset($_SESSION["message"]); ?>
        <?php endif; ?>

        <a class="back-link" href="<?php echo htmlentities($back_url); ?>">&larr; Back to Sessions</a>

        <div class="details-layout">

            <section class="details-card">
                <span class="category-badge">
                    <?php echo htmlentities($session["category"]); ?>
                </span>

                <h1><?php echo htmlentities($session["title"]); ?></h1>

                <p class="details-description">
                    <?php echo nl2br(htmlentities($session["description"])); ?>
                </p>

                <div class="details-divider"></div>

                <h2>Session Details</h2>

                <div class="details-info-grid">
                    <div class="info-item">
                        <span class="info-label">Date</span>
                        <strong><?php echo date("d M Y", strtotime($session["session_date"])); ?></strong>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Time</span>
                        <strong><?php echo date("g:i A", strtotime($session["session_time"])); ?></strong>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Location</span>
                        <strong><?php echo htmlentities($session["location"]); ?></strong>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Participants</span>
                        <strong>
                            <?php echo (int)$session["joined_count"]; ?> /
                            <?php echo (int)$session["capacity"]; ?>
                        </strong>
                    </div>
                </div>

                <div class="host-card">
                    <span class="info-label">Hosted by</span>
                    <strong><?php echo htmlentities($session["creator_name"]); ?></strong>
                </div>
            </section>

            <aside class="join-card">
                <p class="eyebrow">READY TO JOIN?</p>
                <h2><?php echo $spots_left; ?> spots remaining</h2>

                <p class="join-description">
                    Join this peer-led session and learn together with other Taylor's students.
                </p>

                <?php if (!isset($_SESSION["user_id"])): ?>

                    <p class="join-note">Please log in before joining this session.</p>
                    <a
                        class="btn btn-primary full-width-btn"
                        href="login.php?return_url=<?php echo urlencode('session_details.php?id=' . (int)$session['session_id']); ?>"
                    >Login to Join</a>

                <?php elseif ($is_creator): ?>

                    <div class="owner-note">You are hosting this session.</div>

                    <a
                        class="btn btn-primary full-width-btn"
                        href="edit_session.php?id=<?php echo (int)$session["session_id"]; ?>"
                    >Edit Session</a>

                    <a
                        class="btn btn-outline full-width-btn"
                        href="participants.php?id=<?php echo (int)$session["session_id"]; ?>"
                    >View Participants</a>

                <?php elseif ($is_joined): ?>

                    <div class="joined-note">You have already joined this session.</div>
                    <a class="btn btn-outline full-width-btn" href="my_sessions.php">View My Sessions</a>

                <?php elseif ($spots_left <= 0): ?>

                    <div class="full-note">This session is full.</div>

                <?php else: ?>

                    <form class="join-form" method="POST" action="join_session.php">
                        <input
                            type="hidden"
                            name="session_id"
                            value="<?php echo (int)$session["session_id"]; ?>"
                        >

                        <button class="btn btn-primary full-width-btn" type="submit">
                            Join Session
                        </button>
                    </form>

                <?php endif; ?>
            </aside>

        </div>

    </main>

</body>

</html>