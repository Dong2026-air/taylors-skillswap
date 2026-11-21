<?php

session_start();

require_once "pdo.php";

// Only logged-in users can create a skill session.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $session_date = trim($_POST["session_date"] ?? "");
    $session_time = trim($_POST["session_time"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $capacity = trim($_POST["capacity"] ?? "");

    if (
        $title == "" ||
        $category == "" ||
        $description == "" ||
        $session_date == "" ||
        $session_time == "" ||
        $location == "" ||
        $capacity == ""
    ) {
        $message = "Please fill in all fields.";

    } elseif (!is_numeric($capacity) || (int)$capacity < 1) {
        $message = "Capacity must be at least 1.";

    } elseif (strtotime($session_date) < strtotime(date("Y-m-d"))) {
        $message = "Session date cannot be in the past.";

    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO skill_sessions
            (creator_id, title, category, description, session_date, session_time, location, capacity)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $_SESSION["user_id"],
            $title,
            $category,
            $description,
            $session_date,
            $session_time,
            $location,
            (int)$capacity
        ]);

        $_SESSION["success_message"] = "Skill session created successfully!";

        header("Location: create_session.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Create Session - Taylor's SkillSwap</title>
</head>

<body class="create-page">

    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-mark" href="index.php">
                <span class="brand-icon">&harr;</span>
                <span>SkillSwap</span>
            </a>

            <nav class="main-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="browse_sessions.php">Browse Sessions</a>
                <a class="nav-link active" href="create_session.php">Create Session</a>
                <a class="nav-link" href="my_sessions.php">My Sessions</a>
            </nav>

            <div class="nav-account">
                <a class="user-link" href="profile.php">
                    Hi, <?php echo htmlentities($_SESSION["name"]); ?>
                </a>
                <a class="btn btn-outline btn-small" href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main class="create-main">

        <div class="page-heading create-heading">
            <p class="eyebrow">HOST A SESSION</p>
            <h1>Share your skill</h1>
            <p>Create a peer-led skill-sharing session for Taylor's students.</p>
        </div>

        <?php if (isset($_SESSION["success_message"])): ?>
            <div class="status-message success-message">
                <?php echo htmlentities($_SESSION["success_message"]); ?>
            </div>
            <?php unset($_SESSION["success_message"]); ?>
        <?php endif; ?>

        <?php if ($message != ""): ?>
            <div class="status-message error-message">
                <?php echo htmlentities($message); ?>
            </div>
        <?php endif; ?>

        <form class="session-form-card" method="POST" action="create_session.php">

            <div class="form-field">
                <label for="title">Session Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="150"
                    placeholder="e.g. Canva Design Basics"
                    value="<?php echo isset($title) ? htmlentities($title) : ''; ?>"
                    required
                >
            </div>

            <div class="form-field">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Design" <?php echo (isset($category) && $category == "Design") ? "selected" : ""; ?>>Design</option>
                    <option value="Technology" <?php echo (isset($category) && $category == "Technology") ? "selected" : ""; ?>>Technology</option>
                    <option value="Career" <?php echo (isset($category) && $category == "Career") ? "selected" : ""; ?>>Career</option>
                    <option value="Creative" <?php echo (isset($category) && $category == "Creative") ? "selected" : ""; ?>>Creative</option>
                </select>
            </div>

            <div class="form-field">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Tell students what they will learn in this session."
                    required
                ><?php echo isset($description) ? htmlentities($description) : ''; ?></textarea>
            </div>

            <div class="form-grid two-columns">
                <div class="form-field">
                    <label for="session_date">Date</label>
                    <input
                        type="date"
                        id="session_date"
                        name="session_date"
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo isset($session_date) ? htmlentities($session_date) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="session_time">Time</label>
                    <input
                        type="time"
                        id="session_time"
                        name="session_time"
                        value="<?php echo isset($session_time) ? htmlentities($session_time) : ''; ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-grid two-columns">
                <div class="form-field">
                    <label for="location">Location</label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        maxlength="150"
                        placeholder="e.g. Student Hub"
                        value="<?php echo isset($location) ? htmlentities($location) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="capacity">Maximum Participants</label>
                    <input
                        type="number"
                        id="capacity"
                        name="capacity"
                        min="1"
                        max="200"
                        value="<?php echo isset($capacity) ? htmlentities($capacity) : '15'; ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="index.php">Cancel</a>
                <button class="btn btn-primary" type="submit">Create Session</button>
            </div>

        </form>

    </main>

</body>

</html>