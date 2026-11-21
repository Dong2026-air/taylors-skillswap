<?php

session_start();

require_once "pdo.php";

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$sql = "SELECT
            skill_sessions.session_id,
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
        WHERE 1 = 1";

$params = [];

if ($search != "") {
    $sql .= " AND (
                skill_sessions.title LIKE ?
                OR skill_sessions.description LIKE ?
                OR skill_sessions.location LIKE ?
             )";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

function sessionDetailsUrl($sessionId, $search, $category)
{
    $query = [
        "id" => (int)$sessionId
    ];

    if ($search != "") {
        $query["search"] = $search;
    }

    if ($category != "") {
        $query["category"] = $category;
    }

    return "session_details.php?" . http_build_query($query);
}

if ($category != "") {
    $sql .= " AND skill_sessions.category = ?";
    $params[] = $category;
}

$sql .= " GROUP BY
            skill_sessions.session_id,
            skill_sessions.title,
            skill_sessions.category,
            skill_sessions.description,
            skill_sessions.session_date,
            skill_sessions.session_time,
            skill_sessions.location,
            skill_sessions.capacity,
            users.name
          ORDER BY skill_sessions.session_date ASC, skill_sessions.session_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function browseFilterUrl($selectedCategory, $search)
{
    $query = [];

    if ($selectedCategory != "") {
        $query["category"] = $selectedCategory;
    }

    if ($search != "") {
        $query["search"] = $search;
    }

    if (count($query) == 0) {
        return "browse_sessions.php";
    }

    return "browse_sessions.php?" . http_build_query($query);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Browse Sessions - Taylor's SkillSwap</title>
</head>

<body class="browse-page">

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

    <main class="browse-main">

        <div class="page-heading">
            <p class="eyebrow">DISCOVER</p>
            <h1>Browse Sessions</h1>
            <p>Find a peer-led workshop and learn something new on campus.</p>
        </div>

        <form class="browse-search-form" method="GET" action="browse_sessions.php">
            <div class="search-box">
                <span class="search-icon">&#128269;</span>
                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Search sessions by title or skill"
                    value="<?php echo htmlentities($search); ?>"
                >
            </div>

            <?php if ($category != ""): ?>
                <input type="hidden" name="category" value="<?php echo htmlentities($category); ?>">
            <?php endif; ?>

            <button class="sr-only-submit" type="submit">Search</button>
        </form>

        <div class="filter-row">
            <a
                class="filter-chip <?php echo ($category == "") ? "active" : ""; ?>"
                href="<?php echo htmlentities(browseFilterUrl("", $search)); ?>"
            >All</a>

            <a
                class="filter-chip <?php echo ($category == "Design") ? "active" : ""; ?>"
                href="<?php echo htmlentities(browseFilterUrl("Design", $search)); ?>"
            >Design</a>

            <a
                class="filter-chip <?php echo ($category == "Technology") ? "active" : ""; ?>"
                href="<?php echo htmlentities(browseFilterUrl("Technology", $search)); ?>"
            >Technology</a>

            <a
                class="filter-chip <?php echo ($category == "Career") ? "active" : ""; ?>"
                href="<?php echo htmlentities(browseFilterUrl("Career", $search)); ?>"
            >Career</a>

            <a
                class="filter-chip <?php echo ($category == "Creative") ? "active" : ""; ?>"
                href="<?php echo htmlentities(browseFilterUrl("Creative", $search)); ?>"
            >Creative</a>
        </div>

        <p class="results-count">
            <?php echo count($sessions); ?> session<?php echo count($sessions) == 1 ? "" : "s"; ?>
        </p>

        <?php if (count($sessions) == 0): ?>

            <div class="empty-card">
                <p>No skill sessions found.</p>
                <a class="btn btn-outline btn-small" href="browse_sessions.php">Clear filters</a>
            </div>

        <?php else: ?>

            <div class="browse-session-grid">
                <?php foreach ($sessions as $session): ?>

                    <article class="browse-session-card">
                        <span class="category-badge">
                            <?php echo htmlentities($session["category"]); ?>
                        </span>

                        <h2><?php echo htmlentities($session["title"]); ?></h2>

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

                        <a
                            class="btn btn-outline btn-small"
                            href="<?php echo htmlentities(sessionDetailsUrl($session["session_id"], $search, $category)); ?>"
                        >View Details &rarr;</a>
                    </article>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

</body>

</html>