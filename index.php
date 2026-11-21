<?php

session_start();
require_once "pdo.php";

// Show the next three upcoming sessions on the home page.
$sessionStmt = $pdo->prepare(
    "SELECT
        skill_sessions.session_id,
        skill_sessions.title,
        skill_sessions.category,
        skill_sessions.session_date,
        skill_sessions.session_time,
        skill_sessions.location,
        users.name AS creator_name
     FROM skill_sessions
     JOIN users
        ON skill_sessions.creator_id = users.user_id
     WHERE skill_sessions.session_date >= CURDATE()
     ORDER BY skill_sessions.session_date ASC, skill_sessions.session_time ASC
     LIMIT 3"
);

$sessionStmt->execute();
$upcomingSessions = $sessionStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Taylor's SkillSwap</title>
</head>

<body class="home-page">

    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-mark" href="index.php">
                <span class="brand-icon">&harr;</span>
                <span>SkillSwap</span>
            </a>

            <nav class="main-nav">
                <a class="nav-link active" href="index.php">Home</a>
                <a class="nav-link" href="browse_sessions.php">Browse Sessions</a>

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

    <main class="home-main">

        <section class="home-hero">
            <div class="hero-copy">
                <p class="eyebrow eyebrow-light">TAYLOR'S / PEER LEARNING</p>

                <h1>Share what you know.<br>Learn what you love.</h1>

                <p class="hero-description">
                    Discover peer-led skill-sharing sessions at Taylor's.
                </p>

                <div class="hero-actions">
                    <a class="btn btn-primary hero-primary" href="browse_sessions.php">
                        Browse Sessions &rarr;
                    </a>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <a class="btn btn-light" href="create_session.php">Share a Skill</a>
                    <?php else: ?>
                        <a class="btn btn-light" href="register.php">Share a Skill</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hero-feature-card">
                <div class="hero-feature-icon">&harr;</div>
                <h2>Learn together.</h2>
                <p>Host a session. Pick up a skill.</p>
            </div>
        </section>

        <section class="home-section category-section">
            <h2 class="section-title">Explore by category</h2>

            <div class="category-grid">
                <a class="category-card" href="browse_sessions.php?category=Design">Design</a>
                <a class="category-card" href="browse_sessions.php?category=Technology">Technology</a>
                <a class="category-card" href="browse_sessions.php?category=Career">Career</a>
                <a class="category-card" href="browse_sessions.php?category=Creative">Creative</a>
            </div>
        </section>

        <section class="home-section upcoming-section">
            <div class="section-heading-row">
                <h2 class="section-title">Upcoming sessions</h2>
                <a class="btn btn-outline btn-small" href="browse_sessions.php">See all sessions &rarr;</a>
            </div>

            <?php if (count($upcomingSessions) == 0): ?>
                <div class="empty-card">
                    <p>No upcoming sessions yet.</p>
                </div>
            <?php else: ?>
                <div class="home-session-grid">
                    <?php foreach ($upcomingSessions as $session): ?>
                        <article class="home-session-card">
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

                            <a class="btn btn-outline btn-small" href="session_details.php?id=<?php echo (int)$session["session_id"]; ?>">
                                View Details &rarr;
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

</body>

</html>