<?php

session_start();

require_once "pdo.php";

// Only logged-in users can edit a session.
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
$message = "";
$allowedCategories = ["Design", "Technology", "Career", "Creative"];

// Load the session and make sure the current user is the creator.
$stmt = $pdo->prepare(
    "SELECT *
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

    } elseif (!in_array($category, $allowedCategories, true)) {
        $message = "Please select a valid category.";

    } elseif (!is_numeric($capacity) || (int)$capacity < 1) {
        $message = "Capacity must be at least 1.";

    } elseif (strtotime($session_date) < strtotime(date("Y-m-d"))) {
        $message = "Session date cannot be in the past.";

    } else {

        // Do not allow capacity to become smaller than the current number of participants.
        $countStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM registrations
             WHERE session_id = ?"
        );

        $countStmt->execute([$session_id]);
        $joinedCount = (int)$countStmt->fetchColumn();

        if ((int)$capacity < $joinedCount) {
            $message = "Capacity cannot be lower than the current number of participants (" . $joinedCount . ").";

        } else {

            $updateStmt = $pdo->prepare(
                "UPDATE skill_sessions
                 SET title = ?,
                     category = ?,
                     description = ?,
                     session_date = ?,
                     session_time = ?,
                     location = ?,
                     capacity = ?
                 WHERE session_id = ? AND creator_id = ?"
            );

            $updateStmt->execute([
                $title,
                $category,
                $description,
                $session_date,
                $session_time,
                $location,
                (int)$capacity,
                $session_id,
                $user_id
            ]);

            header("Location: session_details.php?id=" . $session_id . "&updated=1");
            exit;
        }
    }

    // Keep the submitted values in the form when validation fails.
    $session["title"] = $title;
    $session["category"] = $category;
    $session["description"] = $description;
    $session["session_date"] = $session_date;
    $session["session_time"] = $session_time;
    $session["location"] = $location;
    $session["capacity"] = $capacity;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Edit Session - Taylor's SkillSwap</title>
</head>

<body class="edit-page">

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

    <main class="edit-main">

        <a class="back-link" href="my_sessions.php">&larr; Back to My Sessions</a>

        <div class="page-heading edit-heading">
            <p class="eyebrow">EDIT SESSION</p>
            <h1>Update your session</h1>
            <p>Change the details of your skill-sharing session.</p>
        </div>

        <?php if ($message != ""): ?>
            <div class="status-message error-message">
                <?php echo htmlentities($message); ?>
            </div>
        <?php endif; ?>

        <form class="session-form-card" method="POST" action="edit_session.php">

            <input
                type="hidden"
                name="session_id"
                value="<?php echo (int)$session_id; ?>"
            >

            <div class="form-field">
                <label for="title">Session Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="150"
                    value="<?php echo htmlentities($session["title"]); ?>"
                    required
                >
            </div>

            <div class="form-field">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Design" <?php echo ($session["category"] == "Design") ? "selected" : ""; ?>>Design</option>
                    <option value="Technology" <?php echo ($session["category"] == "Technology") ? "selected" : ""; ?>>Technology</option>
                    <option value="Career" <?php echo ($session["category"] == "Career") ? "selected" : ""; ?>>Career</option>
                    <option value="Creative" <?php echo ($session["category"] == "Creative") ? "selected" : ""; ?>>Creative</option>
                </select>
            </div>

            <div class="form-field">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    required
                ><?php echo htmlentities($session["description"]); ?></textarea>
            </div>

            <div class="form-grid two-columns">
                <div class="form-field">
                    <label for="session_date">Date</label>
                    <input
                        type="date"
                        id="session_date"
                        name="session_date"
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlentities($session["session_date"]); ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="session_time">Time</label>
                    <input
                        type="time"
                        id="session_time"
                        name="session_time"
                        value="<?php echo htmlentities($session["session_time"]); ?>"
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
                        value="<?php echo htmlentities($session["location"]); ?>"
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
                        value="<?php echo (int)$session["capacity"]; ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="my_sessions.php">Cancel</a>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

        </form>

    </main>

</body>

</html>